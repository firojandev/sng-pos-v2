<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Product\Http\Requests\StoreBatchRequest;
use Modules\Product\Http\Requests\UpdateBatchRequest;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Product;

class BatchController extends Controller
{
    public function index(): View
    {
        $batches = Batch::with('product')->latest()->paginate(10);

        return view('product::batches.index', compact('batches'));
    }

    public function create(): View
    {
        $products = Product::orderBy('name')->get();

        return view('product::batches.create', ['batch' => new Batch(), 'products' => $products]);
    }

    public function store(StoreBatchRequest $request): RedirectResponse
    {
        Batch::create($request->validated());

        return redirect()->route('batches.index')->with('status', 'ব্যাচ সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Batch $batch): View
    {
        $products = Product::orderBy('name')->get();

        return view('product::batches.edit', compact('batch', 'products'));
    }

    public function update(UpdateBatchRequest $request, Batch $batch): RedirectResponse
    {
        $batch->update($request->validated());

        return redirect()->route('batches.index')->with('status', 'ব্যাচ হালনাগাদ করা হয়েছে');
    }

    public function destroy(Batch $batch): RedirectResponse
    {
        $batch->delete();

        return redirect()->route('batches.index')->with('status', 'ব্যাচ মুছে ফেলা হয়েছে');
    }
}
