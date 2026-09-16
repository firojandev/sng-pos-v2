<x-core::button-group size="xs" aria-label="Transfer Actions">
    @can('account-transfers.delete')
        <form
            method="POST"
            action="{{ route('account-transfers.destroy', $transfer) }}"
            class="delete-form"
            data-title="ট্রান্সফার বাতিল করবেন?"
            data-text="এই ট্রান্সফারটি বাতিল করতে চান? সংশ্লিষ্ট অ্যাকাউন্টের ব্যালেন্স আগের অবস্থায় ফিরে যাবে।"
        >
            @csrf
            @method('DELETE')
            <x-core::button
                type="submit"
                variant="soft"
                color="danger"
                icon="trash-2"
                icon-only
                title="বাতিল করুন / Delete"
            />
        </form>
    @endcan
</x-core::button-group>
