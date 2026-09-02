<?php

namespace Database\Seeders;

use App\Models\User;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Customer\Models\Customer;
use Modules\Employee\Models\Employee;
use Modules\Finance\Models\Expense;
use Modules\Finance\Models\ExpenseCategory;
use Modules\Finance\Models\Income;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Brand;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductModel;
use Modules\Product\Models\StockAdjustment;
use Modules\Product\Models\StockMovement;
use Modules\Product\Models\StockTransfer;
use Modules\Product\Models\SubCategory;
use Modules\Product\Models\Unit;
use Modules\Purchase\Models\Purchase;
use Modules\Purchase\Models\PurchaseReturn;
use Modules\Sales\Models\Sale;
use Modules\Sales\Models\SaleReturn;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\SubscriptionPayment;
use Modules\Shop\Models\Warehouse;
use Modules\Supplier\Models\Supplier;

/**
 * Populates every table in the app with realistic Bengali demo data, layered
 * on top of the base DatabaseSeeder (which creates the Super Admin + one
 * demo shop). Run with: php artisan db:seed --class=DemoDataSeeder
 *
 * Idempotent: skips entirely if the demo shop already has products, so
 * re-running doesn't pile up duplicate data.
 */
class DemoDataSeeder extends Seeder
{
    private Generator $faker;

    private Shop $shop;

    private User $admin;

    private int $batchCounter = 0;

    /** @var Collection<int, Warehouse> */
    private Collection $warehouses;

    /** @var Collection<int, array{category: Category, sub_categories: Collection<int, SubCategory>}> */
    private Collection $categories;

    /** @var Collection<int, Unit> */
    private Collection $units;

    /** @var Collection<int, Brand> */
    private Collection $brands;

    /** @var Collection<int, Product> */
    private Collection $products;

    /** @var Collection<int, Supplier> */
    private Collection $suppliers;

    /** @var Collection<int, Customer> */
    private Collection $customers;

    /** @var Collection<int, array{category: ExpenseCategory, sub_categories: Collection<int, ExpenseCategory>}> */
    private Collection $expenseCategories;

    public function run(): void
    {
        $this->shop = Shop::first();

        if (! $this->shop) {
            $this->command?->warn('No shop found — run the base DatabaseSeeder first.');

            return;
        }

        if (Product::withoutGlobalScopes()->where('shop_id', $this->shop->id)->exists()) {
            $this->command?->info('Demo data already present for this shop — skipping.');

            return;
        }

        $this->faker = FakerFactory::create('bn_BD');
        $this->admin = User::where('shop_id', $this->shop->id)->firstOrFail();
        Auth::login($this->admin);

        DB::transaction(function () {
            $this->seedLocations();
            $this->seedStaff();
            $this->seedTaxonomy();
            $this->seedProducts();
            $this->seedParties();
            $this->seedPurchases();
            $this->seedSales();
            $this->seedReturns();
            $this->seedAdjustments();
            $this->seedTransfers();
            $this->seedFinance();
            $this->seedSubscriptionPayments();
        });

        Auth::logout();

        $this->command?->info('Demo data seeded successfully.');
    }

    private function phone(): string
    {
        return '+8801'.$this->faker->unique()->numerify('#########');
    }

    private function nextBatchNo(): string
    {
        return 'BT-'.str_pad((string) ++$this->batchCounter, 5, '0', STR_PAD_LEFT);
    }

    private function seedLocations(): void
    {
        $main = Warehouse::first();
        if (! $main) {
            $mainBranch = Branch::first() ?? Branch::create([
                'name' => 'প্রধান শাখা',
                'phone' => $this->phone(),
                'address' => $this->faker->address(),
                'status' => 'active',
            ]);
            $main = Warehouse::create([
                'branch_id' => $mainBranch->id,
                'name' => 'প্রধান গুদাম',
                'address' => $this->faker->address(),
                'status' => 'active',
            ]);
        }

        $branch2 = Branch::create([
            'name' => 'উত্তরা শাখা',
            'phone' => $this->phone(),
            'address' => $this->faker->address(),
            'status' => 'active',
        ]);

        $second = Warehouse::create([
            'branch_id' => $branch2->id,
            'name' => 'উত্তরা গুদাম',
            'address' => $this->faker->address(),
            'status' => 'active',
        ]);

        $this->warehouses = collect([$main, $second]);
    }

