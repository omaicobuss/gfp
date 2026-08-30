<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gastos e Despesas (Visão Consolidada)
                </h2>
                <p class="text-xs text-gray-500 mt-1">Acompanhe todos os seus gastos avulsos e vencimentos recorrentes em um só lugar</p>
            </div>

            <div class="flex items-center space-x-3">
                <a href="{{ route('recurring-expenses.create') }}" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-50 shadow-sm transition">
                    🔄 Novo Recorrente
                </a>

                <a href="{{ route('expenses.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 shadow-sm transition">
                    ➕ Novo Gasto Avulso
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Alerts -->
            @if (session('status'))
                <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm flex items-center shadow-sm">
                    <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm flex items-center shadow-sm">
                    <svg class="w-5 h-5 mr-2 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            <!-- Cards de Resumo dos Filtros Atuais -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Consolidado</div>
                        <div class="text-2xl font-bold text-gray-900 mt-1">R$ {{ number_format($totalAmount, 2, ',', '.') }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold">
                        💳
                    </div>
                </div>

                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Pago</div>
                        <div class="text-2xl font-bold text-green-600 mt-1">R$ {{ number_format($paidAmount, 2, ',', '.') }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-green-50 text-green-600 flex items-center justify-center text-xl font-bold">
                        ✅
                    </div>
                </div>

                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Pendente</div>
                        <div class="text-2xl font-bold text-amber-600 mt-1">R$ {{ number_format($pendingAmount, 2, ',', '.') }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                        ⏳
                    </div>
                </div>

                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Atrasado</div>
                        <div class="text-2xl font-bold text-red-600 mt-1">R$ {{ number_format($overdueAmount, 2, ',', '.') }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-red-50 text-red-600 flex items-center justify-center text-xl font-bold">
                        🚨
                    </div>
                </div>
            </div>

            <!-- Filtros de Consulta Consolidada (FR-030) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-100">
                <form method="GET" action="{{ route('expenses.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
                    <!-- Busca por Descrição -->
                    <div>
                        <x-input-label for="search" value="Buscar" />
                        <x-text-input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Ex: Mercado, Aluguel..." class="w-full text-sm mt-1" />
                    </div>

                    <!-- Tipo de Gasto -->
                    <div>
                        <x-input-label for="type" value="Tipo de Gasto" />
                        <select name="type" id="type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                            <option value="all" {{ request('type', 'all') === 'all' ? 'selected' : '' }}>Todos (Consolidado)</option>
                            <option value="single" {{ request('type') === 'single' ? 'selected' : '' }}>🔹 Gastos Avulsos</option>
                            <option value="recurring" {{ request('type') === 'recurring' ? 'selected' : '' }}>🔄 Recorrentes</option>
                        </select>
                    </div>

                    <!-- Período: De -->
                    <div>
                        <x-input-label for="start_date" value="Data Inicial" />
                        <x-text-input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="w-full text-sm mt-1" />
                    </div>

                    <!-- Período: Até -->
                    <div>
                        <x-input-label for="end_date" value="Data Final" />
                        <x-text-input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="w-full text-sm mt-1" />
                    </div>

                    <!-- Categoria -->
                    <div>
                        <x-input-label for="category_id" value="Categoria" />
                        <select name="category_id" id="category_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                            <option value="">Todas as Categorias</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status + Botões -->
                    <div class="flex gap-2">
                        <div class="flex-1">
                            <x-input-label for="status" value="Status" />
                            <select name="status" id="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                <option value="">Todos</option>
                                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Pago</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendente</option>
                                <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Atrasado</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-1 pt-6">
                            <button type="submit" class="p-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md shadow-sm transition" title="Aplicar Filtros">
                                🔍
                            </button>
                            @if(request()->hasAny(['search', 'type', 'start_date', 'end_date', 'category_id', 'status']))
                                <a href="{{ route('expenses.index') }}" class="p-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md shadow-sm transition" title="Limpar Filtros">
                                    ❌
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tabela Consolidada de Gastos (FR-029) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-200 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50/50">
                                    <th class="py-3 px-4">Tipo</th>
                                    <th class="py-3 px-4">Data / Venc.</th>
                                    <th class="py-3 px-4">Descrição</th>
                                    <th class="py-3 px-4">Categoria</th>
                                    <th class="py-3 px-4 text-right">Valor</th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                    <th class="py-3 px-4 text-center">Anexos</th>
                                    <th class="py-3 px-4 text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                @forelse ($expenses as $item)
                                    <tr class="hover:bg-gray-50/75 transition">
                                        <!-- Tipo (Avulso vs Recorrente) -->
                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            @if ($item->is_recurring)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-purple-100 text-purple-800" title="Gasto Recorrente">
                                                    🔄 Recorrente
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800" title="Gasto Avulso / Não Recorrente">
                                                    🔹 Avulso
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Data -->
                                        <td class="py-3.5 px-4 font-medium text-gray-900 whitespace-nowrap">
                                            {{ $item->item_date->format('d/m/Y') }}
                                            @if ($item->paid_at && $item->status === 'paid')
                                                <div class="text-[10px] text-green-600">Pago: {{ $item->paid_at->format('d/m/Y') }}</div>
                                            @endif
                                        </td>

                                        <!-- Descrição -->
                                        <td class="py-3.5 px-4">
                                            <div class="font-semibold text-gray-900">{{ $item->description }}</div>
                                            @if ($item->notes)
                                                <div class="text-xs text-gray-500 truncate max-w-xs">{{ $item->notes }}</div>
                                            @endif
                                        </td>

                                        <!-- Categoria -->
                                        <td class="py-3.5 px-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                                🏷️ {{ $item->category->name }}
                                            </span>
                                        </td>

                                        <!-- Valor -->
                                        <td class="py-3.5 px-4 text-right font-bold text-gray-900 whitespace-nowrap">
                                            {{ $item->formatted_amount }}
                                        </td>

                                        <!-- Status -->
                                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $item->status_badge_class }}">
                                                {{ $item->status_label }}
                                            </span>
                                        </td>

                                        <!-- Anexos (Documento / Comprovante) -->
                                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                            <div class="inline-flex items-center space-x-1">
                                                @if ($item->is_recurring)
                                                    @if (isset($item->recurringExpense) && $item->recurringExpense->billingDocument)
                                                        <a href="{{ route('attachments.download', $item->recurringExpense->billingDocument) }}" class="p-1 bg-indigo-50 text-indigo-700 rounded text-xs hover:bg-indigo-100 transition" title="Baixar Boleto Padrão">
                                                            📄 Boleto
                                                        </a>
                                                    @endif

                                                    @if ($item->paymentReceipt)
                                                        <a href="{{ route('attachments.download', $item->paymentReceipt) }}" class="p-1 bg-green-50 text-green-700 rounded text-xs hover:bg-green-100 transition" title="Baixar Comprovante de Pagamento">
                                                            🧾 Recibo
                                                        </a>
                                                    @endif

                                                    @if ((!isset($item->recurringExpense) || !$item->recurringExpense->billingDocument) && !$item->paymentReceipt)
                                                        <span class="text-xs text-gray-400 italic">—</span>
                                                    @endif
                                                @else
                                                    <span class="text-xs text-gray-400 italic">—</span>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Ações -->
                                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                            <div class="inline-flex items-center space-x-1">
                                                @if ($item->is_recurring)
                                                    <!-- Ações para Ocorrência Recorrente -->
                                                    @if (! $item->is_paid)
                                                        <a href="{{ route('occurrences.pay', $item) }}" class="px-2.5 py-1 bg-green-600 hover:bg-green-700 text-white rounded text-xs font-semibold shadow-sm transition" title="Registrar pagamento">
                                                            Pagar
                                                        </a>
                                                    @else
                                                        <form method="POST" action="{{ route('occurrences.unpay', $item) }}" onsubmit="return confirm('Deseja desmarcar o pagamento desta ocorrência?');">
                                                            @csrf
                                                            <button type="submit" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded text-xs font-medium transition" title="Desmarcar pagamento">
                                                                Desmarcar
                                                            </button>
                                                        </form>
                                                    @endif

                                                    <a href="{{ route('occurrences.edit', $item) }}" class="p-1.5 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-md transition" title="Editar ocorrência">
                                                        ✏️
                                                    </a>

                                                    <form action="{{ route('occurrences.destroy', $item) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta ocorrência de vencimento?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-md transition" title="Excluir ocorrência">
                                                            🗑️
                                                        </button>
                                                    </form>
                                                @else
                                                    <!-- Ações para Gasto Avulso -->
                                                    <a href="{{ route('expenses.edit', $item) }}" class="p-1.5 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-md transition" title="Editar gasto">
                                                        ✏️
                                                    </a>

                                                    <form action="{{ route('expenses.destroy', $item) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir o gasto \'{{ addslashes($item->description) }}\'?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-md transition" title="Excluir gasto">
                                                            🗑️
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="py-10 text-center text-sm text-gray-500">
                                            <div class="flex flex-col items-center justify-center">
                                                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center text-xl mb-3">
                                                    💸
                                                </div>
                                                <p class="font-medium text-gray-700">Nenhum gasto ou vencimento encontrado.</p>
                                                <p class="text-xs text-gray-500 mt-1">Utilize os botões acima para cadastrar gastos avulsos ou modelos recorrentes.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação -->
                    <div class="mt-6">
                        {{ $expenses->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
