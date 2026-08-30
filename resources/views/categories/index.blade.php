<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Minhas Categorias
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
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

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Nova Categoria Form Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                        <span class="p-1.5 bg-indigo-50 text-indigo-600 rounded-md mr-2">➕</span>
                        Nova Categoria
                    </h3>

                    <form action="{{ route('categories.store') }}" method="POST">
                        @csrf

                        <div>
                            <x-input-label for="name" value="Nome da Categoria" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" placeholder="Ex: Alimentação, Lazer, Moradia" :value="old('name')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div class="mt-6 flex justify-end">
                            <x-primary-button>
                                Salvar Categoria
                            </x-primary-button>
                        </div>
                    </form>
                </div>

                <!-- Lista de Categorias Card -->
                <div class="md:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center justify-between">
                        <span class="flex items-center">
                            <span class="p-1.5 bg-indigo-50 text-indigo-600 rounded-md mr-2">🏷️</span>
                            Categorias Cadastradas
                        </span>
                        <span class="text-xs font-semibold px-2.5 py-1 bg-gray-100 text-gray-600 rounded-full">
                            {{ $categories->count() }} {{ $categories->count() === 1 ? 'categoria' : 'categorias' }}
                        </span>
                    </h3>

                    @if ($categories->isEmpty())
                        <div class="text-center py-10 bg-gray-50 rounded-lg border border-dashed border-gray-200">
                            <p class="text-sm text-gray-500">Nenhuma categoria cadastrada até o momento.</p>
                        </div>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach ($categories as $category)
                                <div x-data="{ editing: false, name: '{{ addslashes($category->name) }}' }" class="py-3.5 flex items-center justify-between hover:bg-gray-50/50 px-2 rounded-md transition">
                                    <!-- Modo Visualização -->
                                    <div x-show="!editing" class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs">
                                            {{ strtoupper(substr($category->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-900">{{ $category->name }}</span>
                                            @if($category->name === 'Outros')
                                                <span class="ml-2 text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Padrão</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Modo Edição Inline -->
                                    <form x-show="editing" x-cloak action="{{ route('categories.update', $category) }}" method="POST" class="flex items-center space-x-2 flex-1 mr-4">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="name" x-model="name" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm w-full py-1 px-2" required maxlength="100">
                                        <button type="submit" class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-md shadow-sm transition">
                                            Salvar
                                        </button>
                                        <button type="button" @click="editing = false" class="px-3 py-1 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-semibold rounded-md transition">
                                            Cancelar
                                        </button>
                                    </form>

                                    <!-- Ações -->
                                    <div x-show="!editing" class="flex items-center space-x-2">
                                        <button @click="editing = true" class="p-1.5 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-md transition" title="Editar categoria">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                            </svg>
                                        </button>

                                        <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir a categoria \'{{ addslashes($category->name) }}\'?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-md transition" title="Excluir categoria">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
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