    private function seedStaff(): void
    {
        $designations = ['ম্যানেজার', 'ক্যাশিয়ার', 'সেলসম্যান', 'স্টক কিপার', 'অ্যাকাউন্টেন্ট'];
        $departments = ['বিক্রয়', 'হিসাব', 'গুদাম', 'প্রশাসন'];

        foreach (range(1, 5) as $i) {
            $name = $this->faker->name();
            $email = 'staff'.$i.'.'.Str::random(5).'@shop.test';

            $employee = Employee::create([
                'name' => $name,
                'phone' => $this->phone(),
                'email' => $email,
                'designation' => $designations[array_rand($designations)],
                'department' => $departments[array_rand($departments)],
                'salary' => $this->faker->numberBetween(12000, 35000),
                'joining_date' => now()->subMonths($this->faker->numberBetween(1, 24)),
                'address' => $this->faker->address(),
                'status' => 'active',
            ]);

            $user = User::create([
                'shop_id' => $this->shop->id,
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password'),
            ]);
            $user->assignRole('Admin');

            $employee->update(['user_id' => $user->id]);
        }
    }

    private function seedTaxonomy(): void
    {
        $categoryMap = [
            'ইলেকট্রনিক্স' => ['মোবাইল এক্সেসরিজ', 'চার্জার ও ক্যাবল'],
            'মুদি ও ভোগ্যপণ্য' => ['চাল ও ডাল', 'তেল ও মসলা'],
            'পোশাক' => ['পুরুষদের পোশাক', 'নারীদের পোশাক'],
            'প্রসাধনী' => ['ত্বকের যত্ন', 'চুলের যত্ন'],
            'হোম অ্যাপ্লায়েন্স' => ['রান্নাঘর সামগ্রী', 'ঘর সাজসজ্জা'],
            'স্টেশনারি' => ['খাতা ও কলম', 'অফিস সামগ্রী'],
        ];

        $categories = collect();

        foreach ($categoryMap as $categoryName => $subNames) {
            $category = Category::create(['name' => $categoryName, 'description' => null]);

            $subs = collect($subNames)->map(fn ($subName) => SubCategory::create([
                'category_id' => $category->id,
                'name' => $subName,
                'description' => null,
            ]));

            $categories->push(['category' => $category, 'sub_categories' => $subs]);
        }

        $this->categories = $categories;

        $brandNames = ['ওয়ালটন', 'প্রাণ', 'স্কয়ার', 'কিওক্রপ', 'নোভা', 'এসিআই'];
        $this->brands = collect($brandNames)->map(fn ($name) => Brand::create(['name' => $name, 'description' => null]));

        foreach ($this->brands as $brand) {
            foreach (['A100', 'X200', 'Pro'] as $modelSuffix) {
                ProductModel::create(['brand_id' => $brand->id, 'name' => $brand->name.' '.$modelSuffix]);
            }
        }

        $unitDefs = [
            ['নগ', 'Pcs'], ['কেজি', 'Kg'], ['লিটার', 'Ltr'], ['বক্স', 'Box'], ['প্যাকেট', 'Pkt'],
        ];
        $this->units = collect($unitDefs)->map(fn ($u) => Unit::create(['name' => $u[0], 'short_code' => $u[1]]));

        $expenseCategoryMap = [
            'দোকান পরিচালনা' => ['দোকান ভাড়া', 'বিদ্যুৎ বিল', 'ইন্টারনেট বিল'],
            'কর্মী খরচ' => ['বেতন', 'বোনাস', 'আপ্যায়ন'],
            'লজিস্টিকস' => ['পরিবহন খরচ', 'কুরিয়ার চার্জ'],
            'বিবিধ' => ['অফিস সামগ্রী', 'মেরামত'],
        ];

        $expenseCategories = collect();
        foreach ($expenseCategoryMap as $parentName => $subs) {
            $cat = ExpenseCategory::create(['name' => $parentName, 'description' => null]);
            $subCats = collect($subs)->map(fn ($sName) => ExpenseCategory::create([
                'parent_id' => $cat->id,
                'name' => $sName,
                'description' => null,
            ]));
            $expenseCategories->push(['category' => $cat, 'sub_categories' => $subCats]);
        }
        $this->expenseCategories = $expenseCategories;
    }

