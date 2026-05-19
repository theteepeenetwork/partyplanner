<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\CmsPageModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class PublicPageControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'Tests\Support';

    public function testAboutAutoSeedsDefaultPage(): void
    {
        $result = $this->get('about');

        $result->assertStatus(200);
        $result->assertSee('About us', 'html');

        $page = (new CmsPageModel())->where('slug', 'about')->first();
        $this->assertNotNull($page);
        $this->assertSame('published', $page['status']);
    }

    public function testHowItWorksRouteResponds(): void
    {
        $result = $this->get('how-it-works');

        $result->assertStatus(200);
        $result->assertSee('How it works', 'html');
    }
}
