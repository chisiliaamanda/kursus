<?php

namespace App\Jobs;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\CourseSetting\Entities\Course;
use Modules\NotificationSetup\Entities\PostedNotification;

class PostedNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $notification_id;

    public function __construct($notification_id)
    {
        $this->notification_id = $notification_id;
    }

    public function handle()
    {
        $notification = PostedNotification::find($this->notification_id);
        $users = [];
        if ($notification) {
            if ($notification->type == 'All Users') {

                $users = User::where('is_active', 1)->get(['id', 'name', 'email', 'role_id']);

            } elseif ($notification->type == 'All Students') {
                $users = User::where('role_id', 3)->where('is_active', 1)->get(['id', 'name', 'email', 'role_id']);
            } elseif ($notification->type == 'All Instructors') {
                $users = User::where('role_id', 2)->where('is_active', 1)->get(['id', 'name', 'email', 'role_id']);
            } elseif ($notification->type == 'All Staffs') {
                $users = User::where('role_id', 4)->where('is_active', 1)->get(['id', 'name', 'email', 'role_id']);
            } elseif ($notification->type == 'Single User') {
                $users = User::where('id', $notification->user_id)->get(['id', 'name', 'email', 'role_id']);
            } elseif ($notification->type == 'Specific Users') {
                $ids = $notification->receivers->pluck('receiver_id')->toArray();
                $users = User::whereIn('id', $ids)->get(['id', 'name', 'email', 'role_id']);
            } elseif ($notification->type == 'Course Students') {
                $course = Course::find($notification->course_id);
                if ($course) {
                    $users = $course->enrollUsers;
                }

            }

            foreach ($users as $user) {

                if (UserEmailNotificationSetup('POSTED_NOTIFICATION', $user)) {
                    SendGeneralEmail::dispatch($user, 'POSTED_NOTIFICATION', [
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'id' => $notification->id,
                        'name' => $user->name,
                    ]);
                }

                if (UserBrowserNotificationSetup('POSTED_NOTIFICATION', $user)) {
                    send_browser_notification($user, 'POSTED_NOTIFICATION', [
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'id' => $notification->id,
                        'name' => $user->name,
                    ],
                        '',//actionText
                        '',//actionUrl
                    );
                }

                if (UserMobileNotificationSetup('POSTED_NOTIFICATION', $user) && !empty($user->device_token)) {
                    send_mobile_notification($user, 'POSTED_NOTIFICATION', [
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'id' => $notification->id,
                        'name' => $user->name,
                    ], $notification->title, $notification->id, 'posted_notification');
                }
            }
        }
    }
}
