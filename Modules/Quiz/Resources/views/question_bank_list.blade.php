@extends('backend.master')
@section('mainContent')
    @push('styles')
        <link rel="stylesheet" href="{{asset('Modules/Org/Resources/assets/css/select2_style.css')}}"/>

    @endpush
    <style>
        .select2-container {
            width: unset;
        }

        .select2-container--default .select2-selection--single, .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 35px;
            line-height: 35px;
            display: flex;
            align-items: center;
        }

        .groupList {
            height: 35px;
            display: flex;
            align-items: center;
        }

        /*span.select2-dropdown.select2-dropdown--below {*/
        /*    width: max-content !important;*/
        /*    max-width: max-content !important;*/
        /*}*/

        .select2-container--default .select2-results > .select2-results__options li {
            padding-right: 24px;
        }

        .select2-container--default .select2-results > .select2-results__options {
            max-height: 400px;
        }

        .select2-container--default .select2-results > .select2-results__options li {
            padding-right: 0;
            white-space: normal;
            line-height: 1.5;
            height: auto;
            padding-top: 10px;
            padding-bottom: 10px;
        }

        .min_width_300 {
            min-width: 300px;
        }

    </style>
    {!! generateBreadcrumb() !!}
    @if(!isModuleActive('AdvanceQuiz'))
        <div class="row">
            <div class="col-lg-12">
                <div class="white-box mb-30">
                    {{ Form::open(['class' => 'form-horizontal', 'files' => false,  'method' => 'GET','id' => 'search_group']) }}
                    <div class="row">

                        <div class="col-lg-4 mt-30-md md_mb_20">
                            <label class="primary_input_label" for="category_id">{{__('common.Type')}}</label>
                            <select class="primary_select "
                                    id="group" name="group">
                                <option data-display=" {{__('common.Select')}}" value=""> {{__('common.Type')}}
                                </option>
                                @foreach($groups as $g)
                                    <option value="{{$g->id}}" {{$group==$g->id?'selected':''}}>{{$g->title}}</option>
                                @endforeach
                            </select>

                        </div>


                        <div class="col-lg-4 mt-100-md md_mb_20">
                            <label class="primary_input_label" for="" style="    height: 30px;"></label>
                            <button type="submit" class="primary-btn small fix-gr-bg">
                                <span class="ti-search pr-2"></span>
                                {{__('quiz.Search')}}
                            </button>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    @endif
    <section class="admin-visitor-area up_st_admin_visitor">
        <div class="container-fluid p-0">

            <div class="row">
                <div class="col-lg-12">
                    <div class="main-title d-flex align-items-center justify-content-between mb-3">
                        <h3 class="mb-20">{{__('quiz.Question Bank List')}}</h3>
                        @if(isModuleActive('AdvanceQuiz'))
                            <div class="filter">
                                <ul class="d-flex float-right">
                                    <li class="pr-3">
                                        <input placeholder="{{__('common.Search')}}" type="text" id="tableSearch"
                                               class="primary_input_field height_px_35 mr-2">
                                    </li>
                                    <li class="min_width_300 mr-3 ">

                                        <select class="select2  groupList" name="group" id="group">
                                            <option
                                                data-display="{{__('common.Select')}} {{__('quiz.Group')}}"
                                                value="">{{__('common.Select')}} {{__('quiz.Group')}} </option>
                                            @foreach($groups->where('parent_id',0) as $group)
                                                @include('advancequiz::group._single_select_option_id',['group'=>$group,'level'=>1])
                                            @endforeach
                                        </select>
                                    </li>
                                    @if(permissionCheck('question-bank'))
                                        <li>
                                            <a class="primary-btn radius_30px mr-10 fix-gr-bg"
                                               href="{{route('question-bank')}}">
                                                <i class="ti-plus"></i>{{__('common.Add')}} {{__('quiz.Question')}}
                                            </a>
                                        </li>
                                    @endif
                                    @if(permissionCheck('question-bank-bulk'))
                                        <li>

                                            <a class="primary-btn radius_30px mr-10 fix-gr-bg"
                                               href="{{route('question-bank-bulk')}}"><i
                                                    class="ti-plus"></i>{{__('common.Import')}}
                                            </a>
                                        </li>
                                    @endif
                                    @if(permissionCheck('quiz.question-bank-export'))
                                        <li>
                                            <div class="d-flex align-content-lg-end">
                                                <a class="primary-btn radius_30px mr-10 fix-gr-bg"
                                                   href="{{route('quiz.question-bank-export')}}"><i
                                                        class="ti-download"></i>{{__('common.Export')}}
                                                </a>
                                            </div>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        @endif
                    </div>


                    <div class="QA_section QA_section_heading_custom check_box_table">
                        <div class="QA_table">

                            <div class="">
                                <table id="lms_table" class="table Crm_table_active3 quiz-bank-checkbox">
                                    <thead>

                                    <tr>
                                        <th>
                                            <div class="d-flex items-center">

                                                <label class=" primary_checkbox  "
                                                       for="questionSelectAll">
                                                    <input type="checkbox"
                                                           id="questionSelectAll"
                                                           class="common-checkbox selectAllQuizQuestion">
                                                    <span class="checkmark"></span>
                                                </label>

                                                <a href="#" id="deleteAllBtn"
                                                   style="display: none;    margin-top: -5px;"
                                                   class="primary-btn small fix-gr-bg ml-2">
                                                    <span class="ti-trash"></span>
                                                </a>
                                            </div>
                                        </th>
                                        <th>{{__('common.SL')}}</th>
                                        <th>{{__('quiz.Group')}}</th>
                                        {{--                                        <th>{{__('quiz.Category')}}</th>--}}
                                        <th>{{__('quiz.Question')}}</th>
                                        <th>{{__('common.Type')}}</th>
                                        @if(isModuleActive('AdvanceQuiz'))
                                            <th>{{__('common.Level')}}</th>
                                        @endif
                                        <th>{{__('quiz.Image')}}</th>
                                        <th>{{__('common.Action')}}</th>
                                    </tr>
                                    </thead>


                                    <tbody>


                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <div class="modal fade admin-query" id="deleteBank">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{__('common.Delete')}} </h4>
                    <button type="button" class="close" data-dismiss="modal"><i
                            class="ti-close "></i></button>
                </div>

                <div class="modal-body">
                    <form action="{{route('question-bank-delete')}}" method="post">
                        @csrf

                        <div class="text-center">

                            <h4>{{__('common.Are you sure to delete ?')}} </h4>
                        </div>
                        <input type="hidden" name="id" value="" id="classQusId">
                        <div class="mt-40 d-flex justify-content-between">
                            <button type="button" class="primary-btn tr-bg"
                                    data-dismiss="modal">{{__('common.Cancel')}}</button>

                            <button class="primary-btn fix-gr-bg"
                                    type="submit">{{__('common.Delete')}}</button>

                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>


    <div class="modal fade admin-query" id="deleteBankWarring">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{__('common.Warring')}} </h4>
                    <button type="button" class="close" data-dismiss="modal"><i
                            class="ti-close "></i></button>
                </div>

                <div class="modal-body">
                    <div class="text-center">

                        <h4>{{__('quiz.You cannot delete this question because it has been used in')}} <span
                                id="totalAssignQus"></span> {{__('quiz.quiz already')}} </h4>
                    </div>
                </div>

            </div>
        </div>
    </div>


    <div class="modal fade admin-query" id="deleteAllBank">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{__('common.Delete')}} </h4>
                    <button type="button" class="close" data-dismiss="modal"><i
                            class="ti-close "></i></button>
                </div>

                <div class="modal-body">
                    <form action="{{route('question-bank-bulk-delete')}}" method="post">
                        @csrf

                        <div class="text-center">
                            <h4>{{__('common.Are you sure to delete ?')}} </h4>
                        </div>
                        <input type="hidden" name="questions" value="" id="qusList">
                        <div class="mt-40 d-flex justify-content-between">
                            <button type="button" class="primary-btn tr-bg"
                                    data-dismiss="modal">{{__('common.Cancel')}}</button>

                            <button class="primary-btn fix-gr-bg"
                                    type="submit">{{__('common.Delete')}}</button>

                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        //
        @if(isModuleActive('AdvanceQuiz'))
        $('.groupList').select2({
            {{--ajax: {--}}
            {{--    url: '{{route('quiz.group.ajax-option')}}',--}}
            {{--    type: "GET",--}}
            {{--    dataType: 'json',--}}
            {{--    delay: 250,--}}
            {{--    data: function (params) {--}}
            {{--        return {--}}
            {{--            search: params.term,--}}
            {{--            page: params.page || 1,--}}
            {{--        }--}}
            {{--    },--}}
            {{--    cache: false--}}
            {{--}--}}
        });
        @endif

        $("#lms_table").on("change", ".question", function () {
            qusIsCheck();
        });

        function qusIsCheck() {
            if ($("#lms_table input:checkbox:checked").length > 0) {
                $('#deleteAllBtn').show();
            } else {
                $('#deleteAllBtn').hide();

            }
        }

        var questions = [];

        $('#deleteAllBtn').click(function (e) {
            e.preventDefault();
            $('#qusList').val('');

            $('#lms_table input:checkbox').each(function () {
                if (this.checked) {
                    questions.push($(this).val());
                }

            });
            $('#qusList').val(questions.toString());
            $('#deleteAllBank').modal('show');
        });
    </script>

    @php
        $url = route('getAllQuizData');
    @endphp

    <script>
        let table = $('#lms_table').DataTable({

            bLengthChange: true,
            "bDestroy": true,
            stateSave: true,
            processing: true,
            serverSide: true,
            order: [[1, "desc"]],
            "ajax": $.fn.dataTable.pipeline({
                url: '{!! $url !!}',
                pages: 5,
                data: function (d) {
                    d.group = $('.groupList').val()
                }
            }),
            columns: [
                {data: 'delete_btn', name: 'delete_btn', orderable: false, searchable: false},
                {data: 'DT_RowIndex', name: 'id', orderable: true},

                {data: 'questionGroup_title', name: 'questionGroup.title'},
                // {data: 'category_name', name: 'category.name'},
                {data: 'question', name: 'question'},
                {data: 'type', name: 'type'},
                    @if(isModuleActive('AdvanceQuiz'))
                {
                    data: 'level_title', name: 'level_title',
                },
                    @endif
                {
                    data: 'image', name: 'image', orderable: false
                },
                {data: 'action', name: 'action', orderable: false},

            ],
            language: {
                emptyTable: "{{ __("common.No data available in the table") }}",
                search: "<i class='ti-search'></i>",
                searchPlaceholder: '{{ __("common.Quick Search") }}',
                paginate: {
                    next: "<i class='ti-arrow-right'></i>",
                    previous: "<i class='ti-arrow-left'></i>"
                }
            },
            dom: '{{isModuleActive('AdvanceQuiz')?'rtip':'Blfrtip'}}',
            buttons: [
                {
                    extend: 'copyHtml5',
                    text: '<i class="far fa-copy"></i>',
                    title: $("#logo_title").val(),
                    titleAttr: '{{ __("common.Copy") }}',
                    exportOptions: {
                        columns: ':visible',
                        columns: ':not(:last-child)',
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="far fa-file-excel"></i>',
                    titleAttr: '{{ __("common.Excel") }}',
                    title: $("#logo_title").val(),
                    margin: [10, 10, 10, 0],
                    exportOptions: {
                        columns: ':visible',
                        columns: ':not(:last-child)',
                    },

                },
                {
                    extend: 'csvHtml5',
                    text: '<i class="far fa-file-alt"></i>',
                    titleAttr: '{{ __("common.CSV") }}',
                    exportOptions: {
                        columns: ':visible',
                        columns: ':not(:last-child)',
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="far fa-file-pdf"></i>',
                    title: $("#logo_title").val(),
                    titleAttr: '{{ __("common.PDF") }}',
                    exportOptions: {
                        columns: ':visible',
                        columns: ':not(:last-child)',
                    },
                    orientation: 'landscape',
                    pageSize: 'A4',
                    margin: [0, 0, 0, 12],
                    alignment: 'center',
                    header: true,
                    customize: function (doc) {
                        doc.content[1].table.widths =
                            Array(doc.content[1].table.body[0].length + 1).join('*').split('');
                    }

                },
                {
                    extend: 'print',
                    text: '<i class="fa fa-print"></i>',
                    titleAttr: '{{ __("common.Print") }}',
                    title: $("#logo_title").val(),
                    exportOptions: {
                        columns: ':not(:last-child)',
                    }
                },
                {
                    extend: 'colvis',
                    text: '<i class="fa fa-columns"></i>',
                    postfixButtons: ['colvisRestore']
                }
            ],
            columnDefs: [{
                visible: false
            }, {responsivePriority: 1, targets: 0},
                {responsivePriority: 1, targets: 4},
                {responsivePriority: 2, targets: -1},
            ],
            responsive: true,

            paging
        :
        true,
            "lengthChange"
        :
        true,
            "lengthMenu"
        :
        [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]]

        })
        ;


        $(document).on('input', '#tableSearch', function () {
            table.search($(this).val()).draw();
        });

        $(document).on('change', '.groupList ', function () {
            // console.log($(this).val())
            // console.log(table.columns(2))
            // table.columns(2).search($(this).val()).draw();
            // table.data('dt_params', {test: 'test'});
            // table.DataTable().draw();
            table.clearPipeline()
            table.ajax.reload();
        });
        $(document).on('click', '.deleteQuiz_bank', function () {
            let id = $(this).data('id');
            let total = $(this).data('total');
            if (total == 0) {
                $('#classQusId').val(id);
                $("#deleteBank").modal('show');
            } else {
                $("#totalAssignQus").text(total);
                $("#deleteBankWarring").modal('show');

            }

        });


        $(document).on('click', '.selectAllQuizQuestion', function () {
            if ($(this).is(':checked') == true) {

                table.rows().nodes().to$().find('input[type="checkbox"].question').each(function () {
                    $(this).prop('checked', true);
                });
            } else {
                table.rows().nodes().to$().find('input[type="checkbox"].question').each(function () {
                    $(this).prop('checked', false);

                });
            }
            qusIsCheck();
        });
    </script>
    <script src="{{asset('/')}}/Modules/CourseSetting/Resources/assets/js/course.js"></script>
@endpush
