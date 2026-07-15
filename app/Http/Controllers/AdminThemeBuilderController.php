<?php

namespace App\Http\Controllers;

use App\Models\Theme;
use App\Services\ThemeAnalyzer;
use App\Services\ThemeGenerator;
use App\Services\ThemePackager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminThemeBuilderController extends Controller
{
    public function index(): View
    {
        $themes = Theme::orderBy('created_at', 'desc')->get();
        $businessTypes = config('business_types');

        return view('theme-builder.index', compact('themes', 'businessTypes'));
    }

    public function analyze(Request $request): View
    {
        $request->validate([
            'files' => ['nullable', 'array'],
            'files.*' => ['file', 'mimes:html,htm,css,js,jsx,tsx,zip,png,jpg,jpeg,svg'],
            'design_url' => ['nullable', 'url'],
            'paste_html' => ['nullable', 'string', 'max:200000'],
            'business_type' => ['required', 'string'],
        ]);

        if (!$request->hasAny('files.0', 'design_url', 'paste_html')) {
            return back()->withErrors(['files' => 'Upload files, paste a design URL, or paste HTML code.'])->withInput();
        }

        $analyzer = app(ThemeAnalyzer::class);
        $analysis = $analyzer->analyze(
            $request->file('files') ?? [],
            $request->input('design_url'),
            $request->input('business_type'),
            $request->input('paste_html')
        );

        $businessTypes = config('business_types');

        return view('theme-builder.analysis', compact('analysis', 'businessTypes'));
    }

    public function generate(Request $request): View
    {
        $request->validate([
            'analysis' => ['required', 'array'],
            'theme_name' => ['required', 'string', 'max:255'],
            'business_type' => ['required', 'string'],
            'ai_api_key' => ['nullable', 'string'],
            'ai_provider' => ['nullable', 'in:openai,anthropic'],
        ]);

        $apiKey = $request->input('ai_api_key') ?: config('services.ai.api_key');
        $provider = $request->input('ai_provider', 'openai');
        $model = $provider === 'anthropic' ? 'claude-sonnet-4-20250514' : 'gpt-4o';

        $generator = new \App\Services\ThemeGenerator($apiKey, $provider, $model);
        $result = $generator->generate(
            $request->input('analysis'),
            $request->input('theme_name'),
            $request->input('business_type')
        );

        return view('theme-builder.preview', [
            'theme' => $result['theme'],
            'files' => $result['files'],
            'previewUrl' => $result['preview_url'] ?? null,
        ]);
    }

    public function preview(Theme $theme): View
    {
        return view('theme-builder.preview', [
            'theme' => $theme,
            'files' => [],
            'previewUrl' => null,
        ]);
    }

    public function install(Theme $theme): RedirectResponse
    {
        $theme->update(['public' => true]);

        return redirect()->route('themes.index')
            ->with('success', "Theme '{$theme->name}' installed and published to marketplace.");
    }

    public function download(Theme $theme): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $packager = app(ThemePackager::class);
        $zipPath = $packager->package($theme);

        return response()->download($zipPath, $theme->key . '-theme.zip')->deleteFileAfterSend(true);
    }
}
