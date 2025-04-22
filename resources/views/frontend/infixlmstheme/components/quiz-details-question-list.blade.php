<div>
    <style>
        .list-group-item2 {
            padding: 10px;
            margin: 5px;
            border: 2px solid #ccc;
        }
    </style>

    <div class="question_list d-none">

        @foreach($quiz->assign as $key=>$assign)
            @php
                $qus= $assign->questionBank;
            @endphp
            <div class="card" id="question{{$qus->id}}">
                <div class="card-header">
                    <b>{{__('quiz.Question')}} {{++$key}}</b>
                </div>
                <div class="card-body">
                    <p class="card-text">{!! $qus->question !!}</p>
                    <a href="#" class="btn theme_btn_mini hide_show_btn"
                       data-id="{{$qus->id}}" data-type="check">{{__('quiz.Check')}}</a>
                    <a href="#" class="btn theme_btn_mini hide_show_btn"
                       data-id="{{$qus->id}}" data-type="hide">{{__('quiz.Hide Answer')}}</a>

                    <div class="answer{{$qus->id}} d-none list mt-4">
                        <ul class="">
                            @foreach($qus->questionMuInSerial as $option)
                                <li class="list-group-item2 list-option" id="list_option{{$option->id}}">
                                    <label
                                        class="primary_checkbox2 d-flex ">
                                        <input id="option{{$option->id}}"
                                               disabled
                                               type="checkbox">
                                        <span
                                            class="checkmark mr_10"></span>
                                        <span
                                            class="label_name">{{$option->title}}

                                                                           <span
                                                                               class="mr_10  ti-close text-danger d-none"></span>
                                                                           <span
                                                                               class="mr_10  ti-check text-success d-none"></span>
                                                                        </span>
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="card-footer answer{{$qus->id}} d-none ">
                    <b>
                        {{__('quiz.Explanation')}}
                    </b>
                </div>
                <div class="card-body answer{{$qus->id}} d-none">
                    <p class="card-text">
                        {!! $qus->explanation !!}
                    </p>

                </div>
            </div>
        @endforeach

    </div>


    @include(theme('partials._quiz_exp_script'))
</div>
