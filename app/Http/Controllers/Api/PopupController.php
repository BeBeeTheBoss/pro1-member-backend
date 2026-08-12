<?php

namespace App\Http\Controllers\Api;

use App\Models\Popup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PopupController extends Controller
{
    public function __construct(protected Popup $model) {}

    public function index()
    {
        $popup = $this->model
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->where('is_active', true)
            ->first();


        $popup = $popup ? url("storage/popups/" . $popup->image) : null;

        return sendResponse($popup, 200);
    }
}
