<?php

namespace App\Helpers;

use App\Exceptions\GenerateZipFileException;
use App\Helpers\CreateZip;
use App\Helpers\SendEmail;
use App\Models\Tramite;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


/**
 * * Clase para manejar la generación de un archivo .zip a partir de un
 * * determinado proceso, obteniendo los archivos relacionados a los
 * * archivos subidos por el usuario (tipo dato) y los documentos (tipo documento)
 * * ambos presentes en la tabla file.
 * 
 * * Los archivos se clasifican en dos subdirectorios para cada tipo de archivo
 * * /adjuntos y /documentos ambos agrupados en directorios particulares para
 * * c/uj de los trámites con los que cuente el proceso durante el rango de
 * * fecha definido.
*/
class RescatadorArchivosZIP {

    private $processId;

     /**
     * Zip file name
     *
     * @var string
     */
    private $zipFileName;

    /**
     * The base path to file storage.
     * 
     * todo: revisar si puede ser usado ono, sino quitar
     * ! quitar?
     *
     * @var string
     */
    protected $basePath;

    /**
     * The local temporal base path to store previus data of process.
     * 
     * * este directorio existirá dentro de public/uploads
     * 
     * @var string
     */
    protected $pathTemporalLocal;

    /**
     * The base path to store .zip on s3 bucket.
     * 
     * @var string
     */
    protected $pathTemporalS3;

    /**
     * The local temporal base path to store previus data of process.
     *
     * @var string
     */
    protected $dirNameProcess;

    /**
     * Date from when to start getting files 
     *
     * @var string
     */
    protected $startDate;

    /**
     * Date until when to stop getting files
     *
     * @var string
     */
    protected $endDate;


    /**
     * Resulting url after uploading file to s3
     *
     * @var string
     */
    protected $s3FileDownloadUrl;


    public function __construct($processId, $startDate=null, $endDate=null) {

        // Sino existe, crea el directorio auxuliar para archivos comprimidos temporales
        if (!Storage::disk('uploads')->exists($this->pathTemporalLocal)) {
            Storage::disk('uploads')->makeDirectory($this->pathTemporalLocal, 0777, true, true);
        }
        $this->basePath = 'uploads/';
        $this->pathTemporalLocal = env('RESCATADOR_ARCHIVOS_TMP_DIR', 'temporal_zip')."/";
        $this->pathTemporalS3 = env('RESCATADOR_ARCHIVOS_S3_DIR', 'archivos_zip')."/";
        $this->processId = $processId;
        $this->startDate = new Carbon($startDate);
        $this->endDate = new carbon($endDate);
        $this->setDirnameProcess();
        $this->start();
    }


    /**
     * * Define el nombre del directorio que hace referencia al proceso
     * 
     * Todo: Agregar manejo dínamico de fechas para definir nombre final
     *
     * @return void
     */
    private function setDirnameProcess() {
        $processId = $this->processId;
        $this->dirNameProcess = "proceso_$processId"."_(".$this->startDate->format('d-m-Y')." al ".$this->endDate->format('d-m-Y').")";
    }


    /**
     * * Itera sobre los trámites del proceso obteniendo c/u
     * * de los archivos según tipo (dato, documento)
     * 
     *  todo: ordenar/refactorizar código durante el manejo de directorios con Storage::disk
     *
     * @return void
     */
    private function createTemporaryLocalDirs() {
        Log::debug("[RescatadorArchivosZIP] - Creando directorios necesarios para crear el .zip de archivos ...");
        $tramites = Tramite::with(['files'])->where('proceso_id', $this->processId);
        if (null != $this->startDate && null != $this->endDate) {
            $tramites = $tramites->whereBetween('created_at', [$this->startDate->format('Y-m-d')." 00:00:00", $this->endDate->format('Y-m-d')." 23:59:59"]);
        }
        $tramites = $tramites->get();
        
        if (count($tramites) < 1) {
            throw new GenerateZipFileException();
        }

        foreach($tramites as $tramite) {

            $tramiteNameDir = $this->pathTemporalLocal. "/" .$this->dirNameProcess. "/tramite_" .$tramite->id;

            $archivos = $tramite->files;
            
            // Si un trámite no tiene archivos, no se genera directorio y se avanza al siguiente
            if (count($archivos) < 1) {
                continue;
            }

            foreach($archivos as $archivo) {
                $this->createDirectoryByFileType($archivo, $tramiteNameDir);
            }
        }
    }


