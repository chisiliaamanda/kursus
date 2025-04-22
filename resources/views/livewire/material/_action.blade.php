<div class="dropdown CRM_dropdown">
    <button class="btn btn-secondary dropdown-toggle" type="button"
            id="dropdownMenu2" data-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false">
        {{__('common.Action')}}
    </button>
    <div class="dropdown-menu dropdown-menu-right"
         aria-labelledby="dropdownMenu2">

        <a class="dropdown-item" href="{{asset($row->link)}}">{{__('common.View')}}</a>
        @if (permissionCheck('org.material.update'))
            <button data-item="{{$row}}"
                    class="editMaterial dropdown-item"
                    type="button">{{__('common.Edit')}}</button>
        @endif
        @if (permissionCheck('org.material.delete'))
            <button data-id="{{$row->id}}"
                    class="deleteMaterial dropdown-item"
                    type="button">{{__('common.Delete')}}</button>
        @endif


    </div>
</div>
