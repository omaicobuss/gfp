<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        Obrigado por se cadastrar! Antes de começar, por favor confirme seu endereço de e-mail clicando no link de ativação que enviamos para você. Se você não recebeu o e-mail, teremos prazer em enviar outro.
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-3 rounded-md border border-green-200">
            Um novo link de confirmação foi enviado para o seu endereço de e-mail.
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    Reenviar E-mail de Confirmação
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Sair
            </button>
        </form>
    </div>
</x-guest-layout>
