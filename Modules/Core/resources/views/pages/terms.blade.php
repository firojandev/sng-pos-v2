<x-core::legal-layout
    title="ব্যবহারের সাধারণ শর্তাবলী ও নীতিমালা"
    title-en="Terms of Service & General Operating Conditions"
    subtitle="SNGPOS ক্লাউড প্ল্যাটফর্ম, পয়েন্ট অব সেল সফটওয়্যার এবং আনুষঙ্গিক ডিজিটাল সেবা ব্যবহারের বিধিবদ্ধ নিয়মাবলী, পারস্পরিক অধিকার ও দায়বদ্ধতা"
    subtitle-en="Legally binding terms, mutual rights, operational guidelines, and service conditions governing the use of SNGPOS Cloud POS & ERP software"
    active="terms"
    badge="ব্যবহারের শর্তাবলী"
    badge-en="Terms of Service"
    last-updated="১১ সেপ্টেম্বর ২০২৬"
    last-updated-en="September 11, 2026"
    version="v2.0.0"
    :toc="[
        ['id' => 'sec-terms-acceptance', 'title_bn' => '১. শর্তাবলীর গ্রহণযোগ্যতা', 'title_en' => '1. Acceptance of Terms', 'icon' => 'check-circle'],
        ['id' => 'sec-terms-eligibility', 'title_bn' => '২. যোগ্যতা ও অ্যাকাউন্ট সুরক্ষা', 'title_en' => '2. Eligibility & Accounts', 'icon' => 'user-check'],
        ['id' => 'sec-terms-license', 'title_bn' => '৩. সফটওয়্যার লাইসেন্স ও পরিধি', 'title_en' => '3. Scope of Software License', 'icon' => 'key'],
        ['id' => 'sec-terms-subscription', 'title_bn' => '৪. প্ল্যান, ফ্রি ট্রায়াল ও বিলিং', 'title_en' => '4. Plans, Free Tier & Billing', 'icon' => 'credit-card'],
        ['id' => 'sec-terms-prohibited', 'title_bn' => '৫. নিষিদ্ধ কার্যকলাপ', 'title_en' => '5. Prohibited Conduct', 'icon' => 'slash'],
        ['id' => 'sec-terms-accuracy', 'title_bn' => '৬. হিসাব ও হার্ডওয়্যারের দায়', 'title_en' => '6. Ledger & Hardware Responsibility', 'icon' => 'clipboard'],
        ['id' => 'sec-terms-uptime', 'title_bn' => '৭. ক্লাউড আপটাইম ও এসএলএ', 'title_en' => '7. Cloud Uptime & SLA', 'icon' => 'activity'],
        ['id' => 'sec-terms-ip', 'title_bn' => '৮. মেধা সম্পত্তি অধিকার', 'title_en' => '8. Intellectual Property', 'icon' => 'award'],
        ['id' => 'sec-terms-liability', 'title_bn' => '৯. দায়বদ্ধতার সীমাবদ্ধতা', 'title_en' => '9. Limitation of Liability', 'icon' => 'alert-octagon'],
        ['id' => 'sec-terms-termination', 'title_bn' => '১০. সেবা বাতিল ও স্থগিতকরণ', 'title_en' => '10. Termination & Suspension', 'icon' => 'x-circle'],
        ['id' => 'sec-terms-law', 'title_bn' => '১১. আইন ও বিচারিক এখতিয়ার', 'title_en' => '11. Governing Law', 'icon' => 'book-open'],
        ['id' => 'sec-terms-inquiries', 'title_bn' => '১২. যোগাযোগ ও আইনি সহায়তা', 'title_en' => '12. Legal Inquiries', 'icon' => 'help-circle'],
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

    {{-- SECTION 1: Acceptance --}}
    <article class="legal-section" id="sec-terms-acceptance">
        <div class="legal-section-header">
            <div class="legal-section-icon">
                <x-core::icon name="check-circle" size="18" />
            </div>
            <h2 class="legal-section-title">
                <span class="bn">১. শর্তাবলীর গ্রহণযোগ্যতা ও সম্মতি (Acceptance of Terms)</span>
                <span class="en" style="display:none;">1. Acceptance of Terms & Binding Agreement</span>
            </h2>
        </div>
        <div class="legal-prose">
            <p class="bn">
                <strong>{{ $siteTitleBn }} ({{ $siteTitle }})</strong> ক্লাউড পয়েন্ট অব সেল (POS), ইনভেন্টরি কন্ট্রোল এবং এন্টারপ্রাইজ রিসোর্স প্ল্যানিং (ERP) সফটওয়্যার সেবাটিতে আপনাকে স্বাগতম। এই সফটওয়্যারে কোনো নতুন দোকান রেজিস্টার করে, ব্যবহারকারী অ্যাকাউন্ট তৈরি করে, ফ্রি প্যাকেজ চালু করে অথবা যেকোনো পেইড সাবস্ক্রিপশন ফি পরিশোধ করে আপনি এই শর্তাবলীর সকল ধারা ও নিয়মাবলী সম্পূর্ণরূপে মেনে চলার আইনি সম্মতি দিচ্ছেন।
            </p>
            <p class="en" style="display:none;">
                Welcome to <strong>{{ $siteTitle }}</strong> Cloud POS & Business ERP. By registering a shop account, launching our free starter tier, or renewing any subscription plan, you enter into a legally binding contract and acknowledge full acceptance of these Terms of Service.
            </p>

            <p class="bn">
                আপনি যদি এই শর্তাবলীর কোনো অংশের সাথে দ্বিমত পোষণ করেন, তবে অনুগ্রহ করে আমাদের প্ল্যাটফর্মে অ্যাকাউন্ট রেজিস্ট্রেশন বা ব্যবহার করা থেকে বিরত থাকুন। এই শর্তাবলী সফটএনগিয়ার (Softngear) এবং আপনার (দোকান মালিক/মার্চেন্ট) মধ্যকার সেবা চুক্তির মূল ভিত্তি হিসেবে বিবেচিত হবে।
            </p>
            <p class="en" style="display:none;">
                If you do not agree with any provision herein, you must refrain from registering or using our platform. These terms constitute the entire operating agreement between Softngear and the registering merchant.
            </p>
        </div>
    </article>

    {{-- SECTION 2: Eligibility & Credentials --}}
    <article class="legal-section" id="sec-terms-eligibility">
        <div class="legal-section-header">
            <div class="legal-section-icon">
                <x-core::icon name="user-check" size="18" />
            </div>
            <h2 class="legal-section-title">
                <span class="bn">২. ব্যবহারের যোগ্যতা ও অ্যাকাউন্ট সুরক্ষা (Eligibility & Account Security)</span>
                <span class="en" style="display:none;">2. Account Eligibility & Credential Security</span>
            </h2>
        </div>
        <div class="legal-prose">
            <p class="bn">
                {{ $siteTitle }} প্ল্যাটফর্ম ব্যবহারের জন্য নিম্নোক্ত নীতিমালা প্রযোজ্য:
            </p>
            <p class="en" style="display:none;">
                The following eligibility and credential safeguards apply to all merchants:
            </p>

            <ul>
                <li>
                    <strong class="bn">বৈধ ব্যবসায়িক সত্তা:</strong>
                    <span class="bn">রেজিস্ট্রেশনকারীকে অবশ্যই বাংলাদেশের প্রচলিত আইনে বৈধ কোনো ব্যবসা প্রতিষ্ঠান, শপ, রিটেইল আউটলেট বা অনুমোদিত স্বত্বাধিকারী/প্রতিনিধি হতে হবে। রেজিস্ট্রেশনের সময় সঠিক ও নির্ভুল নাম, মোবাইল নম্বর ও ঠিকানা প্রদান করা বাধ্যতামূলক।</span>
                    <span class="en" style="display:none;">Lawful Business: The registering merchant must be a bona fide retail or enterprise business operating lawfully within the People's Republic of Bangladesh.</span>
                </li>
                <li>
                    <strong class="bn">পাসওয়ার্ড ও পিনের গোপনীয়তা:</strong>
                    <span class="bn">আপনার এডমিন পাসওয়ার্ড ও ক্যাশবক্স টার্মিনাল পিন সর্বদা গোপন রাখার দায়িত্ব সম্পূর্ণ আপনার। আপনার ক্রেডেনশিয়াল ব্যবহার করে কৃত সকল বিক্রয়, ক্রয়, ক্যাশ উত্তোলন বা পরিবর্তনের জন্য আপনি ব্যক্তিগতভাবে দায়ী থাকবেন।</span>
                    <span class="en" style="display:none;">Credential Confidentiality: You are strictly responsible for preserving the confidentiality of your admin login passwords and cashier PINs. All actions initiated under your authenticated credentials remain your sole responsibility.</span>
                </li>
                <li>
                    <strong class="bn">কর্মচারীদের ভূমিকা নির্ধারণ (RBAC):</strong>
                    <span class="bn">আপনার প্রতিষ্ঠানে কর্মরত ম্যানেজার, সেলসম্যান বা ক্যাশিয়ারদের ইউজার অ্যাকাউন্ট তৈরি ও তাদের জন্য নির্ধারিত পারমিশন (যেমন: ডিসকাউন্ট অনুমতি, বিল মোছার অনুমতি, স্টক পরিবর্তনের অনুমতি) সতর্কতার সাথে নির্ধারণ করার দায়িত্ব দোকান মালিকের।</span>
                    <span class="en" style="display:none;">Staff Permission Control: Store owners are responsible for allocating fine-grained Role-Based Access Control (RBAC) permissions to counter cashiers, branch managers, or accountants.</span>
                </li>
            </ul>
        </div>
    </article>

    {{-- SECTION 3: License & Scope --}}
    <article class="legal-section" id="sec-terms-license">
        <div class="legal-section-header">
            <div class="legal-section-icon">
                <x-core::icon name="key" size="18" />
            </div>
            <h2 class="legal-section-title">
                <span class="bn">৩. সফটওয়্যার লাইসেন্স ও ব্যবহারের পরিধি (Scope of Software License)</span>
                <span class="en" style="display:none;">3. Scope of Software License & Usage Rights</span>
            </h2>
        </div>
        <div class="legal-prose">
            <p class="bn">
                সফটএনগিয়ার আপনাকে {{ $siteTitle }} সফটওয়্যারটি ব্যবহার করার জন্য একটি অ-হস্তান্তরযোগ্য (Non-transferable), অ-একচেটিয়া (Non-exclusive), এবং সাবস্ক্রিপশন-ভিত্তিক সীমিত লাইসেন্স প্রদান করে।
            </p>
            <p class="en" style="display:none;">
                Softngear grants the merchant a non-exclusive, non-transferable, revocable, subscription-based license to access and operate {{ $siteTitle }} cloud applications.
            </p>

            <div class="legal-callout">
                <strong class="bn">লাইসেন্স সীমাবদ্ধতা:</strong>
                <span class="bn">আপনি কোনো অবস্থাতেই সফটওয়্যারের সোর্স কোড রিভার্স ইঞ্জিনিয়ার, ডিকম্পাইল, কপি, সাব-লাইসেন্স বা অননুমোদিতভাবে বাণিজ্যিক উদ্দেশ্যে পুনরায় বিক্রি করতে পারবেন না। এই সফটওয়্যারটি শুধুমাত্র আপনার নিজস্ব ব্যবসা পরিচালনার জন্য ব্যবহৃত হবে।</span>
                <strong class="en" style="display:none;">License Restrictions:</strong>
                <span class="en" style="display:none;">You may not decompile, reverse engineer, disassemble, replicate, white-label without authorization, or sublicense the software engine to third parties.</span>
            </div>
        </div>
    </article>

    {{-- SECTION 4: Subscription & Billing --}}
    <article class="legal-section" id="sec-terms-subscription">
        <div class="legal-section-header">
            <div class="legal-section-icon">
                <x-core::icon name="credit-card" size="18" />
            </div>
            <h2 class="legal-section-title">
                <span class="bn">৪. প্ল্যান, ফ্রি প্যাকেজ ও বিলিং নীতিমালা (Plans, Free Tier & Billing)</span>
                <span class="en" style="display:none;">4. Subscription Plans, Free Tier & Billing</span>
            </h2>
        </div>
        <div class="legal-prose">
            <p class="bn">
                {{ $siteTitle }} বিভিন্ন ধরনের প্যাকেজ ও সাবস্ক্রিপশন অফার করে:
            </p>
            <p class="en" style="display:none;">
                {{ $siteTitle }} provides scalable service tiers tailored to diverse store sizes:
            </p>

            <ul>
                <li>
                    <strong class="bn">ফ্রি স্টার্টার প্ল্যান (Free Tier):</strong>
                    <span class="bn">নতুন ক্ষুদ্র ব্যবসায়ীদের জন্য আজীবন ফ্রি প্যাকেজ প্রযোজ্য হতে পারে। ফ্রি প্যাকেজের আওতায় নির্দিষ্ট সীমা (যেমন: ১টি শাখা, ১টি গুদাম, ১ জন ইউজার ও ১০০টি পণ্যের ক্যাটালগ) নির্ধারিত থাকে। সীমা অতিক্রম করতে চাইলে মার্চেন্টকে প্রিমিয়াম প্যাকেজে আপগ্রেড করতে হবে।</span>
                    <span class="en" style="display:none;">Free Starter Tier: Designed for boutique shops with defined fair-usage thresholds (e.g. single outlet, single branch, 100 catalog products). Expanding capacity requires upgrading to an active tier.</span>
                </li>
                <li>
                    <strong class="bn">প্রিমিয়াম প্যাকেজ নবায়ন:</strong>
                    <span class="bn">মাসিক বা বাৎসরিক সাবস্ক্রিপশন ফি মেয়াদ শেষ হওয়ার পূর্বে নির্ধারিত অনলাইন গেটওয়ে (বিকাশ, নগদ, কার্ড) বা ব্যাংকিং চ্যানেলের মাধ্যমে পরিশোধযোগ্য। বাৎসরিক অগ্রিম পেমেন্টে বিশেষ ছাড় সুবিধা প্রযোজ্য।</span>
                    <span class="en" style="display:none;">Subscription Renewals: Paid tiers are billed on a recurring monthly or annual basis via automated local payment gateways. Special discounts apply for prepaid annual terms.</span>
                </li>
                <li>
                    <strong class="bn">গ্রেস পিরিয়ড ও সার্ভিস সাময়িক স্থগিত:</strong>
                    <span class="bn">সাবস্ক্রিপশনের মেয়াদ শেষ হওয়ার পর মার্চেন্টকে ৭ দিনের একটি গ্রেস পিরিয়ড প্রদান করা হবে। উক্ত সময়ের মধ্যে নবায়ন সম্পন্ন না হলে টার্মিনালের সেলস অপশন সাময়িকভাবে স্থগিত হতে পারে, তবে আপনার পূর্ববর্তী কোনো ডেটা মুছে ফেলা হবে না।</span>
                    <span class="en" style="display:none;">Grace Period: Following billing expiry, a 7-day grace period is provided. If renewals remain unpaid thereafter, billing operations may be temporarily restricted, while your historical ledger data remains safeguarded.</span>
                </li>
            </ul>
        </div>
    </article>

    {{-- SECTION 5: Prohibited Conduct --}}
    <article class="legal-section" id="sec-terms-prohibited">
        <div class="legal-section-header">
            <div class="legal-section-icon">
                <x-core::icon name="slash" size="18" />
            </div>
            <h2 class="legal-section-title">
                <span class="bn">৫. নিষিদ্ধ ব্যবহার ও অনৈতিক কার্যকলাপ (Prohibited Conduct)</span>
                <span class="en" style="display:none;">5. Prohibited Activities & Compliance</span>
            </h2>
        </div>
        <div class="legal-prose">
            <p class="bn">
                {{ $siteTitle }} প্ল্যাটফর্ম ব্যবহারের সময় নিম্নবর্ণিত কাজগুলো কঠোরভাবে নিষিদ্ধ:
            </p>
            <p class="en" style="display:none;">
                Merchants strictly agree to avoid the following unlawful activities:
            </p>

            <ol>
                <li>
                    <span class="bn">বাংলাদেশের প্রচলিত আইনে নিষিদ্ধ ঘোষিত পণ্য (যেমন: অবৈধ মাদকদ্রব্য, চোরাচালানকৃত পণ্য, নকল বা বিপজ্জনক অস্ত্র ইত্যাদি) বিক্রয়ের উদ্দেশ্যে সফটওয়্যার ব্যবহার।</span>
                    <span class="en" style="display:none;">Logging, managing, or billing illegal contraband, controlled substances without narcotics license, smuggled goods, or counterfeit wares prohibited under Bangladesh law.</span>
                </li>
                <li>
                    <span class="bn">ভ্যাট বা ট্যাক্স ফাঁকি দেওয়ার উদ্দেশ্যে মিথ্যা চালান তৈরি করা বা মানিলন্ডারিং সংশ্লিষ্ট কোনো প্রতারণামূলক কার্যকলাপ পরিচালনা করা।</span>
                    <span class="en" style="display:none;">Engaging in deliberate tax fraud, money laundering, or generation of deceptive fiscal instruments.</span>
                </li>
                <li>
                    <span class="bn">ক্লাউড সার্ভারে ক্ষতিকর মেলওয়্যার, ভাইরাস, বট বা অননুমোদিত স্ক্রিপ্ট আপলোড করার চেষ্টা করা।</span>
                    <span class="en" style="display:none;">Attempting to inject malicious code, automated bots, denial-of-service vectors, or unapproved scrapers into our cloud infrastructure.</span>
                </li>
                <li>
                    <span class="bn">অন্য কোনো দোকানের ডেটাবেসে অননুমোদিত অ্যাক্সেস বা সিস্টেমে দুর্বলতা খোঁজার চেষ্টা (Vulnerability Exploitation) করা।</span>
                    <span class="en" style="display:none;">Attempting unauthorized intrusion into neighboring tenant databases or security perimeter exploitation.</span>
                </li>
            </ol>
        </div>
    </article>

    {{-- SECTION 6: Accuracy & Hardware --}}
    <article class="legal-section" id="sec-terms-accuracy">
        <div class="legal-section-header">
            <div class="legal-section-icon">
                <x-core::icon name="clipboard" size="18" />
            </div>
            <h2 class="legal-section-title">
                <span class="bn">৬. হিসাব ও হার্ডওয়্যারের দায়বদ্ধতা (Transaction & Hardware Responsibility)</span>
                <span class="en" style="display:none;">6. Transaction Accuracy & Hardware Compliance</span>
            </h2>
        </div>
        <div class="legal-prose">
            <p class="bn">
                সফটওয়্যারটি গণনা ও হিসাবসংক্রান্ত কাজগুলো স্বয়ংক্রিয়ভাবে নির্ভুলভাবে সম্পাদন করে। তবে:
            </p>
            <p class="en" style="display:none;">
                The software executes calculations algorithmically based upon input parameters. Therefore:
            </p>

            <ul>
                <li>
                    <strong class="bn">ইনপুট তথ্যের নির্ভুলতা:</strong>
                    <span class="bn">পণ্যের নাম, ক্রয়মূল্য, খুচরা মূল্য, ভ্যাট পারসেন্টেজ, গ্রাহকের ফোন নম্বর ও বাকি খাতার এন্ট্রির নির্ভুলতা নিশ্চিত করার সার্বিক দায় মার্চেন্টের। ভুল তথ্য ইনপুটের কারণে কোনো ব্যবসায়িক ক্ষতির দায় সফটওয়্যার কর্তৃপক্ষের নয়।</span>
                    <span class="en" style="display:none;">Input Accuracy: Store cashiers and owners are solely responsible for validating product purchase prices, retail tags, VAT percentages, and due credit figures entered into invoices.</span>
                </li>
                <li>
                    <strong class="bn">দোকানের হার্ডওয়্যার ও ইন্টারনেট:</strong>
                    <span class="bn">দোকানের থার্মাল প্রিন্টার (58mm/80mm), বারকোড স্ক্যানার, ক্যাশ ড্রয়ার, কম্পিউটার এবং নিরবচ্ছিন্ন ইন্টারনেট সংযোগ কার্যকর রাখার দায়িত্ব মার্চেন্টের। স্থানীয় প্রিন্টারের ড্রাইভার ত্রুটি বা ক্যাবল সমস্যার জন্য সফটওয়্যার দায়ী থাকবে না।</span>
                    <span class="en" style="display:none;">Counter Hardware: Store management is responsible for maintaining functional POS receipt printers, barcode scanners, desktop hardware, and local ISP/mobile hotspot internet connectivity.</span>
                </li>
            </ul>
        </div>
    </article>

    {{-- SECTION 7: Uptime & SLA --}}
    <article class="legal-section" id="sec-terms-uptime">
        <div class="legal-section-header">
            <div class="legal-section-icon">
                <x-core::icon name="activity" size="18" />
            </div>
            <h2 class="legal-section-title">
                <span class="bn">৭. ক্লাউড আপটাইম ও সার্ভিস লেভেল (Cloud Uptime & Service SLA)</span>
                <span class="en" style="display:none;">7. Cloud Service Uptime & Maintenance</span>
            </h2>
        </div>
        <div class="legal-prose">
            <p class="bn">
                আমরা আমাদের ক্লাউড সার্ভারে <strong>৯৯.৯% আপটাইম (Uptime)</strong> বজায় রাখার নিরবচ্ছিন্ন প্রচেষ্টা চালাই। জরুরি নিরাপত্তা প্যাচ, সার্ভার আপগ্রেড বা পূর্বনির্ধারিত রক্ষণাবেক্ষণের (Scheduled Maintenance) ক্ষেত্রে সাধারণত রাত ১২টা থেকে ভোর ৬টার মধ্যে সংক্ষিপ্ত বিরতি নেওয়া হতে পারে এবং এ বিষয়ে পূর্বেই ড্যাশবোর্ডে নোটিশ প্রদান করা হবে।
            </p>
            <p class="en" style="display:none;">
                We target a <strong>99.9% cloud service uptime</strong> SLA. Critical security deployments and scheduled server upgrades are strictly routed during low-traffic overnight hours with advance dashboard notices whenever feasible.
            </p>
        </div>
    </article>

    {{-- SECTION 8: Intellectual Property --}}
    <article class="legal-section" id="sec-terms-ip">
        <div class="legal-section-header">
            <div class="legal-section-icon">
                <x-core::icon name="award" size="18" />
            </div>
            <h2 class="legal-section-title">
                <span class="bn">৮. মেধা সম্পত্তি অধিকার (Intellectual Property Rights)</span>
                <span class="en" style="display:none;">8. Intellectual Property & Brand Ownership</span>
            </h2>
        </div>
        <div class="legal-prose">
            <p class="bn">
                {{ $siteTitle }} ট্রেডমার্ক, লোগো, সফটওয়্যার সোর্স কোড, ডাটাবেস স্কিমা, ইউআই/ইউএক্স নকশা এবং সংশ্লিষ্ট সকল মেধা সম্পত্তির একক ও নিরঙ্কুশ মালিক Softngear।
            </p>
            <p class="en" style="display:none;">
                All proprietary algorithms, source code, UI components, databases, and trademarks embodied in {{ $siteTitle }} remain the exclusive intellectual property of Softngear.
            </p>

            <p class="bn">
                অপরদিকে, আপনার দোকানের ব্র্যান্ড লোগো, পণ্যের তালিকা, কাস্টমার রেকর্ড এবং ব্যবসায়িক লেনদেন সংক্রান্ত যাবতীয় তথ্যের মেধা সম্পত্তি সম্পূর্ণভাবে আপনার নিজস্ব থাকবে।
            </p>
            <p class="en" style="display:none;">
                Conversely, your store trademarks, trade logos, catalog data, and transactional journals belong exclusively to your business.
            </p>
        </div>
    </article>

    {{-- SECTION 9: Limitation of Liability --}}
    <article class="legal-section" id="sec-terms-liability">
        <div class="legal-section-header">
            <div class="legal-section-icon">
                <x-core::icon name="alert-octagon" size="18" />
            </div>
            <h2 class="legal-section-title">
                <span class="bn">৯. দায়বদ্ধতার সীমাবদ্ধতা (Limitation of Liability)</span>
                <span class="en" style="display:none;">9. Limitation of Liability & Warranty Disclaimer</span>
            </h2>
        </div>
        <div class="legal-prose">
            <p class="bn">
                আইন দ্বারা অনুমোদিত সর্বোচ্চ সীমা পর্যন্ত, {{ $siteTitle }} সেবাটি <em>"যেমন আছে" (As-Is)</em> এবং <em>"যেমন উপলব্ধ" (As-Available)</em> ভিত্তিতে সরবরাহ করা হয়।
            </p>
            <p class="en" style="display:none;">
                To the fullest extent permissible by applicable law, {{ $siteTitle }} services are provided on an "As-Is" and "As-Available" foundation without implied warranties of merchantability for unintended edge uses.
            </p>

            <p class="bn">
                সফটওয়্যার কর্তৃপক্ষ কোনো অবস্থাতেই ইন্টারনেট সংযোগের ত্রুটি, জাতীয় টেলিকম নেটওয়ার্ক বিভ্রাট, বিদ্যুৎ বিভ্রাট, মার্চেন্টের অভ্যন্তরীণ ক্যাশ চুরি বা ব্যবহারকারীর নিজস্ব অসতর্কতার কারণে সৃষ্ট পরোক্ষ আর্থিক ক্ষতির জন্য কোনোভাবেই দায়ী থাকবে না।
            </p>
            <p class="en" style="display:none;">
                Softngear shall not be held liable for indirect commercial losses arising from local ISP cutoffs, power blackouts, physical hardware damages, or internal cashier embezzlement within store counters.
            </p>
        </div>
    </article>

    {{-- SECTION 10: Termination --}}
    <article class="legal-section" id="sec-terms-termination">
        <div class="legal-section-header">
            <div class="legal-section-icon">
                <x-core::icon name="x-circle" size="18" />
            </div>
            <h2 class="legal-section-title">
                <span class="bn">১০. সেবা বাতিল ও স্থগিতকরণ (Termination & Suspension)</span>
                <span class="en" style="display:none;">10. Account Termination & Suspension</span>
            </h2>
        </div>
        <div class="legal-prose">
            <p class="bn">
                মার্চেন্ট যেকোনো সময় সেবা গ্রহণ বন্ধ করতে পারেন। তবে কোনো মার্চেন্ট যদি এই শর্তাবলীর ধারা ৫ (নিষিদ্ধ ব্যবহার) লঙ্ঘন করেন, ভুয়া তথ্য প্রদান করেন অথবা প্ল্যাটফর্মের অপব্যবহার করেন, তবে কোনো পূর্ব নোটিশ ছাড়াই তার অ্যাকাউন্ট সাময়িক বা স্থায়ীভাবে স্থগিত করার পূর্ণ অধিকার সফটওয়্যার কর্তৃপক্ষ সংরক্ষণ করে।
            </p>
            <p class="en" style="display:none;">
                Merchants may discontinue service anytime. However, Softngear reserves the right to immediately terminate or suspend accounts found violating conduct policies, engaging in fraudulent billing, or exploiting technical vulnerabilities.
            </p>
        </div>
    </article>

    {{-- SECTION 11: Governing Law --}}
    <article class="legal-section" id="sec-terms-law">
        <div class="legal-section-header">
            <div class="legal-section-icon">
                <x-core::icon name="book-open" size="18" />
            </div>
            <h2 class="legal-section-title">
                <span class="bn">১১. প্রযোজ্য আইন ও বিচারিক এখতিয়ার (Governing Law & Jurisdiction)</span>
                <span class="en" style="display:none;">11. Governing Law & Dispute Jurisdiction</span>
            </h2>
        </div>
        <div class="legal-prose">
            <p class="bn">
                এই ব্যবহারের শর্তাবলী গণপ্রজাতন্ত্রী বাংলাদেশের প্রচলিত আইন দ্বারা পরিচালিত ও ব্যাখ্যায়িত হবে। এই চুক্তি বা সফটওয়্যার ব্যবহারের ফলে কোনো বিতর্ক বা মতভেদ দেখা দিলে তা প্রথমে আলাপ-আলোচনার মাধ্যমে সৌহার্দ্যপূর্ণভাবে সমাধানের চেষ্টা করা হবে। অন্যথায় বাংলাদেশের উপযুক্ত এখতিয়ারভুক্ত আদালতের মাধ্যমেই এর চূড়ান্ত নিষ্পত্তি হবে।
            </p>
            <p class="en" style="display:none;">
                These Terms of Service are governed exclusively by the laws of the People's Republic of Bangladesh. Any dispute arising under this agreement shall be settled through mutual good-faith arbitration, failing which it shall be subjected to the competent courts of Bangladesh.
            </p>
        </div>
    </article>

    {{-- SECTION 12: Inquiries --}}
    <article class="legal-section" id="sec-terms-inquiries">
        <div class="legal-section-header">
            <div class="legal-section-icon">
                <x-core::icon name="help-circle" size="18" />
            </div>
            <h2 class="legal-section-title">
                <span class="bn">১২. যোগাযোগ ও আইনি অনুসন্ধান (Inquiries & Legal Support)</span>
                <span class="en" style="display:none;">12. Legal Inquiries & Contact Support</span>
            </h2>
        </div>
        <div class="legal-prose">
            <p class="bn">
                এই শর্তাবলী সংক্রান্ত যেকোনো আইনি ব্যাখ্যা, চুক্তি সংক্রান্ত জিজ্ঞাসা বা পরামর্শের জন্য আমাদের সাথে যোগাযোগ করুন:
            </p>
            <p class="en" style="display:none;">
                For legal correspondence, service clarifications, or licensing inquiries, contact our official desk:
            </p>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:14px; margin-top:16px;">
                <div style="background:var(--paper); border:1px solid var(--border); border-radius:10px; padding:14px;">
                    <div style="font-size:11.5px; color:var(--ink-400); font-weight:700; text-transform:uppercase;">
                        <span class="bn">অফিসিয়াল হেল্পলাইন</span>
                        <span class="en" style="display:none;">Helpline / WhatsApp</span>
                    </div>
                    <div style="font-size:14px; font-weight:700; color:var(--ink-900); margin-top:4px;">
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $supportPhone) }}" style="color:inherit; text-decoration:none;">{{ $supportPhone }}</a>
                    </div>
                </div>

                <div style="background:var(--paper); border:1px solid var(--border); border-radius:10px; padding:14px;">
                    <div style="font-size:11.5px; color:var(--ink-400); font-weight:700; text-transform:uppercase;">
                        <span class="bn">লিগ্যাল ও কমপ্লায়েন্স ইমেইল</span>
                        <span class="en" style="display:none;">Legal Email</span>
                    </div>
                    <div style="font-size:14px; font-weight:700; color:var(--ink-900); margin-top:4px;">
                        <a href="mailto:{{ $supportEmail }}" style="color:var(--teal-800); text-decoration:none;">{{ $supportEmail }}</a>
                    </div>
                </div>

                <div style="background:var(--paper); border:1px solid var(--border); border-radius:10px; padding:14px;">
                    <div style="font-size:11.5px; color:var(--ink-400); font-weight:700; text-transform:uppercase;">
                        <span class="bn">প্রধান কার্যালয়</span>
                        <span class="en" style="display:none;">Headquarters</span>
                    </div>
                    <div style="font-size:13px; font-weight:600; color:var(--ink-900); margin-top:4px;">
                        {{ $officeAddress }}
                    </div>
                </div>
            </div>
        </div>
    </article>
</x-core::legal-layout>
