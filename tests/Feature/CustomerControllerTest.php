<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_mostra_um_cliente_do_utilizador_autenticado(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $customer = Customer::factory()->create(['user_id' => $user->id]);

        $this->getJson("/api/customers/{$customer->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $customer->id)
            ->assertJsonPath('data.name', $customer->name);
    }

    public function test_nao_mostra_cliente_de_outro_utilizador(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $otherCustomer = Customer::factory()->create();

        $this->getJson("/api/customers/{$otherCustomer->id}")->assertNotFound();
    }

    public function test_actualiza_um_cliente(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $customer = Customer::factory()->create(['user_id' => $user->id, 'name' => 'Nome Antigo']);

        $this->putJson("/api/customers/{$customer->id}", ['name' => 'Nome Novo'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Nome Novo');

        $this->assertSame('Nome Novo', $customer->fresh()->name);
    }

    public function test_elimina_um_cliente_sem_encomendas(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $customer = Customer::factory()->create(['user_id' => $user->id]);

        $this->deleteJson("/api/customers/{$customer->id}")->assertOk();

        $this->assertModelMissing($customer);
    }

    public function test_nao_elimina_um_cliente_com_encomendas_associadas(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $customer = Customer::factory()->create(['user_id' => $user->id]);
        Order::create([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'number' => 'ORD-TEST-0001',
            'status' => 'pending',
            'total' => 0,
            'placed_at' => now(),
        ]);

        $this->deleteJson("/api/customers/{$customer->id}")->assertStatus(409);

        $this->assertModelExists($customer);
    }
}
