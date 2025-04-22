<?php

namespace Modules\FrontendManage\Repositories;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\CourseSetting\Entities\CourseComment;
use Modules\CourseSetting\Entities\CourseCommentReply;


class TopicCommentRepository
{

    public function query($data = [])
    {
        if (isModuleActive('LmsSaas')) {
            $course_comments = CourseComment::where('course_comments.lms_id', app('institute')->id);
            $course_comment_replies = CourseCommentReply::where('course_comment_replies.lms_id', app('institute')->id);
        } else {
            $course_comments = CourseComment::where('course_comments.lms_id', 1);
            $course_comment_replies = CourseCommentReply::where('course_comment_replies.lms_id', 1);
        }

        $course_comments->join('users', 'course_comments.user_id', '=', 'users.id')
            ->join('courses', 'courses.id', '=', 'course_comments.course_id')
            ->select(
                'users.name as user_name',
                'courses.title as course_title',
                'course_comments.comment',
                'course_comments.created_at',
                'course_comments.id',
                'course_comments.user_id',
                'course_comments.course_id',
                DB::raw("'Main Comment' as type"),
                DB::raw("'course_comments' as source_table"),
                DB::raw("'null' as reply_id")
            );

        $course_comment_replies->join('courses', 'courses.id', '=', 'course_comment_replies.course_id')
            ->join('users', 'users.id', '=', 'course_comment_replies.user_id')
            ->select(
                'users.name as user_name',
                'courses.title as course_title',
                'course_comment_replies.reply as comment',
                'course_comment_replies.created_at',
                'course_comment_replies.id',
                'course_comment_replies.user_id',
                'course_comment_replies.course_id',
                DB::raw("'Reply' as type"),
                DB::raw("'course_comment_replies' as source_table"),
                'course_comment_replies.reply_id as reply_id',
            );
//        $query = $course_comments->union($course_comment_replies);

        if (isset($data['f_date']) && $data['f_date']) {
            $course_comments = $course_comments->whereBetween(DB::raw('DATE(course_comments.created_at)'), formatDateRangeData($data['f_date']));
            $course_comment_replies = $course_comment_replies->whereBetween(DB::raw('DATE(course_comment_replies.created_at)'), formatDateRangeData($data['f_date']));
        }

        if (isset($data['f_user']) && $data['f_user']) {
            $course_comments = $course_comments->where('course_comments.user_id', $data['f_user']);
            $course_comment_replies = $course_comment_replies->where('course_comment_replies.user_id', $data['f_user']);
        }

        if (isset($data['f_course']) && $data['f_course']) {
            $course_comments = $course_comments->where('course_comments.course_id', $data['f_course']);
            $course_comment_replies = $course_comment_replies->where('course_comment_replies.course_id', $data['f_course']);
        }

        if (isset($data['f_type']) && $data['f_type']) {
            if ($data['f_type'] == 'Reply') {
                $query = $course_comment_replies;
            } else {
                $query = $course_comments;
            }
        } else {
            $query = $course_comments->union($course_comment_replies);
        }

        return $query;
    }

    public function delete($id, $table)
    {
        if ($table == 'course_comments') {
            $row = CourseComment::find($id);
            if ($row) {
                foreach ($row->replies as $q) {
                    $q->delete();
                }
                $row->delete();
            }
        }

        if ($table == 'course_comment_replies') {
            $row = CourseCommentReply::find($id);
            if ($row) {
                if ($row->reply_id) {
                    $row->delete();
                } else {
                    $replies = CourseCommentReply::where('reply_id', $id)->get();
                    foreach ($replies as $q) {
                        $q->delete();
                    }
                    $row->delete();
                }


            }
        }


        return true;
    }

    public function reply(array $data)
    {
        if ($data['table'] == 'course_comments') {
            $row = CourseComment::where('id', $data['id'])->first();
            return CourseCommentReply::create([
                'user_id' => Auth::id(),
                'course_id' => $row->course_id,
                'comment_id' => $data['id'],
                'reply' => $data['comment'],
                'reply_id' => null,
            ]);

        }

        if ($data['table'] == 'course_comment_replies') {
            $row = CourseCommentReply::where('id', $data['id'])->first();
            return CourseCommentReply::create([
                'user_id' => Auth::id(),
                'course_id' => $row->course_id,
                'comment_id' => $row->comment_id,
                'reply' => $data['comment'],
                'reply_id' => $data['id']
            ]);

        }

    }


}
