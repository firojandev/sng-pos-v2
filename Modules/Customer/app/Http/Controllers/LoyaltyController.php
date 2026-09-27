<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Core\Support\TenantContext;
use Modules\Customer\Http\Requests\EnrollLoyaltyMemberRequest;
use Modules\Customer\Http\Requests\UpdateLoyaltyProgramRequest;
use Modules\Customer\Models\Customer;
use Modules\Customer\Models\CustomerMembership;
use Modules\Customer\Models\LoyaltyPointTransaction;
use Modules\Customer\Models\LoyaltyProgram;
use Modules\Customer\Services\LoyaltyService;
use Modules\Sales\Models\Sale;

/**
 * The company's loyalty programme, this shop's participation and members.
 */
class LoyaltyController extends Controller
{
    public function __construct(private TenantContext $tenant, private LoyaltyService $loyalty) {}

    public function index(): View
    {
        $program = LoyaltyProgram::forCompany($this->tenant->companyId());

        $members = CustomerMembership::query()
            ->with('customer:id,name,phone')
            ->latest('id')
            ->get();

        $pointsByCustomer = LoyaltyPointTransaction::query()
            ->whereIn('customer_id', $members->pluck('customer_id'))
            ->groupBy('customer_id')
            ->selectRaw('customer_id, SUM(points) as points')
            ->pluck('points', 'customer_id');

        $outstandingPoints = (int) $pointsByCustomer->sum();

        return view('customer::loyalty.index', [
            'program' => $program,
            'shopParticipates' => (bool) auth()->user()->shop?->loyalty_enabled,
            'members' => $members,
            'pointsByCustomer' => $pointsByCustomer,
            'outstandingPoints' => $outstandingPoints,
            'outstandingValue' => round($outstandingPoints * (float) ($program->point_value ?? 0), 2),
            'enrollableCustomers' => Customer::query()
                ->where('status', 'active')
                ->whereDoesntHave('membership', fn ($query) => $query->where('status', 'active'))
                ->orderBy('name')
                ->get(['id', 'name', 'phone']),
        ]);
    }

    public function updateProgram(UpdateLoyaltyProgramRequest $request): RedirectResponse
    {
        $program = LoyaltyProgram::forCompany($this->tenant->companyId());
        $program->fill($request->safe()->except('shop_participates'))->save();

        auth()->user()->shop->update(['loyalty_enabled' => $request->validated('shop_participates')]);

        return redirect()->route('loyalty.index')->with('status', 'লয়্যালটি সেটিংস হালনাগাদ করা হয়েছে');
    }

    public function enroll(EnrollLoyaltyMemberRequest $request): RedirectResponse
    {
        $customer = Customer::findOrFail($request->validated('customer_id'));

        $this->loyalty->enroll($customer, $request->validated('card_no'));

        return redirect()->route('loyalty.index')->with('status', "\"{$customer->name}\" সদস্য হিসেবে যুক্ত হয়েছেন");
    }

    public function removeMember(CustomerMembership $membership): RedirectResponse
    {
        $membership->update(['status' => 'inactive']);

        return redirect()->route('loyalty.index')->with('status', 'সদস্যপদ বাতিল করা হয়েছে');
    }

    /**
     * The points a customer can redeem at the counter (for the sale form).
     */
    public function customerSummary(Request $request, Customer $customer): JsonResponse
    {
        $editing = $request->integer('sale_id') ? Sale::find($request->integer('sale_id')) : null;

        return response()->json([
            'active' => $this->loyalty->isActiveAt(auth()->user()->shop),
            ...$this->loyalty->summary($customer, $editing),
        ]);
    }
}
