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
            <div class="section-row">
                <div class="filters"></div>
                <a class="btn btn-gold" href="{{ route('roles.create') }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>
                    <span class="bn">নতুন রোল</span><span class="en">New Role</span>
                </a>
            </div>

            <div class="helper" style="margin-top:0; margin-bottom:14px;">
                <span class="bn">এখানে শুধু আপনার দোকানের জন্য তৈরি কাস্টম রোল দেখানো হয়। "Admin" রোলটি সব পারমিশনসহ পূর্বনির্ধারিত।</span>
                <span class="en" style="display:none;">Only custom roles created for your shop are shown here. The "Admin" role is predefined with all permissions.</span>
            </div>

            <div class="mini-grid">
                @forelse ($roles as $role)
                    <div class="mini-card pm-card">
                        <div class="mini-card-actions">
                            <a class="act" title="Edit" href="{{ route('roles.edit', $role) }}">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" stroke="#5C6B65" stroke-width="1.5" stroke-linejoin="round"/></svg>
                            </a>
                            <form method="POST" action="{{ route('roles.destroy', $role) }}" onsubmit="return confirm('এই রোলটি মুছে ফেলতে চান?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="act" title="Delete">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M4 7h16M9 7V4h6v3m-8 0 1 13h8l1-13" stroke="#C1443C" stroke-width="1.5" stroke-linejoin="round"/></svg>
                                </button>
                            </form>
                        </div>
                        <div class="nm">{{ $role->name }}</div>
                        <div class="sub">
                            <span class="bn">{{ $role->permissions_count }}টি পারমিশন &middot; {{ $role->users_count }} জন ইউজার</span>
                            <span class="en" style="display:none;">{{ $role->permissions_count }} permissions &middot; {{ $role->users_count }} users</span>
                        </div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1;">
                        <x-core::table.empty
                            icon="shield"
                            title="কোনো কাস্টম রোল নেই"
                            title-en="No custom roles found"
                        />
                    </div>
                @endforelse
            </div>

            <div style="margin-top:14px;">
                {{ $roles->links() }}
            </div>
        </div>
    </div>
</x-core::layout>
