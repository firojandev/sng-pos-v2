<x-core::layout title="হাজিরা মেশিন" title-en="Attendance Devices" subtitle="ZKTeco মেশিন থেকে স্বয়ংক্রিয় হাজিরা ও ফাইল ইমপোর্ট" subtitle-en="Automatic attendance from ZKTeco machines and file import" active="attendance">
    <x-employee::tabbar active="devices" />

    @php
        $host = parse_url($serverUrl, PHP_URL_HOST);
        $port = parse_url($serverUrl, PHP_URL_PORT) ?? (str_starts_with($serverUrl, 'https') ? 443 : 80);
    @endphp

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(340px, 1fr)); gap:16px; align-items:start; margin-top:16px;">
        <div class="panel" style="margin-top:0; grid-column:1 / -1;">
            <div class="panel-head"><div class="panel-title"><span class="bn">মেশিনসমূহ</span><span class="en" style="display:none;">Machines</span></div></div>
            <div class="table-responsive">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th><span class="bn">নাম</span><span class="en" style="display:none;">Name</span></th>
                            <th><span class="bn">সিরিয়াল নম্বর</span><span class="en" style="display:none;">Serial Number</span></th>
                            <th><span class="bn">দোকান</span><span class="en" style="display:none;">Shop</span></th>
                            <th><span class="bn">শেষ সংযোগ</span><span class="en" style="display:none;">Last Seen</span></th>
                            <th class="table-cell-center"><span class="bn">আজকের পাঞ্চ</span><span class="en" style="display:none;">Punches Today</span></th>
                            <th class="table-cell-right"><span class="bn">অ্যাকশন</span><span class="en" style="display:none;">Action</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($devices as $device)
                            <tr>
                                <td style="font-weight:600;">
                                    {{ $device->name }}
                                    @unless ($device->is_active)
                                        <x-core::badge color="grey" size="xs" label="বন্ধ" label-en="Off" />
                                    @endunless
                                </td>
                                <td style="font-family:var(--font-mono, monospace);">{{ $device->serial_number }}</td>
                                <td>{{ $device->shop?->name }}</td>
                                <td style="font-size:12.5px;">
                                    @if ($device->last_seen_at)
                                        {{ $device->last_seen_at->diffForHumans() }} <span style="color:var(--ink-500);">({{ $device->last_ip }})</span>
                                    @else
                                        <span style="color:var(--ink-500);"><span class="bn">এখনো সংযোগ হয়নি</span><span class="en" style="display:none;">Not connected yet</span></span>
                                    @endif
                                </td>
                                <td class="table-cell-center">{{ $device->punches_today }}</td>
                                <td class="table-cell-right">
                                    <div style="display:flex; gap:4px; justify-content:flex-end;">
                                        <form method="POST" action="{{ route('attendance.devices.toggle', $device) }}">
                                            @csrf
                                            <x-core::button type="submit" size="sm" variant="soft" :color="$device->is_active ? 'secondary' : 'primary'" :icon="$device->is_active ? 'power' : 'check'" icon-only :title="$device->is_active ? 'বন্ধ করুন / Turn off' : 'চালু করুন / Turn on'" />
                                        </form>
                                        <form method="POST" action="{{ route('attendance.devices.destroy', $device) }}" class="delete-form" data-title="মেশিন মুছে ফেলবেন?" data-text="আগের পাঞ্চগুলো থাকবে।">
                                            @csrf
                                            @method('DELETE')
                                            <x-core::button type="submit" size="sm" variant="soft" color="danger" icon="trash-2" icon-only title="মুছুন / Delete" />
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-core::table.empty icon="smartphone" title="কোনো মেশিন যোগ করা হয়নি" title-en="No machines added" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="panel-body">
                <form method="POST" action="{{ route('attendance.devices.store') }}" style="display:flex; gap:8px; align-items:flex-end; flex-wrap:wrap;">
                    @csrf
                    <div style="width:200px;"><x-core::input size="sm" name="name" label="মেশিনের নাম" label-en="Machine Name" placeholder="যেমন: গেটের মেশিন" placeholder-en="e.g. Front gate" :required="true" /></div>
                    <div style="width:200px;"><x-core::input size="sm" name="serial_number" label="সিরিয়াল নম্বর (SN)" label-en="Serial Number (SN)" :required="true" /></div>
                    <div style="width:200px;"><x-core::select size="sm" name="shop_id" label="দোকান" label-en="Shop" :options="$shops->all()" :value="auth()->user()->shop_id" :required="true" /></div>
                    <x-core::button type="submit" size="sm" variant="solid" color="primary" icon="plus"><span class="bn">যোগ করুন</span><span class="en" style="display:none;">Add Machine</span></x-core::button>
                </form>
            </div>
        </div>

        <div class="panel" style="margin-top:0;">
            <div class="panel-head"><div class="panel-title"><span class="bn">মেশিন সেটআপ</span><span class="en" style="display:none;">Machine Setup</span></div></div>
            <div class="panel-body" style="font-size:13px; color:var(--ink-700); line-height:1.7;">
                <ol style="margin:0; padding-left:18px;">
                    <li><span class="bn">মেশিনের মেনু থেকে <b>Comm → Cloud Server Setting</b> (বা ADMS) খুলুন।</span><span class="en" style="display:none;">On the machine open <b>Comm → Cloud Server Setting</b> (or ADMS).</span></li>
                    <li><span class="bn">সার্ভার ঠিকানা</span><span class="en" style="display:none;">Server address</span>: <b style="font-family:var(--font-mono, monospace);">{{ $host }}</b></li>
                    <li><span class="bn">পোর্ট</span><span class="en" style="display:none;">Port</span>: <b style="font-family:var(--font-mono, monospace);">{{ $port }}</b></li>
                    <li><span class="bn">"Enable Domain Name" চালু করুন এবং Proxy বন্ধ রাখুন।</span><span class="en" style="display:none;">Turn on "Enable Domain Name" and leave the proxy off.</span></li>
                    <li><span class="bn">মেশিনের <b>সিরিয়াল নম্বর</b> (System Info) এখানে যোগ করুন।</span><span class="en" style="display:none;">Add the machine's <b>serial number</b> (System Info) above.</span></li>
                    <li><span class="bn">প্রত্যেক কর্মচারীর প্রোফাইলে মেশিনের ইউজার আইডি (<b>হাজিরা মেশিন আইডি</b>) দিন।</span><span class="en" style="display:none;">Enter each employee's machine user ID (<b>Attendance Device ID</b>) on their profile.</span></li>
                </ol>
                <p style="font-size:12px; color:var(--ink-500); margin:10px 0 0;">
                    <span class="bn">মেনুতে Cloud Server/ADMS না থাকলে মেশিন থেকে হাজিরা ফাইল ডাউনলোড করে নিচে ইমপোর্ট করুন।</span>
                    <span class="en" style="display:none;">If the menu has no Cloud Server/ADMS option, download the attendance file from the machine and import it below.</span>
                </p>
            </div>
        </div>

        <div class="panel" style="margin-top:0;">
            <div class="panel-head"><div class="panel-title"><span class="bn">ফাইল থেকে ইমপোর্ট</span><span class="en" style="display:none;">Import from File</span></div></div>
            <div class="panel-body">
                <p style="font-size:12.5px; color:var(--ink-600); margin:0 0 12px;">
                    <span class="bn">মেশিনের হাজিরা ফাইল (attlog .dat/.txt), CSV বা Excel — প্রথম কলামে মেশিন ইউজার আইডি, পরে তারিখ ও সময় (দিন/মাস/বছর)।</span>
                    <span class="en" style="display:none;">The machine's attendance file (attlog .dat/.txt), CSV or Excel — the machine user ID first, then the date and time (day/month/year).</span>
                </p>
                <form method="POST" action="{{ route('attendance.devices.import') }}" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:10px;">
                    @csrf
                    <x-core::input size="sm" type="file" name="file" label="ফাইল" label-en="File" accept=".dat,.txt,.csv,.xlsx,.xls" :required="true" />
                    <x-core::select size="sm" name="shop_id" label="দোকান" label-en="Shop" :options="$shops->all()" :value="auth()->user()->shop_id" :required="true" />
                    <div><x-core::button type="submit" size="sm" variant="solid" color="primary" icon="upload"><span class="bn">ইমপোর্ট করুন</span><span class="en" style="display:none;">Import</span></x-core::button></div>
                </form>
            </div>
        </div>

        <div class="panel" style="margin-top:0; grid-column:1 / -1;">
            <div class="panel-head" style="display:flex; justify-content:space-between; align-items:center;">
                <div class="panel-title"><span class="bn">অমিল পাঞ্চ</span><span class="en" style="display:none;">Unmatched Punches</span></div>
                @if ($unmatched->isNotEmpty())
                    <form method="POST" action="{{ route('attendance.devices.match') }}">
                        @csrf
                        <x-core::button type="submit" size="sm" variant="soft" color="primary" icon="refresh"><span class="bn">আবার মেলান</span><span class="en" style="display:none;">Match Again</span></x-core::button>
                    </form>
                @endif
            </div>
            <div class="panel-body" style="font-size:12.5px; color:var(--ink-600); padding-bottom:0;">
                <span class="bn">এই ইউজার আইডি কোনো কর্মচারীর প্রোফাইলে নেই। প্রোফাইলে আইডি দিয়ে "আবার মেলান" চাপুন।</span>
                <span class="en" style="display:none;">No employee has these machine user IDs. Enter the ID on the right profile, then press "Match Again".</span>
            </div>
            <div class="table-responsive">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th><span class="bn">মেশিন ইউজার আইডি</span><span class="en" style="display:none;">Machine User ID</span></th>
                            <th class="table-cell-center"><span class="bn">পাঞ্চ</span><span class="en" style="display:none;">Punches</span></th>
                            <th><span class="bn">শেষ পাঞ্চ</span><span class="en" style="display:none;">Last Punch</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($unmatched as $row)
                            <tr>
                                <td style="font-family:var(--font-mono, monospace); font-weight:600;">{{ $row->device_user_id }}</td>
                                <td class="table-cell-center">{{ $row->punches }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($row->last_punch)->format('d M, Y h:i A') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><x-core::table.empty icon="check" title="সব পাঞ্চ কর্মচারীর সাথে মিলেছে" title-en="Every punch is matched to an employee" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-core::layout>
