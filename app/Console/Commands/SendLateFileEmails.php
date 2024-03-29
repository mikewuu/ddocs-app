<?php

namespace App\Console\Commands;

use App\Jobs\SendOverdueFilesNotifications;
use Illuminate\Console\Command;

class SendLateFileEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:late';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually send out reminders for late files.';

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
        dispatch(new SendOverdueFilesNotifications);
    }
}
