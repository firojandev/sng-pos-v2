<?php

/*
 * Payroll defaults. Tax figures are only the starting values copied to a
 * company's tax years (Payroll → Tax Setup), where they are kept and edited;
 * the calculation reads them from the database, never from here.
 */
return [
    'name' => 'Payroll',

    /*
     * Tax-free threshold categories of the Income Tax Act 2023.
     */
    'tax_categories' => [
        'general' => ['bn' => 'সাধারণ', 'en' => 'General'],
        'female_senior' => ['bn' => 'নারী, তৃতীয় লিঙ্গ ও ৬৫+ বছর', 'en' => 'Women, third gender & 65+'],
        'disabled' => ['bn' => 'প্রতিবন্ধী ও তাঁদের পিতা-মাতা/অভিভাবক', 'en' => 'Persons with disability & guardians'],
        'freedom_fighter' => ['bn' => 'গেজেটভুক্ত যুদ্ধাহত মুক্তিযোদ্ধা', 'en' => 'Gazetted war-wounded freedom fighters'],
        'july_fighter' => ['bn' => 'জুলাই গণঅভ্যুত্থানে আহত', 'en' => 'July uprising injured'],
    ],

    /*
     * Minimum tax locations (where the taxpayer lives).
     */
    'tax_locations' => [
        'dhaka_chattogram' => ['bn' => 'ঢাকা/চট্টগ্রাম সিটি কর্পোরেশন', 'en' => 'Dhaka/Chattogram city corporation'],
        'other_city' => ['bn' => 'অন্যান্য সিটি কর্পোরেশন', 'en' => 'Other city corporation'],
        'elsewhere' => ['bn' => 'অন্যান্য এলাকা', 'en' => 'Elsewhere'],
    ],

    /*
     * Income years (July–June) seeded for a new company. Slabs are
     * [width, rate %] above the tax-free threshold; the last has no width.
     */
    'tax_years' => [
        '2025-26' => [
            'assessment_year' => '2026-27',
            'thresholds' => ['general' => 350000, 'female_senior' => 400000, 'disabled' => 475000, 'freedom_fighter' => 500000, 'july_fighter' => 500000],
            'slabs' => [[100000, 5], [400000, 10], [500000, 15], [500000, 20], [2000000, 25], [null, 30]],
            'exemption_percent' => 33.33,
            'exemption_cap' => 450000,
            'minimum_taxes' => ['dhaka_chattogram' => 5000, 'other_city' => 4000, 'elsewhere' => 3000],
            'rebate_income_percent' => 3,
            'rebate_investment_percent' => 15,
            'rebate_cap' => 1000000,
        ],
        '2026-27' => [
            'assessment_year' => '2027-28',
            'thresholds' => ['general' => 375000, 'female_senior' => 425000, 'disabled' => 500000, 'freedom_fighter' => 525000, 'july_fighter' => 525000],
            'slabs' => [[300000, 10], [400000, 15], [500000, 20], [2000000, 25], [null, 30]],
            'exemption_percent' => 33.33,
            'exemption_cap' => 500000,
            'minimum_taxes' => ['dhaka_chattogram' => 5000, 'other_city' => 4000, 'elsewhere' => 3000],
            'rebate_income_percent' => 3,
            'rebate_investment_percent' => 15,
            'rebate_cap' => 1000000,
        ],
    ],
];
