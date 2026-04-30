<?php

declare(strict_types=1);

namespace App\Security;

final class SessionCookieConfig
{
    public static function apply(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => getenv('SESSION_COOKIE_DOMAIN') ?: '.stark.a3n.fr',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'None',
        ]);
    }
}
