<?php

namespace App\Jobs;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Blog\Entities\Blog;
use Modules\Org\Entities\OrgBranch;
use Modules\Org\Entities\OrgPosition;

class BlogNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $blog_id;

    public function __construct($blog_id)
    {
        $this->blog_id = $blog_id;
    }

    public function handle()
    {
        $blog = Blog::find($this->blog_id);
        $users = [];
        if ($blog) {
            if (isModuleActive('Org')) {
                if ($blog->audience == 1 || $blog->position_audience == 1) {
                    $users = User::where('role_id', 3)
                        ->where('status', 1)
                        ->get();
                } else {
                    $branch_ids = $blog->branches->pluck('branch_id')->toArray();
                    $branches = OrgBranch::whereIn('id', $branch_ids)->pluck('code')->toArray();


                    $position_ids = $blog->positions->pluck('position_id')->toArray();
                    $positions = OrgPosition::whereIn('id', $position_ids)->pluck('code')->toArray();

                    $query = User::whereIn('org_chart_code', $branches)->orWhereIn('org_position_code', $positions);


                    $users = $query->select('name', 'email', 'id', 'role_id', 'device_token')->where('role_id', 3)
                        ->where('status', 1)->get();
                }

            } else {
                $users = User::select('name', 'email', 'id', 'role_id', 'device_token')
                    ->where('role_id', 3)
                    ->where('status', 1)
                    ->get();
            }


            foreach ($users as $user) {
                if (UserEmailNotificationSetup('BLOG_PUBLISH', $user)) {
                    SendGeneralEmail::dispatch($user, 'BLOG_PUBLISH', [
                        'title' => $blog->title,
                        'name' => $user->name,
                        'link' => route('blogDetails', [$blog->slug]),
                    ]);
                }

                if (UserBrowserNotificationSetup('BLOG_PUBLISH', $user)) {
                    send_browser_notification($user, 'BLOG_PUBLISH', [
                        'title' => $blog->title,
                        'name' => $user->name,
                        'link' => route('blogDetails', [$blog->slug]),
                    ],
                        trans('common.View'),//actionText
                        route('blogDetails', [$blog->slug]),//actionUrl
                    );
                }

                if (UserMobileNotificationSetup('BLOG_PUBLISH', $user) && !empty($user->device_token)) {
                    send_mobile_notification($user, 'BLOG_PUBLISH', [
                        'title' => $blog->title,
                        'name' => $user->name,
                        'link' => route('blogDetails', [$blog->slug]),
                    ], $blog->title, $blog->id, 'blog');
                }
            }
        }
    }
}
