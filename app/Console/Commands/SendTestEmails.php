<?php

namespace App\Console\Commands;

use App\Checklist;
use App\File;
use App\Jobs\DeleteEmailTestUser;
use App\Mail\FreeCreditsReceived;
use App\Mail\LateFilesReminder;
use App\Mail\NewChecklist;
use App\Mail\NotEnoughCreditsForList;
use App\Mail\UpcomingFilesReminder;
use App\Mail\Welcome;
use App\Mail\WelcomeWithGeneratedPassword;
use App\Recipient;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class SendTestEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:test {email=mail@wumike.com}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send test emails for each view.';

    /**
     * Mike's User model
     * @var
     */
    private $user;

    /**
     * The first Checklist in DB We assume it's the dev seeded one.
     * TODO ::: Create a new checklist so it'll always be consistent.
     * @var
     */
    private $checklist;

    /**
     * Recipient we're sending to.
     *
     * @var
     */
    private $recipient;

    /**
     * The User model that has the same email we're sending emaisl too.
     *
     * @var
     */
    private $existingUser;

    /*
    *
     * @var int
     */
    protected $sentEmails = 0;

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


        if(! Schema::hasTable('checklists')) return;

        $this->existingUser = User::where('email', $this->argument('email'))->first();

        if($this->existingUser) $this->existingUser->update(['email' => 'temporaryemail@emailz.com']);

        $this->user = User::create([
            'name' => 'John Dough',
            'email' => $this->argument('email'),
            'password' => bcrypt('secret')
        ]);


        $this->checklist = Checklist::create([
            'name' => 'Test Checklist',
            'description' => 'Best checklist that could.',
            'user_id' => $this->user->id
        ]);

        $this->recipient = Recipient::create([
            'email' => $this->argument('email'),
            'checklist_id' => $this->checklist->id
        ]);




        $this->sendUserEmails()
             ->sendChecklistEmails()
             ->sendFileRequestEmails()
        ->cleanUpRecords();
        $this->info('and done! Sent Emails = ' . $this->sentEmails);
    }

    protected function sendUserEmails()
    {
        Mail::to($this->user)->send(new Welcome($this->user));
        $this->sentEmails ++;
        Mail::to($this->user)->send(new WelcomeWithGeneratedPassword($this->user, 'abcd1234'));
        $this->sentEmails ++;
        Mail::to($this->user)->send(new NotEnoughCreditsForList($this->user));
        $this->sentEmails ++;

        $this->info('Finished User Emails');
        return $this;
    }

    protected function sendChecklistEmails()
    {

        Mail::to($this->recipient->email)->send(new NewChecklist($this->recipient, $this->checklist));
        $this->sentEmails ++;

        Mail::to($this->checklist->user)->send(new ChecklistComplete($this->checklist));
        $this->sentEmails ++;

        Mail::to($this->recipient->email)->send(new UpcomingFilesReminder($this->recipient, $this->checklist));
        $this->sentEmails ++;

        Mail::to($this->recipient->email)->send(new LateFilesReminder($this->recipient, $this->checklist));
        $this->sentEmails ++;


        Mail::to($this->checklist->user)->send(new FreeCreditsReceived($this->checklist, $this->user));
        $this->sentEmails ++;

        $this->info('Finished Checklist Emails');
        return $this;
    }

    protected function sendFileRequestEmails()
    {

        $file = factory(File::class)->create();

        $fileRequest =  $this->checklist->requestedFiles()->create([
            'name' => 'Super important file',
            'description' => 'not much to say here',
            'due' => '2017-06-01 00:00:00',
            'required' => 1,
            'checklist_id' => $this->checklist->id,
            'file_id' => $file->id
        ]);

        $fileRequest->uploads()->create([
            'path' => 'foo/bar.jpg',
            'file_name' => $file->name,
            'size' => 44444,
            'rejected' => 1,
            'rejected_reason' => 'Not good enough'
        ]);


        Mail::to($this->recipient->email)->send(new FileChangesRequired($this->recipient, $fileRequest));

        $fileRequest->fullDelete();

        $this->sentEmails ++;
        $this->info('Finished File Request Emails');
        return $this;
    }

    protected function cleanUpRecords()
    {
        // Dispatch a job onto the queue so we don't delete prematurely.
        dispatch(new DeleteEmailTestUser($this->user, $this->existingUser, $this->argument('email')));
        return $this;
    }

}
