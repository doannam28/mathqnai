<?php

namespace App\Files;

class Storage
{
    /**
     * @param string $disk
     * @param $path
     * @param $file
     * @return bool|string
     */
    public static function putFile(string $disk, $path, $file): bool|string
    {
        $originalFileName = $file->getClientOriginalName();
        $fileName = $originalFileName;
        $i = 1;
        while (\Illuminate\Support\Facades\Storage::disk($disk)->exists($path . '/' . $fileName)) {
            $fileName = pathinfo($originalFileName, PATHINFO_FILENAME) . '_' . $i . '.' . pathinfo($originalFileName, PATHINFO_EXTENSION);
            $i++;
        }
        return \Illuminate\Support\Facades\Storage::disk($disk)->putFileAs($path, $file, $fileName);
    }

    public static function getFileName($file)
    {
        $originalFileName = $file->getClientOriginalName();
        $fileName = $originalFileName;
        $i = 1;
        while (\Illuminate\Support\Facades\Storage::disk('admin')->exists('images' . '/' . $fileName)) {
            $fileName = pathinfo($originalFileName, PATHINFO_FILENAME) . '_' . $i . '.' . pathinfo($originalFileName, PATHINFO_EXTENSION);
            $i++;
        }
        return $fileName;
    }
}
