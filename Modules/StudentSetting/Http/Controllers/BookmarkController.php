<?php

namespace Modules\StudentSetting\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Modules\StudentSetting\Entities\BookmarkCourse;
use Modules\Setting\Model\GeneralSetting;


class BookmarkController extends Controller
{


    public function bookmarkSave($id)
    {
        try {
            $bookmarked = BookmarkCourse::where('user_id', Auth::id())->where('course_id', $id)->first();
            if (empty($bookmarked)) {
                $bookmark = new BookmarkCourse;
                $bookmark->user_id = Auth::id();
                $bookmark->course_id = $id;
                $bookmark->date = date("jS F Y");
                $bookmark->save();

                Toastr::success(trans('frontend.Wishlist Added Successfully'), trans('common.Success'));
            } else {
                $bookmarked->delete();
                Toastr::success(trans('frontend.Wishlist Remove Successfully'), trans('common.Success'));

            }

            return redirect()->back();
        } catch (\Exception $e) {
            GettingError($e->getMessage(), url()->current(), request()->ip(), request()->userAgent());
        }

    }

}