    private function seedProducts(): void
    {
        $productNamePool = [
            'ইলেকট্রনিক্স' => ['ইয়ারফোন', 'পাওয়ার ব্যাংক', 'মোবাইল কভার', 'ব্লুটুথ স্পিকার', 'USB ক্যাবল', 'মোবাইল চার্জার'],
            'মুদি ও ভোগ্যপণ্য' => ['মিনিকেট চাল', 'মসুর ডাল', 'সয়াবিন তেল', 'হলুদ গুঁড়া', 'চিনি', 'লবণ'],
            'পোশাক' => ['পাঞ্জাবি', 'শার্ট', 'শাড়ি', 'থ্রি-পিস', 'টি-শার্ট', 'জিন্স প্যান্ট'],
            'প্রসাধনী' => ['ফেসওয়াশ', 'শ্যাম্পু', 'সাবান', 'বডি লোশন', 'পারফিউম', 'ক্রিম'],
            'হোম অ্যাপ্লায়েন্স' => ['ব্লেন্ডার', 'রাইস কুকার', 'ইলেকট্রিক কেটলি', 'টেবিল ল্যাম্প', 'ফ্যান', 'ওয়াল ক্লক'],
            'স্টেশনারি' => ['খাতা', 'বল পয়েন্ট কলম', 'পেন্সিল বক্স', 'ফাইল ফোল্ডার', 'স্টিকি নোট', 'মার্কার'],
        ];

        $sku = 1;

        foreach ($this->categories as $entry) {
            /** @var Category $category */
            $category = $entry['category'];
            $names = $productNamePool[$category->name] ?? ['পণ্য'];

            foreach ($names as $index => $name) {
                $subCategory = $entry['sub_categories']->get($index % max($entry['sub_categories']->count(), 1));
                $brand = $this->brands->random();
                $purchasePrice = $this->faker->randomFloat(2, 50, 3000);
                $salePrice = round($purchasePrice * $this->faker->randomFloat(2, 1.15, 1.5), 2);
                $hasDiscount = $this->faker->boolean(30);
                $hasWarranty = $category->name === 'ইলেকট্রনিক্স' || $category->name === 'হোম অ্যাপ্লায়েন্স';
                $hasExpiry = $category->name === 'মুদি ও ভোগ্যপণ্য' || $category->name === 'প্রসাধনী';
                $hasBarcode = $this->faker->boolean(60);

                $product = Product::create([
                    'name' => $name,
                    'sku' => 'SKU-'.str_pad((string) $sku++, 5, '0', STR_PAD_LEFT),
                    'size' => null,
                    'purchase_price' => $purchasePrice,
                    'sale_price' => $salePrice,
                    'is_wholesale' => false,
                    'has_discount' => $hasDiscount,
                    'discount_type' => $hasDiscount ? 'percentage' : null,
                    'discount_value' => $hasDiscount ? $this->faker->numberBetween(5, 15) : null,
                    'has_barcode' => $hasBarcode,
                    'barcode' => $hasBarcode ? $this->faker->unique()->numerify('#############') : null,
                    'category_id' => $category->id,
                    'sub_category_id' => $subCategory?->id,
                    'brand_id' => $brand->id,
                    'short_description' => null,
                    'alert_qty' => $this->faker->numberBetween(5, 20),
                    'is_vat' => false,
                    'status' => 'active',
                    'has_warranty' => $hasWarranty,
                    'warranty_duration' => $hasWarranty ? $this->faker->numberBetween(6, 24) : null,
                    'warranty_type' => $hasWarranty ? 'month' : null,
                    'has_expiry' => $hasExpiry,
                    'expiry_date' => $hasExpiry ? now()->addMonths($this->faker->numberBetween(3, 18)) : null,
                ]);

                $baseUnit = $this->units->random();
                $product->units()->attach($baseUnit->id, ['is_base' => true, 'conversion_factor' => 1]);
            }
        }

        $this->products = Product::all();
    }

