<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // =====================================================================
        // VIEW 3: leave_balance_view
        // Shows each employee's leave usage and remaining balance per leave type
        // =====================================================================
        DB::statement("
            CREATE OR REPLACE VIEW leave_balance_view AS
            SELECT
                e.id AS employee_id,
                e.employee_id AS employee_code,
                CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
                d.name AS department_name,
                lt.id AS leave_type_id,
                lt.name AS leave_type,
                lt.max_days_per_year,
                COALESCE(SUM(
                    CASE WHEN l.status = 'approved' AND YEAR(l.start_date) = YEAR(CURDATE())
                    THEN l.total_days ELSE 0 END
                ), 0) AS days_used,
                lt.max_days_per_year - COALESCE(SUM(
                    CASE WHEN l.status = 'approved' AND YEAR(l.start_date) = YEAR(CURDATE())
                    THEN l.total_days ELSE 0 END
                ), 0) AS days_remaining,
                COALESCE(SUM(
                    CASE WHEN l.status = 'pending' AND YEAR(l.start_date) = YEAR(CURDATE())
                    THEN l.total_days ELSE 0 END
                ), 0) AS days_pending
            FROM employees e
            CROSS JOIN leave_types lt
            LEFT JOIN leaves l ON l.employee_id = e.id AND l.leave_type_id = lt.id
            LEFT JOIN departments d ON e.department_id = d.id
            WHERE e.archived_at IS NULL
              AND e.status = 'active'
            GROUP BY e.id, e.employee_id, e.first_name, e.last_name, d.name,
                     lt.id, lt.name, lt.max_days_per_year
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS leave_balance_view");
    }
};
