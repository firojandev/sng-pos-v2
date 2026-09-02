<x-core::layout
    title="ইউজার"
    title-en="Users"
    subtitle="সিস্টেম লগইন অ্যাকাউন্ট পরিচালনা করুন"
    subtitle-en="Manage system login accounts"
    active="users"
>
    <x-user::tabbar active="users" />

    <div class="panel" style="margin-top:0;">
        <div class="panel-body">
            <div class="section-row">
                <div class="filters"></div>
                <a class="btn btn-gold" href="{{ route('users.create') }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>
                    <span class="bn">নতুন ইউজার</span><span class="en">New User</span>
                </a>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="bn">নাম</th><th class="en" style="display:none;">Name</th>
                            <th class="bn">ইমেইল</th><th class="en" style="display:none;">Email</th>
                            <th class="bn">রোল</th><th class="en" style="display:none;">Role</th>
                            <th class="bn">যোগদানের তারিখ</th><th class="en" style="display:none;">Joined</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>
                                    <div class="row-avatar">
                                        <div class="av" style="background:var(--teal-800);">{{ mb_substr($user->name, 0, 1) }}</div>
                                        <div class="cell-main">{{ $user->name }}</div>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @forelse ($user->roles as $role)
                                        <span class="badge b-teal">{{ $role->name }}</span>
                                    @empty
                                        <span class="badge b-grey bn">রোল নেই</span><span class="badge b-grey en" style="display:none;">No role</span>
                                    @endforelse
                                </td>
                                <td>{{ $user->created_at->format('d M, Y') }}</td>
                                <td>
                                    <div class="row-actions">
                                        <a class="act" title="Edit" href="{{ route('users.edit', $user) }}">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" stroke="#5C6B65" stroke-width="1.5" stroke-linejoin="round"/></svg>
                                        </a>
                                        <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('এই ইউজারকে মুছে ফেলতে চান?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="act" title="Delete">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M4 7h16M9 7V4h6v3m-8 0 1 13h8l1-13" stroke="#C1443C" stroke-width="1.5" stroke-linejoin="round"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-core::table.empty
                                        icon="users"
                                        title="কোনো ব্যবহারকারী নেই"
                                        title-en="No users found"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:14px;">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-core::layout>
