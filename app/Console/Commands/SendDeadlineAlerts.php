<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\Task;
use App\Models\User;
use App\Services\FcmServices;
use Illuminate\Console\Command;

class SendDeadlineAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-deadline-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
{
    $tasks = Task::where('status', '!=', 'completed')
        ->whereDate('end_date', now()->addDay()->toDateString())
        ->get();

    foreach ($tasks as $task) {

        // 1) حفظ الإشعار في قاعدة البيانات
        Notification::create([
            'user_id' => $task->project->created_by,
            'title'   => 'Deadline Reminder',
            'message' => "Task '{$task->title}' deadline is tomorrow",
            'is_read' => false,
        ]);

        // 2) إرسال FCM
        $manager = User::find($task->project->created_by);
        $tokens = $manager->notificationTokens()->pluck('token')->toArray();
        $fcm= new FcmServices();
        $fcm->sendToUser(
            $tokens,
            'Deadline Reminder',
            "Task '{$task->title}' deadline is tomorrow",
            ['task_id' => $task->id]
        );
    }
    return Command::SUCCESS;
}

}
