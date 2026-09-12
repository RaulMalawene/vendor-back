<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_mostra_uma_categoria_do_utilizador_autenticado(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create(['user_id' => $user->id]);

        $this->getJson("/api/categories/{$category->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $category->id)
            ->assertJsonPath('data.name', $category->name);
    }
}
