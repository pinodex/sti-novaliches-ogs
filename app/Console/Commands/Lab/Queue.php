<?php

namespace App\Console\Commands\Lab;

use Illuminate\Console\Command;
use App\Lab\Jobs\Notify;
use App\Models\User;

class Queue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lab:queue {user id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test queue functionality';

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

        dispatch(new Notify($user));

        $this->info('A job has been dispatched');
        $this->info($user->name . ' will get a notification after 10 seconds');
    }
}
