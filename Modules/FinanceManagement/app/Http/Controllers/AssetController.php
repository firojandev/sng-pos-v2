<?php

namespace Modules\FinanceManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\FinanceManagement\DataTables\AssetsDataTable;
use Modules\FinanceManagement\Http\Requests\StoreAssetRequest;
use Modules\FinanceManagement\Http\Requests\UpdateAssetRequest;
use Modules\FinanceManagement\Models\Asset;

class AssetController extends Controller
{
    public function index(AssetsDataTable $dataTable): mixed
    {
        $metrics = [
            'totalAssets' => (float) Asset::sum('amount'),
            'totalCount' => (int) Asset::count(),
        ];

        return $dataTable->render('financemanagement::assets.index', compact('metrics'));
    }

    public function create(): View
    {
        return view('financemanagement::assets.create', [
            'asset' => new Asset,
        ]);
    }

    public function store(StoreAssetRequest $request): RedirectResponse|JsonResponse
    {
        $asset = Asset::create($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'সম্পদ সফলভাবে যোগ করা হয়েছে',
                'asset' => $asset,
            ]);
        }

        return redirect()->route('assets.index')->with('status', 'সম্পদ সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Request $request, Asset $asset): View|JsonResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'id' => $asset->id,
                'name' => $asset->name,
                'amount' => (float) $asset->amount,
                'note' => $asset->note,
                'update_url' => route('assets.update', $asset),
            ]);
        }

        return view('financemanagement::assets.edit', compact('asset'));
    }

    public function update(UpdateAssetRequest $request, Asset $asset): RedirectResponse|JsonResponse
    {
        $asset->update($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'সম্পদ হালনাগাদ করা হয়েছে',
                'asset' => $asset,
            ]);
        }

        return redirect()->route('assets.index')->with('status', 'সম্পদ হালনাগাদ করা হয়েছে');
    }

    public function destroy(Request $request, Asset $asset): RedirectResponse|JsonResponse
    {
        $asset->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'সম্পদ মুছে ফেলা হয়েছে',
            ]);
        }

        return redirect()->route('assets.index')->with('status', 'সম্পদ মুছে ফেলা হয়েছে');
    }
}
