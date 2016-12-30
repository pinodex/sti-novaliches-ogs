<?php

namespace App\Jobs;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ParallelJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var array Array of jobs
     */
    protected $jobs;

    /**
     * Constructs ParallelJob
     * 
     * @param array $jobs Array of jobs
     */
    public function __construct(array $jobs = array())
    {
        $this->jobs = $jobs;
    }

    /**
     * Add job to parallel job set
     * 
     * @param Job $job Job
     */
    public function add(Job $job)
    {
        $this->jobs[] = $job;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->jobs as $job) {
            $job->handle();
        }
    }
}
