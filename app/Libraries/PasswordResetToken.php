<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Cryptographically secure opaque token (128 hex chars = 64 bytes) for password reset MVP.
 * Stored verbatim in DB with HTTPS-only transport assumption; cleared after use.
 */
final class PasswordResetToken
{
    public static function generate(): string
    {
        return bin2hex(random_bytes(64));
    }
}
