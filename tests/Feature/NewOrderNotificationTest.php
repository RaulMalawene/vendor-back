<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use App\Notifications\NewOrderReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NewOrderNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifica_o_vendedor_quando_uma_encomenda_e_criada(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $product = Product::factory()->create([
            'user_id' => $user->id,
            'stock' => 10,
            'price' => 100,
            'is_active' => true,
        ]);

        $customer = Customer::factory()->create(['user_id' => $user->id]);

        $orderId = $this->postJson('/api/orders', [
            'customer_id' => $customer->id,
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ])->assertCreated()->json('data.id');

        Notification::assertSentTo(
            $user,
            NewOrderReceived::class,
            fn ($notification) => $notification->order->id === $orderId,
        );
    }
}
