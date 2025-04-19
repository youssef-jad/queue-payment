<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;


class QueueHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:queue-health';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command To Check Queue proccess and health can be extended with notifications or alerts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $defaultQueueSize = Redis::connection()->llen('queues:default');
        $failedQueueSize = Redis::connection()->llen('laravel_horizon:failed_jobs');
        $failedJobsInDB = DB::table('failed_jobs')->count();
      
        $this->info('Queue Status:');
        $this->table(
            ['Queue', 'Pending Jobs'],
            [
                ['default', $defaultQueueSize],
                ['failed', $failedQueueSize],
            ]
        );

        if ($failedQueueSize > 0) {
            $this->warn("Warning: There are {$failedQueueSize} failed jobs!");
        }
        
        return Command::SUCCESS;
    }
}
