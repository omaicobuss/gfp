<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Painel Financeiro
                </h2>
                <p class="text-xs text-gray-500 mt-1">Visão geral das suas despesas no mês de {{ \Carbon\Carbon::now()->translatedFormat('F \d\e Y') }}</p>
            </div>

            <div class="flex items-center space-x-3">
                <a href="{{ route('categories.index') }}" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-50 shadow-sm transition">
                    🏷️ Categorias
                </a>
                <a href="{{ route('expenses.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 shadow-sm transition">
                    ➕ Novo Gasto
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Cards com Métricas do Mês -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total do Mês -->
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between hover:shadow transition">
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total do Mês</div>
                        <div class="text-2xl font-extrabold text-gray-900 mt-1">R$ {{ number_format($monthlyTotal, 2, ',', '.') }}</div>
                        <div class="text-xs text-gray-400 mt-1">{{ $monthlyCount }} {{ $monthlyCount === 1 ? 'lançamento' : 'lançamentos' }}</div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl">
                        💳
                    </div>
                </div>

                <!-- Pago no Mês -->
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between hover:shadow transition">
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Pago</div>
                        <div class="text-2xl font-extrabold text-green-600 mt-1">R$ {{ number_format($monthlyPaid, 2, ',', '.') }}</div>
                        <div class="text-xs text-green-600 font-medium mt-1">Contas liquidadas</div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-green-50 text-green-600 flex items-center justify-center text-2xl">
                        ✅
                    </div>
                </div>

                <!-- Pendente -->
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between hover:shadow transition">
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pendente</div>
                        <div class="text-2xl font-extrabold text-amber-600 mt-1">R$ {{ number_format($monthlyPending, 2, ',', '.') }}</div>
                        <div class="text-xs text-amber-600 font-medium mt-1">A vencer no mês</div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl">
                        ⏳
                    </div>
                </div>

                <!-- Atrasado -->
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between hover:shadow transition">
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Atrasado</div>
                        <div class="text-2xl font-extrabold text-red-600 mt-1">R$ {{ number_format($monthlyOverdue, 2, ',', '.') }}</div>
                        <div class="text-xs text-red-600 font-medium mt-1">Vencimento expirado</div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-2xl">
                        🚨
                    </div>
                </div>
            </div>

            <!-- Grid Principal: Últimos Gastos + Categorias -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Últimos Gastos (2/3 da tela) -->
                <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-bold text-gray-900 flex items-center">
                            <span class="p-1.5 bg-indigo-50 text-indigo-600 rounded-md mr-2 text-sm">📋</span>
                            Últimos Lançamentos
                        </h3>
                        <a href="{{ route('expenses.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition">
                            Ver todos os gastos →
                        </a>
                    </div>

                    @if ($recentExpenses->isEmpty())
                        <div class="text-center py-12 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                            <div class="text-3xl mb-2">💸</div>
                            <p class="text-sm font-medium text-gray-700">Nenhum gasto registrado ainda.</p>
                            <p class="text-xs text-gray-500 mt-1 mb-4">Cadastre sua primeira despesa para começar a acompanhar suas finanças.</p>
                            <a href="{{ route('expenses.create') }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white rounded-md text-xs font-semibold hover:bg-indigo-700 transition">
                                + Cadastrar Primeiro Gasto
                            </a>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-100 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">
                                        <th class="py-2.5 px-3">Data</th>
                                        <th class="py-2.5 px-3">Descrição</th>
                                        <th class="py-2.5 px-3">Categoria</th>
                                        <th class="py-2.5 px-3 text-right">Valor</th>
                                        <th class="py-2.5 px-3 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 text-sm">
                                    @foreach ($recentExpenses as $expense)
                                        <tr class="hover:bg-gray-50/75 transition">
                                            <td class="py-3 px-3 text-xs text-gray-500 whitespace-nowrap">
                                                {{ $expense->date->format('d/m/Y') }}
                                            </td>
                                            <td class="py-3 px-3 font-medium text-gray-900">
                                                {{ $expense->description }}
                                            </td>
                                            <td class="py-3 px-3">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-700">
                                                    {{ $expense->category->name }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-3 text-right font-semibold text-gray-900 whitespace-nowrap">
                                                {{ $expense->formatted_amount }}
                                            </td>
                                            <td class="py-3 px-3 text-center">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border {{ $expense->status_badge_class }}">
                                                    {{ $expense->status_label }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- Gastos por Categoria (1/3 da tela) -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-bold text-gray-900 flex items-center">
                            <span class="p-1.5 bg-indigo-50 text-indigo-600 rounded-md mr-2 text-sm">🏷️</span>
                            Por Categoria (Mês)
                        </h3>
                    </div>

                    @if ($categoryBreakdown->isEmpty())
                        <div class="text-center py-10 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                            <p class="text-xs text-gray-500">Nenhum gasto registrado neste mês.</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach ($categoryBreakdown as $cat)
                                @php
                                    $percentage = $monthlyTotal > 0 ? round(($cat->total_amount / $monthlyTotal) * 100) : 0;
                                @endphp
                                <div>
                                    <div class="flex justify-between text-xs font-medium mb-1">
                                        <span class="text-gray-700">{{ $cat->category_name }}</span>
                                        <span class="text-gray-900 font-bold">R$ {{ number_format($cat->total_amount, 2, ',', '.') }} ({{ $percentage }}%)</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                        <div class="bg-indigo-600 h-2 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
