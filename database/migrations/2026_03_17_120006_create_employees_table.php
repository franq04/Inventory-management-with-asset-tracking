<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('employees')) {
            return;
        }

        DB::statement('CREATE TABLE `employees` (
  `employee_id` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `suffix` varchar(10) DEFAULT NULL,
  `date_of_birth` date NOT NULL,
  `marital_status` enum(\'single\',\'married\',\'widowed\',\'divorced\',\'separated\') DEFAULT \'single\',
  `gender` enum(\'male\',\'female\',\'other\') NOT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `account_id` bigint(20) UNSIGNED DEFAULT NULL,
    `profile_img` varchar(255) NOT NULL,
    `signature` longblob DEFAULT NULL,
  `position_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;');
        DB::statement('ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD KEY `fk_employee_account` (`account_id`),
  ADD KEY `fk_employees_position` (`position_id`),
  ADD KEY `fk_employees_section` (`section_id`);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
