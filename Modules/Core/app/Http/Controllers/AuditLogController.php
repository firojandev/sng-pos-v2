<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Modules\Core\DataTables\AuditLogDataTable;
use Modules\Core\Models\AuditLog;
use Modules\Customer\Models\Customer;
use Modules\Product\Models\Product;
use Modules\Purchase\Models\Purchase;
use Modules\Sales\Models\Sale;
use Modules\Supplier\Models\Supplier;

class AuditLogController extends Controller
{
    /**
     * Display a listing of the resource with Yajra DataTable.
     */
    public function index(AuditLogDataTable $dataTable)
    {
        $today = now()->startOfDay();

        $metrics = [
            'totalLogs' => AuditLog::count(),
            'todayLogs' => AuditLog::where('created_at', '>=', $today)->count(),
            'createdLogs' => AuditLog::where('action', 'created')->count(),
            'updatedLogs' => AuditLog::where('action', 'updated')->count(),
            'deletedLogs' => AuditLog::where('action', 'deleted')->count(),
        ];

        // Prepare model filter options
        $allAuditableModels = AuditLog::auditableModels();
        $modelOptions = ['all' => 'সব রেকর্ড / All Records'];
        foreach ($allAuditableModels as $class => $meta) {
            $modelOptions[$class] = $meta['bn'].' ('.$meta['en'].')';
        }

        // Action options
        $actionOptions = [
            'all' => 'সকল অ্যাকশন / All Actions',
            'created' => 'নতুন তৈরি / Created',
            'updated' => 'হালনাগাদ / Updated',
            'deleted' => 'মুছে ফেলা / Deleted',
            'restored' => 'পুনরুদ্ধার / Restored',
        ];

        // User options
        $users = User::orderBy('name')->get(['id', 'name', 'email']);
        $userOptions = ['all' => 'সকল ব্যবহারকারী / All Users'];
        foreach ($users as $u) {
            $userOptions[$u->id] = $u->name.($u->email ? ' ('.$u->email.')' : '');
        }

        return $dataTable->render('core::audit-log.index', [
            'metrics' => $metrics,
            'modelOptions' => $modelOptions,
            'actionOptions' => $actionOptions,
            'userOptions' => $userOptions,
        ]);
    }

