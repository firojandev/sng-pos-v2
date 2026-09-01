<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Core\Models\AuditLog;

class AuditLogController extends Controller
{
    /**
     * Human-friendly labels for the models we audit, keyed by fully-qualified class name.
     *
     * @return array<string, array{bn: string, en: string}>
     */
    private function auditableLabels(): array
    {
        return [
            'Modules\Sales\Models\Sale' => ['bn' => 'বিক্রয়', 'en' => 'Sale'],
            'Modules\Purchase\Models\Purchase' => ['bn' => 'ক্রয়', 'en' => 'Purchase'],
            'Modules\Finance\Models\Income' => ['bn' => 'আয়', 'en' => 'Income'],
            'Modules\Finance\Models\Expense' => ['bn' => 'ব্যয়', 'en' => 'Expense'],
            'Modules\Cashbox\Models\CashTransaction' => ['bn' => 'ক্যাশ লেনদেন', 'en' => 'Cash Transaction'],
        ];
    }

    public function index(Request $request): View
    {
        $model = $request->query('model', 'all');
        $action = $request->query('action', 'all');
        $labels = $this->auditableLabels();

        $logs = AuditLog::with('user')
            ->when($model !== 'all' && isset($labels[$model]), fn ($q) => $q->where('auditable_type', $model))
            ->when(in_array($action, ['created', 'updated', 'deleted', 'restored'], true), fn ($q) => $q->where('action', $action))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('core::audit-log.index', [
            'logs' => $logs,
            'model' => $model,
            'action' => $action,
            'labels' => $labels,
        ]);
    }
}
