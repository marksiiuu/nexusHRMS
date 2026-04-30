<?php
namespace Database\Seeders;
use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use App\Models\LeaveType;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Payroll;
use App\Models\BiometricLog;
use App\Models\JobPosting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $leaveTypes = [
            ['name'=>'Sick Leave','code'=>'SL','max_days_per_year'=>15,'is_paid'=>true,'description'=>'Medical illness'],
            ['name'=>'Vacation Leave','code'=>'VL','max_days_per_year'=>15,'is_paid'=>true,'description'=>'Annual vacation'],
            ['name'=>'Emergency Leave','code'=>'EL','max_days_per_year'=>5,'is_paid'=>true,'description'=>'Emergencies'],
            ['name'=>'Maternity Leave','code'=>'ML','max_days_per_year'=>105,'is_paid'=>true,'description'=>'Maternity'],
            ['name'=>'Paternity Leave','code'=>'PL','max_days_per_year'=>7,'is_paid'=>true,'description'=>'Paternity'],
            ['name'=>'Unpaid Leave','code'=>'UPL','max_days_per_year'=>30,'is_paid'=>false,'description'=>'Without pay'],
        ];
        foreach ($leaveTypes as $lt) LeaveType::create($lt);

        // Users with default passwords
        $admin   = User::create(['name'=>'System Administrator','email'=>'admin@hrms.local','password'=>Hash::make('admin1234'),'role'=>'admin','is_active'=>true,'default_password'=>'admin1234']);
        $hrUser  = User::create(['name'=>'Maria Santos','email'=>'hr@hrms.local','password'=>Hash::make('maria1234'),'role'=>'hr_manager','is_active'=>true,'default_password'=>'maria1234']);
        $payUser = User::create(['name'=>'Jose Reyes','email'=>'payroll@hrms.local','password'=>Hash::make('jose1234'),'role'=>'payroll_officer','is_active'=>true,'default_password'=>'jose1234']);
        $recUser = User::create(['name'=>'Ana Recruiter','email'=>'recruiter@hrms.local','password'=>Hash::make('ana1234'),'role'=>'job_recruiter','is_active'=>true,'default_password'=>'ana1234']);

        $deptData = [
            ['name'=>'Human Resources','code'=>'HR','description'=>'People management'],
            ['name'=>'Engineering','code'=>'ENG','description'=>'Software development'],
            ['name'=>'Finance','code'=>'FIN','description'=>'Finance and accounts'],
            ['name'=>'Sales','code'=>'SAL','description'=>'Sales and marketing'],
            ['name'=>'Operations','code'=>'OPS','description'=>'Business operations'],
            ['name'=>'Customer Support','code'=>'CS','description'=>'Customer service'],
        ];
        $departments = [];
        foreach ($deptData as $d) $departments[] = Department::create($d);

        $hrEmployee  = Employee::create(['user_id'=>$hrUser->id,'department_id'=>$departments[0]->id,'employee_id'=>'EMP-00001','biometric_id'=>'BIO-001','first_name'=>'Maria','last_name'=>'Santos','email'=>'hr@hrms.local','phone'=>'+63-917-111-0001','position'=>'HR Manager','employment_type'=>'full_time','status'=>'active','hire_date'=>'2020-01-15','salary'=>65000,'gender'=>'female']);
        $departments[0]->update(['manager_id'=>$hrEmployee->id]);
        $payEmployee = Employee::create(['user_id'=>$payUser->id,'department_id'=>$departments[2]->id,'employee_id'=>'EMP-00002','biometric_id'=>'BIO-002','first_name'=>'Jose','last_name'=>'Reyes','email'=>'payroll@hrms.local','phone'=>'+63-917-111-0002','position'=>'Payroll Officer','employment_type'=>'full_time','status'=>'active','hire_date'=>'2021-03-01','salary'=>45000,'gender'=>'male']);

        $samples = [
            ['Ana','Cruz','EMP-00003','BIO-003','ana.cruz@company.com','+63-917-222-0001','Software Engineer','full_time',1,'2021-06-01',55000,'female'],
            ['Carlo','Lim','EMP-00004','BIO-004','carlo.lim@company.com','+63-917-222-0002','Senior Developer','full_time',1,'2020-09-15',75000,'male'],
            ['Diana','Tan','EMP-00005','BIO-005','diana.tan@company.com','+63-917-222-0003','QA Engineer','full_time',1,'2022-02-01',42000,'female'],
            ['Edward','Go','EMP-00006','BIO-006','edward.go@company.com','+63-917-222-0004','Sales Executive','full_time',3,'2021-11-01',35000,'male'],
            ['Fiona','Uy','EMP-00007','BIO-007','fiona.uy@company.com','+63-917-222-0005','Marketing Specialist','full_time',3,'2022-05-15',38000,'female'],
            ['George','Sy','EMP-00008','BIO-008','george.sy@company.com','+63-917-222-0006','Finance Analyst','full_time',2,'2020-07-01',48000,'male'],
            ['Helen','Ko','EMP-00009','BIO-009','helen.ko@company.com','+63-917-222-0007','Operations Lead','full_time',4,'2019-03-01',58000,'female'],
            ['Ivan','Ng','EMP-00010','BIO-010','ivan.ng@company.com','+63-917-222-0008','Support Specialist','part_time',5,'2023-01-10',25000,'male'],
            ['Julia','Ong','EMP-00011','BIO-011','julia.ong@company.com','+63-917-222-0009','Frontend Developer','full_time',1,'2022-09-01',50000,'female'],
            ['Kevin','Dee','EMP-00012','BIO-012','kevin.dee@company.com','+63-917-222-0010','DevOps Engineer','full_time',1,'2021-04-15',65000,'male'],
        ];

        $created = [];
        foreach ($samples as [$fn,$ln,$eid,$bio,$email,$phone,$pos,$empType,$deptIdx,$hireDate,$salary,$gender]) {
            $defPw = strtolower($fn).rand(1000,9999);
            $u = User::create(['name'=>"$fn $ln",'email'=>$email,'password'=>Hash::make($defPw),'role'=>'employee','is_active'=>true,'default_password'=>$defPw]);
            $created[] = Employee::create(['user_id'=>$u->id,'department_id'=>$departments[$deptIdx]->id,'employee_id'=>$eid,'biometric_id'=>$bio,'first_name'=>$fn,'last_name'=>$ln,'email'=>$email,'phone'=>$phone,'position'=>$pos,'employment_type'=>$empType,'status'=>'active','hire_date'=>$hireDate,'salary'=>$salary,'gender'=>$gender]);
        }

        $departments[1]->update(['manager_id'=>$created[1]->id]);
        $departments[3]->update(['manager_id'=>$created[3]->id]);
        $departments[4]->update(['manager_id'=>$created[6]->id]);

        // Attendance (last 14 working days) — PH time
        $allEmployees = Employee::all();
        $statuses = ['present','present','present','present','late','absent'];
        for ($d=13;$d>=0;$d--) {
            $date = Carbon::today('Asia/Manila')->subDays($d);
            if ($date->isWeekend()) continue;
            foreach ($allEmployees as $emp) {
                $st=$statuses[array_rand($statuses)];
                $ti=$st==='absent'?null:($st==='late'?'09:'.rand(15,45):'08:0'.rand(0,9));
                $to=$st==='absent'?null:'17:'.rand(0,59);
                $hrs=($ti&&$to)?round((strtotime($to)-strtotime($ti))/3600,2):null;
                try {
                    Attendance::create(['employee_id'=>$emp->id,'date'=>$date->format('Y-m-d'),'time_in'=>$ti,'time_out'=>$to,'hours_worked'=>$hrs,'status'=>$st]);
                } catch(\Exception $e){}
            }
        }

        // Biometric logs (today)
        $today = Carbon::today('Asia/Manila');
        foreach ($allEmployees->take(5) as $emp) {
            BiometricLog::create(['employee_id'=>$emp->id,'biometric_id'=>$emp->biometric_id,'log_time'=>$today->copy()->setHour(8)->setMinute(rand(0,15)),'log_type'=>'time_in','device_id'=>'DEVICE-01','processed'=>false]);
        }

        // Leave types & requests
        $ltIds = LeaveType::pluck('id')->toArray();
        $leaveData=[
            [$created[0]->id,$ltIds[0],'2025-07-01','2025-07-03',3,'Fever and flu','approved'],
            [$created[1]->id,$ltIds[1],'2025-07-10','2025-07-14',5,'Family vacation','approved'],
            [$created[2]->id,$ltIds[2],'2025-07-20','2025-07-20',1,'Personal matters','pending'],
            [$created[3]->id,$ltIds[0],'2025-08-05','2025-08-07',3,'Medical check','pending'],
            [$created[4]->id,$ltIds[1],'2025-08-15','2025-08-19',5,'Holiday trip','rejected'],
        ];
        foreach ($leaveData as [$empId,$ltId,$start,$end,$days,$reason,$status]) {
            Leave::create(['employee_id'=>$empId,'leave_type_id'=>$ltId,'start_date'=>$start,'end_date'=>$end,'total_days'=>$days,'reason'=>$reason,'status'=>$status,'approved_by'=>$status!=='pending'?$admin->id:null,'approved_at'=>$status==='approved'?now():null,'rejection_reason'=>$status==='rejected'?'Team coverage needed':null]);
        }

        // Payroll (semi-monthly, last 2 months)
        for ($m=1;$m>=0;$m--) {
            $date=Carbon::today('Asia/Manila')->subMonths($m);
            foreach ($allEmployees->take(6) as $emp) {
                foreach (['first','second'] as $periodType) {
                    $basic=$emp->salary/2;$allow=1500;$gross=$basic+$allow;
                    $tax=$gross*0.10;$sss=562.5;$phil=$gross*0.025;$pag=100;$totalDed=$tax+$sss+$phil+$pag;
                    try {
                        Payroll::create(['employee_id'=>$emp->id,'period_month'=>$date->format('Y-m'),'year'=>$date->year,'month'=>$date->month,'pay_period'=>'semi_monthly','pay_period_type'=>$periodType,'basic_salary'=>$basic,'overtime_pay'=>0,'allowances'=>$allow,'gross_salary'=>$gross,'tax_deduction'=>$tax,'sss_deduction'=>$sss,'philhealth_deduction'=>$phil,'pagibig_deduction'=>$pag,'other_deductions'=>0,'total_deductions'=>$totalDed,'net_salary'=>$gross-$totalDed,'days_worked'=>11,'days_absent'=>rand(0,1),'status'=>$m>0?'paid':'processed','pay_date'=>$m>0?$date->endOfMonth()->format('Y-m-d'):null,'processed_by'=>$payUser->id]);
                    } catch(\Exception $e){}
                }
            }
        }

        // Job postings (recruiter)
        JobPosting::create(['title'=>'Junior Software Developer','department_id'=>$departments[1]->id,'description'=>'We are looking for a Junior Software Developer to join our Engineering team. Must have at least 1 year experience in PHP/Laravel.','requirements'=>'Bachelor\'s degree in Computer Science or related field. Proficient in PHP, Laravel, MySQL, JavaScript.','employment_type'=>'full_time','slots'=>2,'salary_min'=>35000,'salary_max'=>50000,'deadline'=>Carbon::today('Asia/Manila')->addMonth()->format('Y-m-d'),'status'=>'open','created_by'=>$recUser->id]);
        JobPosting::create(['title'=>'HR Assistant','department_id'=>$departments[0]->id,'description'=>'Assist the HR Manager with day-to-day HR operations including recruitment, employee onboarding, and attendance management.','requirements'=>'Bachelor\'s degree in Psychology, HRM, or related field.','employment_type'=>'full_time','slots'=>1,'salary_min'=>20000,'salary_max'=>28000,'deadline'=>Carbon::today('Asia/Manila')->addWeeks(3)->format('Y-m-d'),'status'=>'open','created_by'=>$recUser->id]);
        JobPosting::create(['title'=>'Accounting Intern','department_id'=>$departments[2]->id,'description'=>'Accounting intern to assist with basic bookkeeping and financial records.','employment_type'=>'intern','slots'=>2,'salary_min'=>8000,'salary_max'=>10000,'status'=>'draft','created_by'=>$recUser->id]);

        $this->command->info('✅ Viernes HRMS seeded!');
        $this->command->info('Credentials (email / default password):');
        $this->command->info('  Admin:     admin@hrms.local      / admin1234');
        $this->command->info('  HR:        hr@hrms.local         / maria1234');
        $this->command->info('  Payroll:   payroll@hrms.local    / jose1234');
        $this->command->info('  Recruiter: recruiter@hrms.local  / ana1234');
        $this->command->info('  Employees: firstname.lastname@company.com / auto-generated');
    }
}
