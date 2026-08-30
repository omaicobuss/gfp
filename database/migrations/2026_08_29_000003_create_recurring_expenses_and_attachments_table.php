<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recurring_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('description', 255);
            $table->decimal('expected_amount', 10, 2);
            $table->string('frequency', 20); // 'weekly', 'monthly', 'yearly', 'custom'
            $table->unsignedSmallInteger('frequency_days')->nullable(); // Para frequency = 'custom'
            $table->unsignedTinyInteger('due_day')->nullable(); // Dia do mês (1 a 31) para 'monthly'
            $table->date('due_date'); // Data do primeiro/próximo vencimento
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Índices de otimização
            $table->index(['user_id', 'is_active']);
            $table->index(['user_id', 'due_date']);
            $table->index(['user_id', 'category_id']);
        });

        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('attachable_type');
            $table->unsignedBigInteger('attachable_id');
            $table->string('type', 30); // 'billing_document' ou 'payment_receipt'
            $table->string('original_name', 255);
            $table->string('file_path', 500);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size'); // em bytes
            $table->timestamps();

            // Índices polimórficos e de isolamento de usuário
            $table->index(['attachable_type', 'attachable_id']);
            $table->index(['user_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('recurring_expenses');
    }
};
