<?php

declare(strict_types=1);

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\SiteURI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;

/**
 * Guards admin controllers: getMethod() is uppercase POST, not "post".
 *
 * @internal
 */
final class IncomingRequestPostTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($_SERVER['REQUEST_METHOD']);
    }

    public function testIsPostWorksWhenRequestMethodIsUppercasePost(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $config = new App();
        $uri     = new SiteURI($config, 'http://example.com/admin/pages/edit/about');
        $request = new IncomingRequest($config, $uri, null, new UserAgent());

        $this->assertSame('POST', $request->getMethod());
        $this->assertTrue($request->is('post'));
        $this->assertFalse($request->getMethod() === 'post', 'Lowercase post comparison must never match a real POST request.');
    }
}
