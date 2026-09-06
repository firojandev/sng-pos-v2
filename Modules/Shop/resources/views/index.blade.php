<x-core::layout
    title="দোকানসমূহ"
    title-en="Shops"
    subtitle="সিস্টেমে নিবন্ধিত সকল দোকান ও এডমিন পরিচালনা করুন"
    subtitle-en="Manage all registered shops, subscriptions, and shop admins"
    active="shops"
>
    <div class="section-row" style="margin-bottom:16px;">
        <div class="filters"></div>
        <x-core::button
            as="a"
            href="{{ route('shops.create') }}"
            variant="solid"
            color="primary"
            size="sm"
            icon="plus"
        >
            <span class="bn">নতুন দোকান তৈরি করুন</span>
            <span class="en" style="display:none;">Create New Shop</span>
        </x-core::button>
    </div>

    <div class="table-container table-teal">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'shops-data-table']) !!}
        </div>
    </div>

    {{-- Shop Details View Modal --}}
    <div class="modal-backdrop" id="shopDetailsModal" style="z-index:999;">
        <div class="modal-box" style="width:620px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border);">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="shopping-bag" size="18" />
                    </div>
                    <div class="modal-title" style="font-size:16.5px;">
                        <span class="bn">দোকানের বিস্তারিত বিবরণ</span>
                        <span class="en" style="display:none;">Shop Details</span>
                    </div>
                </div>
                <button type="button" class="drawer-x" onclick="closeModal('shopDetailsModal')" style="width:28px; height:28px; font-size:18px;">&times;</button>
            </div>

            {{-- Modal Loading State --}}
            <div id="shop-modal-loading" style="padding:40px 20px; text-align:center; color:var(--ink-500); display:none;">
                <div class="btn-spinner" style="width:28px; height:28px; margin:0 auto 10px; color:var(--teal-800);">
                    <x-core::icon name="loader" size="28" />
                </div>
                <div style="font-size:13px; font-weight:600;">
                    <span class="bn">দোকানের তথ্য লোড হচ্ছে...</span>
                    <span class="en" style="display:none;">Loading shop details...</span>
                </div>
            </div>

            {{-- Modal Content Container --}}
            <div id="shop-modal-content">
                {{-- Shop Header Banner --}}
                <div style="display:flex; align-items:flex-start; gap:14px; margin-bottom:16px; background:var(--card); border:1px solid var(--border); border-radius:12px; padding:16px;">
                    <div style="width:48px; height:48px; border-radius:12px; background:var(--teal-50); color:var(--teal-700); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <x-core::icon name="shopping-bag" size="24" />
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap;">
                            <h3 id="modal-shop-name" style="font-size:17px; font-weight:800; color:var(--ink-900); margin:0;">—</h3>
                            <span id="modal-shop-status-badge"></span>
                        </div>
                        <div style="display:flex; align-items:center; gap:6px; margin-top:4px; flex-wrap:wrap;">
                            <span id="modal-shop-slug" style="font-size:12px; font-family:monospace; background:var(--ink-50); padding:2px 8px; border-radius:6px; border:1px solid var(--border); color:var(--ink-700);">—</span>
                            <span id="modal-shop-store-code" style="font-size:12px; font-weight:700; font-family:monospace; background:var(--teal-50); padding:2px 8px; border-radius:6px; border:1px solid var(--teal-200); color:var(--teal-800);">—</span>
                        </div>
                    </div>
                </div>

                {{-- Key Contact & Info Grid --}}
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px; margin-bottom:16px;">
                    <div style="background:var(--paper); border:1px solid var(--border); border-radius:10px; padding:12px;">
                        <div style="font-size:11px; font-weight:700; color:var(--ink-500); text-transform:uppercase; margin-bottom:4px;">
                            <span class="bn">ফোন / মোবাইল</span>
                            <span class="en" style="display:none;">Phone Number</span>
                        </div>
                        <div id="modal-shop-phone" style="font-size:13.5px; font-weight:700; font-family:monospace; color:var(--ink-900);">—</div>
                    </div>
                    <div style="background:var(--paper); border:1px solid var(--border); border-radius:10px; padding:12px;">
                        <div style="font-size:11px; font-weight:700; color:var(--ink-500); text-transform:uppercase; margin-bottom:4px;">
                            <span class="bn">নিবন্ধনের তারিখ</span>
                            <span class="en" style="display:none;">Registered Date</span>
                        </div>
                        <div id="modal-shop-created-at" style="font-size:12.5px; font-weight:600; color:var(--ink-800);">—</div>
                    </div>
                </div>

                <div style="background:var(--paper); border:1px solid var(--border); border-radius:10px; padding:12px; margin-bottom:16px;">
                    <div style="font-size:11px; font-weight:700; color:var(--ink-500); text-transform:uppercase; margin-bottom:4px;">
                        <span class="bn">দোকানের ঠিকানা</span>
                        <span class="en" style="display:none;">Address</span>
                    </div>
                    <div id="modal-shop-address" style="font-size:13px; color:var(--ink-800); line-height:1.4;">—</div>
                </div>

                {{-- Subscription Box --}}
                <div style="background:var(--teal-50); border:1px solid var(--teal-100); border-radius:12px; padding:14px; margin-bottom:16px;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                        <div style="font-size:11.5px; font-weight:800; color:var(--teal-900); text-transform:uppercase; letter-spacing:0.5px;">
                            <span class="bn">বর্তমান সাবস্ক্রিপশন প্ল্যান</span>
                            <span class="en" style="display:none;">Current Subscription Plan</span>
                        </div>
                        <span id="modal-subscription-status-badge"></span>
                    </div>
                    <div id="modal-subscription-info" style="font-size:15px; font-weight:800; color:var(--teal-900);">—</div>
                    <div id="modal-subscription-dates" style="font-size:11.5px; color:var(--teal-800); margin-top:3px;"></div>
                </div>

                {{-- Shop Admins List --}}
                <div style="margin-bottom:16px;">
                    <div style="font-size:12px; font-weight:700; color:var(--ink-700); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">
                        <span class="bn">দায়িত্বপ্রাপ্ত এডমিনগণ:</span>
                        <span class="en" style="display:none;">Shop Administrators:</span>
                    </div>
                    <div id="modal-shop-admins" style="display:flex; flex-direction:column; gap:8px;">
                        <!-- Rendered dynamically -->
                    </div>
                </div>

                {{-- Active Features List --}}
                <div>
                    <div style="font-size:12px; font-weight:700; color:var(--ink-700); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">
                        <span class="bn">সক্রিয় মডিউল ও ফিচারসমূহ:</span>
                        <span class="en" style="display:none;">Active Modules & Features:</span>
                    </div>
                    <div id="modal-shop-features" style="display:flex; flex-wrap:wrap; gap:6px;">
                        <!-- Rendered dynamically -->
                    </div>
                </div>
            </div>

            {{-- Modal Footer Actions --}}
            <div style="margin-top:20px; padding-top:14px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:flex-end; gap:8px;">
                <x-core::button
                    type="button"
                    variant="outline"
                    color="secondary"
                    size="sm"
                    onclick="closeModal('shopDetailsModal')"
                >
                    <span class="bn">বন্ধ করুন</span>
                    <span class="en" style="display:none;">Close</span>
                </x-core::button>
                <x-core::button
                    as="a"
                    id="modal-edit-link"
                    href="#"
                    variant="solid"
                    color="primary"
                    size="sm"
                    icon="edit"
                >
                    <span class="bn">সম্পাদনা করুন</span>
                    <span class="en" style="display:none;">Edit Shop</span>
                </x-core::button>
            </div>
        </div>
    </div>

    @push('scripts')
        {!! $dataTable->scripts() !!}

        <script>
        (function () {
            function initShopModalEvents() {
                if (typeof window.$ === 'undefined' || typeof window.jQuery === 'undefined') {
                    setTimeout(initShopModalEvents, 30);
                    return;
                }

                var $ = window.jQuery;

                // Handle click on View Shop Details button
                $(document).on('click', '.btn-view-shop', function (e) {
                    e.preventDefault();
                    var url = $(this).data('url');
                    if (!url) return;

                    $('#shop-modal-loading').show();
                    $('#shop-modal-content').hide();
                    openModal('shopDetailsModal');

                    $.ajax({
                        url: url,
                        type: 'GET',
                        dataType: 'json',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function (data) {
                            // Populate Shop Header
                            $('#modal-shop-name').text(data.name || '—');
                            $('#modal-shop-slug').text(data.slug || '—');
                            $('#modal-shop-store-code').text(data.store_code ? ('#' + data.store_code) : '—');
                            $('#modal-shop-phone').text(data.phone || 'মোবাইল নম্বর দেওয়া নেই');
                            $('#modal-shop-address').text(data.address || 'ঠিকানা দেওয়া নেই');
                            $('#modal-shop-created-at').text(data.created_at || '—');
                            $('#modal-edit-link').attr('href', data.edit_url || '#');

                            // Status badge
                            var isActive = data.status === 'active';
                            $('#modal-shop-status-badge').html(
                                '<span class="badge b-' + (isActive ? 'green' : 'grey') + ' badge-' + (isActive ? 'green' : 'grey') + ' badge-xs">' +
                                (isActive ? 'সক্রিয় (Active)' : 'নিষ্ক্রিয় (Inactive)') +
                                '</span>'
                            );

                            // Subscription
                            if (data.subscription) {
                                var sub = data.subscription;
                                var priceText = sub.price ? (' (' + sub.price + '/' + (sub.billing_cycle === 'Yearly' ? 'বছর' : 'মাস') + ')') : '';
                                $('#modal-subscription-info').text(sub.plan_name + priceText);

                                var dateInfo = '';
                                if (sub.current_period_end) {
                                    dateInfo += 'মেয়াদ সমাপ্তি: ' + sub.current_period_end;
                                } else if (sub.trial_ends_at) {
                                    dateInfo += 'ট্রায়াল সমাপ্তি: ' + sub.trial_ends_at;
                                }
                                $('#modal-subscription-dates').text(dateInfo);

                                $('#modal-subscription-status-badge').html(
                                    '<span class="badge b-teal badge-teal badge-xs">' + (sub.status_label || sub.status) + '</span>'
                                );
                            } else {
                                $('#modal-subscription-info').text('কোনো সক্রিয় সাবস্ক্রিপশন নেই');
                                $('#modal-subscription-dates').empty();
                                $('#modal-subscription-status-badge').empty();
                            }

                            // Admins
                            var $adminsList = $('#modal-shop-admins');
                            $adminsList.empty();
                            if (data.admins && data.admins.length > 0) {
                                $.each(data.admins, function (i, admin) {
                                    var rolesHtml = '';
                                    if (admin.roles && admin.roles.length > 0) {
                                        $.each(admin.roles, function (j, role) {
                                            rolesHtml += '<span class="badge b-teal badge-teal badge-xs" style="padding:1px 6px;">' + role + '</span> ';
                                        });
                                    }

                                    $adminsList.append(
                                        '<div style="display:flex; align-items:center; justify-content:space-between; background:var(--paper); border:1px solid var(--border); border-radius:8px; padding:8px 12px;">' +
                                            '<div style="display:flex; align-items:center; gap:8px;">' +
                                                '<div style="width:28px; height:28px; border-radius:50%; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:11.5px;">' +
                                                    (admin.name ? admin.name.charAt(0) : '?') +
                                                '</div>' +
                                                '<div>' +
                                                    '<div style="font-weight:700; font-size:12.5px; color:var(--ink-900);">' + admin.name + '</div>' +
                                                    '<div style="font-size:11px; font-family:monospace; color:var(--ink-500);">' + admin.email + '</div>' +
                                                '</div>' +
                                            '</div>' +
                                            '<div>' + rolesHtml + '</div>' +
                                        '</div>'
                                    );
                                });
                            } else {
                                $adminsList.html('<span style="font-size:12px; color:var(--ink-400);">কোনো এডমিন যোগ করা নেই</span>');
                            }

                            // Features
                            var $featuresList = $('#modal-shop-features');
                            $featuresList.empty();
                            if (data.enabled_features && data.enabled_features.length > 0) {
                                $.each(data.enabled_features, function (i, feat) {
                                    $featuresList.append(
                                        '<span class="badge b-teal badge-teal badge-xs" style="padding:3px 8px; font-size:11.5px;">' + feat + '</span>'
                                    );
                                });
                            } else {
                                $featuresList.html('<span style="font-size:12px; color:var(--ink-400);">কোনো বিশেষ ফিচার সক্রিয় নেই</span>');
                            }

                            $('#shop-modal-loading').hide();
                            $('#shop-modal-content').show();
                        },
                        error: function () {
                            $('#shop-modal-loading').html(
                                '<span style="color:var(--red-600); font-weight:600;">তথ্য লোড করতে সমস্যা হয়েছে। অনুগ্রহ করে পুনরায় চেষ্টা করুন।</span>'
                            );
                        }
                    });
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initShopModalEvents);
            } else {
                initShopModalEvents();
            }
        })();
        </script>
    @endpush
</x-core::layout>
