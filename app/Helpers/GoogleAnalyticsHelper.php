<?php
namespace App\Helpers;

use Cuenta;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GoogleAnalyticsHelper {

    public static $INSTANTE_ANTES = "antes";
    public static $INSTANTE_DESPUES = "despues";

    public static function haveTaskGAEvents ( $etapaId, $tareaId, $instante ) {
        $haveGAEvents = true;
        $eventsGA = self::getTaskAnalyticsEvents( $etapaId, $tareaId, $instante );        
        if( count($eventsGA) == 0 ){
            $haveGAEvents = false;
        }
        return $haveGAEvents;
    }    

    public static function getGAEventsArray( $etapaId, $tareaId, $instante ) {
        // Eventos GA antes de la tarea
        $dbEventsGA = self::getTaskAnalyticsEvents( $etapaId, $tareaId, $instante );
        Log::debug('eventos ga antes de la tarea');
        Log::info("###Lo que trae busca_analyiticd : " . $dbEventsGA);
        return self::createGAArrayEvents($dbEventsGA);
    }        
    
    static function getTaskAnalyticsEvents ( $instante, $etapaId, $tareaId  ) {
        return DB::table('etapa') //Buscando el evento analytics por tarea iniciada
                    ->select('accion.id',
                        'accion.tipo',
                        'tarea.nombre as tarea_nombre',
                        'tarea.es_final as es_tarea_final',
                        'accion.nombre',
                        'accion.extra',
                        'evento.regla'
                    )
                    ->join('tarea','etapa.tarea_id', '=','tarea.id')
                    ->join('evento', 'evento.tarea_id', '=', 'tarea.id')
                    ->join('accion','evento.accion_id','=', 'accion.id')
                    ->where('etapa.id', $etapaId)
                    ->where('accion.tipo','=','evento_analytics')
                    ->where('evento.tarea_id',$tareaId)
                    ->whereNull('evento.paso_id')
                    ->where('evento.instante', $instante)
                    ->get();
    }

    // PASO    
    public static function haveStepGAEvents ( $etapaId, $pasoId ,$instante ) {
        $haveStepGAEvents = true;
        $eventsGA = self::getStepGAEventsArray( $etapaId, $pasoId, $instante );
        if( count($eventsGA) == 0 ) {
            $haveStepGAEvents = false;
        }
        return $haveStepGAEvents;
    }

    public static function getStepGAEventsArray( $etapaId, $pasoId, $instante ) {        
        // Eventos GA antes de la tarea
        $dbEventsGA = self::getStepAnalyticsEvents( $etapaId, $pasoId, $instante);
        Log::debug('eventos ga antes de la tarea');
        Log::info("###Lo que trae busca_analyiticd : " . $dbEventsGA);
        return self::createGAArrayEvents($dbEventsGA);
    }

    static function getStepAnalyticsEvents ( $instantes, $etapaId, $pasoId ) {
        return DB::table('etapa') //Buscando el evento analytics por tarea iniciada
                    ->select('accion.id',
                        'accion.tipo',
                        'tarea.nombre as tarea_nombre',
                        'tarea.es_final as es_tarea_final',
                        'accion.nombre',
                        'accion.extra',
                        'evento.regla'
                    )
                    ->join('tarea','etapa.tarea_id', '=','tarea.id')
                    ->join('evento', 'evento.tarea_id', '=', 'tarea.id')
                    ->join('accion','evento.accion_id','=', 'accion.id')
                    ->where('etapa.id', $etapaId)
                    ->where('accion.tipo','=','evento_analytics')
                    ->where('evento.paso_id',$pasoId)
                    ->whereIn('evento.instante', $instantes )
                    ->get();
    }
    
    static function createGAArrayEvents( $eventos_analytics ) {
        $eventsGA = array();
        foreach($eventos_analytics as $evento){
            $evento_ga = array();
            $evento_ga['analytics'] = json_decode($evento->extra, true);
            $evento_ga['analytics']['id_seguimiento'] = $evento_ga['analytics']['tipo_id_seguimiento'] == 'id_instancia' ?  env('ANALYTICS') : Cuenta::cuentaSegunDominio()->analytics;
            $evento_ga['es_final'] = $evento->es_tarea_final ? 'si':'no';
            array_push($eventsGA,$evento_ga);
        }
        return $eventsGA;
    }

}