    private function seedParties(): void
    {
        $this->suppliers = collect(range(1, 8))->map(fn () => Supplier::create([
            'name' => $this->faker->company(),
            'phone' => $this->phone(),
            'email' => null,
            'address' => $this->faker->address(),
            'opening_due' => $this->faker->boolean(30) ? $this->faker->randomFloat(2, 500, 5000) : 0,
            'status' => 'active',
        ]));

        $this->customers = collect(range(1, 15))->map(fn () => Customer::create([
            'name' => $this->faker->name(),
            'phone' => $this->phone(),
            'email' => null,
            'address' => $this->faker->address(),
            'opening_due' => $this->faker->boolean(25) ? $this->faker->randomFloat(2, 200, 2000) : 0,
            'status' => 'active',
        ]));
    }

    /**
     * @return array{0: float, 1: float, 2: string}
     */
    private function calculateTotals(float $subtotal, float $discount): array
    {
        $total = max($subtotal - $discount, 0);
        $paidRoll = $this->faker->numberBetween(1, 10);
        $paid = $paidRoll <= 6 ? $total : ($paidRoll <= 9 ? round($total * $this->faker->randomFloat(2, 0.3, 0.8), 2) : 0);
        $due = max($total - $paid, 0);
        $status = $due <= 0 ? 'paid' : ($paid <= 0 ? 'due' : 'partial');

        return [$paid, $due, $status];
    }

    /**
     * @return array<int, array{method: string, amount: float}>
     */
    private function splitPayment(float $amount): array
    {
        if ($amount <= 0) {
            return [];
        }

        $methods = ['cash', 'bank', 'mobile_banking', 'card'];

        if ($this->faker->boolean(70)) {
            return [['method' => $methods[array_rand($methods)], 'amount' => $amount]];
        }

        $first = round($amount * $this->faker->randomFloat(2, 0.3, 0.7), 2);

        return [
            ['method' => 'cash', 'amount' => $first],
            ['method' => $methods[array_rand($methods)], 'amount' => round($amount - $first, 2)],
        ];
    }

