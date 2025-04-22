<?php

namespace App\Http\Controllers;

use App\UserLogin;
use Carbon\Carbon;
use App\Models\User;
use Carbon\CarbonPeriod;
use Modules\Setting\Entities\Badge;
use Modules\Setting\Http\Controllers\BadgeController;
use Omnipay\MobilPay\Api\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Payment\Entities\Withdraw;
use Illuminate\Support\Facades\Response;
use Modules\CourseSetting\Entities\Course;
use Modules\CourseSetting\Entities\CourseEnrolled;

class HomeController extends Controller
{

    public function __construct()
    {

        $this->middleware(['auth', 'verified']);
    }

    public function index()
    {
        if (Auth::user()->role_id == 1) {
            return redirect()->route('dashboard');
        } else if (Auth::user()->role_id == 2) {
            return redirect()->route('dashboard');
        } else if (Auth::user()->role_id == 3) {
            return redirect()->route('studentDashboard');
        } else {
            return redirect('/');
        }
    }

    //dashboard
    public function dashboard()
    {
//        dd(Auth::user()->unreadNotifications[0]->data['actionURL'] ?? '');

        try {
            if (Auth::user()->role_id == 3) {
                return redirect()->route('studentDashboard');
            }
            if (isModuleActive('Affiliate') && Auth::user()->role->name == 'Affiliate') {
                return redirect()->route('affiliate.my_affiliate.index');
            }

            $user = Auth::user();
            if ($user->role_id == 1) {


                $recentEnroll = CourseEnrolled::latest()->take(4)->select(
                    'reveune', 'course_id', 'user_id', 'purchase_price'
                )->with('course', 'course.user', 'user')->get();

                $coursesEarnings = CourseEnrolled::query()
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->get();
                $courses_enrolle = CourseEnrolled::query()
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->get();

            } else {
                $query = CourseEnrolled::latest()->take(4)->select(
                    'reveune', 'course_id', 'user_id', 'purchase_price'
                )->whereHas('course', function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                });

                if (isModuleActive('Organization') && Auth::user()->isOrganization()) {
                    $query->whereHas('course.user', function ($q) {
                        $q->where('organization_id', Auth::id());
                        $q->orWhere('user_id', Auth::id());
                    });
                }

                $recentEnroll = $query->with('course', 'course.user', 'user')->get();
                $query2 = CourseEnrolled::query()
                    ->whereHas('course', function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    });

                if (isModuleActive('Organization') && Auth::user()->isOrganization()) {
                    $query2->whereHas('course.user', function ($q) {
                        $q->where('organization_id', Auth::id());
                        $q->orWhere('user_id', Auth::id());
                    });
                }


                $coursesEarnings = CourseEnrolled::query()
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->get();
                $query3 = CourseEnrolled::query()
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->whereHas('course', function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    });

