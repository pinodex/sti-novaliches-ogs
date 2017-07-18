<?php

namespace App\Console\Commands\Lab;

use Illuminate\Console\Command;
use App\Lab\Notifications\TestNotification;
use App\Models\User;

class Notify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lab:notify {user id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send test notification to user';

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
        $userId = $this->argument('user id');
        $user = User::find($userId);

        if (!$user) {
            $this->error('Cannot find user with ID ' . $userId);

            return;
        }

        $user->notify(new TestNotification);
        $this->info('Notification sent to ' . $user->name);
    }
}
