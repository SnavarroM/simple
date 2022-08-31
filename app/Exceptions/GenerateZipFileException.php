<?php

namespace App\Exceptions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class GenerateZipFileException extends Exception
{
    private $msg;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct($msg='No se encontraron archivos ni documentos para el rango de fecha definido')
    {
        $this->msg = $msg;
    }

    /**
     * Report or log an exception.
     *
     * @return void
     */
    public function report()
    {
        \Log::debug('[GenerateZipFileException]: '.$this->msg);
    }
}
