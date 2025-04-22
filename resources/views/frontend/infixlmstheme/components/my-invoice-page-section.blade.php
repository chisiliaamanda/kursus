<div>
    <section class="admin-visitor-area up_st_admin_visitor pt-5 mt-5">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-11 col-xl-9">
                    <div class="box_header common_table_header">
                        <div class="main-title d-flex">
                            <h3 class="mb-0 mr-30 text-uppercase">INV-{{$enroll->id+1000}}</h3>
                        </div>
                        <div class="table_btn_wrap">
                            <ul>
                                <li>
                                    <button class="primary_btn printBtn">{{__('student.Print')}}</button>
                                </li>
                                <li>
                                    <button class="primary_btn downloadBtn">{{__('student.Download')}}</button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- invoice print part here -->
                    <div class="invoice_print pb-5">
                        <div class="container-fluid p-0">
                            <div id="invoice_print" class="invoice_part_iner">

                                <style>

                                    @media print {
                                        .table {
                                            width: 100%;
                                            margin-bottom: 1rem;
                                            color: #212529;
                                            font-family: Jost, sans-serif;
                                        }

                                        td h3 {
                                            font-size: 24px;
                                            font-weight: 500;
                                            color: var(--system_secendory_color);
                                        }

                                        .w-50 {
                                            width: 50% !important;
                                        }

                                        .invoice_grid {
                                            display: grid;
                                            grid-template-columns: 90px auto;
                                            margin-bottom: 10px;
                                            grid-gap: 25px;
                                        }

                                        h4 {
                                            line-height: 25px;
                                        }

                                        .custom_table3 {
                                            border-radius: 5px;
                                            background-color: red;
                                        }

                                        .custom_table3 tr {
                                            border-bottom: 1px solid #f1f2f3;
                                        }

                                        .table tr th {
                                            background-color: #fafafa !important;
                                        }

                                        .table thead th {
                                            vertical-align: bottom;
                                        }

                                        .table.custom_table3 thead tr th {
                                            font-weight: 600;
                                            border-top: 0;
                                            font-family: Cerebri Sans;
                                            padding: 15px 30px 15px 0;
                                        }

                                        .table.custom_table3 tbody tr td,
                                        .table.custom_table3 thead tr th {
                                            font-size: 16px;
                                            color: #373737;
                                            white-space: nowrap;
                                        }

                                        th p span,
                                        td p span {
                                            color: #212E40;
                                        }

                                        .text-right {
                                            text-align: right !important;
                                        }
                                    }
                                </style>
                                <table style=" margin-bottom: 30px" class="table">
                                    <tbody>
                                    <td>
                                        <img style="width: 108px" src="{{getCourseImage(Settings('logo') )}}"
                                             alt="{{ Settings('site_name')  }}">
                                    </td>
                                    <td style="text-align: right">
                                        <h3 class="invoice_no black_color" style=" margin-bottom: 10px" ;>
                                            INV-{{$enroll->id+1000}}</h3>
                                    </td>
                                    </tbody>
                                </table>

                                <table style="margin-bottom: 0 !important;" class="table">
                                    <tbody>
                                    <tr>
                                        <td class="w-50">
                                            <p class="invoice_grid"
                                               style="font-size:14px; font-weight: 400; color:#3C4777;">
                                                <span
                                                    class="black_color">{{__('student.Date')}}: </span><span>{{date('d F Y',strtotime(@$enroll->created_at))}}</span>
                                            </p>
                                            <p class="invoice_grid"
                                               style="font-size:14px; font-weight: 400; color:#3C4777;">
                                                <span
                                                    class="black_color">{{__('student.Pay Method')}}: </span><span>{{$enroll->payment_method}}</span>
                                            </p>
                                            <p class="invoice_grid"
                                               style="font-size:14px; font-weight: 400; color:#3C4777;">
                                                @if($enroll->courses->sum('purchase_price') == 0 )
                                                    <span class="black_color">{{__('student.Status')}}: </span>
                                                    <span class="black_color">{{__('common.Paid')}}</span></p>
                                            @else
                                                <span class="black_color">{{__('student.Status')}}: </span>
                                                <span
                                                    class="black_color">{{$enroll->status==1?__('student.Paid'):__('student.Unpaid')}}</span></p>
                                            @endif
                                        </td>
                                        <td>
                                            <p class="invoice_grid"
                                               style="font-size:14px; font-weight: 400; color:#3C4777;">
                                                <span
                                                    class="black_color">{{__('student.Company')}}: </span><span>{{Settings('site_title') }}</span>
                                            </p>
                                            <p class="invoice_grid"
                                               style="font-size:14px; font-weight: 400; color:#3C4777;">
                                                <span
                                                    class="black_color">{{__('student.Phone')}}: </span><span>{{Settings('phone') }}</span>
                                            </p>
                                            <p class="invoice_grid"
                                               style="font-size:14px; font-weight: 400; color:#3C4777;">
                                                <span
                                                    class="black_color">{{__('student.Email')}}: </span><span>{{Settings('email') }}</span>
                                            </p>
                                            <p class="invoice_grid"
                                               style="font-size:14px; font-weight: 400; color:#3C4777;">
                                                <span
                                                    class="black_color">{{__('student.Address')}}: </span><span>{{Settings('address') }}</span>
                                            </p>
                                        </td>
                                    </tr>


                                    </tbody>
                                </table>
                                <h4 style=" font-size: 16px; font-weight: 500; color: #000000; margin-top: 0; margin-bottom: 3px "
                                    class="black_color"
                                    ;>{{__('student.Billed To')}},</h4>

                                <table style="margin-bottom: 35px !important;" class="table">
                                    <tbody>
                                    <td>
                                        <p class="invoice_grid"
                                           style="font-size:14px; font-weight: 400; color:#3C4777;">
                                            <span
                                                class="black_color">{{__('student.Name')}}: </span><span> {{@$enroll->bill->first_name}} {{@$enroll->bill->last_name}}</span>
                                        </p>
                                        <p class="invoice_grid"
                                           style="font-size:14px; font-weight: 400; color:#3C4777;">
                                            <span
                                                class="black_color">{{__('student.Phone')}}: </span><span> {{@$enroll->bill->phone}} </span>
                                        </p>
                                        <p class="invoice_grid"
                                           style="font-size:14px; font-weight: 400; color:#3C4777;">
                                            <span
                                                class="black_color">{{__('student.Email')}}: </span><span> {{@$enroll->bill->email}} </span>
                                        </p>
                                        <p class="invoice_grid"
                                           style="font-size:14px; font-weight: 400; color:#3C4777;">
                                            <span class="black_color">{{__('student.Address')}}: </span>
                                            <span class="black_color">
                                            {{@$enroll->bill->address2}}
                                                {{@$enroll->bill->city}}, {{@$enroll->bill->zip_code}}
                                                {{@$enroll->bill->country}}
                                            </span>
                                        </p>
                                    </td>
                                    </tbody>
                                </table>
                                <h2 style=" font-size: 18px; font-weight: 500; color: #000000; margin-top: 70px; margin-bottom: 33px "
                                    class="black_color"
                                    ;>{{__('student.Order List')}}</h2>

                                <table class="table custom_table3 mb-0">
                                    <thead>
                                    <tr>
                                        <th scope="col" style="text-align: left">
                                            <span class="pl-3">
                                            {{__('common.SL')}}
                                            </span>
                                        </th>
                                        <th colspan="2" scope="col" style="text-align: left"
                                            class="black_color">{{__('student.Course name')}}</th>
                                        <th colspan="2" scope="col" style="text-align: left"
                                            class="black_color">{{__('student.Price')}}</th>
                                        {{--                                        <th scope="col" class="black_color">{{__('common.Discount')}}</th>--}}
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @php
                                        $total =0;
                                        $language_code=auth()->user()->language_code??'en';
                                        $sl=1;
                                    @endphp
                                    @if(isset($enroll->courses))
                                        @foreach($enroll->courses as $key=>$item)

                                            <tr>
                                                <td class="black_color">
                                                 <span class="pl-3">
                                           {{$sl++}}
                                                 </span>
                                                </td>
                                                <td colspan="2" style="text-align: left">
                                                    <h5 class="black_color">   {{@$item->course->getTranslation('title',$language_code)}}</h5>
                                                </td>
                                                {{--                                                <td class="black_color">--}}
                                                {{--                                                    @if($item->course->discount_price!=0)--}}
                                                {{--                                                        {{getPriceFormat($item->course->discount_price)}}--}}
                                                {{--                                                    @endif--}}

                                                {{--                                                </td>--}}
                                                @php
                                                    $price =$item->course->discount_price!=0?$item->course->discount_price:$item->course->price;
                                                        $total =$total+$price;
                                                @endphp
                                                <td style="text-align: left"
                                                    class="black_color">{{getPriceFormat($price)}}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    @if($enroll->courses->count() < $enroll->cart_count)
                                        @foreach ($enroll->gifts as $gift)
                                            <tr>
                                                <td class="black_color">
                                                    <span class="pl-3">
                                                   {{$sl++}}
                                                    </span>
                                                </td>
                                                <td colspan="2" style="text-align: left">
                                                    <h5 class="black_color">
                                                        {{@$gift->course->getTranslation('title',$language_code)}}
                                                    </h5>
                                                </td>
                                                @php
                                                    $price =$gift->course->discount_price!=0?$gift->course->discount_price:$gift->course->price;
                                                        $total =$total+$price;
                                                @endphp
                                                <td style="text-align: left"
                                                    class="black_color">{{getPriceFormat($gift->price)}}</td>
                                            </tr>
                                        @endforeach
                                    @endif


                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td class="text-right">{{__('student.Sub Total')}}</td>
                                        <td>{{getPriceFormat($total)}}</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td class="text-right">{{__('common.Discount')}}</td>
                                        @if($enroll->discount==0)
                                            <td>0</td>
                                        @else
                                            <td>{{getPriceFormat($enroll->discount)}}</td>
                                        @endif
                                    </tr>
                                    @if(hasTax())
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td class="text-right">{{__('tax.TAX')}}</td>
                                            <td>{{$enroll->tax==0?0:getPriceFormat($enroll->tax)}}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td class="text-right">{{__('student.Total')}}</td>
                                        <td>{{getPriceFormat($enroll->purchase_price)}}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- invoice print part end -->
                </div>
            </div>
        </div>
    </section>
    <div id="editor"></div>
</div>
