<div data-type="component-text"
     data-preview="{{ !function_exists('themeAsset') ? '' : themeAsset('img/snippets/preview/banner/banner-v4.jpg') }}"
     data-aoraeditor-title="Banner V4" data-aoraeditor-categories="Home Page;Banner">

    <style>
        .banner-area {
            position: relative;
            z-index: 1;
            background-image: var(--bg-image);
            background-repeat: no-repeat;
            background-size: 100%;
            background-position: top right;
            padding-top: 190px;
            padding-bottom: 290px
        }

        .banner-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .banner-img img {
            width: 100% !important;
            height: auto !important;
            object-fit: cover;
        }

        @media only screen and (min-width: 1440px) and (max-width: 1580px) {
            .banner-area {
                padding-bottom: 250px;
                background-size: 120%
            }
        }

        @media only screen and (min-width: 1280px) and (max-width: 1439px) {
            .banner-area {
                background-size: 124%;
                padding-top: 140px;
                padding-bottom: 230px
            }
        }

        @media only screen and (min-width: 992px) and (max-width: 1279px) {
            .banner-area {
                padding-top: 130px;
                padding-bottom: 190px;
                background-position: center right
            }
        }

        @media only screen and (min-width: 768px) and (max-width: 991px) {
            .banner-area {
                padding: 80px 0px;
                background-image: none
            }
        }

        @media only screen and (max-width: 767px) {
            .banner-area {
                padding: 60px 0px;
                background-image: none
            }
        }

        @media only screen and (max-width: 479px) {
            .banner-area {
                padding: 50px 0px
            }
        }

        .banner-text h1 {
            font-size: 64px;
            line-height: 1.25;
            margin-bottom: 30px;
            color: #425073
        }

        @media only screen and (min-width: 1280px) and (max-width: 1439px) {
            .banner-text h1 {
                font-size: 54px
            }
        }

        @media only screen and (min-width: 992px) and (max-width: 1279px) {
            .banner-text h1 {
                font-size: 50px
            }
        }

        @media only screen and (min-width: 768px) and (max-width: 991px) {
            .banner-text h1 {
                font-size: 44px
            }
        }

        @media only screen and (max-width: 767px) {
            .banner-text h1 {
                font-size: 38px
            }
        }

        @media only screen and (min-width: 768px) and (max-width: 991px) {
            .banner-text h1 {
                margin-bottom: 20px
            }
        }

        @media only screen and (max-width: 767px) {
            .banner-text h1 {
                margin-bottom: 24px
            }
        }

        .banner-text p {
            font-size: 18px;
            line-height: 1.66667;
            margin-bottom: 60px;
            color: #777E93
        }

        @media only screen and (min-width: 1280px) and (max-width: 1439px) {
            .banner-text p {
                margin-bottom: 40px
            }
        }

        @media only screen and (min-width: 992px) and (max-width: 1279px) {
            .banner-text p {
                padding-right: 0 !important;
                margin-bottom: 40px
            }
        }

        @media only screen and (min-width: 768px) and (max-width: 991px) {
            .banner-text p {
                font-size: 16px;
                margin-bottom: 36px
            }
        }

        @media only screen and (max-width: 767px) {
            .banner-text p {
                font-size: 16px;
                margin-bottom: 24px
            }
        }

        .banner-text .theme-btn {
            --btn-padding-y: 16px
        }

        @media only screen and (min-width: 768px) and (max-width: 991px) {
            .banner-text .theme-btn {
                --btn-padding-y: 12px
            }
        }

        @media only screen and (max-width: 767px) {
            .banner-text .theme-btn {
                --btn-padding-y: 10px;
                --btn-padding-x: 24px
            }
        }

        .banner-text .theme-btn:not(:last-child) {
            margin-right: 24px
        }

        @media only screen and (min-width: 768px) and (max-width: 991px) {
            .banner-text .theme-btn:not(:last-child) {
                margin-right: 18px
            }
        }

        @media only screen and (max-width: 767px) {
            .banner-text .theme-btn:not(:last-child) {
                margin-right: 12px
            }
        }

        @media only screen and (max-width: 479px) {
            .banner-text .theme-btn:not(:last-child) {
                margin-right: 0;
                margin-bottom: 12px
            }
        }

        /*
                .banner-free {
                    padding: 20px;
                    min-width: 250px;
                    background: #fff;
                    box-shadow: -20px 17px 23px rgba(44, 36, 36, 0.15);
                    border-radius: 20px;
                    --icon: 60px;
                    position: absolute;
                    right: 55px;
                    top: 475px
                }

                @media only screen and (min-width: 1440px) and (max-width: 1580px) {
                    .banner-free {
                        min-width: 220px;
                        top: 310px
                    }
                }

                @media only screen and (min-width: 1280px) and (max-width: 1439px) {
                    .banner-free {
                        padding: 15px;
                        min-width: 200px;
                        --icon: 50px;
                        right: 10px;
                        top: 390px
                    }
                }

                @media only screen and (min-width: 992px) and (max-width: 1279px) {
                    .banner-free {
                        padding: 12px;
                        min-width: 160px;
                        --icon: 40px;
                        right: 10px;
                        top: 420px
                    }
                }

                .banner-free .icon {
                    width: var(--icon);
                    flex: 0 0 auto
                }

                .banner-free .icon img {
                    width: 100%
                }

                .banner-free .content {
                    padding-left: 20px;
                    width: calc(100% - var(--icon));
                    flex: 0 0 auto
                }

                @media only screen and (min-width: 1280px) and (max-width: 1439px) {
                    .banner-free .content {
                        padding-left: 14px
                    }
                }

                @media only screen and (min-width: 992px) and (max-width: 1279px) {
                    .banner-free .content {
                        padding-left: 10px
                    }
                }

                .banner-free .content h4 {
                    font-size: 28px;
                    line-height: 1.5;
                    line-height: 1
                }

                @media only screen and (min-width: 992px) and (max-width: 1279px) {
                    .banner-free .content h4 {
                        font-size: 24px
                    }
                }

                @media only screen and (min-width: 768px) and (max-width: 991px) {
                    .banner-free .content h4 {
                        font-size: 24px
                    }
                }

                @media only screen and (max-width: 767px) {
                    .banner-free .content h4 {
                        font-size: 22px
                    }
                }

                @media only screen and (min-width: 992px) and (max-width: 1279px) {
                    .banner-free .content h4 {
                        font-size: 20px
                    }
                }

                .banner-free .content span {
                    font-size: 18px;
                    line-height: 1
                }

                @media only screen and (min-width: 992px) and (max-width: 1279px) {
                    .banner-free .content span {
                        font-size: 16px
                    }
                } */
        /*
                .banner-star {
                    background: #fff;
                    box-shadow: -20px 17px 23px rgba(44, 36, 36, 0.15);
                    border-radius: 20px;
                    min-width: 150px;
                    text-align: center;
                    padding: 10px;
                    position: absolute;
                    right: 645px;
                    top: 345px
                }

                @media only screen and (min-width: 1440px) and (max-width: 1580px) {
                    .banner-star {
                        right: 590px;
                        top: 265px
                    }
                }

                @media only screen and (min-width: 1280px) and (max-width: 1439px) {
                    .banner-star {
                        right: 485px;
                        top: 195px
                    }
                }

                @media only screen and (min-width: 992px) and (max-width: 1279px) {
                    .banner-star {
                        right: 265px;
                        top: 255px
                    }
                }

                .banner-star .rating {
                    font-size: 16px;
                    line-height: 1.5
                }

                .banner-star .rating i:not(:last-child) {
                    margin-right: 6px
                } */

    </style>
    <form action="{{ route('search') }}">
        <div class="banner-area">
            <div class="banner-img">
                <img src="{{ asset('public/frontend/infixlmstheme/img/banner/banner-v4.png') }}" alt="">
            </div>
            {{-- <div class="banner-free d-none d-lg-flex align-items-center">
                <div class="icon">
                    <img src="{{ themeAsset('img/shape/laptop-star.png') }}" alt="">
                </div>
                <div class="content">
                    <h4 class="fw-bold text-primary mb-0">7100+</h4>
                    <span>Free Courses
                    <i class="far fa-star"></i> <i class="fa fa-edit"></i>
                    </span>
                </div>
            </div> --}}
            {{-- <div class="banner-star d-none d-lg-block">
                <div class="rating text-primary">
                    <div class="fa fa-star"></div>
                    <div class="fa fa-star"></div>
                    <div class="fa fa-star"></div>
                    <div class="fa fa-star"></div>
                    <div class="fa fa-star-half-alt"></div>
                </div>
                <span class="fs-14 lh-1 ">5300+ Rating</span>
            </div> --}}
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-xl-6 col-lg-7 col-md-8">
                        <div class="banner-text">
                            <h1>{{ @$homeContent->slider_title }}</h1>
                            <p class="pe-0 pe-xl-5">{{ @$homeContent->slider_text }}</p>
                            <a href="{{route('courses')}}" class="theme-btn text-capitalize">View All Courses</a>
                            <a href="{{route('courses')}}" class="theme-btn text-capitalize bg-white">View All
                                Courses</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

