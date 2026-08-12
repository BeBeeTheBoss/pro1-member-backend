<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PrivilegeResource;
use App\Models\Privilege;
use Illuminate\Http\Request;

class PrivilegeController extends Controller
{
    public function __construct(protected Privilege $model) {}

    public function index(Request $request)
    {

        $query = $this->model->with('category')->where('is_active', true);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $privileges = $query->latest()->get();

        return sendResponse(PrivilegeResource::collection($privileges), 200);
    }
}
