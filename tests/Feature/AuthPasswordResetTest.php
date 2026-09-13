<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_envia_notificacao_de_recuperacao_quando_o_email_existe(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->postJson('/api/forgot-password', ['email' => $user->email])
            ->assertOk();

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_nao_revela_erro_de_validacao_generico_para_email_desconhecido(): void
    {
        Notification::fake();

        $this->postJson('/api/forgot-password', ['email' => 'inexistente@example.com'])
            ->assertStatus(422);

        Notification::assertNothingSent();
    }

    public function test_repoe_a_palavra_passe_com_um_token_valido(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'nova-palavra-passe',
            'password_confirmation' => 'nova-palavra-passe',
        ])->assertOk();

        $this->assertTrue(Hash::check('nova-palavra-passe', $user->fresh()->password));
    }

    public function test_rejeita_token_invalido(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/reset-password', [
            'token' => 'token-invalido',
            'email' => $user->email,
            'password' => 'nova-palavra-passe',
            'password_confirmation' => 'nova-palavra-passe',
        ])->assertStatus(422);
    }
}
