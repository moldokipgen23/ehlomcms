<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class AdminMediaLibraryController extends Controller
{
    public function index(): View
    {
        $tenants = Tenant::withCount('galleryImages')->orderBy('name')->get();
        $tenantAssets = [];

        $storageDir = storage_path('app/public');

        foreach ($tenants as $tenant) {
            $tenantDir = $storageDir . '/' . $tenant->id;
            $files = [];
            $totalSize = 0;

            if (is_dir($tenantDir)) {
                $allFiles = File::allFiles($tenantDir);
                foreach ($allFiles as $f) {
                    $files[] = [
                        'name' => $f->getFilename(),
                        'path' => $f->getPathname(),
                        'relative' => 'public/' . $tenant->id . '/' . $f->getFilename(),
                        'size' => $f->getSize(),
                        'type' => $f->getExtension(),
                        'modified' => $f->getMTime(),
                    ];
                    $totalSize += $f->getSize();
                }
            }

            $tenantAssets[] = [
                'tenant' => $tenant,
                'files' => $files,
                'total_size' => $totalSize,
                'file_count' => count($files),
                'gallery_count' => $tenant->gallery_images_count,
            ];
        }

        return view('media.index', compact('tenantAssets'));
    }
}
