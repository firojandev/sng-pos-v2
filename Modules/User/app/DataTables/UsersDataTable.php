<?php

namespace Modules\User\DataTables;

use App\DataTables\BaseDataTable;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class UsersDataTable extends BaseDataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<User>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('name', function (User $user) {
                $initial = mb_strtoupper(mb_substr($user->name ?: 'U', 0, 1));
                $code = '<span style="font-size:11px; font-family:var(--font-mono, monospace); color:var(--ink-400);">#USR-'.str_pad((string) $user->id, 4, '0', STR_PAD_LEFT).'</span>';

                $badges = '';
                if ($user->id === auth()->id()) {
                    $badges .= '<span style="display:inline-block; font-size:10px; font-weight:700; padding:1px 6px; border-radius:4px; background:var(--teal-100); color:var(--teal-800); border:1px solid var(--teal-200); margin-left:4px;"><span class="bn">আপনি</span><span class="en" style="display:none;">You</span></span>';
                }

                $shopUser = $user->shops->first();
                if ($shopUser && $shopUser->pivot && $shopUser->pivot->is_owner) {
                    $badges .= '<span style="display:inline-block; font-size:10px; font-weight:700; padding:1px 6px; border-radius:4px; background:var(--gold-100); color:var(--gold-ink); border:1px solid var(--gold-200); margin-left:4px;"><span class="bn">মালিক</span><span class="en" style="display:none;">Owner</span></span>';
                }

                if ($user->isSuperAdmin()) {
                    $badges .= '<span style="display:inline-block; font-size:10px; font-weight:700; padding:1px 6px; border-radius:4px; background:var(--blue-100); color:var(--blue-ink); border:1px solid var(--blue-ic-bg); margin-left:4px;"><span class="bn">সুপার অ্যাডমিন</span><span class="en" style="display:none;">Super Admin</span></span>';
                }

                return '<div class="row-avatar" style="display:flex; align-items:center; gap:10px;">'
                    .'<div class="av" style="width:36px; height:36px; border-radius:8px; background:var(--teal-700); color:#ffffff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:14px; flex-shrink:0;">'.e($initial).'</div>'
                    .'<div style="min-width:0;">'
                    .'<div style="display:flex; align-items:center; gap:4px; flex-wrap:wrap;">'
                    .'<span style="font-weight:700; color:var(--ink-900); font-size:13.5px;">'.e($user->name).'</span>'
                    .$badges
                    .'</div>'
                    .'<div style="margin-top:2px;">'.$code.'</div>'
                    .'</div>'
                    .'</div>';
            })
            ->editColumn('email', function (User $user) {
                $emailLink = '<a href="mailto:'.e($user->email).'" style="font-size:12.5px; font-weight:600; color:var(--ink-800); text-decoration:none; display:inline-flex; align-items:center; gap:5px;">'
                    .'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.6; flex-shrink:0;"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>'
                    .'<span>'.e($user->email).'</span>'
                    .'</a>';

                if ($user->email_verified_at) {
                    $statusBadge = '<span style="display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:600; padding:1px 6px; border-radius:4px; background:var(--green-100); color:var(--green-ink); margin-top:3px;">'
                        .'<span style="width:6px; height:6px; border-radius:50%; background:currentColor;"></span>'
                        .'<span class="bn">ভেরিফাইড</span><span class="en" style="display:none;">Verified</span>'
                        .'</span>';
                } else {
                    $statusBadge = '<span style="display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:600; padding:1px 6px; border-radius:4px; background:var(--gold-100); color:var(--gold-ink); margin-top:3px;">'
                        .'<span style="width:6px; height:6px; border-radius:50%; background:currentColor;"></span>'
                        .'<span class="bn">অযাচাইকৃত</span><span class="en" style="display:none;">Unverified</span>'
                        .'</span>';
                }

                return '<div>'.$emailLink.'<div>'.$statusBadge.'</div></div>';
            })
            ->addColumn('role', function (User $user) {
                if ($user->roles->isEmpty()) {
                    return '<span style="display:inline-block; font-size:11px; font-weight:600; padding:2px 7px; border-radius:4px; background:var(--paper-line); color:var(--ink-500);">'
                        .'<span class="bn">রোল নেই</span><span class="en" style="display:none;">No Role</span>'
                        .'</span>';
                }

                $roleBadges = [];
                foreach ($user->roles as $role) {
                    $isAdmin = $role->name === 'Admin' || $role->name === 'Super Admin';
                    $badgeStyle = $isAdmin
                        ? 'background:var(--teal-800); color:#ffffff; font-weight:700;'
                        : 'background:var(--blue-100); color:var(--blue-ink); border:1px solid var(--blue-ic-bg); font-weight:600;';

                    $permCount = $role->permissions->count();
                    $permHtml = $permCount > 0
                        ? '<span style="font-size:10px; opacity:0.85; margin-left:4px;">('.$permCount.')</span>'
                        : '';

                    $roleBadges[] = '<span style="display:inline-flex; align-items:center; font-size:11.5px; padding:2px 8px; border-radius:5px; margin:2px 3px 2px 0; '.$badgeStyle.'">'
                        .e($role->name).$permHtml
                        .'</span>';
                }

                return '<div style="display:flex; flex-wrap:wrap; align-items:center;">'.implode('', $roleBadges).'</div>';
            })
            ->addColumn('employee_info', function (User $user) {
                $employee = $user->employee;
                if (! $employee) {
                    return '<span style="font-size:12px; color:var(--ink-400); font-style:italic;">'
                        .'<span class="bn">সিস্টেম ইউজার</span><span class="en" style="display:none;">System User</span>'
                        .'</span>';
                }

                $designation = $employee->designation
                    ? '<div style="font-size:12.5px; font-weight:600; color:var(--ink-800);">'.e($employee->designation).'</div>'
                    : '';

                $sub = [];
                if ($employee->department) {
                    $sub[] = e($employee->department);
                }
                if ($employee->phone) {
                    $sub[] = '<span style="font-family:var(--font-mono, monospace);">'.e($employee->phone).'</span>';
                }

                $subHtml = ! empty($sub)
                    ? '<div style="font-size:11px; color:var(--ink-500); margin-top:2px;">'.implode(' &middot; ', $sub).'</div>'
                    : '';

                return '<div>'.($designation ?: '<div style="font-size:12px; color:var(--ink-700);">'.e($employee->name).'</div>').$subHtml.'</div>';
            })
            ->editColumn('created_at', function (User $user) {
                if (! $user->created_at) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                $date = $user->created_at->format('d M, Y');
                $diff = $user->created_at->diffForHumans();

                return '<div style="font-size:12.5px; font-weight:600; color:var(--ink-800); white-space:nowrap;">'.e($date).'</div>'
                    .'<div style="font-size:11px; color:var(--ink-400); white-space:nowrap;">'.e($diff).'</div>';
            })
            ->addColumn('action', function (User $user) {
                return view('user::datatables-actions', compact('user'))->render();
            })
            ->rawColumns(['name', 'email', 'role', 'employee_info', 'created_at', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<User>
     */
    public function query(User $model): QueryBuilder
    {
        $shopId = auth()->user()?->shop_id;

        $query = $model->newQuery()
            ->when($shopId, fn ($q) => $q->where('users.shop_id', $shopId))
            ->with([
                'roles.permissions',
                'employee',
                'shops' => fn ($q) => $shopId ? $q->where('shops.id', $shopId) : $q,
            ])
            ->select([
                'users.id',
                'users.shop_id',
                'users.name',
                'users.email',
                'users.email_verified_at',
                'users.created_at',
                'users.updated_at',
            ]);

        // Filter by role
        if ($role = request('role')) {
            if ($role !== 'all' && $role !== '') {
                $query->whereHas('roles', fn ($q) => $q->where('name', $role));
            }
        }

        // Filter by verification status
        if ($status = request('status')) {
            if ($status === 'verified') {
                $query->whereNotNull('users.email_verified_at');
            } elseif ($status === 'unverified') {
                $query->whereNull('users.email_verified_at');
            }
        }

        // Filter by date range
        if ($from = request('date_from')) {
            $query->whereDate('users.created_at', '>=', $from);
        }
        if ($to = request('date_to')) {
            $query->whereDate('users.created_at', '<=', $to);
        }

        return $query;
    }

    /**
     * Configure HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->defaultHtml()
            ->setTableId('users-data-table')
            ->minifiedAjax('', 'data.role = $("#filter-role").val(); data.status = $("#filter-status").val(); data.date_from = $("#filter-date-from").val(); data.date_to = $("#filter-date-to").val();');
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
                ->title('<span class="bn">ইউজার / নাম</span><span class="en">User / Name</span>')
                ->width(220),
            Column::make('email')
                ->title('<span class="bn">ইমেইল ও ভেরিফিকেশন</span><span class="en">Email & Status</span>')
                ->width(200),
            Column::computed('role')
                ->title('<span class="bn">রোল ও পারমিশন</span><span class="en">Role & Permissions</span>')
                ->width(180),
            Column::computed('employee_info')
                ->title('<span class="bn">সংযুক্ত কর্মী</span><span class="en">Linked Employee</span>')
                ->width(180),
            Column::make('created_at')
                ->title('<span class="bn">যোগদানের তারিখ</span><span class="en">Joined Date</span>')
                ->width(140),
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
        return 'Users_'.date('YmdHis');
    }
}
