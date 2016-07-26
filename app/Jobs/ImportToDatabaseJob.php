<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Extensions\Spreadsheet\AbstractSpreadsheet;

class ImportToDatabaseJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var AbstractSpreadsheet
     */
    protected $spreadsheet;

    /**
     * @var array
     */
    protected $args;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(AbstractSpreadsheet $spreadsheet, $args)
    {
        $this->spreadsheet = $spreadsheet;
        $this->args = $args;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        call_user_func_array([$this->spreadsheet, 'importToDatabase'], $this->args);
    }
}
