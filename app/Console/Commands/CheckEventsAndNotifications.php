<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Notification;
use Carbon\Carbon;
use App\Event;
class CheckEventsAndNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cronDailyCheck';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete old notifications 6 months ago And Change is_expired and is_past inEvents';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        //delete notifications 
        $notifications = Notification::query()->whereDate("created_at","<=",Carbon::now()->subMonth(6)->toDateString())->delete();


        ///is_past and is_expired events
        $queues = Event::all();
        foreach ($queues as $queue) {
            try{
                    //check if arabic => 2 , 1 => english
                    if(is_null($queue->is_past)){
                        if($queue->end_datetime < Carbon::now())
                        {
                            $queue->is_past = 1;
                            $queue->save();
                        }
                       
                    }
                    if(is_null($queue->is_expired)){
                        if($queue->end_datetime < Carbon::now()->subMonth(6)->toDateString())
                        {
                            $queue->is_expired = 1;
                            $queue->save();
                        }
                    }
            }catch(\Exception $e){

            }
            // $queue->delete();
    }
   }
}
