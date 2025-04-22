<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Modules\OrgSubscription\Entities\OrgCourseSubscription;
use Modules\OrgSubscription\Entities\OrgLeaningPathSection;
use Modules\OrgSubscription\Entities\OrgSubscriptionCheckout;

class MyOrgSubscriptionPlanListSection extends Component
{
    public $request, $courses, $plan, $sections;

    public function __construct($plan, $request)
    {
        $search = $request->search;
        $this->sections = OrgLeaningPathSection::where('plan_id', $plan)->where('status', 1)->orderBy('position', 'asc')->get();
        $this->plan = $plan = OrgCourseSubscription::find($plan);
        $this->courses = $plan->activeAssign;
        $this->request = $request;
    }

    public function render()
    {
        $checkout = OrgSubscriptionCheckout::where('user_id', Auth::id())->where('plan_id', $this->plan->id)->first();
        if (!$checkout) {
            abort(403);
        }
        return view(theme('components.my-org-subscription-plan-list-section'), compact('checkout'));
    }
}
