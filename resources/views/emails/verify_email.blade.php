<p>Hola {{ $user->nombre }},</p>
<p>Gracias por registrarte. Por favor, verifica tu correo electrónico haciendo clic en el siguiente enlace:</p>
<a href="{{ $verificationUrl }}">{{ $verificationUrl }}</a>
<p>Si no te registraste, ignora este correo.</p>