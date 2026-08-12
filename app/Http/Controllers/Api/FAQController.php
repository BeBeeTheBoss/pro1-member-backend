<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FAQ;
use Illuminate\Http\Request;

class FAQController extends Controller
{
    public function __construct(protected FAQ $model){}

    public function index(){
        $faqs = $this->model->where('is_active',true)->get();


        return sendResponse($faqs, 200);
    }

}
