<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $files = $request->file('file');
//        $path = Storage::disk('admin')->putFile('images', $files);
        $path = \App\Files\Storage::putFile('admin', 'images', $files);
        return response()->json([
            'location' => '/uploads/'.$path,
        ]);
    }
}
