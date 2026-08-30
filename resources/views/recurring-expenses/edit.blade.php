<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Gasto Recorrente (Modelo)
            </h2>
            <a href="{{ route('recurring-expenses.index') }}" class="text-sm text-gray-600 hover:text-indigo-600 underline">
                ← Voltar para a lista
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8 border border-gray-100" x-data="{ frequency: '{{ old('frequency', $recurringExpense->frequency) }}' }">
                <form action="{{ route('recurring-expenses.update', $recurringExpense) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Descrição -->
                    <div>
                        <x-input-label for="description" value="Descrição do Gasto Recorrente *" />
                        <x-text-input id="description" name="description" type="text" class="mt-1 block w-full" placeholder="Ex: Aluguel..." :value="old('description', $recurringExpense->description)" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                    </div>

                    <!-- Categoria e Valor Esperado -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Categoria -->
                        <div>
                            <div class="flex justify-between items-center">
                                <x-input-label for="category_id" value="Categoria *" />
                                <a href="{{ route('categories.index') }}" class="text-xs text-indigo-600 hover:underline">
                                    + Nova Categoria
                                </a>
                            </div>
                            <select id="category_id" name="category_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                                <option value="">Selecione uma categoria...</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $recurringExpense->category_id) == $category->id ? 'selected' : '' }}>
                                        🏷️ {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('category_id')" />
                        </div>

                        <!-- Valor Esperado -->
                        <div>
                            <x-input-label for="expected_amount" value="Valor Esperado (R$) *" />
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 text-sm font-semibold">
                                    R$
                                </span>
                                <input type="number" step="0.01" min="0.01" name="expected_amount" id="expected_amount" value="{{ old('expected_amount', $recurringExpense->expected_amount) }}" placeholder="0,00" class="pl-10 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required />
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('expected_amount')" />
                        </div>
                    </div>

                    <!-- Frequência e Campos Dinâmicos -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Periodicidade -->
                        <div>
                            <x-input-label for="frequency" value="Frequência / Periodicidade *" />
                            <select id="frequency" name="frequency" x-model="frequency" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                                <option value="monthly">Mensal</option>
                                <option value="weekly">Semanal</option>
                                <option value="yearly">Anual</option>
                                <option value="custom">Personalizada (Intervalo de dias)</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('frequency')" />
                        </div>

                        <!-- Dia do Vencimento (quando Mensal) -->
                        <div x-show="frequency === 'monthly'" x-cloak>
                            <x-input-label for="due_day" value="Dia do Vencimento no Mês (1-31) *" />
                            <x-text-input id="due_day" name="due_day" type="number" min="1" max="31" class="mt-1 block w-full" placeholder="Ex: 5, 10, 25" :value="old('due_day', $recurringExpense->due_day)" />
                            <x-input-error class="mt-2" :messages="$errors->get('due_day')" />
                        </div>

                        <!-- Intervalo em Dias (quando Personalizada) -->
                        <div x-show="frequency === 'custom'" x-cloak>
                            <x-input-label for="frequency_days" value="Intervalo em Dias *" />
                            <x-text-input id="frequency_days" name="frequency_days" type="number" min="1" max="365" class="mt-1 block w-full" placeholder="Ex: 15, 45, 90" :value="old('frequency_days', $recurringExpense->frequency_days)" />
                            <x-input-error class="mt-2" :messages="$errors->get('frequency_days')" />
                        </div>
                    </div>

                    <!-- Próximo Vencimento -->
                    <div>
                        <x-input-label for="due_date" value="Data de Referência / Próximo Vencimento *" />
                        <x-text-input id="due_date" name="due_date" type="date" class="mt-1 block w-full" :value="old('due_date', $recurringExpense->due_date->format('Y-m-d'))" required />
                        <x-input-error class="mt-2" :messages="$errors->get('due_date')" />
                    </div>

                    <!-- Documento de Cobrança Atual e Substituição -->
                    <div class="p-5 bg-gray-50 rounded-xl border border-gray-200 space-y-3">
                        <x-input-label for="billing_document" value="📄 Documento de Cobrança / Boleto" />

                        @if ($recurringExpense->billingDocument)
                            <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-gray-200">
                                <div class="flex items-center space-x-3">
                                    <span class="text-2xl">{{ $recurringExpense->billingDocument->is_pdf ? '📕' : '🖼️' }}</span>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">{{ $recurringExpense->billingDocument->original_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $recurringExpense->billingDocument->formatted_size }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('attachments.download', $recurringExpense->billingDocument) }}" class="px-3 py-1.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded text-xs font-semibold transition">
                                        Baixar
                                    </a>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500">Para substituir o arquivo atual por um novo, selecione o novo documento abaixo:</p>
                        @else
                            <p class="text-xs text-gray-500">Nenhum documento anexado no momento. Formatos aceitos: <strong>PDF, JPG ou PNG</strong> (máx. 10MB).</p>
                        @endif

                        <input id="billing_document" name="billing_document" type="file" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                        <x-input-error class="mt-2" :messages="$errors->get('billing_document')" />
                    </div>

                    <!-- Observações -->
                    <div>
                        <x-input-label for="notes" value="Observações / Anotações (Opcional)" />
                        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" placeholder="Ex: Código de débito automático, chave Pix cadastrada...">{{ old('notes', $recurringExpense->notes) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                    </div>

                    <!-- Status Ativo -->
                    <div>
                        <label for="is_active" class="inline-flex items-center">
                            <input id="is_active" type="checkbox" name="is_active" value="1" {{ old('is_active', $recurringExpense->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ms-2 text-sm text-gray-700 font-medium">Gasto recorrente ativo</span>
                        </label>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-100">
                        <a href="{{ route('recurring-expenses.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            Cancelar
                        </a>

                        <x-primary-button>
                            Atualizar Gasto Recorrente
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
