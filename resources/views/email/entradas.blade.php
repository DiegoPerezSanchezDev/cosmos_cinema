@component('mail::message')
# ¡Gracias por tu compra, {{ $nombreUsuario }}!

Tu compra en Cosmos Cinema ha sido confirmada.

{{-- TODO -> Descomentar cuando se tenga el nº de factura --}}
{{-- **Número de Factura:** {{ $numeroFactura }} --}}

Adjunto encontrarás tus entradas en formato PDF. Por favor, asegúrate de llevarlas contigo (impresas o en tu dispositivo móvil) para acceder a la función.

@component('mail::button', ['url' => $urlSitio])
Visita Nuestro Sitio Web
@endcomponent

¡Esperamos verte pronto!

Gracias,<br>
{{ config('app.name') }}
@endcomponent