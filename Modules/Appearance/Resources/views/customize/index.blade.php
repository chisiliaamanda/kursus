@extends('backend.master')

@section('mainContent')

    {!! generateBreadcrumb() !!}

    <div class="row justify-content-between">
        <div class="col-lg-12">
            <div class="white_box mb_30">
                <div class="white_box_tittle list_header">
                    <h4>{{__('frontendmanage.Theme color scheme')}} {{__('setting.Setting')}} </h4>
                </div>
                <form action="{{route('appearance.themes-customize.settingUpdate')}}" method="POST">
                    @csrf
                    <div class="row">

                        <div class="col-xl-6">
                            <div class="primary_input mb-15">
                                <label class="primary_input_label"

                                       for="">{{__('frontendmanage.Global Theme Color')}}</label>
                                <div class="primary_datepicker_input">
                                    <div class="no-gutters input-right-icon">
                                        <div class="col">
                                            <div class="row">
                                                <div class="col-md-2 mb-25 pl-0">
                                                    <label class="primary_checkbox d-flex mr-12"
                                                           for="frontend_global_color_scheme">
                                                        <input type="radio"
                                                               class="common-radio "
                                                               id="frontend_global_color_scheme"
                                                               name="frontend_global_color_scheme"
                                                               {{Settings('frontend_global_color_scheme')=="yes"?'checked':''}}
                                                               value="yes">
                                                        <span
                                                            class="checkmark mr-2"></span> {{__('common.Yes')}}
                                                    </label>
                                                </div>
                                                <div class="col-md-2 mb-25  pl-0">
                                                    <label class="primary_checkbox d-flex mr-12"
                                                           for="frontend_global_color_scheme_no">
                                                        <input type="radio"
                                                               class="common-radio "
                                                               id="frontend_global_color_scheme_no"
                                                               name="frontend_global_color_scheme"
                                                               {{Settings('frontend_global_color_scheme')=="yes"?'':'checked'}}
                                                               value="no">
                                                        <span
                                                            class="checkmark mr-2"></span> {{__('common.No')}}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-12  ">
                            <div class="search_course_btn text-center">
                                <button type="submit"
                                        class="primary-btn radius_30px mr-10 fix-gr-bg">{{__('common.Update')}} </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="box_header common_table_header">
                <div class="main-title d-md-flex">
                    <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{__('setting.Frontend Theme Color')}}

                        @if(permissionCheck('appearance.themes-customize.index'))
                            <ul class="d-flex float-right text-right">
                                <li><a class="primary-btn radius_30px mr-10 fix-gr-bg position_right"

                                       href="{{ route('appearance.themes-customize.create') }}"><i
                                            class="ti-plus"></i>{{__('setting.Add New')}}</a>
                                </li>
                            </ul>
                        @endif
                    </h3>

                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="QA_section QA_section_heading_custom check_box_table">
                <div class="QA_table ">
                    <!-- table-responsive -->
                    <div class="">
                        <table class="table Crm_table_active3">
                            <thead>
                            <tr>
                                <th scope="col">{{__('common.Title')}}</th>
                                <th scope="col">{{__('common.Theme')}}</th>
                                <th scope="col">{{__('setting.Primary Color')}}</th>
                                <th scope="col">{{__('setting.Secondary Color')}}</th>
                                <th scope="col">{{__('common.Status')}}</th>
                                <th scope="col">{{__('common.Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>

                            @foreach($themes as $theme)
                                @php
                                    if($theme->theme->name!=currentTheme()){
                                        continue;
                                    }
                                @endphp


                                <tr>
                                    <td>{{ $theme->name }}</td>
                                    <td>{{ $theme->theme->title }}</td>
                                    <td>
                                        <div class="row text-center">
                                            <div class="col-sm-6">
                                                {{ $theme->primary_color }}
                                            </div>
                                            <div class="col-sm-6">
                                                <div
                                                    style="height: 20px;width: 50px;background:      {{ $theme->primary_color }}"></div>
                                            </div>
                                        </div>


                                    </td>
                                    <td>

                                        <div class="row text-center">
                                            <div class="col-sm-6">
                                                {{ $theme->secondary_color }}
                                            </div>
                                            <div class="col-sm-6">
                                                <div
                                                    style="height: 20px;width: 50px;background:      {{ $theme->secondary_color }}"></div>
                                            </div>
                                        </div>

                                    </td>

                                    <td>
                                        @if(!empty($theme->theme->id))
                                            @if(@$theme->is_default==1)
                                                <span
                                                    class="primary-btn small fix-gr-bg "> @lang('common.Active') </span>
                                            @else
                                                @if(env('APP_SYNC'))
                                                    <span class="d-inline-block" tabindex="0" data-toggle="tooltip"
                                                          title="Disabled For Demo ">
                                                            <a class="primary-btn small tr-bg text-nowrap"
                                                               href="#"> @lang('common.Make Default')</a>
                                                </span>

                                                @else
                                                    <a class="primary-btn small tr-bg text-nowrap"
                                                       href="{{route('appearance.themes-customize.default',@$theme->id)}}"> @lang('common.Make Default') </a>

                                                @endif
                                            @endif
                                        @endif
                                    </td>
                                    <td>

                                        <div class="dropdown CRM_dropdown">
                                            <button class="btn btn-secondary dropdown-toggle" type="button"
                                                    id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true"
                                                    aria-expanded="false"> {{__('common.Select')}}
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right"
                                                 aria-labelledby="dropdownMenu2">
                                                @if ($theme->id != 1)

                                                    <a class="dropdown-item"
                                                       href="{{ route('appearance.themes-customize.edit', $theme->id) }}">@lang('common.Edit')</a>

                                                @endif


                                                <a class="dropdown-item"
                                                   type="button"
                                                   href="{{ route('appearance.themes-customize.copy', $theme->id) }}">@lang('setting.Clone Theme')</a>

                                                @if ($theme->id != 1)
                                                    @if(permissionCheck('appearance.themes-customize.destroy'))
                                                        <a class="dropdown-item"
                                                           type="button" data-toggle="modal"
                                                           data-target="#deletebackground_settingModal{{@$theme->id}}"
                                                           href="#">@lang('common.Delete')</a>
                                                    @endif
                                                @endif

                                            </div>
                                        </div>

                                    </td>

                                    <div class="modal fade admin-query"
                                         id="deletebackground_settingModal{{@$theme->id}}">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title">@lang('common.Delete')</h4>
                                                    <button type="button" class="close" data-dismiss="modal"><i
                                                            class="ti-close"></i>
                                                    </button>
                                                </div>

                                                <div class="modal-body">
                                                    <div class="text-center">
                                                        <h4>@lang('common.Are you sure to delete ?')</h4>
                                                    </div>

                                                    <div class="mt-40 d-flex justify-content-between">
                                                        <button type="button" class="primary-btn tr-bg"
                                                                data-dismiss="modal">@lang('common.Cancel')
                                                        </button>

                                                        {!! Form::open(['route' => ['appearance.themes-customize.destroy', $theme->id], 'method' => 'delete']) !!}
                                                        <button type="submit"
                                                                class="primary-btn fix-gr-bg">@lang('common.Delete')</button>
                                                        {!! Form::close() !!}


                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </tr>
                            @endforeach


                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
