<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $recurringExpense->description }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">Detalhes do modelo de gasto recorrente</p>
            </div>

            <div class="flex items-center space-x-3">
                <a href="{{ route('recurring-expenses.edit', $recurringExpense) }}" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-50 shadow-sm transition">
                    ✏️ Editar Modelo
                </a>
                <a href="{{ route('recurring-expenses.index') }}" class="text-sm text-gray-600 hover:text-indigo-600 underline">
                    ← Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Alerts -->
            @if (session('status'))
                <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm flex items-center shadow-sm">
                    <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- Informações Principais (2 colunas) -->
                <div class="md:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-6">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Valor Esperado</span>
                            <div class="text-3xl font-extrabold text-gray-900 mt-1">{{ $recurringExpense->formatted_expected_amount }}</div>
                        </div>
                        <div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $recurringExpense->is_active ? 'bg-green-100 text-green-800 border-green-200' : 'bg-gray-100 text-gray-600 border-gray-200' }}">
                                {{ $recurringExpense->is_active ? '🟢 Ativo' : '⚪ Pausado' }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500 font-medium">Categoria:</span>
                            <p class="font-semibold text-gray-900 mt-0.5">🏷️ {{ $recurringExpense->category->name }}</p>
                        </div>

                        <div>
                            <span class="text-gray-500 font-medium">Periodicidade:</span>
                            <p class="font-semibold text-gray-900 mt-0.5">🔄 {{ $recurringExpense->frequency_label }}</p>
                        </div>

                        <div>
                            <span class="text-gray-500 font-medium">Próximo Vencimento:</span>
                            <p class="font-semibold text-gray-900 mt-0.5">📅 {{ $recurringExpense->due_date->format('d/m/Y') }}</p>
                        </div>

                        <div>
                            <span class="text-gray-500 font-medium">Cadastrado em:</span>
                            <p class="font-semibold text-gray-900 mt-0.5">🕒 {{ $recurringExpense->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    @if ($recurringExpense->notes)
                        <div class="pt-4 border-t border-gray-100">
                            <span class="text-gray-500 font-medium text-xs uppercase tracking-wider">Anotações:</span>
                            <p class="text-sm text-gray-700 mt-1 bg-gray-50 p-3 rounded-lg border border-gray-200">{{ $recurringExpense->notes }}</p>
                        </div>
                    @endif
                </div>

                <!-- Documento de Cobrança / Anexo (1 coluna) -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-4">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center">
                        <span class="p-1.5 bg-indigo-50 text-indigo-600 rounded-md mr-2 text-xs">📄</span>
                        Documento Anexo
                    </h3>

                    @if ($recurringExpense->billingDocument)
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 text-center space-y-3">
                            <div class="text-4xl">
                                {{ $recurringExpense->billingDocument->is_pdf ? '📕' : '🖼️' }}
                            </div>
                            <div>
                                <div class="font-semibold text-xs text-gray-900 truncate max-w-full" title="{{ $recurringExpense->billingDocument->original_name }}">
                                    {{ $recurringExpense->billingDocument->original_name }}
                                </div>
                                <div class="text-[11px] text-gray-500 mt-0.5">
                                    {{ $recurringExpense->billingDocument->formatted_size }} • {{ strtoupper(explode('/', $recurringExpense->billingDocument->mime_type)[1] ?? 'ARQUIVO') }}
                                </div>
                            </div>

                            <div class="pt-2 flex justify-center">
                                <a href="{{ route('attachments.download', $recurringExpense->billingDocument) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-semibold hover:bg-indigo-700 transition shadow-sm">
                                    📥 Baixar Documento
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                            <p class="text-xs text-gray-500">Nenhum documento de cobrança anexado.</p>
                            <a href="{{ route('recurring-expenses.edit', $recurringExpense) }}" class="inline-block mt-2 text-xs text-indigo-600 font-semibold hover:underline">
                                + Anexar Documento
                            </a>
                        </div>
                    @endif
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
