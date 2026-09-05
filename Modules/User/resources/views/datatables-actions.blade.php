<div class="row-actions" style="display:flex; align-items:center; justify-content:flex-end; gap:6px;">
    <x-core::button
        size="sm"
        variant="secondary"
        icon="edit"
        class="btn-edit-user"
        data-id="{{ $user->id }}"
        :href="route('users.edit', $user)"
        title="সম্পাদনা / Edit"
    />

    @if ($user->id === auth()->id())
        <span style="display:inline-flex; align-items:center; font-size:11px; font-weight:600; color:var(--ink-400); padding:3px 8px; border-radius:6px; background:var(--paper-line); border:1px solid var(--border); white-space:nowrap;" title="নিজের অ্যাকাউন্ট মুছে ফেলা যাবে না / Cannot delete your own account">
            <span class="bn">লগইন ইউজার</span>
            <span class="en" style="display:none;">Self</span>
        </span>
    @else
        <form method="POST" action="{{ route('users.destroy', $user) }}" class="delete-form" data-title="ইউজার মুছে ফেলতে চান?" data-text="এই ব্যবহারকারীর অ্যাকাউন্ট ও এক্সেস মুছে ফেলা হবে।" style="display:inline-block; margin:0;">
            @csrf
            @method('DELETE')
            <x-core::button
                type="submit"
                size="sm"
                variant="soft"
                color="danger"
                icon="trash-2"
                title="মুছে ফেলুন / Delete"
            />
        </form>
    @endif
</div>
