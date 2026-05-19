<?php

namespace App\Libraries;

use Config\App;

/**
 * Aligns Config\App::$baseURL with the incoming HTTP host when they differ.
 *
 * Prevents header/footer links from pointing at a stale hostname (e.g. partyplanner.test)
 * while the app is served on localhost:8888 or a cloud preview URL.
 */
class AppBaseUrl
{
    /**
     * Whether the configured base URL host should be replaced for this request host.
     *
     * @param list<string> $allowedHostnames
     */
    public static function shouldSyncHost(string $configuredBaseUrl, string $requestHost, array $allowedHostnames = []): bool
    {
        if ($requestHost === '') {
            return false;
        }

        $configuredHost = parse_url($configuredBaseUrl, PHP_URL_HOST);
        if (! is_string($configuredHost) || $configuredHost === '' || strcasecmp($configuredHost, $requestHost) === 0) {
            return false;
        }

        if ($allowedHostnames !== [] && ! in_array($requestHost, $allowedHostnames, true)) {
            return false;
        }

        return true;
    }

    public static function syncFromRequest(): void
    {
        if (is_cli() || ENVIRONMENT === 'testing') {
            return;
        }

        $host = $_SERVER['HTTP_HOST'] ?? '';
        if ($host === '') {
            return;
        }

        $config = config(App::class);
        if (! self::shouldSyncHost($config->baseURL, $host, $config->allowedHostnames)) {
            return;
        }

        $scheme = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $config->baseURL = $scheme . '://' . $host . '/';
    }
}
