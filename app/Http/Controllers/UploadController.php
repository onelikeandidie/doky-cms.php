<?php

namespace App\Http\Controllers;

use App\Libraries\Sync\Sync;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|file',
            'type' => 'required|string:image'
        ]);
        $type = $validated['type'];
        switch ($type) {
            case 'image':
                /** @var UploadedFile $file */
                $file = $validated['image'];
                // Store it inside the sync directory
                $sync = Sync::getInstance();
                $fileName = $file->getClientOriginalName();
                // There is a slight issue. The Markdown parser fucks up if the
                // name has a bunch of spaces or dots before the extension.
                $fileExtension = File::extension($fileName);
                $fileBase = Str::replace('.' . $fileExtension, '', $fileName);
                $fileName = Str::replace(['.', ' '], ['_', '-'], $fileBase) . '.' . $fileExtension;
                $image_sub_dir = '/images/' . $fileName;
                $path = $sync->getDriver()->getDirectory() . $image_sub_dir;
                if (file_exists($path)) {
                    return response()->json(['error' => 'File with the same name already exists.']);
                }
                $fileStore = $file->move($sync->getDriver()->getDirectory() . '/images', $fileName);
                $relPath = $sync->getDriver()->getRelativePath() . $image_sub_dir;
                $assetPath = '/public/storage/sync/' . $relPath;
                return response()->json([
                    'data' => [
                        'filePath' => '/' . $relPath,
                        'imageUrl' => asset($assetPath),
                    ]
                ]);
            default:
                return response()->json(['error' => 'Invalid type.']);
        }
    }

    public function storeImage(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|file',
        ]);
        // Pass the request to the store method
        $request->merge(['type' => 'image']);
        return $this->store($request);
    }
}
