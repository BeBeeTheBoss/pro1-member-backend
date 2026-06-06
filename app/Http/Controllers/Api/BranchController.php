<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function __construct(protected Branch $model) {}

    public function index(){
        return sendResponse(BranchResource::collection($this->model->all()), 200);
    }

    public function select()
    {
        $branches = $this->model
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'branch_code'])
            ->map(fn ($branch) => [
                'id' => $branch->id,
                'name' => $branch->name,
                'branch_code' => $branch->branch_code,
                'value' => $branch->id,
                'label' => $branch->branch_code
                    ? $branch->name . ' (' . $branch->branch_code . ')'
                    : $branch->name,
            ]);

        return sendResponse($branches, 200);
    }

}
