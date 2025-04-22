<div class="row -mt-24">
    @if(isset($result))
        @foreach($result as $course)
            <div class="col-lg-4 col-sm-6 d-flex">
                <div class="course-item w-100">
                    <a href="{{courseDetailsUrl(@$course->id,@$course->type,@$course->slug)}}">
                        <div class="course-item-img lazy">
                            <img src="{{ getCourseImage($course->thumbnail) }}" alt="{{$course->title}}">
                        </div>
                    </a>
                    <div class="course-item-content">
                        <p class="fs-12 mb-2 fw-normal"><span
                                class="text-primary">{{__('frontend.in')}}:</span> {{$course->category->name}} </p>
                        <div class="course-item-rating">
                            <a href="#" class="d-flex align-items-center">
                                <div class="user">
                                    <img src="{{getInstructorImage($course->user->image)}}" alt="">
                                </div>
                                <div class="content">
                                    <span class="lh-1">{{$course->user->name}}</span>
                                    <div class="lh-1 rating">
                                        @php
                                            $main_stars= $course->total_rating;

                                            $stars=intval($main_stars);

                                        @endphp
                                        @for ($i = 0; $i <  $stars; $i++)
                                            <i class="fas fa-star"></i>
                                        @endfor
                                        @if ($main_stars>$stars)
                                            <i class="fas fa-star-half"></i>
                                        @endif
                                        @if($main_stars==0)
                                            @for ($i = 0; $i <  5; $i++)
                                                <i class="far fa-star"></i>
                                            @endfor
                                        @endif

                                    </div>
                                </div>
                            </a>
                        </div>
                        <a class="course-item-title" data-bs-toggle="tooltip" data-bs-placement="bottom"
                           title="{{$course->title}}"
                           href="{{courseDetailsUrl(@$course->id,@$course->type,@$course->slug)}}">
                            {{$course->title}}
                        </a>
                        <div class="course-item-price d-flex flex-wrap align-items-center justify-content-between">

                            @if(auth()->check() && $course->isLoginUserEnrolled)
                                <a href="{{courseDetailsUrl(@$course->id,@$course->type,@$course->slug)}}"
                                   class="theme-btn">
                                    {{__('courses.Get Started')}}
                                </a>
                            @else
                                <a href="{{route('buyNow',[@$course->id])}}"
                                   class="theme-btn">
                                    {{__('frontend.Buy Now')}}
                                </a>
                            @endif
                            @if(showEcommerce())
                                <span>
                                    @if (@$course->discount_price!=null)
                                        <del>{{getPriceFormat($course->discount_price)}}</del>
                                    @endif
                                    <strong class="fw-bold d-inline-block ms-2">
                                        @if (@$course->discount_price!=null)
                                            {{getPriceFormat($course->discount_price)}}
                                        @else
                                            {{getPriceFormat($course->price)}}
                                        @endif
                                    </strong>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif


    <script>
        if ($.isFunction($.fn.lazy)) {
            $('.lazy').lazy();
        }
    </script>
</div>
