<?php

namespace App\Console\Commands\Lab;

use Mail;
use Illuminate\Console\Command;
use App\Lab\Mail\TestMessage;

class Email extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lab:email {address}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Email Sending';

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
        $emailAddress = $this->argument('address');
        
        $this->info("Sending test email to {$emailAddress}...");
        
        Mail::to($emailAddress)->send(new TestMessage);

        $this->info('Test email sent');
    }

    protected function sendEmail()
    {

    }
}
