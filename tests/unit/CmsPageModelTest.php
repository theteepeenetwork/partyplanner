<?php

declare(strict_types=1);

use App\Models\CmsPageModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class CmsPageModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    public function testUpdatePersistsAfterWhereFirstSameAsAdminController(): void
    {
        $now = date('Y-m-d H:i:s');

        $model = new CmsPageModel();
        $model->insert([
            'slug'               => 'about',
            'title'              => 'About (old)',
            'content'            => '<p>old</p>',
            'meta_title'         => 'Meta old',
            'meta_description'   => 'Desc old',
            'status'             => 'published',
            'created_at'         => $now,
            'updated_at'         => $now,
        ]);

        $page = $model->where('slug', 'about')->first();
        $this->assertNotNull($page);
        $this->assertArrayHasKey('id', $page);

        $payload = [
            'title'            => 'About (new)',
            'content'          => '<p>new content</p>',
            'meta_title'       => 'Meta new',
            'meta_description' => 'Desc new',
            'status'           => 'published',
        ];

        $ok = $model->update($page['id'], $payload);
        $this->assertTrue($ok, 'Model::update should succeed: ' . json_encode($model->errors()));

        $fresh = (new CmsPageModel())->where('slug', 'about')->first();
        $this->assertSame('About (new)', $fresh['title']);
        $this->assertSame('<p>new content</p>', $fresh['content']);
        $this->assertSame('Meta new', $fresh['meta_title']);
        $this->assertSame('Desc new', $fresh['meta_description']);
        $this->assertSame('published', $fresh['status']);
    }
}
