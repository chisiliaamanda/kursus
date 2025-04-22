<?php

namespace Modules\NotificationSetup\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\PostedNotificationJob;
use App\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;
use Modules\CourseSetting\Entities\Course;
use Modules\NotificationSetup\Http\Requests\SendNotificationRequest;
use Modules\NotificationSetup\Repositories\PostedNotificationRepository;
use Yajra\DataTables\Facades\DataTables;

class PostedNotificationController extends Controller
{
    protected $postedNotificationRepo;

    public function __construct(PostedNotificationRepository $postedNotificationRepo)
    {
        $this->middleware('auth');
        $this->middleware('RoutePermissionCheck:notifications.posted.index', ['only' => ['index', 'postedDatatable']]);
        $this->middleware('RoutePermissionCheck:notifications.posted.create', ['only' => ['store', 'create']]);
        $this->middleware('RoutePermissionCheck:notifications.posted.destroy', ['only' => ['destroy']]);

        $this->postedNotificationRepo = $postedNotificationRepo;
    }

    public function index()
    {
        try {

            return view('notificationsetup::posted_notifications.index');
        } catch (\Exception $e) {
            GettingError($e->getMessage(), url()->current(), request()->ip(), request()->userAgent());
        }

    }

    public function postedDatatable(Request $request)
    {

        try {
            $data = $this->postedNotificationRepo->query();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('created_at', function ($row) {
                    return showDate($row->created_at);
                })
                ->addColumn('sender', function ($row) {
                    return $row->sender->name;
                })
                ->addColumn('receiver', function ($row) {
                    return $row->receiver->name;
                })
                ->addColumn('action', function ($row) {
                    return view('notificationsetup::posted_notifications.components._action', ['row' => $row]);
                })
                ->rawColumns(['action', 'message'])
                ->toJson();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json([
                'error' => $e->getMessage()
            ], 503);
        }
    }

    public function create()
    {
        try {
            $data['types'] = [
                'All Users',
                'All Students',
                'All Instructors',
                'All Staffs',
                'Single User',
                'Specific Users',
                'Course Students',
            ];
            $data['users'] = User::where('is_active', 1)->get(['id', 'name', 'email']);
            $data['courses'] = Course::where('status', 1)->get(['id', 'title']);

            return view('notificationsetup::posted_notifications.create', $data);

        } catch (\Exception $e) {
            GettingError($e->getMessage(), url()->current(), request()->ip(), request()->userAgent());
        }

    }


    public function store(SendNotificationRequest $request)
    {
        //
        try {
            DB::beginTransaction();
            $notification = $this->postedNotificationRepo->store($request->all());
            DB::commit();
            PostedNotificationJob::dispatch($notification->id);
            Toastr::success(trans('common.Operation successful'), trans('common.Success'));
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollBack();
            GettingError($e->getMessage(), url()->current(), request()->ip(), request()->userAgent());
        }
    }


    public function destroy(Request $request)
    {
        if (demoCheck()) {
            return redirect()->back();
        }
        $rules = [
            'id' => 'required'
        ];

        $this->validate($request, $rules, validationMessage($rules));

        try {
            $success = trans('lang.Deleted') . ' ' . trans('lang.Successfully');
            $this->postedNotificationRepo->delete($request->id);
            Toastr::success($success, 'Success');
            return redirect()->back();

        } catch (\Exception $e) {
            GettingError($e->getMessage(), url()->current(), request()->ip(), request()->userAgent());
        }

    }
}
