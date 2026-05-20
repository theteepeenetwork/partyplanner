<?php

/*
 |--------------------------------------------------------------------------
 | Cloud / agent development (Cursor Cloud, CI agents)
 |--------------------------------------------------------------------------
 | Readable errors like development, but no Kint or debug toolbar injection
 | (avoids browser hangs and slow HTML responses in headless QA).
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

defined('CI_ENVIRONMENT') || define('CI_ENVIRONMENT', 'cloud');

defined('SHOW_DEBUG_BACKTRACE') || define('SHOW_DEBUG_BACKTRACE', true);

defined('CI_DEBUG') || define('CI_DEBUG', false);
