<?php

namespace App\Libraries;

/**
 * Selectable white-label storefront colour themes.
 *
 * Each theme is a full palette. The authoritative render lives in the
 * storefront stylesheet as a `.sf-theme-{key}` class (applied to <body> by the
 * tenant header, so it flows through every storefront AND checkout page). This
 * registry is the single source of truth for the *set* of themes — their keys
 * (validation), labels, and the handful of representative colours the vendor's
 * "My site" editor needs to draw a swatch and live preview. Keeping the full
 * palette in CSS and only preview colours here avoids duplicating every value.
 */
class StorefrontThemes
{
    public const DEFAULT = 'clean';

    /**
     * key => [label, accent (swatch/CTA), bg, ink, border]. Colours mirror the
     * CSS palette (oklch, as authored in the design) for the editor preview.
     *
     * @return array<string, array{label: string, accent: string, bg: string, ink: string, border: string}>
     */
    public static function all(): array
    {
        return [
            'clean' => [
                'label' => 'Clean modern', 'accent' => 'oklch(0.52 0.17 266)',
                'bg' => 'oklch(0.99 0.002 255)', 'ink' => 'oklch(0.21 0.02 265)', 'border' => 'oklch(0.90 0.006 265)',
            ],
            'warm' => [
                'label' => 'Warm editorial', 'accent' => 'oklch(0.58 0.12 45)',
                'bg' => 'oklch(0.955 0.014 75)', 'ink' => 'oklch(0.26 0.02 55)', 'border' => 'oklch(0.87 0.014 75)',
            ],
            'porcelain' => [
                'label' => 'Porcelain & ink', 'accent' => 'oklch(0.31 0.05 258)',
                'bg' => 'oklch(0.975 0.004 250)', 'ink' => 'oklch(0.22 0.015 260)', 'border' => 'oklch(0.91 0.005 255)',
            ],
            'graphite' => [
                'label' => 'Graphite', 'accent' => 'oklch(0.34 0.006 260)',
                'bg' => 'oklch(0.968 0.001 260)', 'ink' => 'oklch(0.24 0.004 260)', 'border' => 'oklch(0.90 0.002 260)',
            ],
            'teal' => [
                'label' => 'Teal', 'accent' => 'oklch(0.52 0.09 215)',
                'bg' => 'oklch(0.975 0.004 250)', 'ink' => 'oklch(0.22 0.015 240)', 'border' => 'oklch(0.91 0.006 220)',
            ],
            'indigo' => [
                'label' => 'Indigo', 'accent' => 'oklch(0.52 0.17 266)',
                'bg' => 'oklch(0.975 0.004 255)', 'ink' => 'oklch(0.22 0.015 265)', 'border' => 'oklch(0.91 0.006 265)',
            ],
        ];
    }

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function isValid(?string $key): bool
    {
        return $key !== null && isset(self::all()[$key]);
    }

    /**
     * A stored value → a known theme key, falling back to the default so the
     * body class and injected palette are always safe.
     */
    public static function resolve(?string $key): string
    {
        return self::isValid($key) ? (string) $key : self::DEFAULT;
    }
}
