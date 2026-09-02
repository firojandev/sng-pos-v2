<div class="table-cell-actions">
    <a href="{{ route('plans.edit', $plan) }}" class="btn btn-soft-teal btn-xs" title="Edit">
        <x-core::icon name="edit" size="13" />
        <span class="bn">এডিট</span><span class="en">Edit</span>
    </a>
    <form method="POST" action="{{ route('plans.destroy', $plan) }}" class="delete-plan-form" style="display:inline;" onsubmit="return confirm('এই প্ল্যান মুছে ফেলতে চান? / Are you sure you want to delete this plan?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-soft-red btn-xs" title="Delete">
            <x-core::icon name="trash" size="13" />
            <span class="bn">মুছুন</span><span class="en">Delete</span>
        </button>
    </form>
</div>
