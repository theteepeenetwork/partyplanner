<?php

namespace Tests\Unit;

use App\Libraries\CmsPageDefaults;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class CmsPageDefaultsTest extends CIUnitTestCase
{
    public function testDefinitionsIncludeSiteNavSlugs(): void
    {
        $defs = CmsPageDefaults::definitions();

        foreach (['about', 'how-it-works', 'contact', 'vendor-info', 'faq'] as $slug) {
            $this->assertArrayHasKey($slug, $defs);
            $this->assertSame('published', $defs[$slug]['status']);
        }
    }
}
