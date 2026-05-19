<?php

namespace App\Database\Seeds;

use App\Libraries\CmsPageDefaults;
use CodeIgniter\Database\Seeder;

class CmsPagesSeeder extends Seeder
{
    public function run(): void
    {
        CmsPageDefaults::seedMissing();
    }
}
