<?php

namespace Modules\Employee\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Modules\Employee\Models\Employee;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class EmployeesDataTable extends BaseDataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Employee>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('name', function (Employee $employee) {
                $initial = mb_strtoupper(mb_substr($employee->name ?: 'E', 0, 1));
                $code = '<span style="font-size:11px; font-family:var(--font-mono, monospace); color:var(--ink-400);">#EMP-'.str_pad((string) $employee->id, 4, '0', STR_PAD_LEFT).'</span>';

                $userBadge = '';
                if ($employee->user_id) {
                    $userBadge = '<span style="display:inline-block; font-size:10px; font-weight:700; padding:1px 6px; border-radius:4px; background:var(--blue-100); color:var(--blue-ink); border:1px solid var(--blue-ic-bg); margin-left:4px;" title="সিস্টেম লগইন অ্যাক্সেস সক্রিয়">'
                        .'<span class="bn">ইউজার</span><span class="en" style="display:none;">User</span>'
                        .'</span>';
                }

                return '<div class="row-avatar" style="display:flex; align-items:center; gap:10px;">'
                    .'<div class="av" style="width:36px; height:36px; border-radius:8px; background:var(--teal-700); color:#ffffff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:14px; flex-shrink:0;">'.e($initial).'</div>'
                    .'<div style="min-width:0;">'
                    .'<div style="display:flex; align-items:center; gap:4px; flex-wrap:wrap;">'
                    .'<span style="font-weight:700; color:var(--ink-900); font-size:13.5px;">'.e($employee->name).'</span>'
                    .$userBadge
                    .'</div>'
                    .'<div style="margin-top:2px;">'.$code.'</div>'
                    .'</div>'
                    .'</div>';
            })
            ->addColumn('contact', function (Employee $employee) {
                $phone = $employee->phone
                    ? '<a href="tel:'.e($employee->phone).'" style="font-size:12px; font-family:var(--font-mono, monospace); font-weight:600; color:var(--ink-800); text-decoration:none; display:flex; align-items:center; gap:5px;">'
                        .'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.6; flex-shrink:0;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>'
                        .'<span>'.e($employee->phone).'</span>'
                        .'</a>'
                    : '';

                $email = $employee->email
                    ? '<a href="mailto:'.e($employee->email).'" style="font-size:11.5px; color:var(--ink-500); text-decoration:none; display:flex; align-items:center; gap:5px; margin-top:2px;">'
                        .'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.6; flex-shrink:0;"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>'
                        .'<span>'.e($employee->email).'</span>'
                        .'</a>'
                    : '';

                if (! $phone && ! $email) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                return '<div>'.$phone.$email.'</div>';
            })
            ->addColumn('designation_department', function (Employee $employee) {
                $designation = '<div style="font-weight:600; color:var(--ink-900); font-size:13px;">'.e($employee->designation ?: '—').'</div>';

                $dept = $employee->department
                    ? '<div style="font-size:11.5px; color:var(--ink-500); display:flex; align-items:center; gap:4px; margin-top:2px;">'
                        .'<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.6; flex-shrink:0;"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/></svg>'
                        .'<span>'.e($employee->department).'</span>'
                        .'</div>'
                    : '';

                return '<div>'.$designation.$dept.'</div>';
            })
            ->editColumn('salary', function (Employee $employee) {
                $formatted = number_format((float) $employee->salary, 2);

                return '<div>'
                    .'<span style="font-family:var(--font-mono, monospace); font-weight:700; font-size:13px; color:var(--ink-900);">৳'.$formatted.'</span>'
                    .'<div style="font-size:11px; color:var(--ink-400); margin-top:2px;"><span class="bn">মাসিক বেতন</span><span class="en" style="display:none;">Monthly</span></div>'
                    .'</div>';
            })
            ->editColumn('joining_date', function (Employee $employee) {
                if (! $employee->joining_date) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                $date = $employee->joining_date->format('d M, Y');
                $diff = $employee->joining_date->diffForHumans();

                return '<div style="font-size:12.5px; font-weight:600; color:var(--ink-800); white-space:nowrap;">'.e($date).'</div>'
                    .'<div style="font-size:11px; color:var(--ink-400); white-space:nowrap;">'.e($diff).'</div>';
            })
            ->editColumn('status', function (Employee $employee) {
                if ($employee->status === 'active') {
                    return '<span style="display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:600; padding:2px 8px; border-radius:4px; background:var(--green-100); color:var(--green-ink);">'
                        .'<span style="width:6px; height:6px; border-radius:50%; background:currentColor;"></span>'
                        .'<span class="bn">সক্রিয়</span><span class="en" style="display:none;">Active</span>'
                        .'</span>';
                }

                return '<span style="display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:600; padding:2px 8px; border-radius:4px; background:var(--paper-line); color:var(--ink-500);">'
                    .'<span style="width:6px; height:6px; border-radius:50%; background:currentColor;"></span>'
                    .'<span class="bn">নিষ্ক্রিয়</span><span class="en" style="display:none;">Inactive</span>'
                    .'</span>';
            })
            ->addColumn('action', function (Employee $employee) {
                return view('employee::datatables-actions', compact('employee'))->render();
            })
            ->rawColumns(['name', 'contact', 'designation_department', 'salary', 'joining_date', 'status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Employee>
     */
    public function query(Employee $model): QueryBuilder
    {
        $shopId = auth()->user()?->shop_id;

        $query = $model->newQuery()
            ->when($shopId, fn ($q) => $q->where('employees.shop_id', $shopId))
            ->with(['user'])
            ->select([
                'employees.id',
                'employees.shop_id',
                'employees.user_id',
                'employees.name',
                'employees.phone',
                'employees.email',
                'employees.designation',
                'employees.department',
                'employees.salary',
                'employees.joining_date',
                'employees.address',
                'employees.status',
                'employees.created_at',
                'employees.updated_at',
            ]);

        // Filter by status
        if ($status = request('status')) {
            if ($status !== 'all' && $status !== '') {
                $query->where('employees.status', $status);
            }
        }

        // Filter by department
        if ($dept = request('department')) {
            if ($dept !== 'all' && $dept !== '') {
                $query->where('employees.department', $dept);
            }
        }

        // Filter by designation
        if ($desig = request('designation')) {
            if ($desig !== 'all' && $desig !== '') {
                $query->where('employees.designation', $desig);
            }
        }

        // Filter by date range (joining_date)
        if ($from = request('date_from')) {
            $query->whereDate('employees.joining_date', '>=', $from);
        }
        if ($to = request('date_to')) {
            $query->whereDate('employees.joining_date', '<=', $to);
        }

        return $query;
    }

    /**
     * Configure HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->defaultHtml()
            ->setTableId('employees-data-table')
            ->minifiedAjax('', 'data.status = $("#filter-status").val(); data.department = $("#filter-department").val(); data.designation = $("#filter-designation").val(); data.date_from = $("#filter-date-from").val(); data.date_to = $("#filter-date-to").val();');
    }

    /**
     * Get the dataTable columns definition.
     *
     * @return array<int, Column>
     */
    public function getColumns(): array
    {
        return [
            Column::make('name')
                ->title('<span class="bn">কর্মচারী / নাম</span><span class="en">Employee / Name</span>')
                ->width(220),
            Column::computed('contact')
                ->title('<span class="bn">যোগাযোগ</span><span class="en">Contact</span>')
                ->width(180),
            Column::computed('designation_department')
                ->title('<span class="bn">পদবি ও বিভাগ</span><span class="en">Designation & Dept</span>')
                ->width(180),
            Column::make('salary')
                ->title('<span class="bn">বেতন</span><span class="en">Salary</span>')
                ->width(130),
            Column::make('joining_date')
                ->title('<span class="bn">যোগদানের তারিখ</span><span class="en">Joining Date</span>')
                ->width(140),
            Column::make('status')
                ->title('<span class="bn">অবস্থা</span><span class="en">Status</span>')
                ->addClass('table-cell-center')
                ->width(90),
            Column::computed('action')
                ->title('<span class="bn">অ্যাকশন</span><span class="en">Action</span>')
                ->orderable(false)
                ->searchable(false)
                ->exportable(false)
                ->printable(false)
                ->width(120)
                ->addClass('table-cell-right'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Employees_'.date('YmdHis');
    }
}
