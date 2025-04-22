@if(isset($result))
    <div class="col-12">
        <div class="instrucotr-slider owl-carousel">
            @foreach ($result as $instructor)
                <div class="instrucotr-item">
                    <div class="instrucotr-item-head position-relative">
                        <a href="{{route('instructorDetails',[$instructor->id,Str::slug($instructor->name,'-')])}}"
                           class="instrucotr-item-img mx-auto">
                            <img src="{{getInstructorImage($instructor->image)}}" alt="">
                        </a>
                    </div>
                    <div class="instrucotr-item-content mx-auto text-center">
                        <h4>
                            <a href="{{route('instructorDetails',[$instructor->id,Str::slug($instructor->name,'-')])}}"
                               class="currentColor">{{$instructor->name}}</a>
                        </h4>
                        <p>{{$instructor->headline}}</p>
                        <div class="instrucotr-item-actions">
                            <ul class="social-list gap-2">
                                <li><a href="{{$instructor->facebook}}"><i class="fab fa-facebook-f"></i></a></li>
                                <li><a href="{{$instructor->twitter}}"><i class="fab fa-twitter"></i></a></li>
                                <li><a href="{{$instructor->linkedin}}"><i class="fab fa-linkedin"></i></a></li>
                                <li><a href="{{$instructor->instagram}}"><i class="fab fa-instagram"></i></a></li>
                                <li><a href="{{$instructor->youtube}}"><i class="fab fa-youtube"></i></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
<div class="col-12">
    <div class="become-instructor position-relative">
        <div class="become-instructor-shape">
            <svg width="160" height="188" viewBox="0 0 160 188" fill="none" class="text-primary"
                 xmlns="http://www.w3.org/2000/svg">
                <g filter="url(#filter0_d_34_2825)">
                    <path
                        d="M46.6814 66.4718C33.3622 42.4769 50.7136 13 78.1573 13H112C131.882 13 148 29.1177 148 49V109.968C148 147.123 98.556 159.925 80.5241 127.44L46.6814 66.4718Z"
                        fill="currentColor"/>
                </g>
                <defs>
                    <filter id="filter0_d_34_2825" x="0.105591" y="0" width="159.894" height="187.027"
                            filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                        <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                        <feColorMatrix in="SourceAlpha" type="matrix"
                                       values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                        <feOffset dx="-15" dy="14"/>
                        <feGaussianBlur stdDeviation="13.5"/>
                        <feComposite in2="hardAlpha" operator="out"/>
                        <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.2 0"/>
                        <feBlend mode="normal" in2="BackgroundImageFix"
                                 result="effect1_dropShadow_34_2825"/>
                        <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_34_2825"
                                 result="shape"/>
                    </filter>
                </defs>
            </svg>
        </div>
        <div class="row align-items-center">
            <div class="col-lg-5 text-center">
                <div class="become-instructor-img ms-auto">
                    <span class="shape"></span>
                    <div class="w-100 h-100 overflow-hidden position-relative">
                        <img src="{{asset(@$homeContent->become_instructor_logo)}}" alt="">
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="become-instructor-content">
                    <h3 class="fw-500">{{@$homeContent->become_instructor_title}}</h3>
                    <p class="mb-4">{{@$homeContent->become_instructor_sub_title}}</p>
                    <a href="{{route('becomeInstructor')}}" class="theme-btn justify-content-center">Join
                        Now</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        'use strict'
        jQuery(document).ready(function () {
            const navLeft = '<svg width="23" height="19" viewBox="0 0 23 19" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M8.3125 0.625244L0.499998 8.43778V10.6253L8.3125 18.4378L10.5313 16.2503L5.40625 11.094H22.8125V7.96903H5.40625L10.5625 2.81275L8.3125 0.625244Z" fill="currentColor"/></svg>';
            const navRight = '<svg width="23" height="18" viewBox="0 0 23 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15.1875 17.8125L23 9.99996V7.81245L15.1875 -8.7738e-05L12.9687 2.18742L18.0937 7.3437H0.6875L0.6875 10.4687H18.0937L12.9375 15.625L15.1875 17.8125Z" fill="currentColor"/></svg>'

            // instrucotr slider
            $('.instrucotr-slider').owlCarousel({
                nav: true,
                rtl: false,
                navText: [navLeft, navRight],
                dots: false,
                items: 3,
                lazyLoad: true,
                autoplay: true,
                autoplayHoverPause: true,
                loop: true,
                margin: 24,
                stagePadding: 0,
                responsive: {
                    0: {items: 1},
                    768: {items: 2},
                    992: {items: 3, stagePadding: 24,},
                    1200: {
                        stagePadding: 0
                    }
                }
            });
        })
    })();
</script>
