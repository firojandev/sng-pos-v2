<div class="modal-head" style="padding:16px 20px; border-bottom:1px solid var(--border); margin-bottom:0; display:flex; align-items:center; justify-content:space-between; background:var(--paper);">
    <div style="display:flex; align-items:center; gap:10px;">
        <div style="width:34px; height:34px; border-radius:8px; background:var(--gold-100); color:var(--gold-ink); display:flex; align-items:center; justify-content:center;">
            <x-core::icon name="badge-dollar-sign" style="width:18px; height:18px;" />
        </div>
        <div>
            <div class="modal-title bn" style="font-size:16px; font-weight:700; color:var(--ink-900);">সরবরাহকারী বাকি পরিশোধ / Pay Due</div>
            <div class="modal-title en" style="display:none; font-size:16px; font-weight:700; color:var(--ink-900);">Pay Supplier Due</div>
            <div style="font-size:12px; color:var(--ink-500); margin-top:2px;">
                <b>{{ $supplier->name }}</b>
                @if ($supplier->phone)
                    <span style="font-family:var(--font-mono, monospace); margin-left:6px;">({{ $supplier->phone }})</span>
                @endif
            </div>
        </div>
    </div>
    <x-core::button type="button" variant="ghost" size="sm" icon="x" onclick="$('#supplierPaymentModal').removeClass('open')" title="বন্ধ / Close" />
</div>

