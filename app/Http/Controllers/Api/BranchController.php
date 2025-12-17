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

}
