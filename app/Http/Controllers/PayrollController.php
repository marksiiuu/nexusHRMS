<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $showArchived = $request->has('archived');
        $query        = Payroll::query()->with('employee.department');

        if ($showArchived) {
            $query->whereNotNull('archived_at');
        } else {
            $query->active();
        }

        if ($request->employee_id) $query->where('employee_id',$request->employee_id);
        if ($request->year)        $query->where('year',$request->year);
        if ($request->month)       $query->where('month',$request->month);
        if ($request->status)      $query->where('status',$request->status);
        if ($request->pay_period)  $query->where('pay_period',$request->pay_period);

        $payrolls      = $query->latest()->paginate(20)->withQueryString();
        $employees     = Employee::whereNull('archived_at')->where('status','active')->get();
        $years         = range(date('Y'), date('Y')-3);
        $archivedCount = Payroll::whereNotNull('archived_at')->count();

        return view('payroll.index', compact('payrolls','employees','years', 'showArchived', 'archivedCount'));
    }

    public function create()
    {
        $employees = Employee::whereNull('archived_at')->where('status','active')->with('department')->get();
        return view('payroll.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id'          => 'required|exists:employees,id',
            'year'                 => 'required|integer|min:2000|max:2099',
            'month'                => 'required|integer|min:1|max:12',
            'pay_period'           => 'required|in:monthly,semi_monthly',
            'pay_period_type'      => 'required|in:first,second,full',
            'basic_salary'         => 'required|numeric|min:0',
            'overtime_pay'         => 'nullable|numeric|min:0',
            'allowances'           => 'nullable|numeric|min:0',
            'tax_deduction'        => 'nullable|numeric|min:0',
            'sss_deduction'        => 'nullable|numeric|min:0',
            'philhealth_deduction' => 'nullable|numeric|min:0',
            'pagibig_deduction'    => 'nullable|numeric|min:0',
            'other_deductions'     => 'nullable|numeric|min:0',
            'pay_date'             => 'nullable|date',
            'notes'                => 'nullable|string|max:1000',
        ]);

        $existing = Payroll::where('employee_id',$validated['employee_id'])
            ->where('year',$validated['year'])->where('month',$validated['month'])
            ->where('pay_period_type',$validated['pay_period_type'])->first();
        if ($existing) return back()->with('warning','Payroll already exists for this employee and period. You can edit the existing draft instead.')->withInput();

        $ot   = $validated['overtime_pay']??0;
        $allow= $validated['allowances']??0;
        $gross= $validated['basic_salary']+$ot+$allow;
        $tax  = $validated['tax_deduction']??0;
        $sss  = $validated['sss_deduction']??0;
        $phil = $validated['philhealth_deduction']??0;
        $pag  = $validated['pagibig_deduction']??0;
        $oth  = $validated['other_deductions']??0;
        $totalDed=$tax+$sss+$phil+$pag+$oth;
        $net  =$gross-$totalDed;

        // Auto count attendance
        $periodStart = $validated['pay_period_type']==='first' ? 1 : 16;
        $periodEnd   = $validated['pay_period_type']==='first' ? 15 :
                       cal_days_in_month(CAL_GREGORIAN, $validated['month'], $validated['year']);

        $daysWorked = Attendance::where('employee_id',$validated['employee_id'])
            ->whereYear('date',$validated['year'])->whereMonth('date',$validated['month'])
            ->whereIn('status',['present','late'])
            ->when($validated['pay_period']!=='monthly', fn($q)=>
                $q->whereDay('date','>=',$periodStart)->whereDay('date','<=',$periodEnd))
            ->count();
        $daysAbsent = Attendance::where('employee_id',$validated['employee_id'])
            ->whereYear('date',$validated['year'])->whereMonth('date',$validated['month'])
            ->where('status','absent')
            ->when($validated['pay_period']!=='monthly', fn($q)=>
                $q->whereDay('date','>=',$periodStart)->whereDay('date','<=',$periodEnd))
            ->count();

        Payroll::create(array_merge($validated,[
            'period_month'         => $validated['year'].'-'.str_pad($validated['month'],2,'0',STR_PAD_LEFT),
            'overtime_pay'         => $ot,'allowances'=>$allow,'gross_salary'=>$gross,
            'tax_deduction'        => $tax,'sss_deduction'=>$sss,
            'philhealth_deduction' => $phil,'pagibig_deduction'=>$pag,
            'other_deductions'     => $oth,'total_deductions'=>$totalDed,'net_salary'=>$net,
            'days_worked'          => $daysWorked,'days_absent'=>$daysAbsent,
            'status'               => $request->has('immediate_release') ? 'paid' : 'draft',
            'pay_date'             => $request->has('immediate_release') ? now() : ($validated['pay_date'] ?? null),
            'processed_by'         => auth()->id(),
        ]));

        return redirect()->route('payroll.index')->with('success','Payroll record created!');
    }

    public function show(Payroll $payroll)
    {
        if (!auth()->user()->hasPayrollAccess() && auth()->user()->employee?->id !== $payroll->employee_id) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to view this payslip.');
        }

        $payroll->load(['employee.department','processor']);
        return view('payroll.show', compact('payroll'));
    }

    public function edit(Payroll $payroll)
    {
        if ($payroll->status==='paid') return back()->with('error','Cannot edit a paid payroll!');
        $employees = Employee::whereNull('archived_at')->where('status','active')->get();
        return view('payroll.edit', compact('payroll','employees'));
    }

    public function update(Request $request, Payroll $payroll)
    {
        if ($payroll->status==='paid') return back()->with('error','Cannot edit a paid payroll!');
        $validated = $request->validate([
            'basic_salary'         => 'required|numeric|min:0',
            'overtime_pay'         => 'nullable|numeric|min:0',
            'allowances'           => 'nullable|numeric|min:0',
            'tax_deduction'        => 'nullable|numeric|min:0',
            'sss_deduction'        => 'nullable|numeric|min:0',
            'philhealth_deduction' => 'nullable|numeric|min:0',
            'pagibig_deduction'    => 'nullable|numeric|min:0',
            'other_deductions'     => 'nullable|numeric|min:0',
            'pay_date'             => 'nullable|date',
            'notes'                => 'nullable|string|max:1000',
            'status'               => 'required|in:draft,processed,paid',
        ]);
        $gross   = $validated['basic_salary']+($validated['overtime_pay']??0)+($validated['allowances']??0);
        $totalDed= ($validated['tax_deduction']??0)+($validated['sss_deduction']??0)
                  +($validated['philhealth_deduction']??0)+($validated['pagibig_deduction']??0)
                  +($validated['other_deductions']??0);
        $payroll->update(array_merge($validated,['gross_salary'=>$gross,'total_deductions'=>$totalDed,'net_salary'=>$gross-$totalDed]));
        return redirect()->route('payroll.index')->with('success','Payroll updated!');
    }

    public function destroy(Payroll $payroll)
    {
        if ($payroll->status==='paid') return back()->with('error','Cannot archive a paid payroll!');
        $payroll->update(['archived_at' => now()]);
        return redirect()->route('payroll.index')->with('success','Payroll archived!');
    }

    public function myPayroll(Request $request)
    {
        $employee = auth()->user()->employee;
        if (!$employee) return redirect()->route('dashboard')->with('error','No employee profile linked.');
        $payrolls = Payroll::active()->where('employee_id',$employee->id)
            ->when($request->year, fn($q)=>$q->where('year',$request->year))
            ->latest()->paginate(12)->withQueryString();
        $years = range(date('Y'), date('Y')-3);
        return view('payroll.my', compact('payrolls','years'));
    }

    // Bulk generate payroll for all active employees for a period
    public function generateBulk(Request $request)
    {
        $request->validate([
            'year'            => 'required|integer',
            'month'           => 'required|integer|min:1|max:12',
            'pay_period'      => 'required|in:monthly,semi_monthly',
            'pay_period_type' => 'required|in:first,second,full',
        ]);

        $existingCount = Payroll::where('year', $request->year)
            ->where('month', $request->month)
            ->where('pay_period_type', $request->pay_period_type)
            ->active()
            ->count();

        if ($existingCount > 0 && !$request->has('force')) {
            return redirect()->back()
                ->with('warning', "Payroll records already exist for this period ($existingCount records found). Do you want to generate for remaining employees?")
                ->withInput();
        }

        $employees = Employee::whereNull('archived_at')->where('status','active')->get();
        $created = 0; $skipped = 0;

        foreach ($employees as $emp) {
            $exists = Payroll::where('employee_id',$emp->id)
                ->where('year',$request->year)->where('month',$request->month)
                ->where('pay_period_type',$request->pay_period_type)->exists();
            if ($exists) { $skipped++; continue; }

            $basic = $emp->salary;
            if ($request->pay_period === 'semi_monthly') $basic = $basic / 2;

            $allow   = 3000;
            $gross   = $basic + $allow;
            $tax     = $gross * 0.10;
            $sss     = 1125;
            $phil    = $gross * 0.025;
            $pag     = 200;
            $totalDed= $tax+$sss+$phil+$pag;

            $daysWorked = Attendance::where('employee_id',$emp->id)
                ->whereYear('date',$request->year)->whereMonth('date',$request->month)
                ->whereIn('status',['present','late'])->count();
            $daysAbsent = Attendance::where('employee_id',$emp->id)
                ->whereYear('date',$request->year)->whereMonth('date',$request->month)
                ->where('status','absent')->count();

            Payroll::create([
                'employee_id'=>$emp->id,
                'period_month'=>$request->year.'-'.str_pad($request->month,2,'0',STR_PAD_LEFT),
                'year'=>$request->year,'month'=>$request->month,
                'pay_period'=>$request->pay_period,'pay_period_type'=>$request->pay_period_type,
                'basic_salary'=>$basic,'overtime_pay'=>0,'allowances'=>$allow,
                'gross_salary'=>$gross,'tax_deduction'=>$tax,'sss_deduction'=>$sss,
                'philhealth_deduction'=>$phil,'pagibig_deduction'=>$pag,
                'other_deductions'=>0,'total_deductions'=>$totalDed,'net_salary'=>$gross-$totalDed,
                'days_worked'=>$daysWorked,'days_absent'=>$daysAbsent,
                'status'=>'draft','processed_by'=>auth()->id(),
            ]);
            $created++;
        }
        return redirect()->route('payroll.index')->with('success',"Bulk payroll: {$created} created, {$skipped} already existed.");
    }

    public function checkBulk(Request $request)
    {
        $request->validate([
            'year'            => 'required|integer',
            'month'           => 'required|integer',
            'pay_period_type' => 'required|string',
        ]);

        $count = Payroll::where('year', $request->year)
            ->where('month', $request->month)
            ->where('pay_period_type', $request->pay_period_type)
            ->active()
            ->count();

        $processedCount = Payroll::where('year', $request->year)
            ->where('month', $request->month)
            ->where('pay_period_type', $request->pay_period_type)
            ->whereIn('status', ['processed', 'paid'])
            ->active()
            ->count();

        return response()->json([
            'exists' => $count > 0,
            'count' => $count,
            'processed' => $processedCount > 0,
            'processed_count' => $processedCount
        ]);
    }
}
