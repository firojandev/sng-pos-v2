<div class="row-actions" style="display:flex; align-items:center; justify-content:flex-end; gap:6px;">
    @can('employees.view')
        <x-core::button
            size="sm"
            variant="soft"
            color="teal"
            icon="user"
            :href="route('employees.profile.edit', $employee)"
            title="পূর্ণ প্রোফাইল / Full Profile"
        />
    @endcan

    @can('employees.edit')
        <x-core::button
            size="sm"
            variant="secondary"
            icon="edit"
            class="btn-edit-employee"
            data-id="{{ $employee->id }}"
            :href="route('employees.edit', $employee)"
            title="সম্পাদনা / Edit"
        />
    @endcan

    @can('employees.delete')
        <form method="POST" action="{{ route('employees.destroy', $employee) }}" class="delete-form" data-title="কর্মচারী মুছে ফেলতে চান?" data-text="এই কর্মচারীর সম্পূর্ণ রেকর্ড মুছে ফেলা হবে।" style="display:inline-block; margin:0;">
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
    @endcan
</div>
