<x-admin-layout>
    <x-label class="text-black text-xl font-semibold mb-4">
        Cambiar Contraseña - {{ $user->name }}
    </x-label>

    <x-validation-errors class="mb-4" />

    <form action="{{ route('admin.users.updatePassword', $user) }}" method="POST" class="bg-white p-6 rounded-lg shadow-md">
        @csrf
        @method('PUT') 
        <div class="mb-4"> 
 

            <x-label class="mb-2">
                Contraseña
            </x-label>
            <div class="relative mb-4">
                <x-input name="password" type="password" class="w-full" placeholder="Ingrese contraseña" id="password-field">
                </x-input>
                <!-- Botón para alternar visibilidad de la contraseña -->
                <button type="button" id="toggle-password-1" class="absolute top-1/2 right-3 transform -translate-y-1/2">
                    <i id="eye-icon-1" class="fa fa-eye items-center"></i>
                </button>
            </div>

            <x-label class="mb-2">
                Confirmar Contraseña
            </x-label>
            <div class="relative mb-4">
                <x-input name="password_confirmation" type="password" class="w-full" placeholder="Confirmar contraseña" id="password-confirmation-field">
                </x-input>
                <!-- Botón para alternar visibilidad de la confirmación de la contraseña -->
                <button type="button" id="toggle-password-2" class="absolute top-1/2 right-3 transform -translate-y-1/2">
                    <i id="eye-icon-2" class="fa fa-eye items-center"></i>
                </button>
            </div>

        </div>

        <div class="flex justify-end">
           

            <x-button>
                Guardar
            </x-button>
        </div>
 
 

        
    </form>

        <!-- Script para alternar visibilidad -->
    <script>
        // Para el campo de la contraseña
        const togglePassword1 = document.getElementById('toggle-password-1');
        const passwordField1 = document.getElementById('password-field');
        const eyeIcon1 = document.getElementById('eye-icon-1');

        togglePassword1.addEventListener('click', function () {
            // Alternar el tipo de la contraseña entre 'password' y 'text'
            const type = passwordField1.type === 'password' ? 'text' : 'password';
            passwordField1.type = type;

            // Alternar el icono entre 'ojo abierto' y 'ojo cerrado'
            eyeIcon1.classList.toggle('fa-eye');
            eyeIcon1.classList.toggle('fa-eye-slash');
        });

        // Para el campo de la confirmación de la contraseña
        const togglePassword2 = document.getElementById('toggle-password-2');
        const passwordField2 = document.getElementById('password-confirmation-field');
        const eyeIcon2 = document.getElementById('eye-icon-2');

        togglePassword2.addEventListener('click', function () {
            // Alternar el tipo de la contraseña entre 'password' y 'text'
            const type = passwordField2.type === 'password' ? 'text' : 'password';
            passwordField2.type = type;

            // Alternar el icono entre 'ojo abierto' y 'ojo cerrado'
            eyeIcon2.classList.toggle('fa-eye');
            eyeIcon2.classList.toggle('fa-eye-slash');
        });
    </script>


</x-admin-layout>
