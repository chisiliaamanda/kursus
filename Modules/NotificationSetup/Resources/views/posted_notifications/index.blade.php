@extends('backend.master')
@push('styles')

@endpush

@section('mainContent')

    {!! generateBreadcrumb() !!}

    <section class="admin-visitor-area up_st_admin_visitor">
        <div class="container-fluid p-0">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="box_header common_table_header">
                        <div class="main-title d-md-flex">
                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{__('setting.Notification List')}}</h3>

                            <ul class="d-flex">
                                @if (permissionCheck('notifications.posted.create'))
                                    <li><a class="primary-btn radius_30px mr-10 fix-gr-bg"
                                           href="{{route('notifications.posted.create')}}"><i
                                                class="ti-plus"></i>{{__('setting.Send Notification')}}</a>
                                    </li>
                                @endif

                            </ul>

                        </div>
                    </div>
                </div>
                <div class="col-lg-12 mt-40">
                    <div class="QA_section QA_section_heading_custom check_box_table">
                        <div class="QA_table ">
                            <!-- table-responsive -->
                            <div class="">
                                <table id="lms_table" class="table Crm_table_active3">
                                    <thead>
                                    <tr>
                                        <th scope="col">{{__('common.SL')}}</th>
                                        <th scope="col">{{__('common.Title')}}</th>
                                        <th scope="col">{{__('setting.Sender')}}</th>
                                        <th scope="col">{{__('setting.Receiver')}}</th>
                                        <th scope="col">{{__('common.Type')}}</th>
                                        {{--                                        <th scope="col">{{__('common.Status')}}</th>--}}
                                        <th scope="col">{{__('setting.Created Date')}}</th>
                                        <th scope="col">{{__('common.Action')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade admin-query" id="deleteItem">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">{{__('common.Delete')}} {{__('setting.Notification')}} </h4>
                                <button type="button" class="close" data-dismiss="modal"><i
                                        class="ti-close "></i></button>
                            </div>

                            <div class="modal-body">
                                <form action="{{route('notifications.posted.destroy')}}" method="POST">
                                    @csrf

                                    <div class="text-center">

                                        <h4>{{__('common.Are you sure to delete ?')}} </h4>
                                    </div>
                                    <input type="hidden" name="id" value="" id="itemDeleteId">
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
            </div>

        </div>
    </section>
    <div class="modal fade admin-query" id="showItem">
        <div class="modal-dialog modal-dialog-centered modal_650px">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{__('setting.Message')}} </h4>
                    <button type="button" class="close" data-dismiss="modal"><i
                            class="ti-close "></i></button>
                </div>

                <div class="modal-body">
                    <div id="notification_msg">
                    </div>

                    <div class="mt-40 d-flex justify-content-end">
                        <button type="button" class="primary-btn tr-bg"
                                data-dismiss="modal">{{__('common.Cancel')}}</button>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection
@push('scripts')

    @php
        $url = route('notifications.posted.datatable');
    @endphp

    <script>
        let table = $('#lms_table').DataTable({
            bLengthChange: true,
            "bDestroy": true,
            "lengthChange": true,
            "lengthMenu": [[10, 25, 50, 100, 100000], [10, 25, 50, 100, "All"]],
            processing: true,
            serverSide: true,
            stateSave: true,
            order: [[0, "desc"]],
            "ajax": $.fn.dataTable.pipeline({
                url: '{!! $url !!}',
                data: function () {
                    //pass variable
                },
                pages: 5 // number of pages to cache
            }),
            columns: [
                {data: 'DT_RowIndex', name: 'id', orderable: true},
                {data: 'title', name: 'title'},
                {data: 'sender', name: 'sender.name'},
                {data: 'receiver', name: 'receiver.name'},
                {data: 'type', name: 'type'},
                // {data: 'status', name: 'status', orderable: false},
                {data: 'created_at', name: 'created_at'},
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
            dom: 'Blfrtip',
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
            }
                ,
                // {responsivePriority: 1, targets: 0},
                // {responsivePriority: 1, targets: 2},
                // {responsivePriority: 1, targets: -1},
                // {responsivePriority: 2, targets: -2},
            ],
            responsive: true,
        });

        $(document).on('click', '.delete_notification', function () {
            let id = $(this).data('id');
            $('#itemDeleteId').val(id);
            $("#deleteItem").modal('show');
        });

        $(document).on('click', '.show_notification', function () {
            let msg = $(this).data('msg');
            $('#notification_msg').html(msg);
            $("#showItem").modal('show');
        });


    </script>

@endpush
