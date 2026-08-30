<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\Category;
use App\Models\RecurringExpense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RecurringExpenseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_authenticated_user_can_view_recurring_expenses_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('recurring-expenses.index'));

        $response->assertOk();
        $response->assertViewIs('recurring-expenses.index');
        $response->assertSee('Gastos Recorrentes');
    }

    public function test_user_can_create_monthly_recurring_expense_with_billing_document(): void
    {
        $user = User::factory()->create();
        $category = Category::where('user_id', $user->id)->first();

        $file = UploadedFile::fake()->create('boleto_aluguel.pdf', 500, 'application/pdf');

        $response = $this->actingAs($user)->post(route('recurring-expenses.store'), [
            'description' => 'Aluguel do Apartamento',
            'category_id' => $category->id,
            'expected_amount' => '1.850,00',
            'frequency' => 'monthly',
            'due_day' => 10,
            'due_date' => '2026-09-10',
            'is_active' => true,
            'billing_document' => $file,
            'notes' => 'Pagar até dia 10 para desconto',
        ]);

        $response->assertRedirect(route('recurring-expenses.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('recurring_expenses', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'description' => 'Aluguel do Apartamento',
            'expected_amount' => 1850.00,
            'frequency' => 'monthly',
            'due_day' => 10,
        ]);

        $recurring = RecurringExpense::where('description', 'Aluguel do Apartamento')->first();
        $this->assertNotNull($recurring->billingDocument);
        $this->assertEquals('boleto_aluguel.pdf', $recurring->billingDocument->original_name);
        Storage::disk('local')->assertExists($recurring->billingDocument->file_path);
    }

    public function test_user_can_create_custom_frequency_recurring_expense(): void
    {
        $user = User::factory()->create();
        $category = Category::where('user_id', $user->id)->first();

        $response = $this->actingAs($user)->post(route('recurring-expenses.store'), [
            'description' => 'Manutenção de Filtro',
            'category_id' => $category->id,
            'expected_amount' => '80.00',
            'frequency' => 'custom',
            'frequency_days' => 45,
            'due_date' => '2026-10-01',
        ]);

        $response->assertRedirect(route('recurring-expenses.index'));

        $this->assertDatabaseHas('recurring_expenses', [
            'user_id' => $user->id,
            'description' => 'Manutenção de Filtro',
            'frequency' => 'custom',
            'frequency_days' => 45,
        ]);
    }

    public function test_upload_exceeding_10mb_is_rejected(): void
    {
        $user = User::factory()->create();
        $category = Category::where('user_id', $user->id)->first();

        // 11MB file (11264 KB)
        $largeFile = UploadedFile::fake()->create('arquivo_pesado.pdf', 11264, 'application/pdf');

        $response = $this->actingAs($user)->post(route('recurring-expenses.store'), [
            'description' => 'Conta com Arquivo Gigante',
            'category_id' => $category->id,
            'expected_amount' => '100.00',
            'frequency' => 'monthly',
            'due_day' => 5,
            'due_date' => '2026-09-05',
            'billing_document' => $largeFile,
        ]);

        $response->assertSessionHasErrors('billing_document');
        $this->assertDatabaseMissing('recurring_expenses', [
            'description' => 'Conta com Arquivo Gigante',
        ]);
    }

    public function test_unsupported_file_type_is_rejected(): void
    {
        $user = User::factory()->create();
        $category = Category::where('user_id', $user->id)->first();

        // .exe or .txt file
        $invalidFile = UploadedFile::fake()->create('script.exe', 100, 'application/x-msdownload');

        $response = $this->actingAs($user)->post(route('recurring-expenses.store'), [
            'description' => 'Conta com Arquivo Inválido',
            'category_id' => $category->id,
            'expected_amount' => '100.00',
            'frequency' => 'monthly',
            'due_day' => 5,
            'due_date' => '2026-09-05',
            'billing_document' => $invalidFile,
        ]);

        $response->assertSessionHasErrors('billing_document');
        $this->assertDatabaseMissing('recurring_expenses', [
            'description' => 'Conta com Arquivo Inválido',
        ]);
    }

    public function test_uploading_new_document_replaces_old_one(): void
    {
        $user = User::factory()->create();
        $category = Category::where('user_id', $user->id)->first();

        $recurring = RecurringExpense::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        // Primeiro upload
        $file1 = UploadedFile::fake()->create('boleto_v1.pdf', 200, 'application/pdf');
        $this->actingAs($user)->put(route('recurring-expenses.update', $recurring), [
            'description' => $recurring->description,
            'category_id' => $category->id,
            'expected_amount' => '100.00',
            'frequency' => 'monthly',
            'due_day' => 5,
            'due_date' => '2026-09-05',
            'billing_document' => $file1,
        ]);

        $doc1 = $recurring->fresh()->billingDocument;
        $path1 = $doc1->file_path;
        Storage::disk('local')->assertExists($path1);

        // Segundo upload (substituição)
        $file2 = UploadedFile::fake()->image('novo_boleto.jpg');
        $this->actingAs($user)->put(route('recurring-expenses.update', $recurring), [
            'description' => $recurring->description,
            'category_id' => $category->id,
            'expected_amount' => '100.00',
            'frequency' => 'monthly',
            'due_day' => 5,
            'due_date' => '2026-09-05',
            'billing_document' => $file2,
        ]);

        // Verifica que o anterior foi removido do disco e do banco
        Storage::disk('local')->assertMissing($path1);
        $this->assertDatabaseMissing('attachments', ['id' => $doc1->id]);

        // Novo documento registrado
        $doc2 = $recurring->fresh()->billingDocument;
        $this->assertEquals('novo_boleto.jpg', $doc2->original_name);
        Storage::disk('local')->assertExists($doc2->file_path);
    }

    public function test_user_can_toggle_active_status(): void
    {
        $user = User::factory()->create();
        $recurring = RecurringExpense::factory()->create([
            'user_id' => $user->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->patch(route('recurring-expenses.toggle-active', $recurring));

        $response->assertRedirect(route('recurring-expenses.index'));
        $this->assertFalse($recurring->fresh()->is_active);

        // Toggle back to active
        $this->actingAs($user)->patch(route('recurring-expenses.toggle-active', $recurring));
        $this->assertTrue($recurring->fresh()->is_active);
    }

    public function test_user_can_download_their_own_attachment(): void
    {
        $user = User::factory()->create();
        $recurring = RecurringExpense::factory()->create(['user_id' => $user->id]);

        $file = UploadedFile::fake()->create('meu_boleto.pdf', 300, 'application/pdf');
        $path = $file->store("attachments/{$user->id}/billing_document", 'local');

        $attachment = Attachment::create([
            'user_id' => $user->id,
            'attachable_type' => RecurringExpense::class,
            'attachable_id' => $recurring->id,
            'type' => 'billing_document',
            'original_name' => 'meu_boleto.pdf',
            'file_path' => $path,
            'mime_type' => 'application/pdf',
            'file_size' => 300 * 1024,
        ]);

        $response = $this->actingAs($user)->get(route('attachments.download', $attachment));

        $response->assertOk();
        $response->assertHeader('Content-Disposition', 'attachment; filename=meu_boleto.pdf');
    }

    public function test_user_cannot_download_another_users_attachment(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $recurringA = RecurringExpense::factory()->create(['user_id' => $userA->id]);

        $file = UploadedFile::fake()->create('sigiloso.pdf', 300, 'application/pdf');
        $path = $file->store("attachments/{$userA->id}/billing_document", 'local');

        $attachmentA = Attachment::create([
            'user_id' => $userA->id,
            'attachable_type' => RecurringExpense::class,
            'attachable_id' => $recurringA->id,
            'type' => 'billing_document',
            'original_name' => 'sigiloso.pdf',
            'file_path' => $path,
            'mime_type' => 'application/pdf',
            'file_size' => 300 * 1024,
        ]);

        $response = $this->actingAs($userB)->get(route('attachments.download', $attachmentA));

        $response->assertForbidden();
    }

    public function test_user_cannot_access_or_modify_another_users_recurring_expense(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $recurringA = RecurringExpense::factory()->create(['user_id' => $userA->id]);

        // Tentativa de ver
        $this->actingAs($userB)->get(route('recurring-expenses.show', $recurringA))->assertForbidden();

        // Tentativa de editar
        $this->actingAs($userB)->get(route('recurring-expenses.edit', $recurringA))->assertForbidden();

        // Tentativa de atualizar
        $this->actingAs($userB)->put(route('recurring-expenses.update', $recurringA), [
            'description' => 'Hackeado',
            'category_id' => Category::where('user_id', $userB->id)->first()->id,
            'expected_amount' => '1.00',
            'frequency' => 'monthly',
            'due_day' => 1,
            'due_date' => '2026-09-01',
        ])->assertForbidden();

        // Tentativa de excluir
        $this->actingAs($userB)->delete(route('recurring-expenses.destroy', $recurringA))->assertForbidden();

        $this->assertDatabaseHas('recurring_expenses', ['id' => $recurringA->id]);
    }
}
