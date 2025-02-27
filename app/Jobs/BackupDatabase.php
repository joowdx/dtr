<?php

namespace App\Jobs;

use App\Actions\BackupDatabase as BackupDatabaseAction;
use App\Models\User;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class BackupDatabase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private ?User $user;

    private Carbon $time;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->user = Auth::user();

        $this->queue = 'main';

        $this->time = now();
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return date('Y-m-d');
    }

    /**
     * Execute the job.
     */
    public function handle(BackupDatabaseAction $dumper): void
    {
        try {
            $dump = $dumper();

            Notification::make()
                ->title('Database backup successful')
                ->body('The database has been successfully backed up at '.$dump->created_at)
                ->sendToDatabase($this->user, true);
        } catch (Exception $exception) {
            Notification::make()
                ->title('Database backup failed')
                ->body($exception->getMessage())
                ->sendToDatabase($this->user, true);
        }
    }
}
