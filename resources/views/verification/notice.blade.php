<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if (session('registerSuccess'))
                        <div class="mb-4 font-medium text-sm text-green-600">
                            {{ session('registerSuccess') }}
                        </div>
                    @endif
                    {{ __('¡Gracias por registrarte!') }}
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        {{ __('Se te ha enviado un enlace de verificación a tu dirección de correo electrónico.') }}
                        {{ __('Por favor, haz clic en el enlace para verificar tu cuenta y poder iniciar sesión.') }}
                    </div>
                </div>
            </div>
        </div>
    </x-app-layout>