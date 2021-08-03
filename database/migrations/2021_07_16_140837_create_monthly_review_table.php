<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateMonthlyReviewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE OR REPLACE VIEW monthly_review AS
                select 
                date_format(`approval_date`,'%m-%Y') AS `period`, 
                year(`approval_date`) AS year,
                month(`approval_date`) AS month_int,
                monthname(`approval_date`) AS month, 
                count(`id`) AS total_requests, sum(`amount`) AS total_income from `parking_requests` where `approval_date` is not null 
                group by period ,month_int, month, year order by year desc");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW monthly_review');
    }
}
