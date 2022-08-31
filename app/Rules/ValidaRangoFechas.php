<?php

namespace App\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;


class ValidaRangoFechas implements Rule
{
    private $fechaFin;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($fechaFin)
    {
        $this->fechaFin = $fechaFin;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $validation = true;
    
        try {
            $fechaInicio = new Carbon($value." 00:00:00");
            $fechaFin = new Carbon($this->fechaFin." 23:59:59");

            \Log::info("[Valida Rango Fechas] => : ", [
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,
                'diferenciaDias' => $fechaInicio->diffInDays($fechaFin),
                'FechaInicioesMayorQueFechaFin' => $fechaInicio->gte($fechaFin),
            ]);
            
            $diferenciaDias = $fechaInicio->diffInDays($fechaFin);

            if ($diferenciaDias >= 7) {
                $validation = false;
            }
            
            if ($fechaInicio->gte($fechaFin)) {
                $validation = false;
            }
        } catch(\Exception $e) {
            $validation = false;
            \Log::info("[MANAGER][Error al solicitar adjuntos y documentos][Libro de Hechizos] => : ", [
                'error' => $e
            ]);
        }

       
        return $validation;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'El rango de fechas ingresado no es válido, entre la fecha inicial y la final no deben haber más de 7 dias continuados.';
    }
}
