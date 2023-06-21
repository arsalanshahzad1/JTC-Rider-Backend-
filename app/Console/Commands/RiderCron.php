<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RiderCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rider:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function distance($lat1, $lon1, $lat2, $lon2) {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        return $miles;
    }
    
    public function handle()
    {
        $endTime = now()->addMinutes(2);

        while (now() < $endTime) {
            // Perform your desired task inside the loop

            // Sleep for a certain duration (e.g., 10 seconds)
            sleep(10);
        }

        // Action to perform after the 2-minute duration
    }
}
