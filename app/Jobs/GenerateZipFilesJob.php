<?php

namespace App\Jobs;

use App\Exceptions\GenerateZipFileException;
use App\Helpers\RescatadorArchivosZIP;
use App\Helpers\SendEmail;
use App\Models\Job;
use Carbon\Carbon;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class GenerateZipFilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $proceso_id;
    protected $fecha_inicio;
    protected $fecha_fin;
    protected $job;
    protected $downloadUrl;
    protected $userEmail;
    protected $job_id;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($params)
    {
        $this->proceso_id = $params['proceso_id'];
        $this->fecha_inicio = $params['fecha_inicio'];
        $this->fecha_fin = $params['fecha_fin'];

        $this->userEmail = $params['user_email'];

        $this->job  = new Job();
        $this->job->user_id = $params['user_id'];
        $this->job->user_type = "backend";
        $this->job->status = Job::$created;
        $this->job->save();

        $this->job_id = $this->job->id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $newJob = Job::find($this->job_id);
        $newJob->status = Job::$running;
        $newJob->save();

        try {

            $createZip = new RescatadorArchivosZIP(
                $this->proceso_id,
                $this->fecha_inicio,
                $this->fecha_fin
            );

            $this->downloadUrl = $createZip->uploadToS3();
            $this->sendEmailZipNotification([
                'link' => $this->downloadUrl
            ]);

            $newJob->status = Job::$finished;
            
        } catch(GenerateZipFileException $e) {
            $newJob->status = Job::$finished;

            Log::error('[GenerateZipFileException][Exception]', [
                'log' => 'No se encontraron archivos ni documentos para el rango de fecha definido.'
            ]);

            $this->sendEmailZipNotification([
                'startDate' => $this->fecha_inicio,
                'endDate' => $this->fecha_fin
            ], true);
        } catch (\Excepcion $e) {

            $newJob->status = Job::$error;

            Log::error('[GenerateZipFilesJob][Exception]', [
                'log' => $e->getMessage()
            ]);
        }

        $newJob->save();
    }

    private function sendEmailZipNotification($params=[], $error=false) {
        $now = Carbon::now();
        $email = new SendEmail();
        $email->setSubject("Archivos correspondientes al proceso[".$this->proceso_id."] Solicitado el: ".$now->format("d-m-y H:i"));

        if ($error) {
            $email->setBody($this->errorNotification($params));
        } else {
            $email->setBody($this->successNotification($params));
        }

        $email->setEmailTo([$this->userEmail]);
        $email->sendEmail();
    }

    private function successNotification($params) {
        return "
            <h1>Solicitud de obtención de archivos</h1>
            <p>
                En el siguiente enlace podrás descargar un archivo comprimido en formato .zip <br>
                donde encontrarás todos los archivos que fueron adjuntados por el usuario y los documentos <br>
                generados por SIMPLE, agrupados por id trámite para el proceso solicitado. <br><br>
                El link tiene una duración de 24hrs, transcurrido ese tiempo quedará deshabilitado.<br>
                Si no lo descargaste durante ese perido de tiempo deberás volver a solicitarlo.<br><br>
                <a target='_blank' href='".$params['link']."'>Para descargar haga clic aquí</a>
            </p>";
    }

    private function errorNotification($params) {
        $startDate = new Carbon($params['startDate']);
        $endDate = new carbon($params['endDate']);
        return "
            <h1>Solicitud de obtención de archivos</h1>
            <p>
                No se han encontrado registros de trámites para el rango de fechas solicitado (".$startDate->format('d-m-Y')." - ".$endDate->format('d-m-Y').").<br>
            </p>";
    }
}