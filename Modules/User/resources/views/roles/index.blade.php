<x-core::layout
    title="রোল ও পারমিশন"
    title-en="Roles & Permissions"
    subtitle="দোকানের ইউজারদের জন্য রোল তৈরি ও পরিচালনা করুন"
    subtitle-en="Create and manage roles for your shop's users"
    active="users"
>
    <x-user::tabbar active="roles" />

    <div class="panel" style="margin-top:0;">
        <div class="panel-body">
            <div class="section-row" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                <div class="filters"></div>
                <x-core::button size="sm" color="primary" icon="plus" href="{{ route('roles.create') }}">
                    <span class="bn">নতুন রোল</span><span class="en" style="display:none;">New Role</span>
                </x-core::button>
            </div>

            <div class="helper" style="margin-top:0; margin-bottom:14px; color:var(--ink-600);">
                <span class="bn">এখানে আপনার দোকানের জন্য নির্ধারিত রোলগুলো দেখানো হচ্ছে। প্রয়োজন অনুযায়ী প্রতিটি রোলের পারমিশন পরিবর্তন করতে পারেন।</span>
                <span class="en" style="display:none;">Roles configured for your shop are listed here. You can customize permissions for each role as needed.</span>
            </div>

            <div class="mini-grid">
                @forelse ($roles as $role)
                    <div class="mini-card pm-card" style="position:relative; background:var(--card); border:1px solid var(--border); border-radius:8px; padding:14px 16px;">
                        <div class="mini-card-actions" style="position:absolute; top:12px; right:12px; display:flex; align-items:center; gap:6px;">
                            <x-core::button
                                tag="a"
                                size="sm"
                                variant="ghost"
                                color="secondary"
                                icon="edit"
                                href="{{ route('roles.edit', $role) }}"
                                title="Edit"
                            />
                            @if ($role->name !== 'Admin')
                                <form method="POST" action="{{ route('roles.destroy', $role) }}" class="delete-form" data-title="রোল মুছে ফেলতে চান?" data-text="এই রোলটি স্থায়ীভাবে মুছে ফেলা হবে।">
                                    @csrf
                                    @method('DELETE')
                                    <x-core::button
                                        type="submit"
                                        size="sm"
                                        variant="ghost"
                                        color="danger"
                                        icon="trash-2"
                                        title="Delete"
                                    />
                                </form>
                            @endif
                        </div>
                        <div class="nm" style="font-weight:600; font-size:15px; color:var(--ink-900);">
                            {{ $role->name }}
                            @if ($role->name === 'Admin')
                                <x-core::badge size="xs" color="primary" variant="subtle" style="margin-left:6px;">
                                    <span class="bn">ডিফল্ট</span><span class="en" style="display:none;">Default</span>
                                </x-core::badge>
                            @endif
                        </div>
                        <div class="sub" style="color:var(--ink-600); font-size:12px; margin-top:6px;">
                            <span class="bn">{{ $role->permissions_count }}টি পারমিশন &middot; {{ $role->users_count }} জন ইউজার</span>
                            <span class="en" style="display:none;">{{ $role->permissions_count }} permissions &middot; {{ $role->users_count }} users</span>
                        </div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1;">
                        <x-core::table.empty
                            icon="shield"
                            title="কোনো রোল পাওয়া যায়নি"
                            title-en="No roles found"
                        />
                    </div>
                @endforelse
            </div>

            @if ($roles->hasPages())
                <div style="margin-top:14px;">
                    {{ $roles->links() }}
                </div>
            @endif
        </div>
    </div>
</x-core::layout>