    private function seedPurchases(): void
    {
        for ($i = 1; $i <= 25; $i++) {
            $warehouse = $this->faker->boolean(85) ? $this->warehouses->first() : $this->warehouses->last();
            $supplier = $this->suppliers->random();
            $purchaseDate = now()->subDays($this->faker->numberBetween(1, 90));

            $itemCount = $this->faker->numberBetween(1, 4);
            $items = $this->products->random(min($itemCount, $this->products->count()));
            $subtotal = 0;
            $lines = [];

            foreach ($items as $product) {
                $quantity = $this->faker->numberBetween(10, 100);
                $price = (float) $product->purchase_price;
                $lineTotal = $quantity * $price;
                $subtotal += $lineTotal;
                $lines[] = ['product' => $product, 'quantity' => $quantity, 'price' => $price, 'total' => $lineTotal];
            }

            $discount = $this->faker->boolean(20) ? round($subtotal * 0.02, 2) : 0;
            [$paid, $due, $status] = $this->calculateTotals($subtotal, $discount);

            $purchase = Purchase::create([
                'supplier_id' => $supplier->id,
                'warehouse_id' => $warehouse->id,
                'purchase_date' => $purchaseDate,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => max($subtotal - $discount, 0),
                'paid_amount' => $paid,
                'due_amount' => $due,
                'payment_status' => $status,
                'note' => null,
            ]);
            $purchase->update(['invoice_no' => 'PU-'.str_pad((string) $purchase->id, 4, '0', STR_PAD_LEFT)]);
            $purchase->created_at = $purchaseDate;
            $purchase->save();

            foreach ($lines as $line) {
                $batch = Batch::create([
                    'product_id' => $line['product']->id,
                    'warehouse_id' => $warehouse->id,
                    'batch_no' => $this->nextBatchNo(),
                    'quantity' => $line['quantity'],
                    'mfg_date' => $line['product']->has_expiry ? $purchaseDate->copy()->subDays(10) : null,
                    'expiry_date' => $line['product']->has_expiry ? $purchaseDate->copy()->addMonths($this->faker->numberBetween(3, 12)) : null,
                ]);

                $purchase->items()->create([
                    'product_id' => $line['product']->id,
                    'batch_id' => $batch->id,
                    'batch_no' => $batch->batch_no,
                    'mfg_date' => $batch->mfg_date,
                    'expiry_date' => $batch->expiry_date,
                    'quantity' => $line['quantity'],
                    'purchase_price' => $line['price'],
                    'total' => $line['total'],
                ]);

                StockMovement::create([
                    'product_id' => $line['product']->id,
                    'batch_id' => $batch->id,
                    'type' => 'purchase',
                    'quantity_change' => $line['quantity'],
                    'quantity_before' => 0,
                    'quantity_after' => $line['quantity'],
                    'reference_type' => Purchase::class,
                    'reference_id' => $purchase->id,
                    'created_by' => $this->admin->id,
                ]);
            }

            foreach ($this->splitPayment($paid) as $payment) {
                $purchase->payments()->create($payment);
            }
        }
    }

    private function seedSales(): void
    {
        for ($i = 1; $i <= 50; $i++) {
            $batchQuery = Batch::where('quantity', '>', 5)->inRandomOrder();
            $availableBatches = $batchQuery->limit($this->faker->numberBetween(1, 3))->get();

            if ($availableBatches->isEmpty()) {
                continue;
            }

            $warehouseId = $availableBatches->first()->warehouse_id;
            $saleDate = now()->subDays($this->faker->numberBetween(0, 60));
            $customer = $this->faker->boolean(70) ? $this->customers->random() : null;

            $subtotal = 0;
            $lines = [];

            foreach ($availableBatches->where('warehouse_id', $warehouseId) as $batch) {
                $product = $this->products->firstWhere('id', $batch->product_id);

                if (! $product) {
                    continue;
                }

                $maxQty = min((float) $batch->quantity, 10);
                $quantity = $this->faker->numberBetween(1, max((int) $maxQty, 1));
                $price = (float) $product->sale_price;
                $lineTotal = $quantity * $price;
                $subtotal += $lineTotal;
                $lines[] = ['product' => $product, 'batch' => $batch, 'quantity' => $quantity, 'price' => $price, 'total' => $lineTotal];
            }

            if (empty($lines)) {
                continue;
            }

            $discount = $this->faker->boolean(15) ? round($subtotal * 0.03, 2) : 0;
            [$paid, $due, $status] = $this->calculateTotals($subtotal, $discount);

            $sale = Sale::create([
                'customer_id' => $customer?->id,
                'warehouse_id' => $warehouseId,
                'sale_date' => $saleDate,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => max($subtotal - $discount, 0),
                'paid_amount' => $paid,
                'due_amount' => $due,
                'profit' => null,
                'payment_status' => $status,
                'payment_method' => null,
                'note' => null,
            ]);
            $sale->update(['invoice_no' => 'SL-'.str_pad((string) $sale->id, 4, '0', STR_PAD_LEFT)]);
            $sale->created_at = $saleDate;
            $sale->save();

            foreach ($lines as $line) {
                $before = (float) $line['batch']->quantity;
                $line['batch']->decrement('quantity', $line['quantity']);

                $sale->items()->create([
                    'product_id' => $line['product']->id,
                    'batch_id' => $line['batch']->id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['price'],
                    'total' => $line['total'],
                ]);

                StockMovement::create([
                    'product_id' => $line['product']->id,
                    'batch_id' => $line['batch']->id,
                    'type' => 'sale',
                    'quantity_change' => -$line['quantity'],
                    'quantity_before' => $before,
                    'quantity_after' => $before - $line['quantity'],
                    'reference_type' => Sale::class,
                    'reference_id' => $sale->id,
                    'created_by' => $this->admin->id,
                ]);
            }

            foreach ($this->splitPayment($paid) as $payment) {
                $sale->payments()->create($payment);
            }
        }
    }

