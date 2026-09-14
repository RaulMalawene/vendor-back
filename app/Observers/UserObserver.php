<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    /**
     * Clientes de teste criados automaticamente para todo novo vendedor,
     * para facilitar testes do fluxo de encomendas sem precisar de
     * cadastro manual. Ver conversa com o utilizador em 2026-09-14.
     *
     * @var list<array{name: string, email: string, phone: string}>
     */
    private const TEST_CUSTOMERS = [
        ['name' => 'Cliente Teste 1', 'email' => 'cliente1@teste.com', 'phone' => '84 000 0001'],
        ['name' => 'Cliente Teste 2', 'email' => 'cliente2@teste.com', 'phone' => '84 000 0002'],
    ];

    public function created(User $user): void
    {
        foreach (self::TEST_CUSTOMERS as $customer) {
            $user->customers()->firstOrCreate(
                ['name' => $customer['name']],
                ['email' => $customer['email'], 'phone' => $customer['phone']],
            );
        }
    }
}
