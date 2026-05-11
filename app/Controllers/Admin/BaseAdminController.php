<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

abstract class BaseAdminController extends BaseController
{
    protected function layout(string $innerView, array $data = []): string
    {
        $data['inner'] = view($innerView, $data);

        return view('admin/layout', $data);
    }
}
