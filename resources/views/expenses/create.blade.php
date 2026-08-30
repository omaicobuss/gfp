<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Novo Gasto
            </h2>
            <a href="{{ route('expenses.index') }}" class="text-sm text-gray-600 hover:text-indigo-600 underline">
                ← Voltar para a lista
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8 border border-gray-100">
                <form action="{{ route('expenses.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Descrição -->
                    <div>
                        <x-input-label for="description" value="Descrição do Gasto *" />
                        <x-text-input id="description" name="description" type="text" class="mt-1 block w-full" placeholder="Ex: Compras do Supermercado, Almoço de Domingo..." :value="old('description')" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                    </div>

                    <!-- Categoria e Valor em duas colunas -->
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
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        🏷️ {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('category_id')" />
                        </div>

                        <!-- Valor (BRL) -->
                        <div>
                            <x-input-label for="amount" value="Valor (R$) *" />
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 text-sm font-semibold">
                                    R$
                                </span>
                                <input type="number" step="0.01" min="0.01" name="amount" id="amount" value="{{ old('amount') }}" placeholder="0,00" class="pl-10 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required />
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('amount')" />
                        </div>
                    </div>

                    <!-- Data e Status de Pagamento -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                        <!-- Data do Gasto / Vencimento -->
                        <div>
                            <x-input-label for="date" value="Data do Gasto / Vencimento *" />
                            <x-text-input id="date" name="date" type="date" class="mt-1 block w-full" :value="old('date', date('Y-m-d'))" required />
                            <x-input-error class="mt-2" :messages="$errors->get('date')" />
                        </div>

                        <!-- Já foi pago? -->
                        <div class="pt-6">
                            <label for="is_paid" class="inline-flex items-center p-3 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 cursor-pointer w-full transition">
                                <input id="is_paid" type="checkbox" name="is_paid" value="1" {{ old('is_paid') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 w-5 h-5">
                                <span class="ms-3 text-sm font-medium text-gray-700">
                                    ✅ Este gasto já foi pago
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Observações / Notas -->
                    <div>
                        <x-input-label for="notes" value="Observações / Anotações (Opcional)" />
                        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" placeholder="Adicione qualquer detalhe adicional sobre este gasto...">{{ old('notes') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-100">
                        <a href="{{ route('expenses.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            Cancelar
                        </a>

                        <x-primary-button>
                            Cadastrar Gasto
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
