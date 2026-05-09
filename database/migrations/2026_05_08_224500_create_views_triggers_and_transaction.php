<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // =====================================================================
        // VIEW 1: employee_directory_view
        // Shows a comprehensive employee directory with department info
        // =====================================================================
        DB::statement("
            CREATE OR REPLACE VIEW employee_directory_view AS
            SELECT
                e.id AS employee_id,
                e.employee_id AS employee_code,
                CONCAT(e.first_name, ' ', e.last_name) AS full_name,
                e.email,
                e.phone,
                e.position,
                e.employment_type,
                e.status AS employee_status,
                e.hire_date,
                e.salary,
                d.name AS department_name,
                d.code AS department_code,
                TIMESTAMPDIFF(YEAR, e.hire_date, CURDATE()) AS years_of_service,
                e.created_at,
                e.updated_at
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.id
            WHERE e.archived_at IS NULL
        ");

        // =====================================================================
        // VIEW 2: payroll_summary_view
        // Shows payroll summary per employee per period with deduction breakdown
        // =====================================================================
        DB::statement("
            CREATE OR REPLACE VIEW payroll_summary_view AS
            SELECT
                p.id AS payroll_id,
                e.employee_id AS employee_code,
                CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
                d.name AS department_name,
                p.period_month,
                p.year,
                p.month,
                p.basic_salary,
                p.overtime_pay,
                p.allowances,
                p.gross_salary,
                (p.sss_deduction + p.philhealth_deduction + p.pagibig_deduction) AS government_deductions,
                p.tax_deduction,
                p.other_deductions,
                p.total_deductions,
                p.net_salary,
                p.days_worked,
                p.days_absent,
                p.status AS payroll_status,
                p.pay_date,
                p.created_at
            FROM payrolls p
            INNER JOIN employees e ON p.employee_id = e.id
            LEFT JOIN departments d ON e.department_id = d.id
            WHERE p.archived_at IS NULL
        ");

        // =====================================================================
        // TRIGGER 1: before_leave_insert
        // Automatically calculates total_days before a leave record is inserted
        // =====================================================================
        DB::unprepared("
            CREATE TRIGGER before_leave_insert
            BEFORE INSERT ON leaves
            FOR EACH ROW
            BEGIN
                SET NEW.total_days = DATEDIFF(NEW.end_date, NEW.start_date) + 1;
            END
        ");

        // =====================================================================
        // TRIGGER 2: after_attendance_insert
        // Automatically calculates hours_worked when attendance is inserted
        // with both time_in and time_out
        // =====================================================================
        DB::unprepared("
            CREATE TRIGGER after_attendance_insert
            BEFORE INSERT ON attendances
            FOR EACH ROW
            BEGIN
                IF NEW.time_in IS NOT NULL AND NEW.time_out IS NOT NULL THEN
                    SET NEW.hours_worked = ROUND(
                        TIMESTAMPDIFF(MINUTE, NEW.time_in, NEW.time_out) / 60.0, 2
                    );
                END IF;
            END
        ");

        // =====================================================================
        // STORED PROCEDURE (TRANSACTION): sp_process_monthly_payroll
        // Processes payroll for all active employees for a given month/year
        // Uses a TRANSACTION to ensure all-or-nothing payroll processing
        // =====================================================================
        DB::unprepared("
            CREATE PROCEDURE sp_process_monthly_payroll(
                IN p_year INT,
                IN p_month INT,
                IN p_processed_by BIGINT
            )
            BEGIN
                DECLARE v_employee_id BIGINT;
                DECLARE v_salary DECIMAL(12,2);
                DECLARE v_days_worked INT;
                DECLARE v_days_absent INT;
                DECLARE v_period_month VARCHAR(7);
                DECLARE v_daily_rate DECIMAL(12,2);
                DECLARE v_basic_pay DECIMAL(12,2);
                DECLARE v_sss DECIMAL(12,2);
                DECLARE v_philhealth DECIMAL(12,2);
                DECLARE v_pagibig DECIMAL(12,2);
                DECLARE v_total_deductions DECIMAL(12,2);
                DECLARE v_net_salary DECIMAL(12,2);
                DECLARE v_gross_salary DECIMAL(12,2);
                DECLARE v_days_in_month INT;
                DECLARE done INT DEFAULT FALSE;

                DECLARE emp_cursor CURSOR FOR
                    SELECT e.id, e.salary
                    FROM employees e
                    WHERE e.status = 'active'
                      AND e.archived_at IS NULL
                      AND NOT EXISTS (
                          SELECT 1 FROM payrolls p
                          WHERE p.employee_id = e.id
                            AND p.year = p_year
                            AND p.month = p_month
                      );

                DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

                -- Format period_month as YYYY-MM
                SET v_period_month = CONCAT(p_year, '-', LPAD(p_month, 2, '0'));
                SET v_days_in_month = DAY(LAST_DAY(CONCAT(p_year, '-', LPAD(p_month, 2, '0'), '-01')));

                -- START TRANSACTION
                START TRANSACTION;

                OPEN emp_cursor;

                read_loop: LOOP
                    FETCH emp_cursor INTO v_employee_id, v_salary;
                    IF done THEN
                        LEAVE read_loop;
                    END IF;

                    -- Count attendance days
                    SELECT COUNT(*) INTO v_days_worked
                    FROM attendances
                    WHERE employee_id = v_employee_id
                      AND YEAR(date) = p_year
                      AND MONTH(date) = p_month
                      AND status IN ('present', 'late', 'half_day');

                    SET v_days_absent = v_days_in_month - v_days_worked;
                    IF v_days_absent < 0 THEN
                        SET v_days_absent = 0;
                    END IF;

                    -- Calculate pay
                    SET v_daily_rate = v_salary / v_days_in_month;
                    SET v_basic_pay = ROUND(v_daily_rate * v_days_worked, 2);
                    SET v_gross_salary = v_basic_pay;

                    -- Government deductions (simplified rates)
                    SET v_sss = ROUND(v_gross_salary * 0.045, 2);        -- 4.5% SSS
                    SET v_philhealth = ROUND(v_gross_salary * 0.025, 2); -- 2.5% PhilHealth
                    SET v_pagibig = 100.00;                              -- Fixed Pag-IBIG

                    SET v_total_deductions = v_sss + v_philhealth + v_pagibig;
                    SET v_net_salary = v_gross_salary - v_total_deductions;

                    -- Insert payroll record
                    INSERT INTO payrolls (
                        employee_id, period_month, year, month,
                        basic_salary, overtime_pay, allowances, gross_salary,
                        tax_deduction, sss_deduction, philhealth_deduction,
                        pagibig_deduction, other_deductions, total_deductions,
                        net_salary, days_worked, days_absent,
                        status, pay_date, processed_by,
                        created_at, updated_at
                    ) VALUES (
                        v_employee_id, v_period_month, p_year, p_month,
                        v_basic_pay, 0, 0, v_gross_salary,
                        0, v_sss, v_philhealth,
                        v_pagibig, 0, v_total_deductions,
                        v_net_salary, v_days_worked, v_days_absent,
                        'processed', CURDATE(), p_processed_by,
                        NOW(), NOW()
                    );

                END LOOP;

                CLOSE emp_cursor;

                -- COMMIT TRANSACTION
                COMMIT;
            END
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS employee_directory_view");
        DB::statement("DROP VIEW IF EXISTS payroll_summary_view");
        DB::unprepared("DROP TRIGGER IF EXISTS before_leave_insert");
        DB::unprepared("DROP TRIGGER IF EXISTS after_attendance_insert");
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_process_monthly_payroll");
    }
};
