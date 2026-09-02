<x-core::layout
    title="মডেল"
    title-en="Model"
    subtitle="পণ্যের মডেল পরিচালনা করুন"
    subtitle-en="Manage product models"
    active="products"
>
    <x-product::tabbar active="models" />

    <div class="panel" style="margin-top:0;">
        <div class="panel-body">
            <div class="section-row">
                <div class="filters"></div>
                <a class="btn btn-gold" href="{{ route('models.create') }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>
                    <span class="bn">নতুন মডেল</span><span class="en">New Model</span>
                </a>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="bn">মডেলের নাম</th><th class="en" style="display:none;">Model Name</th>
                            <th class="bn">ব্র্যান্ড</th><th class="en" style="display:none;">Brand</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($models as $model)
                            <tr>
                                <td class="cell-main">{{ $model->name }}</td>
                                <td>{{ $model->brand->name ?? '—' }}</td>
                                <td>
                                    <div class="row-actions">
                                        <a class="act" title="Edit" href="{{ route('models.edit', $model) }}">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" stroke="#5C6B65" stroke-width="1.5" stroke-linejoin="round"/></svg>
                                        </a>
                                        <form method="POST" action="{{ route('models.destroy', $model) }}" onsubmit="return confirm('এই মডেল মুছে ফেলতে চান?');">
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
                                <td colspan="3">
                                    <x-core::table.empty
                                        icon="layers"
                                        title="কোনো মডেল নেই"
                                        title-en="No models found"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:14px;">
                {{ $models->links() }}
            </div>
        </div>
    </div>
</x-core::layout>
