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
        Schema::create('recurring_expense_occurrences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recurring_expense_id')->constrained('recurring_expenses')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('description', 255);
            $table->date('due_date');
            $table->decimal('expected_amount', 10, 2);
            $table->decimal('actual_amount', 10, 2)->nullable();
            $table->string('status', 20)->default('pending'); // 'paid', 'pending', 'overdue'
            $table->date('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Evitar duplicidade de ocorrência na mesma data para o mesmo modelo
            $table->unique(['recurring_expense_id', 'due_date']);

            // Índices para consultas consolidadas e relatórios
            $table->index(['user_id', 'due_date']);
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_expense_occurrences');
    }
};