<form id="form-supplier-due-payment" action="{{ route('due-ledger.supplier.payment.store', $supplier) }}" method="POST" style="display:flex; flex-direction:column; flex:1; overflow:hidden;">
    @csrf
    <input type="hidden" name="cash_account_id" value="{{ $defaultCashAccount?->id }}">

    <div style="padding:16px 20px; overflow-y:auto; flex:1;">
        {{-- Total Due & Balances Ribbon --}}
        <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:10px; margin-bottom:16px; background:var(--paper); padding:12px 14px; border-radius:10px; border:1px solid var(--border);">
            <div>
                <div style="font-size:11px; color:var(--ink-500);">মোট বকেয়া / Total Due</div>
                <div style="font-size:15px; font-weight:800; color:var(--red-600); font-family:var(--font-mono, monospace); margin-top:2px;">৳{{ number_format($supplier->total_due, 2) }}</div>
            </div>
            <div>
                <div style="font-size:11px; color:var(--ink-500);">ওপেনিং বাকি / Opening</div>
                <div style="font-size:14px; font-weight:700; color:var(--ink-700); font-family:var(--font-mono, monospace); margin-top:2px;">৳{{ number_format($supplier->opening_due, 2) }}</div>
            </div>
            <div>
                <div style="font-size:11px; color:var(--ink-500);">বিল বাকি / Purchases Due</div>
                <div style="font-size:14px; font-weight:700; color:var(--gold-ink, #b45309); font-family:var(--font-mono, monospace); margin-top:2px;">৳{{ number_format($supplier->purchases->sum('due_amount'), 2) }}</div>
            </div>
            <div>
                <div style="font-size:11px; color:var(--ink-500);">অবশিষ্ট থাকবে / Balance After</div>
                <div id="supplier-balance-after" style="font-size:15px; font-weight:800; color:var(--ink-900); font-family:var(--font-mono, monospace); margin-top:2px;">৳{{ number_format($supplier->total_due, 2) }}</div>
            </div>
        </div>

        {{-- Payment Type & Basic Details --}}
        <div style="display:grid; grid-template-columns:1.1fr 1.3fr 1.6fr; gap:12px; margin-bottom:12px;">
            <div>
                <x-core::input
                    type="date"
                    name="payment_date"
                    size="sm"
                    label="পেমেন্ট তারিখ / Date"
                    :value="date('Y-m-d')"
                />
            </div>
            <div>
                <x-core::select
                    name="payment_type"
                    id="supplier-payment-type-select"
                    size="sm"
                    label="পেমেন্টের মাধ্যম / Payment Method"
                >
                    <option value="cash" selected>নগদ (Cash)</option>
                    <option value="bank">ব্যাংক / MFS (Bank)</option>
                    <option value="both">উভয় (ক্যাশ + ব্যাংক)</option>
                </x-core::select>
            </div>
            <div>
                <x-core::input
                    type="text"
                    name="note"
                    size="sm"
                    label="নোট (ঐচ্ছিক) / Note"
                    placeholder="পেমেন্ট সংক্রান্ত মন্তব্য..."
                />
            </div>
        </div>

        {{-- Dynamic Payment Amount Section: Cash, Bank, or Both --}}
        <div style="background:var(--paper); border:1px solid var(--border); border-radius:8px; padding:12px 14px; margin-bottom:14px;">
            {{-- Mode: Cash Only --}}
            <div id="supplier-mode-cash" class="payment-mode-pane" style="display:block;">
                <div style="display:flex; align-items:flex-end; gap:12px;">
                    <div style="flex:1;">
                        <x-core::input
                            type="number"
                            step="0.01"
                            min="0"
                            max="{{ $supplier->total_due }}"
                            name="cash_amount"
                            id="supplier-cash-amount-input"
                            size="sm"
                            label="নগদ পরিশোধের পরিমাণ / Cash Amount"
                            placeholder="0.00"
                            style="font-weight:700; font-family:var(--font-mono, monospace); color:var(--gold-ink); text-align:right;"
                            :stepper="false"

                        />
                    </div>
                    @if ($defaultCashAccount)
                        <div style="font-size:11.5px; color:var(--ink-500); padding-bottom:6px;">
                            অ্যাকাউন্ট: <b>{{ $defaultCashAccount->name }}</b> (৳{{ number_format($defaultCashAccount->current_balance, 2) }})
                        </div>
                    @endif
                </div>
            </div>

            {{-- Mode: Bank Only --}}
            <div id="supplier-mode-bank" class="payment-mode-pane" style="display:none;">
                <div style="display:grid; grid-template-columns:1.2fr 1fr; gap:12px;">
                    <div>
                        <x-core::select
                            name="bank_account_id"
                            id="supplier-bank-account-select"
                            size="sm"
                            label="ব্যাংক / MFS অ্যাকাউন্ট"
                        >
                            @forelse ($bankAccounts as $acc)
                                <option value="{{ $acc->id }}" {{ $defaultBankAccount?->id === $acc->id ? 'selected' : '' }}>
                                    {{ $acc->display_name }} (৳{{ number_format($acc->current_balance, 2) }})
                                </option>
                            @empty
                                <option value="">কোনো ব্যাংক বা MFS অ্যাকাউন্ট নেই</option>
                            @endforelse
                        </x-core::select>
                    </div>
                    <div>
                        <x-core::input
                            type="number"
                            step="0.01"
                            min="0"
                            max="{{ $supplier->total_due }}"
                            name="bank_amount"
                            id="supplier-bank-amount-input"
                            size="sm"
                            label="ব্যাংক পরিশোধের পরিমাণ / Bank Amount"
                            placeholder="0.00"
                            style="font-weight:700; font-family:var(--font-mono, monospace); color:var(--teal-800); text-align:right;"
                            :stepper="false"
                        />
                    </div>
                </div>
            </div>

            {{-- Mode: Both (Cash + Bank) --}}
            <div id="supplier-mode-both" class="payment-mode-pane" style="display:none;">
                <div style="display:grid; grid-template-columns:1fr 1.2fr 1fr; gap:12px;">
                    <div>
                        <x-core::input
                            type="number"
                            step="0.01"
                            min="0"
                            name="both_cash_amount"
                            id="supplier-both-cash-amount-input"
                            size="sm"
                            label="ক্যাশ পরিশোধ / Cash"
                            placeholder="0.00"
                            style="font-weight:700; font-family:var(--font-mono, monospace); color:var(--green-ink); text-align:right;"
                            :stepper="false"
                        />
                    </div>
                    <div>
                        <x-core::select
                            name="both_bank_account_id"
                            id="supplier-both-bank-account-select"
                            size="sm"
                            label="ব্যাংক / MFS অ্যাকাউন্ট"
                        >
                            @forelse ($bankAccounts as $acc)
                                <option value="{{ $acc->id }}" {{ $defaultBankAccount?->id === $acc->id ? 'selected' : '' }}>
                                    {{ $acc->display_name }} (৳{{ number_format($acc->current_balance, 2) }})
                                </option>
                            @empty
                                <option value="">কোনো ব্যাংক বা MFS অ্যাকাউন্ট নেই</option>
                            @endforelse
                        </x-core::select>
                    </div>
                    <div>
                        <x-core::input
                            type="number"
                            step="0.01"
                            min="0"
                            name="both_bank_amount"
                            id="supplier-both-bank-amount-input"
                            size="sm"
                            label="ব্যাংক পরিশোধ / Bank"
                            placeholder="0.00"
                            style="font-weight:700; font-family:var(--font-mono, monospace); color:var(--teal-800); text-align:right;"
                            :stepper="false"
                        />
                    </div>
                </div>
            </div>

            {{-- Sub-bar with helper actions and Live Total --}}
            <div style="display:flex; align-items:center; justify-content:space-between; margin-top:10px; padding-top:8px; border-top:1px dashed var(--border);">
                <div style="display:flex; align-items:center; gap:8px;">
                    <x-core::button
                        type="button"
                        variant="secondary"
                        size="sm"
                        icon="check"
                        id="btn-supplier-pay-full"
                        data-total="{{ $supplier->total_due }}"
                    >
                        <span class="bn">সম্পূর্ণ পরিশোধ (৳{{ number_format($supplier->total_due, 2) }})</span>
                        <span class="en" style="display:none;">Full Pay (৳{{ number_format($supplier->total_due, 2) }})</span>
                    </x-core::button>
                    <x-core::button
                        type="button"
                        variant="ghost"
                        size="sm"
                        icon="rotate-ccw"
                        id="btn-supplier-pay-reset"
                    >
                        <span class="bn">রিসেট</span>
                        <span class="en" style="display:none;">Reset</span>
                    </x-core::button>
                </div>
                <div style="font-size:13px; color:var(--ink-700);">
                    মোট পরিশোধ: <strong id="lbl-supplier-pay-total" style="font-size:15px; font-weight:800; color:var(--gold-ink); font-family:var(--font-mono, monospace);">৳0.00</strong>
                </div>
            </div>
        </div>

        {{-- Purchases & Opening Due Breakdown Table (FIFO) --}}
        <div style="border:1px solid var(--border); border-radius:8px; overflow:hidden;">
            <div style="padding:8px 12px; background:var(--paper); font-size:12px; font-weight:700; color:var(--ink-700); border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <span>বকেয়া বিলসমূহ / Outstanding Purchases (FIFO বণ্টন)</span>
                <span style="font-size:11px; font-weight:normal; color:var(--ink-500);">টাকা লিখলে স্বয়ংক্রিয়ভাবে ক্রমানুসারে বণ্টন হবে</span>
            </div>
            <div class="table-responsive" style="max-height:240px; overflow-y:auto;">
                <table class="app-table" style="width:100%; margin:0;">
                    <thead>
                        <tr>
                            <th style="font-size:11.5px; width:140px;">বিল / বিবরণ</th>
                            <th style="font-size:11.5px; width:95px;">তারিখ</th>
                            <th style="font-size:11.5px; text-align:right; width:100px;">মোট বিল</th>
                            <th style="font-size:11.5px; text-align:right; width:100px;">বাকি</th>
                            <th style="font-size:11.5px; text-align:right; width:130px;">এই পরিশোধ</th>
                            <th style="font-size:11.5px; text-align:right; width:100px;">অবশিষ্ট বাকি</th>
                            <th style="font-size:11.5px; text-align:center; width:90px;">অবস্থা</th>
                        </tr>
                    </thead>
                    <tbody id="supplier-allocation-tbody">
                        @if ($supplier->opening_due > 0)
                            <tr class="allocation-row" data-type="opening" data-due="{{ $supplier->opening_due }}">
                                <td>
                                    <div style="font-weight:700; color:var(--ink-900); font-size:12.5px;">প্রারম্ভিক বাকি</div>
                                    <div style="font-size:11px; color:var(--ink-500);">Opening Due</div>
                                </td>
                                <td style="color:var(--ink-400); font-size:12px;">—</td>
                                <td style="text-align:right; font-family:var(--font-mono, monospace); font-size:12px;">৳{{ number_format($supplier->opening_due, 2) }}</td>
                                <td style="text-align:right; font-family:var(--font-mono, monospace); font-weight:700; color:var(--red-600); font-size:12.5px;">৳{{ number_format($supplier->opening_due, 2) }}</td>
                                <td style="text-align:right;">
                                    <x-core::input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="{{ $supplier->opening_due }}"
                                        size="sm"
                                        :no-margin="true"
                                        name="opening_amount"
                                        class="allocation-input"
                                        value="0.00"
                                        data-due="{{ $supplier->opening_due }}"
                                        style="font-family:var(--font-mono, monospace); font-weight:700; text-align:right; width:110px;"
                                        :stepper="false"
                                    />
                                </td>
                                <td style="text-align:right; font-family:var(--font-mono, monospace); font-size:12px;" class="cell-remaining">৳{{ number_format($supplier->opening_due, 2) }}</td>
                                <td style="text-align:center;" class="cell-status">
                                    <span style="font-size:11px; font-weight:600; padding:1px 6px; border-radius:4px; background:var(--red-100); color:var(--red-600);">বাকি</span>
                                </td>
                            </tr>
                        @endif

                        @forelse ($supplier->purchases as $purchase)
                            <tr class="allocation-row" data-type="invoice" data-id="{{ $purchase->id }}" data-due="{{ $purchase->due_amount }}">
                                <td>
                                    <div style="font-weight:700; font-family:var(--font-mono, monospace); color:var(--ink-900); font-size:12.5px;">#{{ $purchase->invoice_no }}</div>
                                    <div style="font-size:11px; color:var(--ink-500);">পরিশোধিত: ৳{{ number_format($purchase->paid_amount, 2) }}</div>
                                </td>
                                <td style="font-size:12px; color:var(--ink-700);">{{ optional($purchase->purchase_date)->format('d M, Y') ?? '—' }}</td>
                                <td style="text-align:right; font-family:var(--font-mono, monospace); font-size:12px;">৳{{ number_format($purchase->total, 2) }}</td>
                                <td style="text-align:right; font-family:var(--font-mono, monospace); font-weight:700; color:var(--red-600); font-size:12.5px;">৳{{ number_format($purchase->due_amount, 2) }}</td>
                                <td style="text-align:right;">
                                    <x-core::input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="{{ $purchase->due_amount }}"
                                        size="sm"
                                        :no-margin="true"
                                        name="invoices[{{ $purchase->id }}]"
                                        class="allocation-input"
                                        value="0.00"
                                        data-due="{{ $purchase->due_amount }}"
                                        style="font-family:var(--font-mono, monospace); font-weight:700; text-align:right; width:110px;"
                                        :stepper="false"
                                    />
                                </td>
                                <td style="text-align:right; font-family:var(--font-mono, monospace); font-size:12px;" class="cell-remaining">৳{{ number_format($purchase->due_amount, 2) }}</td>
                                <td style="text-align:center;" class="cell-status">
                                    <span style="font-size:11px; font-weight:600; padding:1px 6px; border-radius:4px; background:var(--red-100); color:var(--red-600);">বাকি</span>
                                </td>
                            </tr>
                        @empty
                            @if ($supplier->opening_due <= 0)
                                <tr>
                                    <td colspan="7" style="text-align:center; padding:20px; color:var(--ink-500);">কোনো বকেয়া বিল নেই</td>
                                </tr>
                            @endif
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Foot --}}
    <div class="modal-foot" style="padding:12px 20px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:flex-end; gap:8px; background:var(--paper);">
        <x-core::button
            type="button"
            variant="secondary"
            size="sm"
            onclick="$('#supplierPaymentModal').removeClass('open')"
        >
            <span class="bn">বাতিল</span>
            <span class="en" style="display:none;">Cancel</span>
        </x-core::button>
        <x-core::button
            type="submit"
            variant="solid"
            color="primary"
            size="sm"
            icon="check"
            id="btn-submit-supplier-payment"
        >
            <span class="bn">পেমেন্ট নিশ্চিত করুন</span>
            <span class="en" style="display:none;">Confirm Payment</span>
        </x-core::button>
    </div>
</form>
