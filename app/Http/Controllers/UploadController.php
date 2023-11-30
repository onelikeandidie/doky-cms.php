<?php

namespace App\Http\Controllers;

use App\Libraries\Sync\Sync;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file',
            'type' => 'required|string:image'
        ]);
        $type = $validated['type'];
        switch ($type) {
            case 'image':
                /** @var UploadedFile $file */
                $file = $validated['file'];
                // Store it inside the sync directory
                $sync = Sync::getInstance();
                $image_sub_dir = '/images/' . $file->getClientOriginalName();
                $path = $sync->getDriver()->getDirectory() . $image_sub_dir;
                if (file_exists($path)) {
                    return response()->json(['error' => 'File with the same name already exists.']);
                }
                $fileStore = $file->move($sync->getDriver()->getDirectory() . '/images', $file->getClientOriginalName());
                $assetPath = asset('/public/storage/sync/' . $sync->getDriver()->getRelativePath() . $image_sub_dir);
                return redirect('dashboard')->with('highlight', $assetPath);
            default:
                return response()->json(['error' => 'Invalid type.']);
        }
    }
}
