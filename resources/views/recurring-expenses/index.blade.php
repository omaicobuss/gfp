<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gastos Recorrentes (Modelos)
                </h2>
                <p class="text-xs text-gray-500 mt-1">Gerencie suas contas e despesas periódicas (aluguel, condomínio, assinaturas)</p>
            </div>

            <a href="{{ route('recurring-expenses.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition shadow-sm">
                ➕ Novo Gasto Recorrente
            </a>
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

            <!-- Cards Resumo -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Custo Mensal Estimado em Recorrentes</div>
                        <div class="text-2xl font-bold text-gray-900 mt-1">R$ {{ number_format($totalMonthlyExpected, 2, ',', '.') }}</div>
                        <div class="text-xs text-gray-400 mt-1">Baseado na periodicidade das contas ativas</div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl font-bold">
                        🔄
                    </div>
                </div>

                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Gastos Recorrentes Ativos</div>
                        <div class="text-2xl font-bold text-indigo-600 mt-1">{{ $activeCount }} {{ $activeCount === 1 ? 'modelo ativo' : 'modelos ativos' }}</div>
                        <div class="text-xs text-gray-400 mt-1">Total cadastrado: {{ $recurringExpenses->total() }}</div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-green-50 text-green-600 flex items-center justify-center text-2xl font-bold">
                        📋
                    </div>
                </div>
            </div>

            <!-- Filtros de Consulta -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-100">
                <form method="GET" action="{{ route('recurring-expenses.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                    <!-- Busca por Descrição -->
                    <div>
                        <x-input-label for="search" value="Buscar" />
                        <x-text-input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Ex: Aluguel, Netflix..." class="w-full text-sm mt-1" />
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

                    <!-- Frequência -->
                    <div>
                        <x-input-label for="frequency" value="Frequência" />
                        <select name="frequency" id="frequency" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                            <option value="">Todas</option>
                            <option value="weekly" {{ request('frequency') === 'weekly' ? 'selected' : '' }}>Semanal</option>
                            <option value="monthly" {{ request('frequency') === 'monthly' ? 'selected' : '' }}>Mensal</option>
                            <option value="yearly" {{ request('frequency') === 'yearly' ? 'selected' : '' }}>Anual</option>
                            <option value="custom" {{ request('frequency') === 'custom' ? 'selected' : '' }}>Personalizada</option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div>
                        <x-input-label for="status" value="Status" />
                        <select name="status" id="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                            <option value="">Todos</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Ativo</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Pausado</option>
                        </select>
                    </div>

                    <!-- Botões -->
                    <div class="flex items-center gap-2">
                        <button type="submit" class="p-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md shadow-sm transition" title="Filtrar">
                            🔍
                        </button>
                        @if(request()->hasAny(['search', 'category_id', 'frequency', 'status']))
                            <a href="{{ route('recurring-expenses.index') }}" class="p-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md shadow-sm transition" title="Limpar Filtros">
                                ❌
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Tabela de Gastos Recorrentes -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-200 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50/50">
                                    <th class="py-3 px-4">Descrição</th>
                                    <th class="py-3 px-4">Categoria</th>
                                    <th class="py-3 px-4 text-right">Valor Esperado</th>
                                    <th class="py-3 px-4">Frequência</th>
                                    <th class="py-3 px-4">Próx. Vencimento</th>
                                    <th class="py-3 px-4 text-center">Documento</th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                    <th class="py-3 px-4 text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                @forelse ($recurringExpenses as $item)
                                    <tr class="hover:bg-gray-50/75 transition {{ ! $item->is_active ? 'opacity-60 bg-gray-50/30' : '' }}">
                                        <!-- Descrição -->
                                        <td class="py-3.5 px-4">
                                            <a href="{{ route('recurring-expenses.show', $item) }}" class="font-semibold text-gray-900 hover:text-indigo-600 transition">
                                                {{ $item->description }}
                                            </a>
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

                                        <!-- Valor Esperado -->
                                        <td class="py-3.5 px-4 text-right font-bold text-gray-900 whitespace-nowrap">
                                            {{ $item->formatted_expected_amount }}
                                        </td>

                                        <!-- Frequência -->
                                        <td class="py-3.5 px-4 text-xs font-medium text-gray-600 whitespace-nowrap">
                                            {{ $item->frequency_label }}
                                        </td>

                                        <!-- Próximo Vencimento -->
                                        <td class="py-3.5 px-4 text-xs font-medium text-gray-900 whitespace-nowrap">
                                            {{ $item->due_date->format('d/m/Y') }}
                                        </td>

                                        <!-- Documento Anexo (FR-016) -->
                                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                            @if ($item->billingDocument)
                                                <a href="{{ route('attachments.download', $item->billingDocument) }}" class="inline-flex items-center px-2 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded text-xs font-semibold transition" title="Baixar {{ $item->billingDocument->original_name }} ({{ $item->billingDocument->formatted_size }})">
                                                    📄 Anexo ({{ $item->billingDocument->formatted_size }})
                                                </a>
                                            @else
                                                <span class="text-xs text-gray-400 italic">—</span>
                                            @endif
                                        </td>

                                        <!-- Status (Ativo / Pausado) -->
                                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                            <form method="POST" action="{{ route('recurring-expenses.toggle-active', $item) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border transition {{ $item->is_active ? 'bg-green-100 text-green-800 border-green-200 hover:bg-green-200' : 'bg-gray-100 text-gray-600 border-gray-200 hover:bg-gray-200' }}" title="Clique para {{ $item->is_active ? 'pausar' : 'reativar' }}">
                                                    {{ $item->is_active ? '🟢 Ativo' : '⚪ Pausado' }}
                                                </button>
                                            </form>
                                        </td>

                                        <!-- Ações -->
                                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                            <div class="inline-flex items-center space-x-1">
                                                <a href="{{ route('recurring-expenses.show', $item) }}" class="p-1.5 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-md transition" title="Ver detalhes">
                                                    👁️
                                                </a>

                                                <a href="{{ route('recurring-expenses.edit', $item) }}" class="p-1.5 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-md transition" title="Editar modelo">
                                                    ✏️
                                                </a>

                                                <form action="{{ route('recurring-expenses.destroy', $item) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir o gasto recorrente \'{{ addslashes($item->description) }}\'?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-md transition" title="Excluir modelo">
                                                        🗑️
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="py-10 text-center text-sm text-gray-500">
                                            <div class="flex flex-col items-center justify-center">
                                                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center text-xl mb-3">
                                                    🔄
                                                </div>
                                                <p class="font-medium text-gray-700">Nenhum gasto recorrente cadastrado.</p>
                                                <p class="text-xs text-gray-500 mt-1">Crie modelos para aluguel, condomínio, internet e assinaturas mensais.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação -->
                    <div class="mt-6">
                        {{ $recurringExpenses->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
