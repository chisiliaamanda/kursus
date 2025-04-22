<div data-type="component-text"
     data-preview="{{!function_exists('themeAsset')?'':themeAsset('img/snippets/preview/apk/3.jpg')}}"
     data-aoraeditor-title="APK Sections V3" data-aoraeditor-categories="Home Page">

    <style>
        .apk {
            background-color: var(--system_primery_color);
            margin-top: var(--section-sepreate-lg);
            position: relative;
            z-index: 1;
        }

        .apk::before {
            content: "";
            width: 100%;
            height: 18%;
            position: absolute;
            bottom: 0;
            left: 0;
            background: var(--footer_background_color);
        }

        @media only screen and (max-width: 991px) {
            .apk .row.align-items-center {
                flex-direction: column-reverse
            }
        }

        .apk-content {
            padding-right: 130px;
            padding-top: 20px
        }

        @media only screen and (min-width: 1440px) and (max-width: 1580px) {
            .apk-content {
                padding-right: 30px
            }
        }

        @media only screen and (min-width: 1280px) and (max-width: 1439px) {
            .apk-content {
                padding-right: 30px
            }
        }

        @media only screen and (min-width: 992px) and (max-width: 1279px) {
            .apk-content {
                padding-right: 0;
                padding-top: 50px
            }
        }

        @media only screen and (max-width: 991px) {
            .apk-content {
                padding: 50px 0;
                padding-right: 0;
                text-align: center
            }
        }

        @media only screen and (max-width: 767px) {
            .apk-content {
                padding: 30px 0
            }
        }

        .apk-content h3 {
            font-size: 48px;
            line-height: 1.25;
            color: #fff;
        }

        @media only screen and (min-width: 1280px) and (max-width: 1439px) {
            .apk-content h3 {
                font-size: 42px
            }
        }

        @media only screen and (min-width: 992px) and (max-width: 1279px) {
            .apk-content h3 {
                font-size: 36px
            }
        }

        @media only screen and (min-width: 768px) and (max-width: 991px) {
            .apk-content h3 {
                font-size: 32px
            }
        }

        @media only screen and (max-width: 767px) {
            .apk-content h3 {
                font-size: 28px
            }
        }

        @media only screen and (min-width: 768px) and (max-width: 991px) {
            .apk-content h3 {
                margin-bottom: 16px !important;
                padding-right: 0 !important
            }
        }

        @media only screen and (max-width: 767px) {
            .apk-content h3 {
                margin-bottom: 12px !important;
                padding-right: 0 !important
            }
        }

        .apk-content h3 span {
            position: relative;
            z-index: 1;
            color: #FFCC6A !important
        }

        .apk-content h3 span::before {
            content: "";
            width: 100%;
            height: 100%;
            position: absolute;
            bottom: -10px;
            left: 20px;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg width='243' height='21' viewBox='0 0 243 21' fill='none' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M2 14.0007C238 30 241 2 241 2' stroke='%23FFDC83' stroke-width='4' stroke-linecap='round'/%3e%3c/svg%3e ");
            background-repeat: no-repeat;
            background-position: left bottom;
        }

        .apk-content p {
            margin-bottom: 36px;
            color: #fff
        }

        @media only screen and (min-width: 768px) and (max-width: 991px) {
            .apk-content p {
                margin-bottom: 26px
            }
        }

        @media only screen and (max-width: 767px) {
            .apk-content p {
                margin-bottom: 20px
            }
        }

        .apk-content a {
            color: var(--system_secendory_color);
            display: block;
            border-radius: 6px;
            overflow: hidden
        }

        @media only screen and (max-width: 767px) {
            .apk-content a svg {
                width: 130px;
                height: auto
            }
        }

        .apk-content .gap-4 {
            gap: 12px !important
        }

        @media only screen and (max-width: 991px) {
            .apk-content .gap-4 {
                justify-content: center
            }
        }

        .apk-img {
            position: relative;
            z-index: 1;
            width: 520px;
            margin: 0 auto;
            margin-top: -100px
        }

        @media only screen and (min-width: 992px) and (max-width: 1279px) {
            .apk-img {
                width: 430px
            }
        }

        @media only screen and (min-width: 768px) and (max-width: 991px) {
            .apk-img {
                margin-top: -80px;
                width: 350px
            }
        }

        @media only screen and (max-width: 767px) {
            .apk-img {
                margin-top: -60px;
                width: 300px
            }
        }

        .apk-skill {
            margin-top: 60px;
            position: relative;
            z-index: 2;
        }


        @media only screen and (min-width: 768px) and (max-width: 991px) {
            .apk-skill {
                margin-top: 20px
            }
        }

        @media only screen and (max-width: 767px) {
            .apk-skill {
                margin-top: 0;
            }
        }

        .apk-skill-card {
            border-radius: 18px;
            padding: 56px 60px;
            padding-right: 24px;
            background-image: var(--bg-image);
            background-position: bottom right;
            background-size: contain;
            background-repeat: no-repeat;
            background-color: #fff;
            background-blend-mode: multiply
        }

        @media only screen and (min-width: 992px) and (max-width: 1279px) {
            .apk-skill-card {
                padding: 36px 40px
            }
        }

        @media only screen and (min-width: 768px) and (max-width: 991px) {
            .apk-skill-card {
                padding: 26px 30px
            }
        }

        @media only screen and (max-width: 767px) {
            .apk-skill-card {
                padding: 24px;
                margin-top: 30px
            }
        }

        .apk-skill-card span {
            font-size: 14px;
            line-height: 1.14286;
            color: var(--system_primery_color);
            display: inline-block;
            margin-bottom: 20px;
            font-weight: 700
        }

        @media only screen and (min-width: 768px) and (max-width: 991px) {
            .apk-skill-card span {
                margin-bottom: 10px
            }
        }

        .apk-skill-card h3 {
            font-size: 30px;
            line-height: 1.46667;
            margin-bottom: 24px;
            color: var(--system_secendory_color)
        }

        @media only screen and (min-width: 992px) and (max-width: 1279px) {
            .apk-skill-card h3 {
                font-size: 26px
            }
        }

        @media only screen and (min-width: 768px) and (max-width: 991px) {
            .apk-skill-card h3 {
                font-size: 24px
            }
        }

        @media only screen and (max-width: 767px) {
            .apk-skill-card h3 {
                font-size: 22px
            }
        }

        @media only screen and (min-width: 768px) and (max-width: 991px) {
            .apk-skill-card h3 {
                margin-bottom: 20px
            }
        }

        @media only screen and (max-width: 767px) {
            .apk-skill-card h3 {
                margin-bottom: 16px
            }
        }

        .apk-skill-card .theme-btn {
            --btn-padding-y: 9px;
            --btn-padding-x: 17px
        }

        @media only screen and (min-width: 768px) and (max-width: 991px) {
            .apk-skill-card .theme-btn {
                --btn-padding-y: 7px
            }
        }

    </style>
    <section class="apk">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="apk-content">

                        <h3 class="mb-4">For Better <span class="text-primary">Experience</span> Use Mobile App</h3>
                        <p>Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia
                            consequat duis enim velit mollit xercitation.</p>
                        <div class="d-flex align-items-center gap-4">
                            <a href="#" target="_blank">
                                <img src="{{url('public/frontend/infixlmstheme/img/svg/google_play_btn.svg')}}"
                                     alt=""> </a>
                            <a href="#" target="_blank">
                                <img src="{{url('public/frontend/infixlmstheme/img/svg/apple_store_btn.svg')}}"
                                     alt="">
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <div class="apk-img">
                        <img src="{{themeAsset('img/others/apk-img-v2.png')}}" alt="">
                    </div>
                </div>
            </div>
            <div class="row apk-skill">
                <div class="col-md-6">
                    <div class="apk-skill-card" style="--bg-image: url('{{themeAsset('img/others/apk-card-1.png')}}')">
                        <span>New Collection</span>
                        <h3>Level - Up Your Raw Coding <br> Skills in Lockdown</h3>
                        <a href="#" class="theme-btn">Find Out More</a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="apk-skill-card" style="--bg-image: url('{{themeAsset('img/others/apk-card-2.png')}}')">
                        <span>New Collection</span>
                        <h3>Level - Up Your Raw Coding <br> Skills in Lockdown</h3>
                        <a href="#" class="theme-btn">Find Out More</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>
