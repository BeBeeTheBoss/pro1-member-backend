<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PrivilegeCategory;

class PrivilegeCategoryController extends Controller
{
    public function __construct(protected PrivilegeCategory $model) {}

    public function index()
    {
        $categories = $this->model->orderBy('id')->get();

        return sendResponse($categories, 200);
    }
}
