<?php

namespace Modules\Core\Support;

use Modules\Core\Models\Setting;

class LandingPageContent
{
    /**
     * Get all default values for every landing page section.
     *
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            // General / Brand
            'site_title' => 'MasterPOS',
            'brand_tag' => 'Cloud POS & ERP',
            'trial_button_text_bn' => 'ফ্রি ট্রায়াল',
            'trial_button_text_en' => 'Free Trial',
            'trial_button_url' => '/login',

            // Hero Section
            'hero_badge_bn' => '⚡ বাংলাদেশের #১ ক্লাউড POS সফটওয়্যার',
            'hero_badge_en' => "⚡ Bangladesh's #1 Cloud POS Software",
            'hero_title_bn' => 'ব্যবসা পরিচালনার স্মার্ট সমাধান',
            'hero_title_en' => 'Smart Solution to Run Your Business',
            'hero_title_gradient_bn' => 'সহজ ও দ্রুততম POS',
            'hero_title_gradient_en' => 'Fastest Cloud POS',
            'hero_subtitle_bn' => 'খাতা-কলমে হিসাবের ঝামেলা ভুলে যান। সেলস কাউন্টার, বাকির খাতা, ইনভেন্টরি স্টক ও লাভ-ক্ষতির লাইভ হিসাব এখন এক ক্লিকেই।',
            'hero_subtitle_en' => 'Say goodbye to paper ledger chaos. Instant sales counter, due ledger, live stock inventory, and profit & loss reports — all in one click.',
            'hero_btn_primary_text_bn' => 'সরাসরি ব্যবহার দেখুন',
            'hero_btn_primary_text_en' => 'Explore Demo',
            'hero_btn_primary_url' => '#simulator',
            'hero_btn_secondary_text_bn' => '১৪ দিনের ফ্রি ট্রায়াল',
            'hero_btn_secondary_text_en' => '14-Day Free Trial',
            'hero_btn_secondary_url' => '/login',
            'hero_active_users' => '৫,০০০+ ব্যবসায়ী যুক্ত',
            'hero_trust_text_bn' => 'ক্রেডিট কার্ডের প্রয়োজন নেই • ২ মিনিটে সেটআপ • ২৪/৭ ব্যাকআপ',
            'hero_trust_text_en' => 'No Credit Card Needed • 2-Min Setup • 24/7 Cloud Backup',

            // Social Proof / Stats
            'stat_1_number' => '৯৯.৯%',
            'stat_1_label_bn' => 'সিস্টেম আপটাইম গ্যারান্টি',
            'stat_1_label_en' => 'System Uptime Guarantee',
            'stat_2_number' => '৫০,০০০+',
            'stat_2_label_bn' => 'প্রতিদিনের সফল লেনদেন',
            'stat_2_label_en' => 'Daily Successful Invoices',
            'stat_3_number' => '৩ সেকেন্ড',
            'stat_3_label_bn' => 'দ্রুততম ক্যাশ মেমো প্রিন্ট',
            'stat_3_label_en' => 'Fastest Invoice Print',
            'stat_4_number' => '২৪/৭',
            'stat_4_label_bn' => 'গ্রাহক সহায়তা ও ব্যাকআপ',
            'stat_4_label_en' => 'Customer Support & Backup',

            // Problem vs Solution (খাতা-কলমে বনাম MasterPOS)
            'vs_badge_bn' => 'তুলনামূলক বিশ্লেষণ',
            'vs_badge_en' => 'Direct Comparison',
            'vs_title_bn' => 'সনাতন পদ্ধতি বনাম MasterPOS',
            'vs_title_en' => 'Traditional Method vs MasterPOS',
            'vs_subtitle_bn' => 'কেন শত শত ব্যবসায়ী তাদের খাতা-কলমের হিসাব ছেড়ে ক্লাউড সিস্টেমে স্থানান্তর হচ্ছেন?',
            'vs_subtitle_en' => 'Why hundreds of smart retail merchants are moving from pen-and-paper to cloud software?',
            'vs_pain_items' => [
                ['bn' => 'খাতা-কলমে হিসাবে ভুল ও গড়মিল হওয়ার নিয়মিত ঝুঁকি', 'en' => 'Frequent ledger errors and calculation mistakes'],
                ['bn' => 'কাস্টমারের বাকি টাকার হিসাব রাখতে পাতার পর পাতা খোঁজা', 'en' => 'Tedious page-turning to track customer due records'],
                ['bn' => 'দোকানে স্টক আছে কিনা সরাসরি বোঝার কোনো উপায় থাকে না', 'en' => 'No real-time insight into actual warehouse stock levels'],
                ['bn' => 'প্রতিদিনের ক্যাশবক্স ও ক্যাশিয়ার হিসাব মেলাতে ঘণ্টার পর ঘণ্টা সময় অপচয়', 'en' => 'Hours wasted daily trying to reconcile cashbox registers'],
                ['bn' => 'দোকানের বাইরে থাকলে বিক্রির কোনো সঠিক তথ্য পাওয়া যায় না', 'en' => 'Zero visibility into store sales while away or travelling'],
            ],
            'vs_solution_items' => [
                ['bn' => '১০০% অটোমেটিক ও নির্ভুল হিসাব, প্রতিটি লেনদেন ক্লাউডে সুরক্ষিত', 'en' => '100% automated precision with cloud-backed transaction logs'],
                ['bn' => 'এক ক্লিকে কাস্টমার লেজার, বাকির স্টেটমেন্ট ও সরাসরি SMS অ্যালার্ট', 'en' => '1-click due ledger statements with direct SMS alerts'],
                ['bn' => 'লাইভ স্টক ট্র্যাকিং, কম স্টকে স্বয়ংক্রিয় লো-স্টক নোটিফিকেশন', 'en' => 'Real-time stock alerts before items run out of inventory'],
                ['bn' => 'ক্যাশবক্স ওপেনিং ও ক্লোজিং ব্যালেন্স সেকেন্ডে অটো-অডিট', 'en' => 'Instant cashbox audit with opening and closing shifts'],
                ['bn' => 'মোবাইল বা ল্যাপটপ থেকে পৃথিবীর যেকোনো প্রান্ত থেকে লাইভ তদারকি', 'en' => 'Live store monitoring from any device anywhere in the world'],
            ],

            // Core Features
            'features_badge_bn' => 'শক্তিশালী ফিচারসমূহ',
            'features_badge_en' => 'Powerful Core Modules',
            'features_title_bn' => 'ব্যবসার প্রতিটি ধাপ নিয়ন্ত্রণের পূর্ণাঙ্গ টুলস',
            'features_title_en' => 'Complete Toolkit to Control Every Aspect of Your Store',
            'features_subtitle_bn' => 'সহজ ইন্টারফেস, যাতে কম্পিউটার না জানা যেকেউ ৫ মিনিটে শিখতে পারে।',
            'features_subtitle_en' => 'Intuitive interface that anyone can master in under 5 minutes without prior IT skills.',
            'features_list' => [
                [
                    'icon' => 'zap',
                    'badge_bn' => 'অত্যন্ত দ্রুত',
                    'badge_en' => 'Ultra Fast',
                    'title_bn' => 'সুপারফাস্ট সেলস পিওএস',
                    'title_en' => 'Superfast POS Counter',
                    'desc_bn' => 'বারকোড স্ক্যানার বা কিবোর্ড শর্টকাট দিয়ে মাত্র ৩ সেকেন্ডে বিক্রির মেমো তৈরি ও প্রিন্ট করুন।',
                    'desc_en' => 'Generate and print thermal receipts in 3 seconds flat with barcode scanner & keyboard shortcuts.',
                ],
                [
                    'icon' => 'book-open',
                    'badge_bn' => 'ডিজিটাল লেজার',
                    'badge_en' => 'Digital Ledger',
                    'title_bn' => 'স্বয়ংক্রিয় বাকির খাতা ও SMS',
                    'title_en' => 'Automated Due Ledger & SMS',
                    'desc_bn' => 'কোন কাস্টমারের কাছে কত বাকি আছে তার পাই-পাই হিসাব। এক ক্লিকে পেমেন্ট তাগাদার SMS পাঠান।',
                    'desc_en' => 'Keep immaculate track of every penny owed with 1-click due collection reminder SMS.',
                ],
                [
                    'icon' => 'boxes',
                    'badge_bn' => 'স্মার্ট স্টক',
                    'badge_en' => 'Smart Inventory',
                    'title_bn' => 'ইনভেন্টরি ও ব্যাচ ট্র্যাকিং',
                    'title_en' => 'Stock & Expiry Tracking',
                    'desc_bn' => 'পণ্য কেনা, বিক্রি ও বর্তমান স্টকের লাইভ হিসাব। ডেট এক্সপায়ার ও লো-স্টক সতর্কতা সংকেত।',
                    'desc_en' => 'Track batch numbers, expiration dates, purchase prices, and automated low-stock reorder alerts.',
                ],
                [
                    'icon' => 'wallet',
                    'badge_bn' => 'অর্থায়ন',
                    'badge_en' => 'Cashflow Audit',
                    'title_bn' => 'মাল্টি-ক্যাশবক্স ও ব্যাংক অ্যাকাউন্ট',
                    'title_en' => 'Multi-Cashbox & Bank Ledgers',
                    'desc_bn' => 'নগদ ক্যাশ, বিকাশ, নগদ, রকেট ও ব্যাংক ট্রান্সফারের আলাদা ক্যাশবক্স ও শিফট ব্যালেন্স অডিট।',
                    'desc_en' => 'Manage cash drawer, bKash, Nagad, bank transfers and cashier shift handover seamlessly.',
                ],
                [
                    'icon' => 'trending-up',
                    'badge_bn' => 'অ্যানালিটিক্স',
                    'badge_en' => 'Live Profit & Loss',
                    'title_bn' => 'দৈনিক লাভ-ক্ষতি ও ব্যয় অডিট',
                    'title_en' => 'Live Profit & Expense Audit',
                    'desc_bn' => 'দোকানের বিদ্যুৎ বিল, কর্মচারীর বেতন ও অন্যান্য ব্যয়ের পর প্রকৃত মোট ও নিট মুনাফার লাইভ রিপোর্ট।',
                    'desc_en' => 'See exact gross & net profit margins after factoring in store rents, salaries and utility expenses.',
                ],
                [
                    'icon' => 'network',
                    'badge_bn' => 'মাল্টি-ব্রাঞ্চ',
                    'badge_en' => 'Multi-Branch',
                    'title_bn' => 'মাল্টি-আউটলেট ও ওয়ারহাউস',
                    'title_en' => 'Multi-Branch & Warehouse Hub',
                    'desc_bn' => 'একটি কেন্দ্রীয় অ্যাকাউন্ট থেকে একাধিক দোকান ও গোডাউনের স্টক স্থানান্তর ও সেলস ম্যানেজমেন্ট।',
                    'desc_en' => 'Manage multiple retail branches and warehouse hubs with inter-branch stock transfers from one dashboard.',
                ],
            ],

            // Interactive Simulator Section
            'sim_badge_bn' => 'লাইভ ডেমো এক্সপেরিয়েন্স',
            'sim_badge_en' => 'Interactive Demo',
            'sim_title_bn' => 'নিজে টেস্ট করে দেখুন কীভাবে POS কাজ করে',
            'sim_title_en' => 'Test Drive The POS Counter Yourself',
            'sim_subtitle_bn' => 'যেকোনো পণ্যে ক্লিক করুন, কার্ট আপডেট হবে এবং মাত্র ৩ সেকেন্ডে মেমো তৈরি হবে।',
            'sim_subtitle_en' => 'Click any product on the left to add it to cart and complete an invoice in seconds.',

            // Business Verticals
            'vert_badge_bn' => 'যেকোনো ধরনের ব্যবসা',
            'vert_badge_en' => 'Any Industry',
            'vert_title_bn' => 'আপনার ব্যবসার জন্য বিশেষভাবে কাস্টমাইজড',
            'vert_title_en' => 'Tailored Specifically For Your Business Type',
            'vert_subtitle_bn' => 'মুদি দোকান থেকে ডিপার্টমেন্টাল স্টোর, ফার্মেসি থেকে ফ্যাশন আউটলেট — সবার জন্য উপযোগী।',
            'vert_subtitle_en' => 'From retail grocers to pharmacies and fashion outlets — ready for your workflow.',
            'verticals_list' => [
                [
                    'icon' => 'shopping-cart',
                    'name_bn' => 'মুদি ও সুপারশপ',
                    'name_en' => 'Grocery & Superstore',
                    'desc_bn' => 'বারকোড স্ক্যানিং, ওজন স্কেল ইন্টিগ্রেশন এবং দ্রুততম সেলস কাউন্টার।',
                    'desc_en' => 'Barcode scanning, digital weight scale support, and high-speed checkout.',
                    'tag_bn' => 'হাই-স্পিড বিলিং',
                    'tag_en' => 'High-Speed Billing',
                ],
                [
                    'icon' => 'scissors',
                    'name_bn' => 'ফ্যাশন ও ক্লথিং শপ',
                    'name_en' => 'Fashion & Garments',
                    'desc_bn' => 'সাইজ, কালার, ফেব্রিক ভ্যারিয়েন্ট ও কাস্টম বারকোড স্টিকার প্রিন্ট।',
                    'desc_en' => 'Manage size, color, design variants and print customized barcode tags.',
                    'tag_bn' => 'ভ্যারিয়েন্ট ট্র্যাকিং',
                    'tag_en' => 'Variant Tracking',
                ],
                [
                    'icon' => 'activity',
                    'name_bn' => 'ফার্মেসি ও মেডিসিন',
                    'name_en' => 'Pharmacy & Healthcare',
                    'desc_bn' => 'জেনেরিক নাম, ব্যাচ নম্বর ও মেয়াদোত্তীর্ণ তারিখের অগ্রিম নোটিফিকেশন।',
                    'desc_en' => 'Generic search, manufacturer tracking, and advance expiry date alerts.',
                    'tag_bn' => 'মেয়াদোত্তীর্ণ অ্যালার্ট',
                    'tag_en' => 'Expiry Warning',
                ],
                [
                    'icon' => 'coffee',
                    'name_bn' => 'রেস্টুরেন্ট ও ক্যাফে',
                    'name_en' => 'Restaurant & Café',
                    'desc_bn' => 'টেবিল বুকিং, কিচেন অর্ডার টিকিট (KOT) ও ভ্যাট ইনভয়েসিং।',
                    'desc_en' => 'Table mapping, kitchen order tickets (KOT) and customizable service charges.',
                    'tag_bn' => 'KOT ও টেবিল বিল',
                    'tag_en' => 'KOT & Table Billing',
                ],
                [
                    'icon' => 'cpu',
                    'name_bn' => 'ইলেকট্রনিক্স ও হার্ডওয়্যার',
                    'name_en' => 'Electronics & Hardware',
                    'desc_bn' => 'সিরিয়াল নম্বর ট্র্যাকিং, ওয়ারেন্টি মেয়াদ ও কিস্তির হিসাব রক্ষা।',
                    'desc_en' => 'IMEI / Serial number tracking, customer warranty cards & EMI installments.',
                    'tag_bn' => 'ওয়ারেন্টি ও সিরিয়াল',
                    'tag_en' => 'Warranty & Serial',
                ],
                [
                    'icon' => 'truck',
                    'name_bn' => 'পাইকারি ও ডিস্ট্রিবিউশন',
                    'name_en' => 'Wholesale & Distribution',
                    'desc_bn' => 'কার্টন / পিস ইউনিট কনভার্সন, পাইকারি রেট ও সাপ্লায়ার লেজার।',
                    'desc_en' => 'Carton-to-piece conversions, wholesale tiers and supplier credit ledgers.',
                    'tag_bn' => 'ইউনিট রূপান্তর',
                    'tag_en' => 'Unit Conversion',
                ],
            ],

            // Pricing Header
            'pricing_badge_bn' => 'সাশ্রয়ী প্যাকেজ',
            'pricing_badge_en' => 'Affordable Pricing',
            'pricing_title_bn' => 'ব্যবসার আকার অনুযায়ী সেরা প্ল্যান বেছে নিন',
            'pricing_title_en' => 'Choose The Perfect Plan For Your Store',
            'pricing_subtitle_bn' => 'কোনো গোপন চার্জ নেই। যেকোনো সময় আপগ্রেড বা বাতিল করার স্বাধীনতা।',
            'pricing_subtitle_en' => 'No hidden fees. Freedom to upgrade, downgrade, or cancel at any time.',
            'pricing_annual_discount_bn' => '২০% ছাড়',
            'pricing_annual_discount_en' => '20% OFF',

            // Testimonials / Reviews
            'reviews_badge_bn' => 'গ্রাহক সন্তুষ্টি',
            'reviews_badge_en' => 'Client Testimonials',
            'reviews_title_bn' => 'সফল ব্যবসায়ীদের বাস্তব অভিজ্ঞতা',
            'reviews_title_en' => 'Loved By Retail Shop Owners Across Bangladesh',
            'reviews_subtitle_bn' => 'দেখুন কীভাবে MasterPOS তাদের দোকানের পরিচালন খরচ কমিয়েছে ও মুনাফা বাড়িয়েছে।',
            'reviews_subtitle_en' => 'See how MasterPOS reduced operational errors and maximized profit for our clients.',
            'reviews_list' => [
                [
                    'author' => 'মোঃ রফিকুল ইসলাম',
                    'role_bn' => 'মালিক ও স্বত্বাধিকারী',
                    'role_en' => 'Owner',
                    'shop' => 'আল-মদিনা ডিপার্টমেন্টাল স্টোর',
                    'city' => 'মিরপুর, ঢাকা',
                    'quote_bn' => 'আগে প্রতিদিন রাতে বাকির খাতা মেলাতে ঘণ্টাখানেক সময় লাগত। MasterPOS নেওয়ার পর এক ক্লিকে এসএমএস চলে যায়, আর ক্যাশবক্স হিসাব এখন একদম পানির মতো পরিষ্কার!',
                    'quote_en' => 'Before MasterPOS, reconciling daily sales and due registers took hours every night. Now customer reminders go out via automated SMS, and cashbox tracking is crystal clear.',
                    'rating' => 5,
                    'initials' => 'র',
                ],
                [
                    'author' => 'তানভীর আহমেদ',
                    'role_bn' => 'ব্যবস্থাপনা পরিচালক',
                    'role_en' => 'Managing Director',
                    'shop' => 'ব্লু-মুন ফ্যাশন আউটলেট',
                    'city' => 'জিইসি মোড়, চট্টগ্রাম',
                    'quote_bn' => 'আমাদের চট্টগ্রাম ও ঢাকার দুটি শোরুমের স্টক এখন এক জায়গা থেকেই নিয়ন্ত্রণ করি। কোন সাইজের জামা কোন ব্রাঞ্চে বেশি বিক্রি হচ্ছে তা দেখেই অর্ডার দিতে পারি।',
                    'quote_en' => 'We monitor inventory across both our Dhaka and Chittagong branches simultaneously. Knowing exactly which apparel variants sell best has skyrocketed our turnover.',
                    'rating' => 5,
                    'initials' => 'ত',
                ],
                [
                    'author' => 'ডাঃ সাজ্জাদ হোসেন',
                    'role_bn' => 'ফার্মেসি স্বত্বাধিকারী',
                    'role_en' => 'Founder & Pharmacist',
                    'shop' => 'নিরাময় মেডিসিন সেন্টার',
                    'city' => 'উপশহর, রাজশাহী',
                    'quote_bn' => 'ওষুধের মেয়াদোত্তীর্ণ তারিখ ও জেনেরিক সার্চ খুব চমৎকার কাজ করে। সফটওয়্যারটির ইন্টারফেস এত সহজ যে আমাদের নতুন সেলসম্যানও ১ম দিন থেকেই মেমো কাটতে পারছে।',
                    'quote_en' => 'The generic medicine lookup and batch expiration warnings are life-savers. The UI is so friendly that even brand-new counter staff started billing without any training.',
                    'rating' => 5,
                    'initials' => 'স',
                ],
            ],

            // FAQ Section
            'faq_badge_bn' => 'সাধারণ জিজ্ঞাসা',
            'faq_badge_en' => 'Got Questions?',
            'faq_title_bn' => 'প্রায়শই জিজ্ঞাসিত প্রশ্নাবলি',
            'faq_title_en' => 'Frequently Asked Questions',
            'faq_subtitle_bn' => 'আপনার মনে থাকা যেকোনো প্রশ্নের উত্তর এখানে পেয়ে যাবেন।',
            'faq_subtitle_en' => 'Find answers to commonly asked questions about our software and setup.',
            'faqs_list' => [
                [
                    'question_bn' => 'MasterPOS ব্যবহার করতে কী ধরনের কম্পিউটার বা ডিভাইস লাগবে?',
                    'question_en' => 'What kind of hardware or computer is required to use MasterPOS?',
                    'answer_bn' => 'যেকোনো সাধারণ ল্যাপটপ, ডেস্কটপ কম্পিউটার, ট্যাবলেট এমনকি আপনার স্মার্টফোনেও ব্রাউজারের মাধ্যমে MasterPOS ব্যবহার করা যায়। যেকোনো স্ট্যান্ডার্ড থার্মাল পিওএস প্রিন্টার ও বারকোড স্ক্যানার সরাসরি সাপোর্ট করে।',
                    'answer_en' => 'MasterPOS runs on any standard desktop PC, laptop, tablet, or smartphone via modern web browsers. It natively supports any standard 58mm/80mm thermal receipt printer and USB/wireless barcode scanner.',
                ],
                [
                    'question_bn' => 'আমার দোকানের ডেটা কি নিরাপদ ও ব্যাকআপ থাকবে?',
                    'question_en' => 'Is my shop business data safe and backed up in the cloud?',
                    'answer_bn' => 'হ্যাঁ, সম্পূর্ণ নিরাপদ। আমাদের ক্লাউড সার্ভারে আন্তর্জাতিক মানের 256-bit SSL এনক্রিপশন ব্যবহৃত হয় এবং প্রতি ২৪ ঘণ্টায় স্বয়ংক্রিয় অফ-সাইট ক্লাউড ব্যাকআপ সংরক্ষিত হয়। ফলে কম্পিউটার নষ্ট হলেও আপনার ডেটা অক্ষত থাকে।',
                    'answer_en' => 'Yes, absolutely. All business records are encrypted with 256-bit SSL protocols and automatically backed up every 24 hours offsite, ensuring your records remain 100% intact even if your physical computer crashes.',
                ],
                [
                    'question_bn' => 'ইন্টারনেট সাময়িক বিচ্ছিন্ন থাকলে কি বিক্রি বন্ধ হয়ে যাবে?',
                    'question_en' => 'What happens if our internet connection temporarily drops?',
                    'answer_bn' => 'না, সাধারণ মোবাইল ডাটা বা হটস্পট সংযোগের মাধ্যমেই অতি স্বল্প ব্যান্ডউইথে MasterPOS নিরবচ্ছিন্নভাবে চালানো যায়।',
                    'answer_en' => 'No. MasterPOS is optimized to run smoothly on low-bandwidth mobile hot-spots and 3G/4G connections without interruption.',
                ],
                [
                    'question_bn' => 'ফ্রি ট্রায়াল শেষ হওয়ার পর কীভাবে সাবস্ক্রিপশন চালু করব?',
                    'question_en' => 'How do I upgrade or renew my subscription after the free trial?',
                    'answer_bn' => 'ট্রায়াল শেষ হওয়ার আগে আপনার ড্যাশবোর্ড থেকেই বিকাশ, নগদ, রকেট বা ব্যাংক কার্ডের মাধ্যমে পছন্দের মাসিক বা বাৎসরিক প্যাকেজ বেছে নিতে পারবেন। কোনো ডেটা হারাবে না।',
                    'answer_en' => 'You can renew or upgrade directly inside your dashboard using bKash, Nagad, Rocket, or local debit/credit cards without losing any entered products or sales history.',
                ],
                [
                    'question_bn' => 'সফটওয়্যার শেখার জন্য কি আপনারা কোনো ট্রেনিং বা সাপোর্ট দেন?',
                    'question_en' => 'Do you provide training or customer support for onboarding staff?',
                    'answer_bn' => 'হ্যাঁ! আমাদের ডেডিকেটেড সাপোর্ট টিম আপনাকে এবং আপনার কর্মচারীদের ভিডিও কল, অ্যানিডেস্ক বা সরাসরি ফোনে বিনামূল্যে সম্পূর্ণ ট্রেনিং ও সহায়তা প্রদান করবে।',
                    'answer_en' => 'Yes! Our customer support team provides free remote training and dedicated phone/WhatsApp support to help your team get started smoothly.',
                ],
            ],

            // Final CTA Banner
            'cta_title_bn' => 'আজই আপনার দোকানের হিসাব ডিজিটাল করুন',
            'cta_title_en' => 'Modernize Your Store Operations Today',
            'cta_subtitle_bn' => 'মাত্র ২ মিনিটে অ্যাকাউন্ট খুলে শুরু করুন ১৪ দিনের ফ্রি ট্রায়াল। কোনো ক্রেডিট কার্ড বা অগ্রিম পেমেন্টের প্রয়োজন নেই।',
            'cta_subtitle_en' => 'Get started in 2 minutes with our 14-day free trial. No credit card or upfront deposit required.',
            'cta_btn_text_bn' => 'ফ্রি ট্রায়াল শুরু করুন',
            'cta_btn_text_en' => 'Start Free Trial',
            'cta_btn_url' => '/login',
            'cta_phone_btn_text' => '+880 1886 861430',
            'cta_phone_btn_url' => 'tel:+8801886861430',

            // Footer & Social
            'support_phone' => '+880 1886 861430',
            'support_email' => 'support@softngear.com',
            'office_address' => 'Shop 407, 3rd Floor, Shwapnochura Plaza, Rajshahi',
            'meta_description' => 'বাংলাদেশের আধুনিক ও দ্রুততম ক্লাউড POS এবং ব্যবসা পরিচালনা সফটওয়্যার।',
            'footer_about_bn' => 'বাংলাদেশের আধুনিক ব্যবসায়ী ও রিটেইল স্টোরের জন্য সর্বাধিক দ্রুত, নির্ভুল এবং নির্ভরযোগ্য ক্লাউড পিওএস সমাধান।',
            'footer_about_en' => "Bangladesh's fastest, most accurate and trusted cloud POS & inventory solution for modern merchants.",
            'social_facebook' => 'https://facebook.com',
            'social_youtube' => 'https://youtube.com',
            'social_whatsapp' => 'https://wa.me/8801886861430',
        ];
    }

    /**
     * Get a specific landing page setting value with default fallback.
     */
    public static function get(string $key, mixed $fallback = null): mixed
    {
        $defaults = self::defaults();
        $defaultVal = $defaults[$key] ?? $fallback;

        return Setting::get($key, $defaultVal);
    }

    /**
     * Retrieve all landing page settings merged with default values.
     *
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        $defaults = self::defaults();
        $result = [];

        foreach ($defaults as $key => $defaultVal) {
            $result[$key] = Setting::get($key, $defaultVal);
        }

        $result['landing_page_enabled'] = Setting::isLandingPageEnabled();

        return $result;
    }

    /**
     * Save an array of landing page values to settings table.
     *
     * @param  array<string, mixed>  $data
     */
    public static function saveMany(array $data): void
    {
        $defaults = self::defaults();

        foreach ($data as $key => $value) {
            $defaultType = 'string';
            if (isset($defaults[$key])) {
                if (is_array($defaults[$key])) {
                    $defaultType = 'json';
                } elseif (is_bool($defaults[$key])) {
                    $defaultType = 'boolean';
                }
            }

            if (is_array($value)) {
                $defaultType = 'json';
            }

            Setting::set($key, $value, $defaultType, 'landing');
        }
    }
}
