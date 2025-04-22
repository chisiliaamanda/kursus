@extends(theme('layouts.master'))
@section('title')
    {{Settings('site_title')  ? Settings('site_title')  : 'Infix LMS'}} | {{__('forum.Forum')}}
@endsection
@section('css')
    <link rel="stylesheet" href="{{asset('public/frontend/infixlmstheme/css/forum.css')}}">
@endsection
@section('js')
    <script src="{{asset('public/frontend/infixlmstheme/js/forum.js')}}"></script>
@endsection

@section('mainContent')
    <x-breadcrumb :banner="$frontendContent->forum_banner"
                  :title="$frontendContent->forum_title"
                  :subTitle="$frontendContent->forum_sub_title"/>

    <div class="fourm_area section_spacing4">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="fourm_header d-flex align-items-center justify-content-between flex-wrap gap_15">

                        <div class="fourm_header_right">
                            <div class="nav fouram_tabs" id="nav-tab" role="tablist">
                                <a class="nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home"
                                   role="tab" aria-controls="nav-home" aria-selected="true">Course</a>
                                {{--                                @if ($groups!=null)--}}
                                {{--                                    <a class="nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-profile"--}}
                                {{--                                       role="tab" aria-controls="nav-profile" aria-selected="false">Group</a>--}}
                                {{--                                @endif--}}
                            </div>
                        </div>
                    </div>
                    <div class="fourm_body">
                        <div class="tab-content" id="nav-tabContent">
                            <div class="tab-pane fade show active" id="nav-home" role="tabpanel"
                                 aria-labelledby="nav-home-tab">
                                <!-- content  -->
                                <div class="table-responsive">
                                    <table class="table fourm_table mb-0">
                                        <thead>
                                        <tr>
                                            <th>Course</th>
                                            <th>User</th>
                                            <th>Category</th>
                                            <th>Replies</th>
                                            <th>Views</th>
                                            {{-- <th>Activity</th> --}}
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($courses as $course)

                                            <tr>
                                                <td>
                                                    <a href="{{route('forum.CourseForum',$course->id)}}">
                                                        <div class="topic_name">
                                                            <h3>
                                                                {{@$course->title}}
                                                            </h3>
                                                        </div>
                                                    </a>
                                                </td>
                                                <td>
                                                    <ul class="users_list">
                                                        @foreach ($course->forums as $forum_key => $forum)
                                                            @php
                                                                if($forum_key > 2){
                                                                    continue;
                                                                }
                                                            @endphp
                                                            {{-- @dd($forum->user) --}}
                                                            <li>
                                                                <div class="single_user style3">
                                                                    <div class="thumb ">
                                                                        <div
                                                                            class="profile_info profile_img collaps_icon d-flex align-items-center">
                                                                            <div class="studentProfileThumb"
                                                                                 style="background-image: url('{{getProfileImage($forum->user->image)}}');margin: 0"></div>
                                                                        </div>

                                                                    </div>
                                                                </div>
                                                                <div class="user_hover_card">
                                                                    <div class="user_card_top">
                                                            <span>
                                                                <div class="thumb">
                                                                    <div
                                                                        class="profile_info profile_img collaps_icon d-flex align-items-center">
                                                                        <div class="studentProfileThumb"
                                                                             style="background-image: url('{{getProfileImage($forum->user->image)}}');margin: 0"></div>
                                                                    </div>
                                                                </div>
                                                            </span>
                                                                        <h3>{{$forum->user->name}}</h3>
                                                                        <p>{{ \Carbon\Carbon::parse($forum->user->created_at)->diffForhumans() }}
                                                                            .
                                                                            Joined {{showDate($forum->user->created_at)}}</p>
                                                                    </div>
                                                                    <div class="user_points">
                                                                        <span>Point’s</span>
                                                                        <h3>{{$forum->user->forumReply->sum('points')}}</h3>
                                                                    </div>
                                                                    <div class="user_card_info">
                                                                        <p>Post’s
                                                                            <span> - {{$forum->user->forums->count()}}</span>
                                                                        </p>
                                                                        <p>Reply
                                                                            <span> - {{$forum->user->forumReply->count()}}</span>
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        @endforeach
                                                        <li>
                                                            <span class="ti-more-alt dot_icon"></span>
                                                        </li>
                                                    </ul>

                                                </td>
                                                <td>
                                                    <div class="category_mark d-flex align-items-center ">
                                                        <span class="squire_bulet"> </span> {{@$course->category->name}}
                                                    </div>
                                                </td>
                                                <td>
                                                    {{-- @dd($course->forums) --}}
                                                    @php
                                                        $replies=0;
                                                        foreach($course->forums as $forum){
                                                            $replies+=$forum->replies->count();
                                                        }
                                                        echo $replies;
                                                    @endphp

                                                </td>
                                                <td>
                                                @php
                                                    $views=0;
                                                    foreach($course->forums as $forum){
                                                        $views+=$forum->views->count();
                                                    }
                                                    echo $views;
                                                @endphp
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div
                                    class="fourm_footer d-flex align-items-center justify-content-between gap_15 flex-wrap">
                                    {{ $courses->links() }}
                                    <p>Showing {{@$data_from}}–{{@$data_to}} of {{ $courses->total() }} Results</p>
                                </div>
                                <!-- content  -->
                            </div>

                            <div class="tab-pane fade" id="nav-profile" role="tabpanel"
                                 aria-labelledby="nav-profile-tab">
                                another page
                            </div>


                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection
