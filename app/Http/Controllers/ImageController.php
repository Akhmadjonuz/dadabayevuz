<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;

class ImageController extends Controller
{
    public function show($filename)
    {
        $path = 'images/' . $filename;
        
        if (!Storage::exists($path)) {
            abort(404);
        }

        $image = Storage::get($path);
        $contentType = Storage::mimeType($path);

        return (new Response($image, 200))->header('Content-Type', $contentType);
    }
}
