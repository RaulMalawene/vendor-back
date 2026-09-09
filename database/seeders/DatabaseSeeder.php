<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'name' => 'Vendedor Demo',
            'email' => 'demo@vp.mz',
            'password' => 'password',
        ]);

        $user->company()->create([
            'name' => 'Loja Demo, Lda',
            'contact_name' => 'Ana Mondlane',
            'phone' => '84000000',
            'email' => 'geral@lojademo.mz',
            'bank_name' => 'BIM Millennium',
            'bank_account_holder' => 'Loja Demo, Lda',
            'bank_account_number' => '1234567890123456',
        ]);

        $categories = Category::factory()
            ->count(4)
            ->sequence(
                ['name' => 'Bebidas'],
                ['name' => 'Alimentar'],
                ['name' => 'Higiene'],
                ['name' => 'Diversos'],
            )
            ->create(['user_id' => $user->id]);

        Product::factory()
            ->count(15)
            ->recycle($categories)
            ->create(['user_id' => $user->id]);

        Product::factory()
            ->count(3)
            ->lowStock()
            ->recycle($categories)
            ->create(['user_id' => $user->id]);

        Product::factory()
            ->count(2)
            ->outOfStock()
            ->recycle($categories)
            ->create(['user_id' => $user->id]);

        $customers = Customer::factory()->count(5)->create(['user_id' => $user->id]);

        $concurrencyProduct = Product::factory()->create([
            'user_id' => $user->id,
            'category_id' => $categories->first()->id,
            'name' => 'Produto Concorrência',
            'sku' => 'SKU-CONC-01',
            'price' => 200,
            'stock' => 10,
            'is_active' => true,
        ]);

        Order::create([
            'user_id' => $user->id,
            'customer_id' => $customers->first()->id,
            'number' => 'ORD-2026-0001',
            'status' => OrderStatus::Pending,
            'total' => 0,
            'placed_at' => now(),
        ]);
    }
}