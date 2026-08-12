<?php

namespace App\Http\Controllers;

use App\Models\FAQ;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FAQController extends Controller
{
    public function __construct(protected FAQ $model){}

    public function index(){

        $faqs = $this->model->latest()->get();

        $user = Auth::guard('admin')->user();

        return Inertia::render('FAQs/Index',[
            'faqs' => $faqs,
            'user' => $user
        ]);
    }

    public function create(){

        $user = Auth::guard('admin')->user();

        return Inertia::render('FAQs/Create',[
            'user' => $user
        ]);
    }

    public function store(Request $request){

        info($request->all());

        $this->model->create($request->all());

        return redirect()->route('faqs')->with('success', 'FAQ created successfully');

    }

    public function edit($id){
        $faq = $this->model->find($id);

        $user = Auth::guard('admin')->user();

        return Inertia::render('FAQs/Edit',[
            'faq' => $faq,
            'user' => $user
        ]);
    }

    public function update(Request $request){

        info($request->all());

        $faq = $this->model->find($request->id);

        $faq->update($request->all());

        return redirect()->route('faqs')->with('success', 'FAQ updated successfully');

    }

    public function destroy($id){
        $faq = $this->model->find($id);
        $faq->delete();
        return redirect()->route('faqs')->with('success', 'FAQ deleted successfully');
    }

}
