<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>GFP — Gestão Financeira Pessoal</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50 text-gray-900 min-h-screen flex flex-col justify-between">
        <!-- Navigation -->
        <header class="w-full bg-white border-b border-gray-100 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="font-bold text-2xl text-indigo-600 tracking-tight">GFP</span>
                    <span class="text-sm text-gray-500 font-medium hidden sm:inline">Gestão Financeira Pessoal</span>
                </div>

                @if (Route::has('login'))
                    <nav class="flex items-center space-x-4">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Ir para o Painel
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-indigo-600 transition">
                                Entrar
                            </a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Criar Conta
                                </a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </div>
        </header>

        <!-- Hero Section -->
        <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20 flex flex-col justify-center items-center text-center">
            <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100 mb-6">
                Controle Financeiro Simples & Seguro
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 tracking-tight max-w-4xl">
                Organize seus gastos pessoais com <span class="text-indigo-600">clareza e tranquilidade</span>.
            </h1>

            <p class="mt-6 text-lg sm:text-xl text-gray-600 max-w-2xl">
                Gerencie despesas avulsas e recorrentes, anexe boletos e comprovantes, acompanhe pagamentos e nunca mais perca uma data de vencimento.
            </p>

            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-6 py-3 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-indigo-700 shadow-md hover:shadow-lg transition">
                    Começar Gratuitamente
                </a>
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-6 py-3 bg-white border border-gray-300 rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-50 shadow-sm transition">
                    Já tenho uma conta
                </a>
            </div>

            <!-- Features Grid -->
            <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8 text-left w-full max-w-5xl">
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm hover:shadow transition">
                    <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center font-bold text-lg mb-4">
                        🔒
                    </div>
                    <h3 class="font-semibold text-lg text-gray-900 mb-2">Acesso Seguro</h3>
                    <p class="text-sm text-gray-600">Autenticação robusta com verificação de e-mail, proteção contra força bruta e isolamento completo dos seus dados financeiros.</p>
                </div>

                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm hover:shadow transition">
                    <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center font-bold text-lg mb-4">
                        📅
                    </div>
                    <h3 class="font-semibold text-lg text-gray-900 mb-2">Gastos Recorrentes</h3>
                    <p class="text-sm text-gray-600">Cadastre despesas periódicas (mensais, anuais ou personalizadas), anexe documentos de cobrança e registre comprovantes de pagamento.</p>
                </div>

                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm hover:shadow transition">
                    <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center font-bold text-lg mb-4">
                        📊
                    </div>
                    <h3 class="font-semibold text-lg text-gray-900 mb-2">Controle Total</h3>
                    <p class="text-sm text-gray-600">Categorias personalizadas, filtros por período e status, além de lembretes automáticos para contas próximas do vencimento.</p>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 py-6 text-center text-xs text-gray-500">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                &copy; {{ date('Y') }} GFP — Gestão Financeira Pessoal. Todos os direitos reservados.
            </div>
        </footer>
    </body>
</html>