                if (isModuleActive('Organization') && Auth::user()->isOrganization()) {
                    $query2->whereHas('course.user', function ($q) {
                        $q->where('organization_id', Auth::id());
                        $q->orWhere('user_id', Auth::id());
                    });
                }
                $courses_enrolle = $query3->get();

            }

            $courshEarningM_onth_name = [];
            foreach ($coursesEarnings as $key => $earning) {
                $courshEarningM_onth_name[] = $earning->month;
            }
            $courshEarningMonthly = [];
            foreach ($coursesEarnings as $key => $earn) {
                $courshEarningMonthly[] = $earn->earning;
            }
            // return $coursesEarnings;
            //====================================Payment Statistics====================================


            $withdraws_query = Withdraw::
            selectRaw('monthname(issueDate) as month')
                ->selectRaw('YEAR(issueDate) as year')
                ->select('status')
                ->whereYear('issueDate', '=', date('Y'))
                ->whereMonth('issueDate', '=', date('m'));


            if ($user->role_id != 1) {
                $withdraws_query->where('instructor_id', $user->id);
            }
            $withdraws = $withdraws_query->get();
            $withdraws_paid = $withdraws->where('status', '=', 1);
            $withdraws_unpaid = $withdraws->where('status', '=', 0);


            $payment_statistics['paid'] = $withdraws_paid;
            $payment_statistics['unpaid'] = $withdraws_unpaid;
            $payment_statistics['month'] = Carbon::now()->format('F');

            //============================Course Overview===============================
            $query4 = Course::with('user', 'enrolls');

            if (isModuleActive('Organization') && Auth::user()->isOrganization()) {
                $query4->whereHas('user', function ($q) {
                    $q->where('organization_id', Auth::id());
                    $q->orWhere('user_id', Auth::id());
                });
            }
            $allCourses = $query4->get();
            $enable_courses = $allCourses->where('status', 1)->count();
            $disable_courses = $allCourses->where('status', 0)->count();

            $courses = $allCourses->where('type', 1)->count();
            $quizzes = $allCourses->where('type', 2)->count();
            $classes = $allCourses->where('type', 3)->count();

            $course_overview['active'] = $enable_courses;
            $course_overview['pending'] = $disable_courses;
            $course_overview['courses'] = $courses;
            $course_overview['quizzes'] = $quizzes;
            $course_overview['classes'] = $classes;

            //====================================Course Enroll===================================
            $enrolls = [];

            foreach ($courses_enrolle as $course) {
                if (date('Y') == $course->created_at->format('Y') && date('F') == $course->created_at->format('F'))
                    $enrolls[] = $course;
            }
            $enroll_day = [];
            foreach ($enrolls as $key => $enroll) {
                $enroll_day[] = $enroll->day;
            }
            $enroll_count = [];
            foreach ($enrolls as $key => $enroll) {
                $enroll_count[] = $enroll->count;
            }

            $students = null;
            if (isModuleActive('CPD')) {
                $students = User::with('cpds')->where('role_id', 3)->get();
            }

///test start


