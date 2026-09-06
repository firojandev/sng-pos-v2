<?php

return [
    'name' => 'Purchase',

    /*
    |--------------------------------------------------------------------------
    | Purchase Delivery Orders Feature Toggle
    |--------------------------------------------------------------------------
    | When set to false, Purchase Delivery Orders are hidden from the sidebar
    | and direct access to all delivery order routes is strictly forbidden (403).
    | Set to true (or set ENABLE_PURCHASE_DELIVERY_ORDERS=true in .env) to re-enable.
    */
    'delivery_orders_enabled' => (bool) env('ENABLE_PURCHASE_DELIVERY_ORDERS', false),
];