    /**
     * Show detailed change log and diff for AJAX modal/drawer.
     */
    public function show(AuditLog $auditLog): JsonResponse
    {
        $auditLog->load(['user', 'shop']);

        $meta = $auditLog->modelMeta();
        $actionLabel = $auditLog->actionLabel();
        $browserDevice = $auditLog->browserAndDevice();

        // Process changes diff
        $old = $auditLog->old_values ?? [];
        $new = $auditLog->new_values ?? [];

        $allKeys = array_unique(array_merge(array_keys($old), array_keys($new)));
        sort($allKeys);

        $diffRows = [];
        foreach ($allKeys as $key) {
            $oldVal = $old[$key] ?? null;
            $newVal = $new[$key] ?? null;

            $status = 'unchanged';
            if (! array_key_exists($key, $old) && array_key_exists($key, $new)) {
                $status = 'added';
            } elseif (array_key_exists($key, $old) && ! array_key_exists($key, $new)) {
                $status = 'removed';
            } elseif ($oldVal !== $newVal) {
                $status = 'modified';
            }

            $diffRows[] = [
                'field' => $key,
                'field_label' => $this->humanizeField($key),
                'old_value' => $this->formatValue($oldVal),
                'new_value' => $this->formatValue($newVal),
                'raw_old' => $oldVal,
                'raw_new' => $newVal,
                'status' => $status,
            ];
        }

        // Check if target model still exists and determine quick link if applicable
        $targetRecord = null;
        try {
            $liveModel = $auditLog->auditable;
            if ($liveModel) {
                $title = $liveModel->name ?? $liveModel->invoice_no ?? $liveModel->title ?? ('ID #'.$liveModel->getKey());
                $targetRecord = [
                    'exists' => true,
                    'title' => $title,
                    'view_url' => $this->resolveModelUrl($liveModel),
                ];
            } else {
                $targetRecord = [
                    'exists' => false,
                    'title' => 'ID #'.$auditLog->auditable_id.' (নট ফাউন্ড / Not Found)',
                    'view_url' => null,
                ];
            }
        } catch (\Throwable $e) {
            $targetRecord = [
                'exists' => false,
                'title' => 'ID #'.$auditLog->auditable_id,
                'view_url' => null,
            ];
        }

        return response()->json([
            'success' => true,
            'log' => [
                'id' => $auditLog->id,
                'action' => $auditLog->action,
                'action_label' => $actionLabel,
                'created_at' => $auditLog->created_at?->format('d M, Y, h:i A'),
                'created_at_human' => $auditLog->created_at?->diffForHumans(),
                'ip_address' => $auditLog->ip_address ?: 'N/A',
                'user_agent' => $auditLog->user_agent ?: 'N/A',
                'browser' => $browserDevice['browser'],
                'platform' => $browserDevice['platform'],
                'user' => $auditLog->user ? [
                    'id' => $auditLog->user->id,
                    'name' => $auditLog->user->name,
                    'email' => $auditLog->user->email,
                    'avatar' => $auditLog->user->avatar_url,
                ] : null,
                'shop_name' => $auditLog->shop?->name,
                'model' => [
                    'class' => $auditLog->auditable_type,
                    'name_bn' => $meta['bn'],
                    'name_en' => $meta['en'],
                    'icon' => $meta['icon'],
                    'color' => $meta['color'],
                    'id' => $auditLog->auditable_id,
                ],
            ],
            'target_record' => $targetRecord,
            'diff' => $diffRows,
            'raw_old_json' => ! empty($old) ? json_encode($old, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : null,
            'raw_new_json' => ! empty($new) ? json_encode($new, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : null,
        ]);
    }

    /**
     * Resolve a view URL for a live model if one exists in the system.
     */
    private function resolveModelUrl($model): ?string
    {
        try {
            if ($model instanceof Sale) {
                return route('sales.index').'?search='.urlencode($model->invoice_no);
            }
            if ($model instanceof Purchase) {
                return route('purchase.index').'?search='.urlencode($model->invoice_no ?? $model->id);
            }
            if ($model instanceof Customer) {
                return route('due-ledger.customer.details', $model->id);
            }
            if ($model instanceof Supplier) {
                return route('due-ledger.supplier.details', $model->id);
            }
            if ($model instanceof User) {
                return route('users.index').'?search='.urlencode($model->email ?? $model->name);
            }
            if ($model instanceof Product) {
                return route('products.index').'?search='.urlencode($model->name);
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    /**
     * Translate field names to friendly names.
     */
    private function humanizeField(string $key): string
    {
        $fieldDictionary = [
            'name' => 'নাম (Name)',
            'phone' => 'ফোন নম্বর (Phone)',
            'email' => 'ইমেইল (Email)',
            'address' => 'ঠিকানা (Address)',
            'status' => 'অবস্থা (Status)',
            'opening_due' => 'ওপেনিং বাকি (Opening Due)',
            'total' => 'মোট টাকা (Total Amount)',
            'subtotal' => 'সাবটোটাল (Subtotal)',
            'discount' => 'ছাড় / ডিসকাউন্ট (Discount)',
            'delivery_charge' => 'ডেলিভারি চার্জ (Delivery Charge)',
            'paid_amount' => 'পরিশোধিত টাকা (Paid Amount)',
            'due_amount' => 'বাকি টাকা (Due Amount)',
            'payment_status' => 'পরিশোধ অবস্থা (Payment Status)',
            'payment_method' => 'পেমেন্ট মাধ্যম (Payment Method)',
            'invoice_no' => 'চালান / ইনভয়েস নং (Invoice No)',
            'sale_date' => 'বিক্রয় তারিখ (Sale Date)',
            'purchase_date' => 'ক্রয় তারিখ (Purchase Date)',
            'note' => 'নোট / মন্তব্য (Note)',
            'amount' => 'টাকার পরিমাণ (Amount)',
            'type' => 'ধরন (Type)',
            'balance_after' => 'পরবর্তী ব্যালেন্স (Balance After)',
            'account_id' => 'অ্যাকাউন্ট (Account ID)',
            'warehouse_id' => 'গুদাম / ওয়্যারহাউস (Warehouse ID)',
            'category_id' => 'ক্যাটাগরি (Category ID)',
            'brand_id' => 'ব্র্যান্ড (Brand ID)',
            'purchase_price' => 'ক্রয়মূল্য (Purchase Price)',
            'sale_price' => 'বিক্রয়মূল্য (Sale Price)',
            'sku' => 'এসকেইউ (SKU)',
            'barcode' => 'বারকোড (Barcode)',
            'alert_qty' => 'সতর্কীকরণ স্টক (Alert Qty)',
            'salary' => 'বেতন (Salary)',
            'designation' => 'পদবী (Designation)',
            'department' => 'বিভাগ (Department)',
        ];

        return $fieldDictionary[$key] ?? ucwords(str_replace('_', ' ', $key));
    }

    /**
     * Format values for presentation.
     */
    private function formatValue(mixed $val): string
    {
        if ($val === null) {
            return '—';
        }
        if (is_bool($val)) {
            return $val ? 'True (হ্যাঁ)' : 'False (না)';
        }
        if (is_array($val)) {
            return json_encode($val, JSON_UNESCAPED_UNICODE);
        }

        return (string) $val;
    }
}
