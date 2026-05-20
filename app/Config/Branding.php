<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Homepage copy and A/B test variants.
 *
 * Set HERO_SUBTITLE_VARIANT in .env to `a`, `b`, or `c` (defaults to `a`).
 */
class Branding extends BaseConfig
{
    /** @var array<string, string> */
    public array $heroSubtitleOptions = [
        'a' => 'Plan your entire event in one organised place.',
        'b' => 'Keep every booking, message, quote, and supplier beautifully organised.',
        'c' => 'Discover, compare, and book trusted event suppliers with confidence.',
    ];

    /**
     * Active subtitle variant key (`a`, `b`, or `c`).
     */
    public string $heroSubtitleVariant = 'a';

    public function __construct()
    {
        parent::__construct();

        $envVariant = env('HERO_SUBTITLE_VARIANT');
        if (is_string($envVariant) && $envVariant !== '' && isset($this->heroSubtitleOptions[$envVariant])) {
            $this->heroSubtitleVariant = $envVariant;
        }
    }

    public function heroSubtitle(): string
    {
        return $this->heroSubtitleOptions[$this->heroSubtitleVariant]
            ?? $this->heroSubtitleOptions['a'];
    }
}
