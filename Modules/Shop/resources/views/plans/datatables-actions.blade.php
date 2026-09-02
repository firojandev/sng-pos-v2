<x-core::button-group size="xs" aria-label="Plan Actions">
    <x-core::button
        :href="route('plans.edit', $plan)"
        variant="soft"
        color="teal"
        icon="edit"
        icon-only
        title="এডিট / Edit"
    />
    <form
        method="POST"
        action="{{ route('plans.destroy', $plan) }}"
        class="delete-plan-form"
    >
        @csrf
        @method('DELETE')
        <x-core::button
            type="submit"
            variant="soft"
            color="red"
            icon="trash-2"
            icon-only
            title="মুছুন / Delete"
        />
    </form>
</x-core::button-group>

