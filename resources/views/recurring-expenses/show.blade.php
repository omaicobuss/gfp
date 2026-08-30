<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $recurringExpense->description }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">Detalhes do modelo de gasto recorrente e histórico de vencimentos</p>
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

            <!-- Histórico de Vencimentos e Ocorrências (FR-017, FR-020) -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-4">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 flex items-center">
                            <span class="p-1.5 bg-indigo-50 text-indigo-600 rounded-md mr-2 text-sm">📅</span>
                            Vencimentos e Ocorrências Geradas
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">Histórico de faturas geradas e registro de pagamentos realizados</p>
                    </div>

                    <form action="{{ route('recurring-expenses.generate', $recurringExpense) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold transition">
                            🔄 Gerar Próximos Vencimentos
                        </button>
                    </form>
                </div>

                @if ($recurringExpense->occurrences->isEmpty())
                    <div class="text-center py-10 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                        <p class="text-sm text-gray-500">Nenhuma ocorrência gerada ainda.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-200 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50/50">
                                    <th class="py-3 px-4">Vencimento</th>
                                    <th class="py-3 px-4 text-right">Valor Esperado</th>
                                    <th class="py-3 px-4 text-right">Valor Pago</th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                    <th class="py-3 px-4 text-center">Comprovante</th>
                                    <th class="py-3 px-4 text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                @foreach ($recurringExpense->occurrences as $occ)
                                    <tr class="hover:bg-gray-50/75 transition">
                                        <!-- Vencimento -->
                                        <td class="py-3.5 px-4 font-medium text-gray-900 whitespace-nowrap">
                                            {{ $occ->due_date->format('d/m/Y') }}
                                            @if ($occ->paid_at)
                                                <div class="text-[11px] text-green-600">Pago em {{ $occ->paid_at->format('d/m/Y') }}</div>
                                            @endif
                                        </td>

                                        <!-- Valor Esperado -->
                                        <td class="py-3.5 px-4 text-right text-gray-600 whitespace-nowrap">
                                            {{ $occ->formatted_expected_amount }}
                                        </td>

                                        <!-- Valor Pago -->
                                        <td class="py-3.5 px-4 text-right font-bold text-gray-900 whitespace-nowrap">
                                            {{ $occ->formatted_actual_amount ?? '—' }}
                                        </td>

                                        <!-- Status -->
                                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $occ->status_badge_class }}">
                                                {{ $occ->status_label }}
                                            </span>
                                        </td>

                                        <!-- Comprovante Anexo (FR-018) -->
                                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                            @if ($occ->paymentReceipt)
                                                <a href="{{ route('attachments.download', $occ->paymentReceipt) }}" class="inline-flex items-center px-2 py-1 bg-green-50 hover:bg-green-100 text-green-700 rounded text-xs font-semibold transition" title="Baixar comprovante">
                                                    🧾 Recibo
                                                </a>
                                            @else
                                                <span class="text-xs text-gray-400 italic">—</span>
                                            @endif
                                        </td>

                                        <!-- Ações -->
                                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                            <div class="inline-flex items-center space-x-1">
                                                @if (! $occ->is_paid)
                                                    <a href="{{ route('occurrences.pay', $occ) }}" class="px-2.5 py-1 bg-green-600 hover:bg-green-700 text-white rounded text-xs font-semibold shadow-sm transition">
                                                        Pagar
                                                    </a>
                                                @else
                                                    <form method="POST" action="{{ route('occurrences.unpay', $occ) }}" onsubmit="return confirm('Deseja desmarcar o pagamento desta ocorrência?');">
                                                        @csrf
                                                        <button type="submit" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded text-xs font-medium transition" title="Desmarcar pagamento">
                                                            Desmarcar
                                                        </button>
                                                    </form>
                                                @endif

                                                <a href="{{ route('occurrences.edit', $occ) }}" class="p-1.5 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-md transition" title="Editar ocorrência">
                                                    ✏️
                                                </a>

                                                <form action="{{ route('occurrences.destroy', $occ) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta ocorrência?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-md transition" title="Excluir ocorrência">
                                                        🗑️
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
