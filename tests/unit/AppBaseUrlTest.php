<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Libraries\AppBaseUrl;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AppBaseUrlTest extends CIUnitTestCase
{
    public function testShouldSyncWhenRequestHostDiffersFromConfigured(): void
    {
        $this->assertTrue(
            AppBaseUrl::shouldSyncHost('http://partyplanner.test/', 'localhost:8888')
        );
    }

    public function testShouldNotSyncWhenHostsMatch(): void
    {
        $this->assertFalse(
            AppBaseUrl::shouldSyncHost('http://partyplanner.test/', 'partyplanner.test')
        );
    }

    public function testShouldNotSyncWhenAllowedHostnamesRestrictRequest(): void
    {
        $this->assertFalse(
            AppBaseUrl::shouldSyncHost(
                'http://partyplanner.test/',
                'evil.example',
                ['partyplanner.test']
            )
        );
    }
}
