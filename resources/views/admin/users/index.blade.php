<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Painel Administrativo — Gerenciamento de Usuários
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Alert Messages -->
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

            <!-- Filtros e Busca -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-100">
                <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <x-input-label for="search" value="Buscar por nome ou e-mail" />
                        <div class="relative mt-1">
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Digite o nome ou e-mail..." class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm pl-10">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                🔍
                            </div>
                        </div>
                    </div>

                    <div>
                        <x-input-label for="status" value="Filtrar por Status / Papel" />
                        <select name="status" id="status" onchange="this.form.submit()" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                            <option value="">Todos os Usuários</option>
                            <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verificados (Ativos)</option>
                            <option value="unverified" {{ request('status') === 'unverified' ? 'selected' : '' }}>Não Verificados</option>
                            <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Bloqueados</option>
                            <option value="admin" {{ request('status') === 'admin' ? 'selected' : '' }}>Apenas Administradores</option>
                        </select>
                    </div>
                </form>
            </div>

            <!-- Tabela de Usuários -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-200 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50/50">
                                    <th class="py-3 px-4">Usuário</th>
                                    <th class="py-3 px-4">Papel</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4">Cadastro</th>
                                    <th class="py-3 px-4 text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                @forelse ($users as $user)
                                    <tr class="hover:bg-gray-50/75 transition {{ $user->isBlocked() ? 'bg-red-50/20' : '' }}">
                                        <!-- Usuário -->
                                        <td class="py-3.5 px-4">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 text-white flex items-center justify-center font-bold text-xs shadow-sm">
                                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <div class="font-medium text-gray-900 flex items-center">
                                                        {{ $user->name }}
                                                        @if (Auth::id() === $user->id)
                                                            <span class="ml-2 px-1.5 py-0.2 text-[10px] font-semibold bg-gray-100 text-gray-600 rounded">Você</span>
                                                        @endif
                                                    </div>
                                                    <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Papel -->
                                        <td class="py-3.5 px-4">
                                            @if ($user->isAdmin())
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                    👑 Administrador
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                                    Usuário
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Status -->
                                        <td class="py-3.5 px-4">
                                            @if ($user->isBlocked())
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                                    ⛔ Bloqueado
                                                </span>
                                            @elseif ($user->isVerified())
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                    ✅ Ativo / Verificado
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                                    ⏳ Não Verificado
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Data de Cadastro -->
                                        <td class="py-3.5 px-4 text-xs text-gray-500">
                                            {{ $user->created_at->format('d/m/Y H:i') }}
                                        </td>

                                        <!-- Ações -->
                                        <td class="py-3.5 px-4 text-right">
                                            <div class="inline-flex items-center space-x-2">
                                                @if (Auth::id() !== $user->id)
                                                    <!-- Bloquear / Desbloquear -->
                                                    <form method="POST" action="{{ route('admin.users.toggle-block', $user) }}" onsubmit="return confirm('Tem certeza que deseja {{ $user->isBlocked() ? 'desbloquear' : 'bloquear' }} o usuário \'{{ addslashes($user->name) }}\'?');">
                                                        @csrf
                                                        @method('PATCH')
                                                        @if ($user->isBlocked())
                                                            <button type="submit" class="px-2.5 py-1 text-xs font-medium text-green-700 bg-green-50 hover:bg-green-100 rounded-md border border-green-200 transition">
                                                                Desbloquear
                                                            </button>
                                                        @else
                                                            <button type="submit" class="px-2.5 py-1 text-xs font-medium text-amber-700 bg-amber-50 hover:bg-amber-100 rounded-md border border-amber-200 transition">
                                                                Bloquear
                                                            </button>
                                                        @endif
                                                    </form>

                                                    <!-- Excluir -->
                                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('ATENÇÃO: A exclusão de um usuário é definitiva e removerá todos os seus dados. Deseja continuar com a exclusão de \'{{ addslashes($user->name) }}\'?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="px-2.5 py-1 text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-md border border-red-200 transition">
                                                            Excluir
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-xs text-gray-400 italic">Sua Conta</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-sm text-gray-500">
                                            Nenhum usuário encontrado com os filtros selecionados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação -->
                    <div class="mt-6">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
