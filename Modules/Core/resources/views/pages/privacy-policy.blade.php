<x-core::legal-layout
    title="গোপনীয়তা নীতি ও তথ্যের সুরক্ষা"
    title-en="Privacy Policy & Data Protection"
    subtitle="SNGPOS ক্লাউড সফটওয়্যার ব্যবহারে আপনার ব্যবসা, বিক্রয় লেনদেন এবং গ্রাহকদের ব্যক্তিগত তথ্যের সর্বোচ্চ গোপনীয়তা, নিরাপত্তা ও সুরক্ষা নীতিমালা"
    subtitle-en="Comprehensive privacy policy and data security commitments for your business, sales transactions, and customer records on SNGPOS Cloud"
    active="privacy"
    badge="তথ্য সুরক্ষা ও গোপনীয়তা"
    badge-en="Privacy & Data Protection"
    last-updated="১১ সেপ্টেম্বর ২০২৬"
    last-updated-en="September 11, 2026"
    version="v2.0.0"
    :toc="[
        ['id' => 'sec-intro', 'title_bn' => '১. ভূমিকা ও মূলনীতি', 'title_en' => '1. Introduction & Scope', 'icon' => 'info'],
        ['id' => 'sec-data-collected', 'title_bn' => '২. সংগৃহীত তথ্যাবলি', 'title_en' => '2. Information We Collect', 'icon' => 'database'],
        ['id' => 'sec-usage', 'title_bn' => '৩. তথ্য ব্যবহারের উদ্দেশ্য', 'title_en' => '3. Purpose of Processing', 'icon' => 'cpu'],
        ['id' => 'sec-security', 'title_bn' => '৪. ডেটা সুরক্ষা ও ক্লাউড ব্যাকআপ', 'title_en' => '4. Security & Cloud Backups', 'icon' => 'shield-check'],
        ['id' => 'sec-ownership', 'title_bn' => '৫. ডেটার মালিকানা ও গোপনীয়তা', 'title_en' => '5. Ownership & Confidentiality', 'icon' => 'lock'],
        ['id' => 'sec-cookies', 'title_bn' => '৬. কুকিজ ও লোকাল স্টোরেজ', 'title_en' => '6. Cookies & Local Storage', 'icon' => 'cookie'],
        ['id' => 'sec-third-party', 'title_bn' => '৭. থার্ড-পার্টি ও গেটওয়ে', 'title_en' => '7. Third-Party Services', 'icon' => 'share-2'],
        ['id' => 'sec-rights', 'title_bn' => '৮. মার্চেন্টের অধিকার ও এক্সপোর্ট', 'title_en' => '8. Merchant Rights & Export', 'icon' => 'download'],
        ['id' => 'sec-retention', 'title_bn' => '৯. ডেটা সংরক্ষণ ও মুছুন', 'title_en' => '9. Data Retention & Deletion', 'icon' => 'trash-2'],
        ['id' => 'sec-updates', 'title_bn' => '১০. নীতিমালার হালনাগাদ', 'title_en' => '10. Policy Amendments', 'icon' => 'refresh-cw'],
        ['id' => 'sec-contact', 'title_bn' => '১১. যোগাযোগ ও সাপোর্ট', 'title_en' => '11. Support & Grievance', 'icon' => 'help-circle'],
    ]"
