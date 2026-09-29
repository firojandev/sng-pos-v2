<x-core::layout title="রোল ও পারমিশন" title-en="Roles & Permissions" subtitle="কোম্পানি কর্মচারীদের রোল — এইচআর, পে-রোল, হিসাব ও টাস্কের অনুমতি" subtitle-en="Roles for company employees — permissions for HR, payroll, accounting and tasks" active="company-roles">
    <div class="section-row" style="margin-bottom:16px; display:flex; justify-content:flex-end;">
        <x-core::button as="a" href="{{ route('company.roles.create') }}" size="sm" variant="solid" color="primary" icon="plus"><span class="bn">নতুন রোল</span><span class="en" style="display:none;">New Role</span></x-core::button>
    </div>

    <div class="table-container table-teal">
        <div class="table-responsive">
            <table class="app-table">
                <thead>
                    <tr>
                        <th><span class="bn">রোল</span><span class="en" style="display:none;">Role</span></th>
                        <th class="table-cell-center"><span class="bn">পারমিশন</span><span class="en" style="display:none;">Permissions</span></th>
                        <th class="table-cell-center"><span class="bn">ইউজার</span><span class="en" style="display:none;">Users</span></th>
                        <th class="table-cell-right"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as ['role' => $role, 'users' => $userCount])
                        <tr>
                            <td style="font-weight:600;">{{ $role->name }}</td>
                            <td class="table-cell-center">{{ count($role->permissions ?? []) }}</td>
                            <td class="table-cell-center">{{ $userCount }}</td>
                            <td class="table-cell-right">
                                <div style="display:flex; gap:4px; justify-content:flex-end;">
                                    <x-core::button as="a" href="{{ route('company.roles.edit', $role) }}" size="sm" variant="soft" color="primary" icon="edit" icon-only title="সম্পাদনা / Edit" />
                                    <form method="POST" action="{{ route('company.roles.destroy', $role) }}" class="delete-form" data-title="রোল মুছে ফেলবেন?" data-text="এই রোলের কর্মচারীরা রোল ছাড়া থাকবেন।">
                                        @csrf
                                        @method('DELETE')
                                        <x-core::button type="submit" size="sm" variant="soft" color="danger" icon="trash-2" icon-only title="মুছুন / Delete" />
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-core::table.empty icon="shield" title="কোনো রোল নেই" title-en="No roles yet" description="রোল তৈরি করে কর্মচারীদের দিন।" description-en="Create a role, then give it to employees." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-core::layout>
