<?php

namespace Modules\Payment\Entities;

use App\Traits\Tenantable;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Modules\CourseSetting\Entities\Course;

use Modules\BundleSubscription\Entities\BundleCoursePlan;
use Modules\Gift\Entities\GiftRecord;

class Cart extends Model
{
    use Tenantable;

    protected $fillable = ['course_id', 'user_id', 'price', 'instructor_id', 'tracking', 'pre_booking_amount'];
    protected $guarded = ['id'];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'id');
    }

    public function bundle()
    {
        return $this->belongsTo(BundleCoursePlan::class, 'bundle_course_id', 'id');
    }

    public function schedule()
    {
        return $this->belongsTo('Modules\Appointment\Entities\Schedule', 'schedule_id', 'id')->withDefault();
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id', 'id')->withDefault();
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->withDefault();
    }

    public function cart_gift()
    {
        return $this->belongsTo(GiftRecord::class, 'gift_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->withDefault();
    }

}