    private function seedReturns(): void
    {
        $sales = Sale::with('items')->inRandomOrder()->limit(4)->get();

        foreach ($sales as $sale) {
            $item = $sale->items->first();

            if (! $item || $item->quantity < 1) {
                continue;
            }

            $returnQty = min((float) $item->quantity, $this->faker->numberBetween(1, 2));
            $lineTotal = $returnQty * (float) $item->unit_price;

            $due = (float) $sale->due_amount;
            $reduceDue = min($due, $lineTotal);
            $refund = round($lineTotal - $reduceDue, 2);

            $saleReturn = SaleReturn::create([
                'sale_id' => $sale->id,
                'return_date' => now()->subDays($this->faker->numberBetween(0, 10)),
                'subtotal' => $lineTotal,
                'refund_amount' => $refund,
                'note' => 'গ্রাহক কর্তৃক ফেরত',
                'created_by' => $this->admin->id,
            ]);
            $saleReturn->update(['return_no' => 'RT-SL-'.str_pad((string) $saleReturn->id, 4, '0', STR_PAD_LEFT)]);

            $saleReturn->items()->create([
                'sale_item_id' => $item->id,
                'product_id' => $item->product_id,
                'batch_id' => $item->batch_id,
                'quantity' => $returnQty,
                'unit_price' => $item->unit_price,
                'total' => $lineTotal,
            ]);

            if ($item->batch_id && ($batch = Batch::find($item->batch_id))) {
                $before = (float) $batch->quantity;
                $batch->increment('quantity', $returnQty);

                StockMovement::create([
                    'product_id' => $item->product_id,
                    'batch_id' => $batch->id,
                    'type' => 'sale_return',
                    'quantity_change' => $returnQty,
                    'quantity_before' => $before,
                    'quantity_after' => $before + $returnQty,
                    'reference_type' => SaleReturn::class,
                    'reference_id' => $saleReturn->id,
                    'created_by' => $this->admin->id,
                ]);
            }

            $newDue = max($due - $reduceDue, 0);
            $sale->update([
                'due_amount' => $newDue,
                'payment_status' => $newDue <= 0 ? 'paid' : ($sale->paid_amount <= 0 ? 'due' : 'partial'),
            ]);
        }

        $purchases = Purchase::with('items')->inRandomOrder()->limit(3)->get();

        foreach ($purchases as $purchase) {
            $item = $purchase->items->first();

            if (! $item || $item->quantity < 1) {
                continue;
            }

            $returnQty = min((float) $item->quantity, $this->faker->numberBetween(1, 3));
            $lineTotal = $returnQty * (float) $item->purchase_price;

            $due = (float) $purchase->due_amount;
            $reduceDue = min($due, $lineTotal);
            $refund = round($lineTotal - $reduceDue, 2);

            $purchaseReturn = PurchaseReturn::create([
                'purchase_id' => $purchase->id,
                'return_date' => now()->subDays($this->faker->numberBetween(0, 10)),
                'subtotal' => $lineTotal,
                'refund_amount' => $refund,
                'note' => 'সরবরাহকারীকে ফেরত',
                'created_by' => $this->admin->id,
            ]);
            $purchaseReturn->update(['return_no' => 'RT-PU-'.str_pad((string) $purchaseReturn->id, 4, '0', STR_PAD_LEFT)]);

            $purchaseReturn->items()->create([
                'purchase_item_id' => $item->id,
                'product_id' => $item->product_id,
                'batch_id' => $item->batch_id,
                'quantity' => $returnQty,
                'unit_price' => $item->purchase_price,
                'total' => $lineTotal,
            ]);

            if ($item->batch_id && ($batch = Batch::find($item->batch_id)) && $batch->quantity >= $returnQty) {
                $before = (float) $batch->quantity;
                $batch->decrement('quantity', $returnQty);

                StockMovement::create([
                    'product_id' => $item->product_id,
                    'batch_id' => $batch->id,
                    'type' => 'purchase_return',
                    'quantity_change' => -$returnQty,
                    'quantity_before' => $before,
                    'quantity_after' => $before - $returnQty,
                    'reference_type' => PurchaseReturn::class,
                    'reference_id' => $purchaseReturn->id,
                    'created_by' => $this->admin->id,
                ]);
            }

            $newDue = max($due - $reduceDue, 0);
            $purchase->update([
                'due_amount' => $newDue,
                'payment_status' => $newDue <= 0 ? 'paid' : ($purchase->paid_amount <= 0 ? 'due' : 'partial'),
            ]);
        }
    }

