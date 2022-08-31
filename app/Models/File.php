<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class File extends Model
{
    protected $table = 'file';

    public function tramite()
    {
        return $this->belongsTo(Tramite::class, 'tramite_id', 'id');
    }

    /**
     * Elimina registros no confirmados de la tabla file y del file system segun corresponda.
     *
     * Un archivo sin confirmar es aquel que si bien ha sido subido a SIMPLE no ha sido confirmado
     * al campo mediante la accion de avanzar de etapa, ratificando el archivo subido al campo correspondiente.
     *
     * Cuando ya existe un archivo asociado a un campo y el usuario a subido un nuevo archivo el cual se ha confirmado
     * al avanzar nuevamente la etapa entonces este archivo en estado confirmado debe eliminarse y ser reemplazado
     * por el nuevo archivo confirmado.
     *
     * Archivo file o file_transfer
     *
     * $params["tipo"]              String "dato" || "s3"
     * $params["campoId"]           Campo id
     * $params["tramiteId"]         Tramite id
     * $params["clearConfirmed"]    Boolean
     * $params["exceptionFileId"]   File id  || null
     *
    */
    static function eliminaArchivoSinConfirmarOAntiguos($params): void {
        $params['clearConfirmed'] = isset($params['clearConfirmed']) ? $params['clearConfirmed'] : false;
        $params['exceptionFileId'] = isset($params['exceptionFileId']) ? $params['exceptionFileId'] : null;

        // Obtengo todos los files correspondientes al tipo de campo y tramite
        $archivos = self::where('campo_id', $params["campoId"])
            ->where('tramite_id', $params["tramiteId"])
            ->where('tipo', $params["tipo"])
            ->get();

        foreach($archivos as $archivo) {
            $fileExtra = json_decode($archivo->extra, true);

            if(!isset($fileExtra['file_confirmed'])) {
                continue;
            }

            // Verificamos si el archivo no esta confirmado y lo eliminamos
            if(!$fileExtra['file_confirmed']) {
                $archivo->forceDelete();
                // solo un campo file se borra del file system
                if ("dato" == $params["tipo"]) {
                    unlink('uploads/datos/' . $archivo->filename);
                }
                Log::info("[Model FILE - Delete] - Desde File-System y bdd, archivo Nombre:[$archivo->filename] asociado a: ", [
                    "tipo" => "Dato - File system",
                    "tramiteId" => $params["tramiteId"],
                    "campoId" => $params["campoId"]
                ]);
            } else {
                /*
                    Hay un caso donde tendremos que eliminar un archivo confirmado,
                    este sera cuando el usuario sube un nuevo archivo y avanza de etapa y como solo pude haber
                    un registro de archivo confirmado entonces eliminaremos cualquier registro confirmado
                    con excepcion del nuevo archivo subido el => exceptionFileId
                */
                if ($params['clearConfirmed'] && $archivo->id != $params['exceptionFileId']) {
                    // elimino si es archivo confirmado y no es el nuevo archivo recien subido
                    $archivo->forceDelete();
                    // solo un campo file se borra del file system
                    if ("dato" == $params["tipo"]) {
                        unlink('uploads/datos/' . $archivo->filename);
                    }
                    Log::info("[Model FILE - Delete] - Se borra archivo anterior, Nombre:[$archivo->filename] asociado a: ", [
                        "tipo" => "Dato - FIle system",
                        "tramiteId" => $params["tramiteId"],
                        "campoId" => $params["campoId"]
                    ]);
                }
            }
        }

        return;
    }
}
