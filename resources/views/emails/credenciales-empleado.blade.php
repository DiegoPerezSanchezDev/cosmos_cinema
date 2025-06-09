@component('mail::message')
# ¡Bienvenido/a a Cosmos, {{ $nombre }}!

Aquí tienes tus credenciales de acceso:

**Correo Electrónico:** {{ $emailEmpleado }} <br>
**Contraseña:** {{ $password }} <br>

@if($esAdmin)
**Usuario Administrador:** {{ $nombreAdminUsuario }} <br>
**Código de Administrador:** {{ $codigoAdmin }}
@endif

Por favor, guarda esta información en un lugar seguro.

Gracias,
El equipo de Cosmos
@endcomponent