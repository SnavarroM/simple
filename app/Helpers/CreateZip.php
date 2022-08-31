<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use ZipArchive;

class CreateZip {

    /**
     * The base path of the file is compressed
     *
     * @var string
     */
    protected $pathOriginDirectory;

    /**
     * The base path with the name of the resulting .zip file
     *
     * @var string
     */
    protected $pathZipResult;


    public function __construct($pathOriginDirectory, $pathZipResult) {
        $this->pathOriginDirectory = $pathOriginDirectory;
        $this->pathZipResult = $pathZipResult;
        $this->handle();
    }

    /**
     * * Zip a folder (include itself).
     *
     * @return void
     */
    public function handle()
    {
        $pathInfo = pathInfo($this->pathOriginDirectory);

        // path base del directorio a comprimir ej: De "uploads/proceso/docs" toma "uploads/proceso"
        $parentPath = $pathInfo['dirname'];
        // nombre del ultimo directorio de la ruta. ej anterior toma solo "proceso"
        $dirName = $pathInfo['basename'];

        Log::debug("[CreateZip][ZipArchive] - Iniciando proceso de compresión, directorio de origen", [
            "PathInfo" => $pathInfo,
            "UrlZipName" => $this->pathZipResult
        ]);

        try {
            $newZip = new ZipArchive();
            if($newZip->open($this->pathZipResult, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
                $newZip->addEmptyDir($dirName);
                Log::debug("[CreateZip][ZipArchive] - Iniciando compresión...");
                $this->folderToZip($this->pathOriginDirectory, $newZip, strlen("$parentPath/"));
                Log::debug("[CreateZip][ZipArchive] - Compresión finalizada, cerrando ...");
                $newZip->close();
            } else {
                Log::error("[CreateZip][ZipArchive] - No logra abrir el archivo o directorio a comprimir", [
                    "filename" => $this->pathZipResult
                ]);
            }
        } catch (\Exception $e) {
            Log::error("[CreateZip][ZipArchive] - Ha ocurrido un error al tratar de comprimir el archivo", [
                "file" => $this->pathZipResult,
                "message" => $e->getMessage()
            ]);
        }
    }

     /**
     * Add files and sub-directories in a folder to zip file.
     * @param string $folder
     * @param ZipArchive $zipFile
     * @param int $exclusiveLength Number of text to be exclusived from the file path.
     * 
     * @return void
     */
    public function folderToZip($folder, &$zipFile, $exclusiveLength) {
        $handle = opendir($folder);
        while (false !== $element = readdir($handle)) {
            if ($element != '.' && $element != '..') {
                $filePath = "$folder/$element";
                // Remove prefix from file path before add to zip.
                $localPath = substr($filePath, $exclusiveLength);
                if (is_file($filePath)) {
                    $zipFile->addFile($filePath, $localPath);
                } elseif (is_dir($filePath)) {
                    // Add sub-directory.
                    $zipFile->addEmptyDir($localPath);
                    $this->folderToZip($filePath, $zipFile, $exclusiveLength);
                }
            }
        }
        closedir($handle);
    }
}