    private function seedAdjustments(): void
    {
        $reasons = ['গণনা সংশোধন', 'ক্ষতিগ্রস্ত পণ্য', 'মেয়াদোত্তীর্ণ', 'বোনাস স্টক', 'চুরি/হারিয়ে যাওয়া'];
        $batches = Batch::inRandomOrder()->limit(10)->get();

        foreach ($batches as $batch) {
            $type = $this->faker->boolean(50) ? 'increase' : 'decrease';
            $before = (float) $batch->quantity;

            if ($type === 'decrease' && $before < 2) {
                $type = 'increase';
            }

            $quantity = $type === 'increase'
                ? $this->faker->numberBetween(5, 30)
                : min($before, $this->faker->numberBetween(1, 5));

            $after = $type === 'increase' ? $before + $quantity : max($before - $quantity, 0);
            $batch->update(['quantity' => $after]);

            $adjustment = StockAdjustment::create([
                'product_id' => $batch->product_id,
                'batch_id' => $batch->id,
                'type' => $type,
                'quantity' => $quantity,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'reason' => $reasons[array_rand($reasons)],
                'created_by' => $this->admin->id,
            ]);

            StockMovement::create([
                'product_id' => $batch->product_id,
                'batch_id' => $batch->id,
                'type' => $type === 'increase' ? 'adjustment_increase' : 'adjustment_decrease',
                'quantity_change' => $type === 'increase' ? $quantity : -$quantity,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'reference_type' => StockAdjustment::class,
                'reference_id' => $adjustment->id,
                'note' => $adjustment->reason,
                'created_by' => $this->admin->id,
            ]);
        }
    }

