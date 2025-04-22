<?php

namespace App\Http\Livewire;

use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;
use Modules\OrgSubscription\Entities\OrgCourseSubscription;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Traits\WithFilters;
use Rappasoft\LaravelLivewireTables\Traits\WithSearch;
use Rappasoft\LaravelLivewireTables\Views\Column;

class ShowAutoEnrollmentPath extends DataTableComponent
{
    public array $bulkActions = [
        'exportSelected' => 'Export',
    ];
    public bool $columnSelect = true;
    public bool $rememberColumnSelection = true;
    public string $tableName = 'org_course_subscriptions';
    use WithSearch;
    use WithFilters;
    use WithPagination;

    public $page = 1;
    public $plan = null;


    protected $listeners = ['refreshDatatable' => '$refresh'];


    public function mount()
    {
        $this->plan = null;
        $this->emit('refreshDatatable');
    }


    public function columns(): array
    {
        return [
            Column::make(__('common.Title'), 'title')
                ->sortable()
                ->searchable(),


            Column::make(__('org-subscription.Duration'), 'days')
                ->sortable()
                ->searchable(),

            Column::make(__('org.Created by'), 'created_by')
                ->sortable()
                ->searchable(),
            Column::make(__('org.Created date'), 'created_at')
                ->sortable()
                ->searchable(),


        ];
    }

    public function builder(): Builder
    {
        $query = OrgCourseSubscription::with('createdBy')->where('status', 1)->latest();
        if (!empty($this->plan)) {
            $query->where('type', $this->plan);
        }
        return $query;
    }

//    public function rowView(): string
//    {
//        $this->emptyMessage = trans("common.No data available in the table");
//        return 'livewire.org-auto-enrollment-plan.row';
//    }
//
//    public function paginationView()
//    {
//        return 'backend.partials._pagination';
//    }


    public function render()
    {

        return view('livewire.org-auto-enrollment-plan.datatable')
            ->with([
                'columns' => $this->columns(),
                'rowView' => $this->rowView(),
                'filtersView' => $this->filtersView(),
                'customFilters' => $this->filters(),
                'rows' => $this->rows,
                'modalsView' => $this->modalsView(),
                'bulkActions' => $this->bulkActions,
            ]);
    }

    public function configure(): void
    {
        $this->setPrimaryKey('id');
    }
}