//test end
            $badges = [];

            if (Settings('gamification_status') && Settings('gamification_leaderboard_show_badges_status')) {
                $badgeController = new BadgeController();
                $types = array_keys($badgeController->badgesTypes());
                $myBadgesIds = Auth::user()->userLatestBadges->pluck('badge_id')->toArray();
                $notForStudent = [
                    'blogs',
                    'sales',
                    'rating',
                    'registration'
                ];
                $reg_badges = Badge::select('id', 'point')->where('type', 'registration')->where(function ($query) {
                    $totalDay = 0;
                    if (Auth::check()) {
                        $created = new \Illuminate\Support\Carbon(Auth::user()->created_at);
                        $now = Carbon::now();
                        $totalDay = $now->diffInDays($created);
                    }
                    $query->where('point', '<=', $totalDay);
                })->orderBy('point', 'asc')->get()->pluck('id')->toArray();
                $myBadgesIds = array_merge($myBadgesIds, $reg_badges);
                $badges = Badge::select('title', 'image', 'type', 'point')
                    ->where('status', 1)
                    ->whereIn('type', $types)->where('status', 1)
                    ->whereNotIn('id', $myBadgesIds)
                    ->orderBy('point', 'asc')
                    ->whereIn('type', $notForStudent)
                    ->get()
                    ->groupBy('type');
            }

            return view('dashboard', compact('badges', 'recentEnroll', 'courshEarningM_onth_name', 'courshEarningMonthly', 'payment_statistics', 'enroll_day', 'enroll_count', 'course_overview', 'allCourses', 'students'));

        } catch (\Exception $e) {
            GettingError($e->getMessage(), url()->current(), request()->ip(), request()->userAgent());

        }
    }

    public function userLoginChartByDays(\Illuminate\Http\Request $request)
    {
        $userLoginChartByDays = [];
        $type = $request->type;
        $days = $request->days;

        if ($type == "days") {

            $from = Carbon::now()->subDays($days - 1);
            $to = Carbon::now();


        } else {
            $allDays = explode(' - ', $days);
            $from = Carbon::parse($allDays[0]);
            $to = Carbon::parse($allDays[1]);
        }


        $period = CarbonPeriod::create($from, $to);
        $dates = [];
        $data = [];

        foreach ($period as $key => $value) {
            $day = $value->format('Y-m-d');
            $dates[] = $day;
            $query5 = UserLogin::whereDate('login_at', $day);
            if (isModuleActive('Organization') && Auth::user()->isOrganization()) {
                $query5->whereHas('user', function ($q) {
                    $q->where('organization_id', Auth::id());
                    $q->orWhere('user_id', Auth::id());
                });
            }
            $data[] = $query5->count();
        }
        $userLoginChartByDays['date'] = $dates;
        $userLoginChartByDays['data'] = $data;

        return $userLoginChartByDays;
    }

    public function userLoginChartByTime(\Illuminate\Http\Request $request)
    {
        $userLoginChartByDays = [];
        $type = $request->type;
        $days = $request->days;

        if ($type == "days") {

            $from = Carbon::now()->subDays($days - 1);
            $to = Carbon::now();


        } else {
            $allDays = explode(' - ', $days);
            $from = Carbon::parse($allDays[0]);
            $to = Carbon::parse($allDays[1]);
        }


        $period = CarbonPeriod::create($from, $to);
        $hours = [];

        foreach ($period as $key => $value) {
            $day = $value->format('Y-m-d');


            $query6 = UserLogin::whereDate('login_at', $day);

            if (isModuleActive('Organization') && Auth::user()->isOrganization()) {
                $query6->whereHas('user', function ($q) {
                    $q->where('organization_id', Auth::id());
                    $q->orWhere('user_id', Auth::id());
                });
            }

            $loginData = $query6->get(['id', 'login_at'])->groupBy(function ($date) {
                return Carbon::parse($date->login_at)->format('H');
            });

            for ($i = 0; $i <= 23; $i++) {
                if (!isset($hours[$i])) {
                    $hours[$i] = 0;
                }
                if (!isset($loginData[$i])) {
                    $loginData[$i] = [];
                }
                $hours[$i] = count($loginData[$i]) + $hours[$i];
            }
        }
        return $hours;
    }

    public function getDashboardData()
    {
        try {
            $user = Auth::user();

            if ($user->role_id == 2) {
                $allCourseEnrolled = CourseEnrolled::with('user', 'course')
                    ->whereHas('course', function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    })->get();

                $allCourses = Course::where('user_id', $user->id)->get();

                $thisMonthEnroll = CourseEnrolled::whereYear('created_at', Carbon::now()->year)
                    ->whereMonth('created_at', Carbon::now()->format('m'))
                    ->whereHas('course', function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    })->sum('purchase_price');


                $today = CourseEnrolled::whereDate('created_at', Carbon::today())
                    ->whereHas('course', function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    })->sum('purchase_price');

                $month = CourseEnrolled::select(
                    DB::raw('sum(purchase_price) as totalSell'),
                    DB::raw("DATE_FORMAT(created_at,'%m') as months")
                )->whereHas('course', function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                })->groupBy('months')->get();
                $rev = $allCourseEnrolled->sum('reveune');
            } else {
                $query = CourseEnrolled::query();
                if (isModuleActive('Organization') && Auth::user()->isOrganization()) {
                    $query->whereHas('course.user', function ($q) {
                        $q->where('organization_id', Auth::id());
                        $q->orWhere('user_id', Auth::id());
                    });
                }
                $allCourseEnrolled = $query->get();
                $query2 = Course::query();
                if (isModuleActive('Organization') && Auth::user()->isOrganization()) {
                    $query2->whereHas('user', function ($q) {
                        $q->where('organization_id', Auth::id());
                        $q->orWhere('user_id', Auth::id());
                    });
                }
                $allCourses = $query2->get();

                $query3 = CourseEnrolled::whereYear('created_at', Carbon::now()->year)
                    ->whereMonth('created_at', Carbon::now()->format('m'));

                if (isModuleActive('Organization') && Auth::user()->isOrganization()) {
                    $query3->whereHas('course.user', function ($q) {
                        $q->where('organization_id', Auth::id());
                        $q->orWhere('user_id', Auth::id());
                    });
                }

                $thisMonthEnroll = $query3->sum('purchase_price');

                $query4 = CourseEnrolled::whereDate('created_at', Carbon::today());
                if (isModuleActive('Organization') && Auth::user()->isOrganization()) {
                    $query4->whereHas('course.user', function ($q) {
                        $q->where('organization_id', Auth::id());
                        $q->orWhere('user_id', Auth::id());
                    });
                }
                $today = $query4->sum('purchase_price');

//
                $rev = $allCourseEnrolled->sum('purchase_price') - $allCourseEnrolled->sum('reveune');
            }

            $info['allCourse'] = $allCourses->count();
            $info['totalEnroll'] = $allCourseEnrolled->count();
            $info['thisMonthEnroll'] = $thisMonthEnroll;
            $info['thisMonthEnroll'] = number_format($info['thisMonthEnroll'], 2, '.', '');
            $info['today'] = $today;
            $info['today'] = number_format($info['today'], 2, '.', '');

            $query6 = User::where('role_id', 3);

            if (isModuleActive('Organization') && Auth::user()->isOrganization()) {
                $query6->where('organization_id', Auth::id());
            }


            $info['student'] = $query6->count();
            $query7 = User::where('role_id', 2);

            if (isModuleActive('Organization') && Auth::user()->isOrganization()) {
                $query7->where('organization_id', Auth::id());
            }
            $info['instructor'] = $query7->count();
            $info['totalSell'] = $allCourseEnrolled->sum('purchase_price');
            $info['totalSell'] = number_format($info['totalSell'], 2, '.', '');

            $info['adminRev'] = number_format($rev, 2, '.', '');


