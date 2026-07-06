<?php

namespace App\Libraries;

/**
 * White-label host parsing: decides whether the current request is on a
 * vendor tenant subdomain (`<slug>.<tenant.baseDomain>`) or on the main
 * marketplace host.
 *
 * The base domain comes from the `tenant.baseDomain` env var
 * (partysmith.co.uk in production, partyplanner.test for local dev).
 * Any host that is NOT a subdomain of the base domain — including the base
 * domain itself, www, and unrelated hosts like partyplanner.home — is
 * treated as the marketplace, so marketplace behaviour is untouched.
 */
final class TenantHost
{
    public const DEFAULT_BASE_DOMAIN = 'partysmith.co.uk';

    public static function baseDomain(): string
    {
        $base = env('tenant.baseDomain', self::DEFAULT_BASE_DOMAIN);

        return is_string($base) && trim($base) !== '' ? strtolower(trim($base)) : self::DEFAULT_BASE_DOMAIN;
    }

    /**
     * Tenant subdomain for the current request, or null on the marketplace.
     * Read from $_SERVER directly so it is usable both in Routes.php (before
     * the request object exists) and in the VendorTenant filter.
     */
    public static function current(): ?string
    {
        return self::subdomainFromHost((string) ($_SERVER['HTTP_HOST'] ?? ''), self::baseDomain());
    }

    /**
     * Extract the tenant subdomain from a Host header value, or null when the
     * host is the marketplace (base domain, www, empty, or unrelated domain).
     *
     * A multi-label remainder (a.b.partysmith.co.uk → "a.b") is returned
     * as-is: it can never match a stored single-label slug, so tenant
     * resolution 404s — fail closed rather than guessing.
     */
    public static function subdomainFromHost(string $host, string $baseDomain): ?string
    {
        $host       = strtolower(trim($host));
        $baseDomain = strtolower(trim($baseDomain));

        // Strip a :port suffix (but leave bare IPv6 hosts alone).
        $colon = strrpos($host, ':');
        if ($colon !== false && ctype_digit(substr($host, $colon + 1))) {
            $host = substr($host, 0, $colon);
        }
        $host = rtrim($host, '.');

        if ($host === '' || $baseDomain === '') {
            return null;
        }
        if ($host === $baseDomain || $host === 'www.' . $baseDomain) {
            return null;
        }
        $suffix = '.' . $baseDomain;
        if (! str_ends_with($host, $suffix)) {
            return null;
        }

        $subdomain = substr($host, 0, -strlen($suffix));
        if ($subdomain === '' || $subdomain === 'www') {
            return null;
        }

        return $subdomain;
    }
}