>
    @php
        $siteTitle = \Modules\Core\Models\Setting::getSiteTitle();
        $siteTitleBn = $siteTitle === 'SNGPOS' ? 'এসএনজিপস' : $siteTitle;
        $content = \Modules\Core\Support\LandingPageContent::all();
        $supportPhone = $content['support_phone'] ?? '+880 1886 861430';
        $supportEmail = $content['support_email'] ?? 'support@softngear.com';
        $officeAddress = $content['office_address'] ?? 'Shop 407, 3rd Floor, Shwapnochura Plaza, Rajshahi';
    @endphp

    {{-- SECTION 1: Introduction --}}
    <article class="legal-section" id="sec-intro">
        <div class="legal-section-header">
            <div class="legal-section-icon">
                <x-core::icon name="info" size="18" />
            </div>
            <h2 class="legal-section-title">
                <span class="bn">১. ভূমিকা ও মূলনীতি (Introduction & Scope)</span>
                <span class="en" style="display:none;">1. Introduction & Scope of Policy</span>
            </h2>
        </div>
        <div class="legal-prose">
            <p class="bn">
                <strong>{{ $siteTitleBn }} ({{ $siteTitle }})</strong>-এ আপনার গোপনীয়তা রক্ষা করা আমাদের অন্যতম প্রধান অগ্রাধিকার। {{ $siteTitle }} হলো সফটএনগিয়ার (Softngear) কর্তৃক উদ্ভাবিত ও পরিচালিত একটি ক্লাউড-ভিত্তিক পয়েন্ট অব সেল (POS), ডিজিটাল বাকি খাতা, ইনভেন্টরি স্টক ও ইআরপি ব্যবসা পরিচালনা প্ল্যাটফর্ম।
            </p>
            <p class="en" style="display:none;">
                At <strong>{{ $siteTitle }}</strong>, protecting your privacy and business data is our highest commitment. {{ $siteTitle }} is a next-generation cloud Point of Sale (POS), digital due ledger, inventory control, and enterprise retail management software engineered by Softngear.
            </p>

            <p class="bn">
                এই গোপনীয়তা নীতিমালায় সুস্পষ্টভাবে ব্যাখ্যা করা হয়েছে যে আপনি যখন আমাদের প্ল্যাটফর্ম, ওয়েব ড্যাশবোর্ড, সেলস টার্মিনাল বা মোবাইল ইন্টারফেস ব্যবহার করেন, তখন কীভাবে আপনার ব্যক্তিগত, ব্যবসায়িক ও গ্রাহকদের তথ্য সংগ্রহ, প্রক্রিয়াকরণ, সংরক্ষণ ও সুরক্ষিত রাখা হয়। বাংলাদেশের <em>ডিজিটাল নিরাপত্তা আইন (Digital Security Act)</em> এবং আন্তর্জাতিক ডেটা সুরক্ষার সর্বোচ্চ মানদণ্ড মেনে আমরা সকল কার্যক্রম পরিচালনা করি।
            </p>
            <p class="en" style="display:none;">
                This Privacy Policy outlines how we collect, process, store, and safeguard your personal, merchant, and customer records when you access our cloud platform, billing registers, or management portals. We operate strictly in compliance with applicable digital data protection standards and Bangladesh ICT laws.
            </p>

            <div class="legal-callout">
                <strong class="bn">মূল নিশ্চয়তা:</strong>
                <span class="bn">আপনার দোকানের কোনো পণ্য ক্যাটালগ, বিক্রয় হিসাব, লাভ-ক্ষতির অংক বা গ্রাহকের ফোন নম্বর আমরা কোনো অবস্থাতেই কোনো বিজ্ঞাপনী সংস্থা বা তৃতীয় পক্ষের কাছে বিক্রয়, ভাড়া বা অপব্যবহারের জন্য উন্মুক্ত করি না।</span>
                <strong class="en" style="display:none;">Core Guarantee:</strong>
                <span class="en" style="display:none;">We never sell, rent, monetize, or expose your store catalog, revenue numbers, margins, or customer contact registers to third-party advertisers or brokers under any circumstances.</span>
            </div>
        </div>
    </article>

    {{-- SECTION 2: Data Collected --}}
    <article class="legal-section" id="sec-data-collected">
        <div class="legal-section-header">
            <div class="legal-section-icon">
                <x-core::icon name="database" size="18" />
            </div>
            <h2 class="legal-section-title">
                <span class="bn">২. যেসকল তথ্য আমরা সংগ্রহ করি (Information We Collect)</span>
                <span class="en" style="display:none;">2. Information We Collect</span>
            </h2>
        </div>
        <div class="legal-prose">
            <p class="bn">
                দোকান পরিচালনা ও পিওএস সেবা নিরবচ্ছিন্নভাবে প্রদানের স্বার্থে আমরা নিম্নবর্ণিত ক্যাটাগরির তথ্য সংগ্রহ ও সংরক্ষণ করি:
            </p>
            <p class="en" style="display:none;">
                To deliver uninterrupted cloud POS operations, stock calculations, and billing services, we collect and process the following categories of information:
            </p>

            <ul>
                <li>
                    <strong class="bn">দোকান মালিক ও ব্যবহারকারীর প্রোফাইল:</strong>
                    <span class="bn">নাম, মোবাইল নম্বর (যা প্রধান লগইন আইডি হিসেবে ব্যবহৃত হয়), ইমেইল ঠিকানা, ইউজারনেম, এনক্রিপ্টেড পাসওয়ার্ড, এবং শাখা বা ক্যাশবক্স এক্সেস পিন।</span>
                    <strong class="en" style="display:none;">Account & Owner Credentials:</strong>
                    <span class="en" style="display:none;">Full name, mobile number (primary login identifier), email address, username, securely hashed passwords, and staff terminal PINs.</span>
                </li>
                <li>
                    <strong class="bn">দোকান ও ব্যবসা প্রতিষ্ঠানের বিবরণ:</strong>
                    <span class="bn">দোকানের নাম, ব্যবসার ধরন (মুদি, ফার্মেসি, গার্মেন্টস, ইলেকট্রনিক্স ইত্যাদি), স্লাগ/ইউআরএল, ট্রেড লাইসেন্স নম্বর (যদি দেন), ভ্যাট/টিআইএন/বিন নম্বর, মুদ্রার প্রতীক (যেমন: ৳), দোকানের শাখা ও গুদামের তালিকা এবং ভৌগোলিক ঠিকানা।</span>
                    <strong class="en" style="display:none;">Shop & Business Entity Details:</strong>
                    <span class="en" style="display:none;">Store name, business vertical, unique store slug, BIN/VAT registration number, currency symbol, branch locations, warehouse titles, and street address.</span>
                </li>
                <li>
                    <strong class="bn">পণ্য ও ইনভেন্টরি স্টক ডেটা:</strong>
                    <span class="bn">পণ্যের নাম, বারকোড/ইউপিসি/এসকেইউ, ব্র্যান্ড, ক্যাটাগরি, ক্রয়মূল্য, খুচরা বিক্রয়মূল্য, পাইকারি দর, ব্যাচ নম্বর, মেয়াদোত্তীর্ণের তারিখ এবং বর্তমান স্টক মজুদের পরিমাণ।</span>
                    <strong class="en" style="display:none;">Inventory & Catalog Records:</strong>
                    <span class="en" style="display:none;">Product names, barcodes, SKUs, brands, categories, purchase costs, retail and wholesale prices, batch numbers, expiration dates, and real-time inventory counts.</span>
                </li>
                <li>
                    <strong class="bn">বিক্রয়, ক্রয় ও ক্যাশবক্স লেনদেন:</strong>
                    <span class="bn">প্রতিটি ক্যাশ মেমোর নম্বর, তারিখ, ক্রয়ের রসিদ, বিক্রীত পণ্যের বিবরণ, মোট টাকা, ডিসকাউন্ট, ট্যাক্স/ভ্যাট, ক্যাশবক্সের প্রারম্ভিক ও সমাপনী ব্যালেন্স, আয়ের ও ব্যয়ের ভাউচারসমূহ।</span>
                    <strong class="en" style="display:none;">Sales, Purchasing & Cashbox Ledger:</strong>
                    <span class="en" style="display:none;">Invoice numbers, transaction timestamps, items billed, gross amounts, discounts, VAT rates, drawer opening/closing cash balances, expense categories, and supplier purchase orders.</span>
                </li>
                <li>
                    <strong class="bn">গ্রাহক ও সরবরাহকারীর তথ্য (CRM & বাকি খাতা):</strong>
                    <span class="bn">দোকানের ক্রেতাদের নাম, ফোন নম্বর, ঠিকানা, পূর্বের বাকি, বর্তমান দেনা-পাওনা এবং পেমেন্ট রিসিট হিস্ট্রি। একইভাবে সরবরাহকারীর প্রাপ্য ও প্রদেয় ব্যালেন্স।</span>
                    <strong class="en" style="display:none;">Customer & Supplier Contacts (Due Ledger):</strong>
                    <span class="en" style="display:none;">Customer names, telephone numbers, delivery addresses, outstanding dues, credit balance history, and supplier ledger entries recorded by your cashiers.</span>
                </li>
                <li>
                    <strong class="bn">সিস্টেম লগ ও অডিট ট্রেইল:</strong>
                    <span class="bn">লগইন আইপি অ্যাড্রেস, ব্রাউজার এজেন্ট, ডিভাইস মডেল, এবং অডিট হিস্ট্রি (কোন ক্যাশিয়ার কখন কোনো বিল বাতিল, ডিসকাউন্ট প্রয়োগ বা স্টক সংশোধন করেছেন)।</span>
                    <strong class="en" style="display:none;">System Logs & Audit Trail:</strong>
                    <span class="en" style="display:none;">IP addresses, browser signatures, device timestamps, and immutable audit logs capturing invoice edits, voided bills, cash drawer reconciliations, and role permissions.</span>
                </li>
            </ul>
        </div>
    </article>

    {{-- SECTION 3: Usage --}}
    <article class="legal-section" id="sec-usage">
        <div class="legal-section-header">
            <div class="legal-section-icon">
                <x-core::icon name="cpu" size="18" />
            </div>
            <h2 class="legal-section-title">
                <span class="bn">৩. তথ্যের ব্যবহারের উদ্দেশ্য ও ক্ষেত্রসমূহ (Purpose of Processing)</span>
                <span class="en" style="display:none;">3. Purpose of Processing & Use Cases</span>
            </h2>
        </div>
        <div class="legal-prose">
            <p class="bn">
                সংগৃহীত তথ্যাবলি শুধুমাত্র নিম্নলিখিত নির্দিষ্ট ও বৈধ উদ্দেশ্যে প্রক্রিয়া করা হয়:
            </p>
            <p class="en" style="display:none;">
                All collected information is processed solely for specific, transparent, and legitimate operational objectives:
            </p>

            <ol>
                <li>
                    <strong class="bn">পয়েন্ট অব সেল (POS) ও ইনভেন্টরি অটোমেশন:</strong>
                    <span class="bn">দ্রুত ক্যাশ মেমো তৈরি, থার্মাল ও এ৪ প্রিন্টারে চালান প্রিন্ট, বারকোড স্ক্যানিং ও স্টক থেকে তাৎক্ষণিক বিয়োগ কার্যকর করা।</span>
                    <span class="en" style="display:none;">To power counter billing, thermal and A4 receipt generation, barcode processing, and live warehouse inventory synchronization.</span>
                </li>
                <li>
                    <strong class="bn">ডিজিটাল বাকি খাতা ও এসএমএস এলার্ট:</strong>
                    <span class="bn">কাস্টমারের বাকি লেনদেন নির্ভুলভাবে সংরক্ষণ করা এবং মার্চেন্টের অনুমোদনক্রমে কাস্টমারের মোবাইলে স্বয়ংক্রিয় এসএমএস রসিদ ও বাকি পরিশোধের তাগাদা পাঠানো।</span>
                    <span class="en" style="display:none;">To balance credit ledgers and dispatch automated SMS invoices or due settlement reminders to your designated customers upon your authorization.</span>
                </li>
                <li>
                    <strong class="bn">আর্থিক প্রতিবেদন ও হিসাবরক্ষণ:</strong>
                    <span class="bn">দৈনিক, সাপ্তাহিক ও মাসিক বিক্রির লাভ-ক্ষতি, গ্রস মার্জিন, ক্যাশবক্স ক্যাশ-ইন/ক্যাশ-আউট এবং ট্যাক্স/ভ্যাট রিপোর্ট গণনা।</span>
                    <span class="en" style="display:none;">To aggregate real-time profit & loss statements, gross margin figures, daily cashflow reconciliation, and tax summaries.</span>
                </li>
                <li>
                    <strong class="bn">নিরাপত্তা ও অডিট পর্যবেক্ষণ:</strong>
                    <span class="bn">দোকানের অভ্যন্তরীণ চুরি বা ক্যাশ গরমিল প্রতিরোধে অডিট ট্রেইলের মাধ্যমে যেকোনো অনাকাঙ্ক্ষিত লেনদেন শনাক্ত করা।</span>
                    <span class="en" style="display:none;">To maintain tamper-resistant audit logs protecting store owners against counter pilferage, unauthorized bill voids, or unrecorded cash outflows.</span>
                </li>
                <li>
                    <strong class="bn">গ্রাহক সহায়তা ও সিস্টেম হালনাগাদ:</strong>
                    <span class="bn">সফটওয়্যার পরিচালনা সংক্রান্ত কোনো সমস্যা দেখা দিলে মার্চেন্টকে তাৎক্ষণিক দূরবর্তী সহায়তা (Remote Support) প্রদান এবং গুরুত্বপূর্ণ আপডেট নিশ্চিত করা।</span>
                    <span class="en" style="display:none;">To provide dedicated customer helpline assistance, troubleshooting, remote setup diagnostics, and mission-critical cloud patches.</span>
                </li>
            </ol>
        </div>
    </article>

    {{-- SECTION 4: Security & Cloud Backups --}}
    <article class="legal-section" id="sec-security">
        <div class="legal-section-header">
            <div class="legal-section-icon">
                <x-core::icon name="shield-check" size="18" />
            </div>
            <h2 class="legal-section-title">
                <span class="bn">৪. ডেটা সুরক্ষা ও ক্লাউড ব্যাকআপ (Security & Cloud Backups)</span>
                <span class="en" style="display:none;">4. Security Protocols & Cloud Backups</span>
            </h2>
        </div>
        <div class="legal-prose">
            <p class="bn">
                {{ $siteTitleBn }}-এ সংরক্ষিত ডেটার সুরক্ষায় আমরা আন্তর্জাতিক মানের মাল্টি-লেয়ার সিকিউরিটি আর্কিটেকচার প্রয়োগ করেছি:
            </p>
            <p class="en" style="display:none;">
                {{ $siteTitle }} utilizes robust, enterprise-grade multi-layer security infrastructure to protect business databases:
            </p>

            <ul>
                <li>
                    <strong class="bn">২৫৬-বিট এসএসএল/টিএলএস এনক্রিপশন:</strong>
                    <span class="bn">আপনার ব্রাউজার থেকে ক্লাউড সার্ভারে প্রেরিত প্রতিটি ডেটা প্যাকেট মিলিটারি-গ্রেড 256-bit SSL এনক্রিপশনের মাধ্যমে স্থানান্তরিত হয়।</span>
                    <span class="en" style="display:none;">Every byte exchanged between your cash counter browser and our cloud cluster is shielded with 256-bit SSL/TLS cryptographic protocols.</span>
                </li>
                <li>
                    <strong class="bn">মাল্টি-ট্যানেন্ট লজিক্যাল আইসোলেশন:</strong>
                    <span class="bn">প্রতিটি দোকানের ডেটাবেস লজিক সম্পূর্ণ আলাদা ও সুরক্ষিত। এক দোকানের এডমিন বা কর্মচারী কখনোই অন্য কোনো দোকানের পণ্য তালিকা, দাম বা কাস্টমার ডেটা দেখতে পারে না।</span>
                    <span class="en" style="display:none;">Strict multi-tenant software boundaries ensure that each merchant's data is totally partitioned. Store A cannot view, access, or infer Store B's database records.</span>
                </li>
                <li>
                    <strong class="bn">স্বয়ংক্রিয় দৈনিক ক্লাউড ব্যাকআপ:</strong>
                    <span class="bn">প্রতি ২৪ ঘণ্টায় সমগ্র ডেটাবেসের সুরক্ষিত অফসাইট ক্লাউড ব্যাকআপ নেওয়া হয়। এর ফলে আপনার দোকানের কম্পিউটার, মোবাইল বা ডিভাইস নষ্ট, চুরি বা ক্ষতিগ্রস্ত হলেও কোনো ডেটা বিনষ্ট হয় না।</span>
                    <span class="en" style="display:none;">Automated offsite database snapshots are compiled every 24 hours. Even if your store hardware fails, your business ledger remains 100% intact and instantly recoverable.</span>
                </li>
                <li>
                    <strong class="bn">পাসওয়ার্ড ও পিন ক্রিপ্টোগ্রাফি:</strong>
                    <span class="bn">সকল ইউজার পাসওয়ার্ড শক্তিশালী Bcrypt অ্যালগরিদম দ্বারা হ্যাশ করা থাকে। সফটএনগিয়ার কর্তৃপক্ষ বা কোনো প্রকৌশলীর পক্ষেও আপনার আসল পাসওয়ার্ড দেখা সম্ভব নয়।</span>
                    <span class="en" style="display:none;">All passwords and sensitive security credentials are irreversibly hashed with modern Bcrypt algorithms. Not even our engineers can view plain-text credentials.</span>
                </li>
            </ul>
        </div>
    </article>

    {{-- SECTION 5: Ownership --}}
    <article class="legal-section" id="sec-ownership">
        <div class="legal-section-header">
            <div class="legal-section-icon">
                <x-core::icon name="lock" size="18" />
            </div>
            <h2 class="legal-section-title">
                <span class="bn">৫. তথ্যের মালিকানা ও ব্যবসায়িক গোপনীয়তা (Ownership & Confidentiality)</span>
                <span class="en" style="display:none;">5. Merchant Data Ownership & Confidentiality</span>
            </h2>
        </div>
        <div class="legal-prose">
            <p class="bn">
                <strong>আপনি আপনার ডেটার একমাত্র মালিক:</strong> আপনার প্রতিষ্ঠানে ব্যবহৃত সকল পণ্যের নাম, ক্রয়-বিক্রয় মূল্য, কাস্টমারের তালিকা, দৈনিক লেনদেন ও ক্যাশবক্স হিসেব সম্পূর্ণভাবে আপনার নিজস্ব সম্পদ। {{ $siteTitle }} শুধুমাত্র আপনার নির্দেশ ও সেবার চুক্তি অনুযায়ী এই তথ্য ক্লাউডে প্রক্রিয়াকরণ ও সংরক্ষণ করে।
            </p>
            <p class="en" style="display:none;">
                <strong>You Own 100% of Your Data:</strong> You retain complete proprietary ownership over every product catalogue, inventory record, price sheet, customer register, and financial journal entered into your account. {{ $siteTitle }} acts strictly as a trusted data custodian and software provider.
            </p>

            <p class="bn">
                আমরা কোনো অবস্থাতেই আপনার ব্যবসায়িক মুনাফা, বেচাকেনার পরিমাণ বা কাস্টমারের ফোন নম্বর কোনো প্রতিদ্বন্দ্বী প্রতিষ্ঠান বা তৃতীয় পক্ষের বিপণন সংস্থার সাথে শেয়ার করি না। আদালতের আইনানুগ নির্দেশ ব্যতীত অন্য কোনো ব্যক্তি বা সংস্থাকে আপনার ডেটা সরবরাহ করা হয় না।
            </p>
            <p class="en" style="display:none;">
                We never share, disclose, or monetize your store earnings, product movements, or customer phone lists with competitors, marketing firms, or data aggregators. We only provide disclosure if explicitly ordered under a valid warrant by a court of competent jurisdiction under the laws of Bangladesh.
            </p>
        </div>
    </article>

    {{-- SECTION 6: Cookies --}}
    <article class="legal-section" id="sec-cookies">
        <div class="legal-section-header">
            <div class="legal-section-icon">
                <x-core::icon name="cookie" size="18" />
            </div>
            <h2 class="legal-section-title">
                <span class="bn">৬. কুকিজ ও ব্রাউজার স্টোরেজ (Cookies & Local Storage)</span>
                <span class="en" style="display:none;">6. Cookies & Browser Local Storage</span>
            </h2>
        </div>
        <div class="legal-prose">
            <p class="bn">
                আমাদের ওয়েব অ্যাপ্লিকেশন নির্বিঘ্নে ব্যবহারের সুবিধার্থে ব্রাউজারে সীমিত সংখ্যক সেশন কুকি ও লোকাল স্টোরেজ ব্যবহার করা হয়:
            </p>
            <p class="en" style="display:none;">
                To optimize fast point-of-sale response and save your preferences, our application uses light session cookies and local storage tokens:
            </p>

            <ul>
                <li>
                    <strong class="bn">সেশন ও সিকিউরিটি কুকি (Session & CSRF):</strong>
                    <span class="bn">ব্যবহারকারীর নিরাপদ লগইন বজায় রাখা এবং সাইবার আক্রমণ (CSRF) প্রতিরোধে ব্যবহৃত হয়।</span>
                    <span class="en" style="display:none;">Mandatory tokens maintaining authenticated user sessions and preventing Cross-Site Request Forgery (CSRF).</span>
                </li>
                <li>
                    <strong class="bn">থিম পছন্দ (Theme Preference):</strong>
                    <span class="bn">আপনার নির্বাচিত লাইট মোড বা ডার্ক মোড সেটিংস ব্রাউজারে মনে রাখার জন্য (`theme=light/dark`) ব্যবহৃত হয়।</span>
                    <span class="en" style="display:none;">Saves your visual choice between Light and Dark mode (`theme=light/dark`).</span>
                </li>
                <li>
                    <strong class="bn">ভাষা সেটিংস (Language Preference):</strong>
                    <span class="bn">আপনার বাংলা বা ইংরেজি ইন্টারফেস পছন্দ মনে রাখার জন্য (`lang=bn/en`) ব্যবহৃত হয়।</span>
                    <span class="en" style="display:none;">Remembers your bilingual UI toggle between Bangla and English (`lang=bn/en`).</span>
                </li>
            </ul>
        </div>
    </article>

    {{-- SECTION 7: Third-Party Services --}}
    <article class="legal-section" id="sec-third-party">
        <div class="legal-section-header">
            <div class="legal-section-icon">
                <x-core::icon name="share-2" size="18" />
            </div>
            <h2 class="legal-section-title">
                <span class="bn">৭. থার্ড-পার্টি সার্ভিস ও গেটওয়ে (Third-Party Integrations)</span>
                <span class="en" style="display:none;">7. Third-Party Services & Integrations</span>
            </h2>
        </div>
        <div class="legal-prose">
            <p class="bn">
                নির্দিষ্ট সেবা সম্পাদনের জন্য আমরা বিশ্বস্ত অনুমোদিত গেটওয়ে ব্যবহার করি:
            </p>
            <p class="en" style="display:none;">
                To execute specific features, we interface with licensed telecommunication and financial gateways:
            </p>

            <ul>
                <li>
                    <strong class="bn">এসএমএস গেটওয়ে পার্টনার:</strong>
                    <span class="bn">কাস্টমারকে ডিজিটাল মেমো বা বাকি তাগাদার এসএমএস পাঠানোর সময় শুধুমাত্র প্রাপকের মোবাইল নম্বর ও বার্তার টেক্সটটি টেলিযোগাযোগ নিয়ন্ত্রক সংস্থা (BTRC) অনুমোদিত গেটওয়েতে পাঠানো হয়।</span>
                    <span class="en" style="display:none;">SMS Gateways: Only recipient mobile numbers and billing SMS texts are passed to BTRC-authorized telecom gateway providers.</span>
                </li>
                <li>
                    <strong class="bn">পেমেন্ট গেটওয়ে (সাবস্ক্রিপশন ফি):</strong>
                    <span class="bn">প্যাকেজ নবায়নের সময় বিকাশ, নগদ, রকেট বা ভিসা/মাস্টারকার্ডের নিরাপদ পেমেন্ট উইন্ডো ব্যবহৃত হয়। আমরা কোনো কার্ড নম্বর, সিভিভি বা ব্যাংকিং ওটিপি আমাদের সার্ভারে সংরক্ষণ করি না।</span>
                    <span class="en" style="display:none;">Payment Gateways: Online subscription renewals are handled directly through licensed gateways (bKash, Nagad, Card networks). We never see, touch, or store sensitive bank account PINs or card CVVs.</span>
                </li>
            </ul>
        </div>
    </article>

    {{-- SECTION 8: Merchant Rights & Data Export --}}
    <article class="legal-section" id="sec-rights">
        <div class="legal-section-header">
            <div class="legal-section-icon">
                <x-core::icon name="download" size="18" />
            </div>
            <h2 class="legal-section-title">
                <span class="bn">৮. মার্চেন্টের অধিকার ও ডেটা এক্সপোর্ট (Merchant Rights & Export)</span>
                <span class="en" style="display:none;">8. Merchant Rights & Data Portability</span>
            </h2>
        </div>
        <div class="legal-prose">
            <p class="bn">
                দোকান মালিক হিসেবে আপনার নিজ ডেটার ওপর পূর্ণ অধিকার রয়েছে:
            </p>
            <p class="en" style="display:none;">
                As an authorized merchant on {{ $siteTitle }}, you retain unambiguous data autonomy:
            </p>

            <ul>
                <li>
                    <strong class="bn">এক ক্লিকে ডেটা এক্সপোর্ট:</strong>
                    <span class="bn">যেকোনো সময় আপনার ড্যাশবোর্ড থেকে সকল পণ্য তালিকা, বিক্রয় রিপোর্ট, গ্রাহকের বাকি খাতা ও ব্যালেন্স শিট এক্সেল (Excel), সিএসভি (CSV) ও পিডিএফ (PDF) ফরম্যাটে ডাউনলোড করে নিতে পারবেন।</span>
                    <span class="en" style="display:none;">Data Portability: Instantly export your product inventories, customer due balances, sales records, and tax reports to Excel, CSV, or PDF formats whenever needed.</span>
                </li>
                <li>
                    <strong class="bn">তথ্য সংশোধন ও আপডেট:</strong>
                    <span class="bn">দোকানের প্রোফাইল, ফোন নম্বর, শাখা, স্টক ও কর্মচারীর দায়িত্ব আপনি যেকোনো সময় আপনার এডমিন অ্যাকাউন্ট থেকে পরিবর্তন বা পরিমার্জন করতে পারেন।</span>
                    <span class="en" style="display:none;">Right to Rectify: Edit and update store addresses, phone numbers, branch structures, employee roles, and catalog prices at your discretion.</span>
                </li>
            </ul>
        </div>
    </article>

    {{-- SECTION 9: Retention & Deletion --}}
    <article class="legal-section" id="sec-retention">
        <div class="legal-section-header">
            <div class="legal-section-icon">
                <x-core::icon name="trash-2" size="18" />
            </div>
            <h2 class="legal-section-title">
                <span class="bn">৯. ডেটা সংরক্ষণ ও অ্যাকাউন্ট মুছে ফেলা (Data Retention & Account Deletion)</span>
                <span class="en" style="display:none;">9. Data Retention & Account Deletion</span>
            </h2>
        </div>
        <div class="legal-prose">
            <p class="bn">
                যতদিন আপনার অ্যাকাউন্টটি সক্রিয় থাকবে, ততদিন আপনার ব্যবসায়ের নিরবচ্ছিন্ন হিসাব নিশ্চিত করতে ডেটা ক্লাউডে সুরক্ষিত থাকবে। আপনি যদি কখনো {{ $siteTitle }} ব্যবহার বন্ধ করতে চান:
            </p>
            <p class="en" style="display:none;">
                Your data is retained for as long as your shop account remains active to provide continuous ledger records. If you choose to discontinue service:
            </p>

            <p class="bn">
                আপনি আপনার সমস্ত ব্যবসায়িক রিপোর্ট এক্সপোর্ট করে নেওয়ার পর আমাদের সাপোর্ট ডেস্কে লিখিত ইমেইলের মাধ্যমে অ্যাকাউন্ট ও ডেটা স্থায়ীভাবে মুছে ফেলার (Permanent Deletion) অনুরোধ জানাতে পারেন। যাচাইকরণের পর ৩০ দিনের মধ্যে সংশ্লিষ্ট ডেটাবেস থেকে অ্যাকাউন্টের সমুদয় তথ্য স্থায়ীভাবে মুছে ফেলা হবে।
            </p>
            <p class="en" style="display:none;">
                After exporting your historical ledgers, you may request permanent deletion of your account and databases by writing to our support desk. Upon owner identity verification, your operational data will be permanently purged within 30 days.
            </p>
        </div>
    </article>

    {{-- SECTION 10: Policy Updates --}}
    <article class="legal-section" id="sec-updates">
        <div class="legal-section-header">
            <div class="legal-section-icon">
                <x-core::icon name="refresh-cw" size="18" />
            </div>
            <h2 class="legal-section-title">
                <span class="bn">১০. নীতিমালার হালনাগাদ (Policy Amendments)</span>
                <span class="en" style="display:none;">10. Amendments to this Policy</span>
            </h2>
        </div>
        <div class="legal-prose">
            <p class="bn">
                প্রযুক্তির অগ্রগতি, নতুন ফিচার সংযোজন বা সরকারি আইন পরিবর্তনের সাথে সামঞ্জস্য রেখে আমরা সময়ে সময়ে এই গোপনীয়তা নীতি হালনাগাদ করতে পারি। নীতিমালায় কোনো তাৎপর্যপূর্ণ পরিবর্তন এলে তা আমাদের ওয়েব ড্যাশবোর্ড নোটিফিকেশন বা রেজিস্টার্ড মোবাইল/ইমেইলের মাধ্যমে মার্চেন্টদের অবহিত করা হবে।
            </p>
            <p class="en" style="display:none;">
                We may periodically update this Privacy Policy to reflect software enhancements, cloud security advances, or regulatory revisions. Significant modifications will be communicated through in-app dashboard alerts or email notices.
            </p>
        </div>
    </article>

    {{-- SECTION 11: Contact --}}
    <article class="legal-section" id="sec-contact">
        <div class="legal-section-header">
            <div class="legal-section-icon">
                <x-core::icon name="help-circle" size="18" />
            </div>
            <h2 class="legal-section-title">
                <span class="bn">১১. যোগাযোগ ও সাপোর্ট ডেস্ক (Contact & Support Desk)</span>
                <span class="en" style="display:none;">11. Inquiries & Contact Support</span>
            </h2>
        </div>
        <div class="legal-prose">
            <p class="bn">
                এই গোপনীয়তা নীতি বা আপনার তথ্যের সুরক্ষা সংক্রান্ত যেকোনো জিজ্ঞাসা, পরামর্শ বা সহায়তার জন্য আমাদের ডেডিকেটেড সাপোর্ট টিমের সাথে যোগাযোগ করুন:
            </p>
            <p class="en" style="display:none;">
                If you have questions, feedback, or grievance regarding data privacy on {{ $siteTitle }}, reach out to our dedicated support team:
            </p>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:14px; margin-top:16px;">
                <div style="background:var(--paper); border:1px solid var(--border); border-radius:10px; padding:14px;">
                    <div style="font-size:11.5px; color:var(--ink-400); font-weight:700; text-transform:uppercase;">
                        <span class="bn">সাপোর্ট হটলাইন / হোয়াটসঅ্যাপ</span>
                        <span class="en" style="display:none;">Phone / WhatsApp</span>
                    </div>
                    <div style="font-size:14px; font-weight:700; color:var(--ink-900); margin-top:4px;">
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $supportPhone) }}" style="color:inherit; text-decoration:none;">{{ $supportPhone }}</a>
                    </div>
                </div>

                <div style="background:var(--paper); border:1px solid var(--border); border-radius:10px; padding:14px;">
                    <div style="font-size:11.5px; color:var(--ink-400); font-weight:700; text-transform:uppercase;">
                        <span class="bn">ইমেইল ঠিকানা</span>
                        <span class="en" style="display:none;">Support Email</span>
                    </div>
                    <div style="font-size:14px; font-weight:700; color:var(--ink-900); margin-top:4px;">
                        <a href="mailto:{{ $supportEmail }}" style="color:var(--teal-800); text-decoration:none;">{{ $supportEmail }}</a>
                    </div>
                </div>

                <div style="background:var(--paper); border:1px solid var(--border); border-radius:10px; padding:14px;">
                    <div style="font-size:11.5px; color:var(--ink-400); font-weight:700; text-transform:uppercase;">
                        <span class="bn">অফিস কার্যালয়</span>
                        <span class="en" style="display:none;">Office Location</span>
                    </div>
                    <div style="font-size:13px; font-weight:600; color:var(--ink-900); margin-top:4px;">
                        {{ $officeAddress }}
                    </div>
                </div>
            </div>
        </div>
    </article>
</x-core::legal-layout>
