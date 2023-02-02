<?php

use Illuminate\Support\Facades\Storage;

if (!function_exists('getModelIdFromFilename')) {
    function getModelIdFromFilename($prefix, $filename)
    {
        $filename = str_replace($prefix, '', $filename);
        $fileParts = explode('_', $filename);

        return $fileParts[1];
    }
}

if (!function_exists('getUserOrganizationFilePath')) {
    function getUserOrganizationFilePath()
    {
        $user = auth()->user();

        if (!$user) {
            return null;
        }

        return "files/{$user->organization_id}";
    }
}

if (!function_exists('saveFile')) {
    function saveFile($file , $isCreatedPdf = false)
    {
        $extension = $isCreatedPdf ? 'PDF' : $file->extension();
        $fileName = auth()->user()->id . '_' . time() . '.' . $extension;
        $user = auth()->user();
        $orgPath =  "files/{$user->organization_id}";


        $filePath = "/{$orgPath}/{$fileName}"; 
        $contents = $isCreatedPdf ? $file->output() : file_get_contents($file);

        Storage::put($filePath, $contents);

        return $fileName;
    }
}

