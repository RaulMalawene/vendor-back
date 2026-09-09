<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
        $table->string('name');
        $table->string('sku');
        $table->decimal('price', 12, 2)->default(0);
        $table->unsignedInteger('stock')->default(0);
        $table->boolean('is_active')->default(true);
        $table->timestamps();
        $table->softDeletes();
        $table->unique(['user_id', 'sku']);
        $table->index(['user_id', 'is_active']);
    });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
