<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendUserAccountEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $email;
    protected string $phone;
    protected string $password;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email, $phone, $password)
    {
        $this->email = $email;
        $this->phone = $phone;
        $this->password = $password;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            Mail::to($this->email)->send(new \App\Mail\SendUserAccountEmail($this->phone, $this->password));
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
