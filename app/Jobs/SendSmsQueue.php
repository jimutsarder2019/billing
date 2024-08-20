<?php

namespace App\Jobs;

use App\Mail\SendEmailTest;
use App\Services\Message\MessageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSmsQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $data;
    public $timeout = 0;
    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if ($this->data['sms_for'] == 'multiple') {
                $d =  MessageService::SendMessage($this->data['req_data']);
            } else {
                echo ucwords(str_replace('_', ' ', $this->data['tamp'])) . " SMS sending ....\n";
                SendSingleMessage([
                    'template_type' => $this->data['tamp'],
                    'number'        => $this->data['phone'],
                    'customer_id'   => $this->data['customer_id'],
                    'invoice'       => isset($this->data['invoice']) ? $this->data['invoice'] : null,
                ]);
            }
            echo "SMS Sent Successfully \n";
        } catch (\Throwable $th) {
            dd($th);
        }
    }
}