    private function seedTransfers(): void
    {
        $from = $this->warehouses->first();
        $to = $this->warehouses->last();

        $candidateBatches = Batch::where('warehouse_id', $from->id)->where('quantity', '>', 5)->inRandomOrder()->limit(3)->get();

        $statuses = ['pending', 'approved', 'received'];

        foreach ($candidateBatches as $index => $batch) {
            $targetStatus = $statuses[$index] ?? 'pending';
            $quantity = min((float) $batch->quantity, $this->faker->numberBetween(2, 5));

            $transfer = StockTransfer::create([
                'from_warehouse_id' => $from->id,
                'to_warehouse_id' => $to->id,
                'status' => 'pending',
                'requested_by' => $this->admin->id,
                'note' => 'নিয়মিত স্টক পুনর্বণ্টন',
            ]);
            $transfer->update(['transfer_no' => 'TR-'.str_pad((string) $transfer->id, 4, '0', STR_PAD_LEFT)]);

            $transfer->items()->create([
                'product_id' => $batch->product_id,
                'batch_id' => $batch->id,
                'batch_no' => $batch->batch_no,
                'quantity' => $quantity,
            ]);

            if ($targetStatus === 'pending') {
                continue;
            }

            $transfer->update(['status' => 'approved', 'approved_by' => $this->admin->id, 'approved_at' => now()]);

            if ($targetStatus === 'approved') {
                continue;
            }

            // Dispatch: remove from source.
            $before = (float) $batch->quantity;
            $batch->decrement('quantity', $quantity);
            StockMovement::create([
                'product_id' => $batch->product_id,
                'batch_id' => $batch->id,
                'type' => 'transfer_out',
                'quantity_change' => -$quantity,
                'quantity_before' => $before,
                'quantity_after' => $before - $quantity,
                'reference_type' => StockTransfer::class,
                'reference_id' => $transfer->id,
                'created_by' => $this->admin->id,
            ]);
            $transfer->update(['status' => 'dispatched', 'dispatched_by' => $this->admin->id, 'dispatched_at' => now()]);

            // Receive: add to destination (new batch there).
            $destBatch = Batch::create([
                'product_id' => $batch->product_id,
                'warehouse_id' => $to->id,
                'batch_no' => $batch->batch_no,
                'quantity' => $quantity,
                'mfg_date' => $batch->mfg_date,
                'expiry_date' => $batch->expiry_date,
            ]);
            StockMovement::create([
                'product_id' => $batch->product_id,
                'batch_id' => $destBatch->id,
                'type' => 'transfer_in',
                'quantity_change' => $quantity,
                'quantity_before' => 0,
                'quantity_after' => $quantity,
                'reference_type' => StockTransfer::class,
                'reference_id' => $transfer->id,
                'created_by' => $this->admin->id,
            ]);
            $transfer->update(['status' => 'received', 'received_by' => $this->admin->id, 'received_at' => now()]);
        }
    }

    private function seedFinance(): void
    {
        $incomeSources = ['বিবিধ আয়', 'সার্ভিস চার্জ', 'কমিশন', 'ভাড়া আয়', 'বিনিয়োগ আয়'];
        $paymentMethods = ['নগদ', 'ব্যাংক', 'বিকাশ'];

        foreach (range(1, 15) as $i) {
            Income::create([
                'source' => $incomeSources[array_rand($incomeSources)],
                'amount' => $this->faker->randomFloat(2, 500, 15000),
                'income_date' => now()->subDays($this->faker->numberBetween(0, 90)),
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'note' => null,
            ]);
        }

        foreach (range(1, 20) as $i) {
            $entry = $this->expenseCategories->random();
            $category = $entry['category'];
            $subCategory = $entry['sub_categories']->random();

            Expense::create([
                'expense_category_id' => $category->id,
                'expense_sub_category_id' => $subCategory->id,
                'title' => $subCategory->name,
                'amount' => $this->faker->randomFloat(2, 300, 20000),
                'expense_date' => now()->subDays($this->faker->numberBetween(0, 90)),
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'note' => null,
            ]);
        }
    }

    private function seedSubscriptionPayments(): void
    {
        $subscription = $this->shop->subscription()->with('plan')->first();

        if (! $subscription || ! $subscription->plan) {
            return;
        }

        foreach ([2, 1, 0] as $monthsAgo) {
            SubscriptionPayment::create([
                'subscription_id' => $subscription->id,
                'amount' => $subscription->plan->price,
                'method' => 'bank',
                'paid_at' => now()->subMonths($monthsAgo)->startOfMonth(),
                'note' => 'মাসিক সাবস্ক্রিপশন পেমেন্ট',
            ]);
        }
    }
}
