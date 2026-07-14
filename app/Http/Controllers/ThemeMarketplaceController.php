<?php

namespace App\Http\Controllers;

use App\Models\Theme;
use Illuminate\View\View;

class ThemeMarketplaceController extends Controller
{
    public function index(): View
    {
        $themes = Theme::where('public', true)->orderBy('name')->get();
        return view('themes.marketplace', compact('themes'));
    }
}
