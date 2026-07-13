<?php

namespace App\Http\Controllers;

use App\Services\ErrorLogReader;
use Illuminate\View\View;

class AdminSystemHealthController extends Controller
{
    public function index(ErrorLogReader $reader): View
    {
        return view('system.errors', [
            'entries' => $reader->recent(100),
        ]);
    }
}
