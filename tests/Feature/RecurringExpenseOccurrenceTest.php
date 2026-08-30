<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\Category;
use App\Models\RecurringExpense;
use App\Models\RecurringExpenseOccurrence;
use App\Models\User;
use App\Services\RecurringExpenseService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RecurringExpenseOccurrenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_service_generates_correct_occurrences_for_monthly_recurring_expense(): void
    {
        $user = User::factory()->create();
        $category = Category::where('user_id', $user->id)->first();

        $recurring = RecurringExpense::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'frequency' => 'monthly',
            'due_day' => 15,
            'due_date' => '2026-09-15',
            'expected_amount' => 500.00,
        ]);

        $created = RecurringExpenseService::generateOccurrences($recurring, Carbon::parse('2026-11-30'));

        $this->assertEquals(3, $created); // Set, Out, Nov
        $this->assertDatabaseHas('recurring_expense_occurrences', [
            'recurring_expense_id' => $recurring->id,
            'expected_amount' => 500.00,
        ]);

        $dates = $recurring->occurrences()->orderBy('due_date')->pluck('due_date')->map->format('Y-m-d')->toArray();
        $this->assertEquals(['2026-09-15', '2026-10-15', '2026-11-15'], $dates);
    }

    public function test_user_can_record_payment_with_different_actual_amount_and_receipt(): void
    {
        $user = User::factory()->create();
        $occurrence = RecurringExpenseOccurrence::factory()->create([
            'user_id' => $user->id,
            'expected_amount' => 200.00,
            'actual_amount' => null,
            'status' => 'pending',
        ]);

        $receipt = UploadedFile::fake()->create('comprovante_pix.pdf', 300, 'application/pdf');

        $response = $this->actingAs($user)->post(route('occurrences.pay.store', $occurrence), [
            'actual_amount' => '210.50', // Com juros de atraso
            'paid_at' => Carbon::today()->toDateString(),
            'payment_receipt' => $receipt,
            'notes' => 'Pago com juros',
        ]);

        $response->assertRedirect(route('expenses.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('recurring_expense_occurrences', [
            'id' => $occurrence->id,
            'actual_amount' => 210.50,
            'status' => 'paid',
            'notes' => 'Pago com juros',
        ]);

        $occurrence->refresh();
        $this->assertNotNull($occurrence->paymentReceipt);
        $this->assertEquals('comprovante_pix.pdf', $occurrence->paymentReceipt->original_name);
        Storage::disk('local')->assertExists($occurrence->paymentReceipt->file_path);
    }

    public function test_payment_receipt_exceeding_10mb_is_rejected(): void
    {
        $user = User::factory()->create();
        $occurrence = RecurringExpenseOccurrence::factory()->create([
            'user_id' => $user->id,
        ]);

        $largeReceipt = UploadedFile::fake()->create('grande.pdf', 11264, 'application/pdf');

        $response = $this->actingAs($user)->post(route('occurrences.pay.store', $occurrence), [
            'actual_amount' => '200.00',
            'paid_at' => Carbon::today()->toDateString(),
            'payment_receipt' => $largeReceipt,
        ]);

        $response->assertSessionHasErrors('payment_receipt');
        $this->assertNull($occurrence->fresh()->actual_amount);
    }

    public function test_user_can_unpay_occurrence(): void
    {
        $user = User::factory()->create();
        $occurrence = RecurringExpenseOccurrence::factory()->paid()->create([
            'user_id' => $user->id,
            'due_date' => Carbon::tomorrow()->toDateString(),
        ]);

        $response = $this->actingAs($user)->post(route('occurrences.unpay', $occurrence));

        $response->assertSessionHas('status');
        $this->assertNull($occurrence->fresh()->actual_amount);
        $this->assertNull($occurrence->fresh()->paid_at);
        $this->assertEquals('pending', $occurrence->fresh()->status);
    }

    public function test_user_can_update_occurrence(): void
    {
        $user = User::factory()->create();
        $category = Category::where('user_id', $user->id)->first();
        $occurrence = RecurringExpenseOccurrence::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'description' => 'Descrição Velha',
        ]);

        $response = $this->actingAs($user)->put(route('occurrences.update', $occurrence), [
            'description' => 'Descrição Nova',
            'category_id' => $category->id,
            'due_date' => Carbon::today()->toDateString(),
            'expected_amount' => '300.00',
        ]);

        $response->assertRedirect(route('expenses.index'));
        $this->assertEquals('Descrição Nova', $occurrence->fresh()->description);
        $this->assertEquals(300.00, $occurrence->fresh()->expected_amount);
    }

    public function test_user_can_delete_occurrence(): void
    {
        $user = User::factory()->create();
        $occurrence = RecurringExpenseOccurrence::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete(route('occurrences.destroy', $occurrence));

        $response->assertRedirect(route('expenses.index'));
        $this->assertDatabaseMissing('recurring_expense_occurrences', [
            'id' => $occurrence->id,
        ]);
    }

    public function test_user_cannot_pay_or_modify_another_users_occurrence(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $occurrenceA = RecurringExpenseOccurrence::factory()->create([
            'user_id' => $userA->id,
        ]);

        // Tentativa de pagar ocorrência de outro usuário
        $this->actingAs($userB)->post(route('occurrences.pay.store', $occurrenceA), [
            'actual_amount' => '100.00',
            'paid_at' => Carbon::today()->toDateString(),
        ])->assertForbidden();

        // Tentativa de editar ocorrência de outro usuário
        $this->actingAs($userB)->get(route('occurrences.edit', $occurrenceA))->assertForbidden();

        // Tentativa de excluir ocorrência de outro usuário
        $this->actingAs($userB)->delete(route('occurrences.destroy', $occurrenceA))->assertForbidden();
    }
}
