<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_nao_permite_confirmar_encomendas_acima_do_stock(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $product = Product::factory()->create([
            'user_id' => $user->id,
            'stock' => 10,
            'price' => 100,
            'is_active' => true,
        ]);

        $customerA = Customer::factory()->create(['user_id' => $user->id]);
        $customerB = Customer::factory()->create(['user_id' => $user->id]);

        $orderA = $this->postJson('/api/orders', [
            'customer_id' => $customerA->id,
            'items' => [['product_id' => $product->id, 'quantity' => 7]],
        ])->assertCreated()->json('data.id');

        $orderB = $this->postJson('/api/orders', [
            'customer_id' => $customerB->id,
            'items' => [['product_id' => $product->id, 'quantity' => 5]],
        ])->assertCreated()->json('data.id');

        $this->patchJson("/api/orders/{$orderA}/status", ['status' => 'confirmed'])
            ->assertOk();

        $this->patchJson("/api/orders/{$orderB}/status", ['status' => 'confirmed'])
            ->assertStatus(422);

        $this->assertSame(3, $product->fresh()->stock);
    }
}