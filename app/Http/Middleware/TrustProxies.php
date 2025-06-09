<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request; // Importante: asegúrate de que Request se importe correctamente

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * Para desarrollo local con ngrok o proxies similares, puedes usar '*'.
     * ¡PRECAUCIÓN! En producción, debes configurar esto con las IPs/CIDRs
     * específicas de tus balanceadores de carga o proxies inversos.
     *
     * @var array|string|null
     */
    protected $proxies = '*'; // Confía en todos los proxies para desarrollo con ngrok

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO | // Esta es la cabecera clave para el esquema (http/https)
        Request::HEADER_X_FORWARDED_AWS_ELB; // Puedes mantener esta o ajustarla según tus necesidades
                                            // HEADER_X_FORWARDED_PREFIX también existe para prefijos de ruta
}