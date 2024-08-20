<?php

namespace App\Console\Commands;

use App\Http\Controllers\OltController;
use App\Models\OLT;
use Illuminate\Console\Command;

class OLTScheduleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'olt:checkup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load OLT Data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $olt_class = new OltController();
            // $olts = OLT::whereNotNull('olt_ip')->get();
            $olts = OLT::select('id', 'mac', 'name', 'type')->get();
            foreach ($olts as $key => $olt_item) {
                $olt_class->oltCallApi($olt_item->id, $olt_item);
                echo "$olt_item->name \n";
            }
        } catch (\Throwable $th) {

            dd($th);
        }
    }
}
