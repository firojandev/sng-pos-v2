<x-core::button-group size="xs" aria-label="Company Actions">
    <x-core::button
        :href="route('companies.edit', $company)"
        variant="soft"
        color="primary"
        icon="edit"
        icon-only
        title="সম্পাদনা / Edit"
    />
</x-core::button-group>