    /**
     * Se simplifica la verificación de los directorios que deben
     * ser creados según el archivo de trámite que corresponda
     */
    private function createDirectoryByFileType($archivo, $tramiteNameDir) {
        Log::debug("[RescatadorArchivosZIP] - Creando subdirectorios de documentos y adjuntos...");
        if ("documento" == $archivo->tipo) {
            $this->verifyAndCreateDirectoryByFile([
                'realFilePath' => "/documentos/",
                'destinyFilePath' => $tramiteNameDir."/documentos",
                'fileName' => $archivo->filename
            ]);
        } else if ("dato" == $archivo->tipo) {
            $this->verifyAndCreateDirectoryByFile([
                'realFilePath' => "/datos/",
                'destinyFilePath' => $tramiteNameDir."/adjuntos",
                'fileName' => $archivo->filename
            ]);
        }
    }


    /**
     * Check and create the necessary directories to move the file
     * from its original path to the temporary directory
     *
     * @return void
     */
    private function verifyAndCreateDirectoryByFile($params) {

        $destinyFilePath = $params['destinyFilePath'];
        $originalFile = $params['realFilePath'].$params['fileName'];
        $temporaryFile = $params['destinyFilePath']."/".$params['fileName'];

        if (Storage::disk('uploads')->exists($originalFile)) {
            if (!Storage::disk('uploads')->exists($temporaryFile)) {
                Storage::disk('uploads')->makeDirectory($destinyFilePath);
                Storage::disk('uploads')->copy($originalFile, $temporaryFile);
            }
        }
    }

    /**
     * Description
     *
     * @return void
     */
    private function makeDirectoryForFile($directoryOrigin, $directoryDestiny) {
        // si existe el archivo, continuar
        if (Storage::disk('uploads')->exists($directoryOrigin)) {
            // si no existe el archivo en el destino, continuar
            if (!Storage::disk('uploads')->exists($directoryDestiny)) {
                Storage::disk('uploads')->copy($directoryOrigin, $directoryDestiny);
            }
        }
    }

    /**
     * Description
     *
     * @return void
     */
    private function start() {

        $this->createTemporaryLocalDirs();

        $pathDirname = public_path("uploads/".$this->pathTemporalLocal . $this->dirNameProcess);
        $pathResultZipName = public_path("uploads/".$this->pathTemporalLocal . $this->dirNameProcess. ".zip");
        $creatingZip = new CreateZip($pathDirname, $pathResultZipName);
    }

    /**
     * Manage file upload to s3 and get temporary download url
     *
     * @return string
     */
    public function uploadToS3() {
        Log::debug("[RescatadorArchivosZIP] - Subiendo archivo a s3 ...");
        if( !Storage::disk('s3')->exists($this->pathTemporalS3) ){
            Storage::disk('s3')->makeDirectory($this->pathTemporalS3);
        }

        $s3DirName = $this->pathTemporalS3.$this->dirNameProcess . ".zip";
        $pathZipName = $this->pathTemporalLocal . $this->dirNameProcess . ".zip";

        Storage::disk('s3')->put($s3DirName, Storage::disk('uploads')->get($pathZipName));

        $downloadUrl = $this->getFileDownloadUrl();

        $this->clean();

        return $downloadUrl;
    }

    /**
     * Generates the download url of the uploaded file
     *
     * @return string
     */
    protected function getFileDownloadUrl() {
        Log::debug("[RescatadorArchivosZIP] - Obteniendo url S3 para descarga de .zip...");
        $s3 = Storage::disk('s3');
        $client = $s3->getDriver()->getAdapter()->getClient();
        $expiry = "+1440 minutes";

        $command = $client->getCommand('GetObject', [
            'Bucket' => env('AWS_BUCKET'),
            'Key'    => $this->pathTemporalS3.$this->dirNameProcess.".zip"
        ]);

        $link = $client->createPresignedRequest($command, $expiry);

        Log::info('[RescatadorArchivosZIP][Link temporal de descarga s3][URL Lifetime 24 hrs]', [
            'Link temporal' => $link->getUri()
        ]);

        return $link->getUri();
    }

    /**
     * Description
     *
     * @return void
     */
    protected function clean() {
        Log::debug("[RescatadorArchivosZIP] - Eliminando archivos procesados...");
        $dirToDelte = $this->pathTemporalLocal . $this->dirNameProcess;
        $sipToDelete = $dirToDelte.".zip";
        Storage::disk('uploads')->deleteDirectory($dirToDelte);
        Storage::disk('uploads')->delete($sipToDelete);
    }
}