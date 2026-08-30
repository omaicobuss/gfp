<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        Não recebeu o link de ativação da sua conta ou ele expirou? Informe seu e-mail abaixo para enviarmos um novo link de confirmação.
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('verification.resend') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                Voltar para o Login
            </a>

            <x-primary-button class="ms-3">
                Reenviar E-mail
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
