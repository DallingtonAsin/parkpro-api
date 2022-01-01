<?php

namespace Database\Factories;

use App\Models\ParkingRequest;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\ParkingFee;

class ParkingRequestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ParkingRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        $approved_at = $rejected_at = null;

        $customer_id = $this->faker->randomElement([1,2,3,4,5]);
        $parking_area_id = $this->faker->randomElement([1,2,3]);
        $vehicle_cat_id = $this->faker->randomElement([1,2,3]);
        $start_time = $this->faker->time($format= 'H:i:s', $max='now');
        $end_time = $this->faker->time($format= 'H:i:s', $max='now');
        $parking_hours = round($this->differenceInHours($start_time, $end_time), 1);
        $fee_per_hour = ParkingFee::where('parking_area_id', $parking_area_id)->where('vehicle_cat_id', $vehicle_cat_id)->value('fee_per_hour');
        $amount = $fee_per_hour*$parking_hours;
        $status = $this->faker->randomElement(["PENDING", "REJECTED", "APPROVED"]);
        $request_date = $this->getRandomDate('01-01-2022 00:00:00', '31-12-2022 00:00:00');
        if($status == 'APPROVED'){
            $approved_at = $request_date;   
        }
        if($status == 'REJECTED'){
            $rejected_at = $request_date;
        }

        return [
                'order_no' => Str::random(10),
                'customer_id' => $customer_id,
                'telephone_no' => $this->faker->e164phoneNumber,
                'parking_area_id' => $this->faker->randomElement([1,2,3]),
                'vehicle_details' => Str::random(6),
                'vehicle_cat_id' => $vehicle_cat_id,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'parking_hours' => $parking_hours,
                'amount' => $amount,
                'status' => $status,
                'request_date' => $request_date,
                'approval_date' => $approved_at,
                'reject_date' => $rejected_at,
        ];
    }

    private function differenceInHours($startdate, $enddate){
        $starttimestamp = strtotime($startdate);
        $endtimestamp = strtotime($enddate);
        $difference = abs($endtimestamp - $starttimestamp)/3600;
        return $difference;
    }

    private function getRandomDate($start_date, $end_date){
        $min = strtotime($start_date);
        $max = strtotime($end_date);
        $val = rand($min, $max);
        return date('Y-m-d H:i:s', $val);
    }


}