//            $info['month'] = $month;
            return Response::json($info);
        } catch (\Exception $e) {
            GettingError($e->getMessage(), url()->current(), request()->ip(), request()->userAgent());
        }
    }


    public function validateGenerate()
    {
        return view('validate_generate');
    }


    public function validateGenerateSubmit()
    {
        $field = request()->field;
        $rules = request()->rules;
        $arr = [];


        $single_rule = explode('|', $rules);


        foreach ($single_rule as $rule) {
            $string = explode(':', $rule);
            $rule_name = $rule_message_key = $string[0];

            if (in_array($rule_name, ['max', 'min'])) {
                $rule_message_key = $rule_message_key . '.string';
            }

            $message = __('validation.' . $rule_message_key);

            $field_string = str_replace('_', ' ', $field);

            $message = str_replace(
                [':attribute', ':ATTRIBUTE', ':Attribute'],
                [$field_string, \Illuminate\Support\Str::upper($field_string), \Illuminate\Support\Str::ucfirst($field_string)],
                $message
            );
            if (in_array($rule_name, ['max', 'min'])) {
                $message = str_replace(
                    [':' . $rule_name],
                    [$string[1]],
                    $message
                );
            }

            if ($rule_name == 'required_if') {
                $ex = explode(',', $string[1]);
                $message = str_replace(
                    [':other'],
                    [str_replace('_', ' ', $ex[0])],
                    $message
                );
                if (isset($ex[2])) {
                    $message = str_replace(
                        [':value', "'"],
                        [str_replace('_', ' ', $ex[2]), ''],
                        $message
                    );
                } else {
                    $message = str_replace(
                        [':value', "'"],
                        [str_replace('_', ' ', $ex[1]), ''],
                        $message
                    );
                }
            }

            if ($rule_name == 'mimes') {

                $message = str_replace(
                    [':values'],
                    [str_replace('_', ' ', $string[1])],
                    $message
                );
            }
            if ($rule_name == 'same') {

                $message = str_replace(
                    [':other'],
                    [str_replace('_', ' ', $string[1])],
                    $message
                );
            }
            if ($rule_name == 'required_with') {

                $message = str_replace(
                    [':values'],
                    [str_replace('_', ' ', $string[1])],
                    $message
                );
            }

            if ($rule_name == 'after_or_equal') {

                $message = str_replace(
                    [':date'],
                    [str_replace('_', ' ', $string[1])],
                    $message
                );
            }
            if ($rule_name == 'after') {

                $message = str_replace(
                    [':date'],
                    [str_replace('_', ' ', $string[1])],
                    $message
                );
            }


            $arr [$field . '.' . $rule_name] = $message;
        }

        $defaultFile = public_path('/../resources/lang/default/validation.php');
        $languages = include "{$defaultFile}";
        $languages = array_merge($languages, $arr);
        file_put_contents($defaultFile, '');
        file_put_contents($defaultFile, '<?php return ' . var_export($languages, true) . ';');

        return view('validate_generate', compact('field', 'rules', 'arr'));
    }
}
