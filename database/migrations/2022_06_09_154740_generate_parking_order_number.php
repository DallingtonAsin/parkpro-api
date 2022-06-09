<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


class GenerateParkingOrderNumber extends Migration
{
    /**
    * Run the migrations.
    *
    * @return void
    */
    public function up()
    {
        DB::unprepared('
        CREATE FUNCTION `GenerateParkingRequestOrderNo`() RETURNS varchar(255) CHARSET utf8mb3
        NO SQL
        BEGIN
        
        DECLARE ORDER_ID VARCHAR(255);
        DECLARE total INT;
        
        SET total = (SELECT count(*) FROM parking_requests);
        IF total > 0 THEN 
        
        SET ORDER_ID = (SELECT FLOOR(10000 + RAND() * 999999999999) AS order_no
        FROM parking_requests
        WHERE "order_no" NOT IN (SELECT a.order_no FROM parking_requests AS a) limit 1);
        
        ELSE 
        SET ORDER_ID = FLOOR(RAND() * 999999999999);
        END IF;
        
        RETURN ORDER_ID;
        
        END
        ');
    }
    
    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        //
    }
}
