<?php
namespace App\Helpers;

use App\Models\Anuncio;
use Illuminate\Http\Request;

class AnuncioHelper {

    private $listadoAnuncios;

    public function __construct($enviroment='frontend') {
        
        $this->listadoAnuncios = $this->getAnunciosFrontend();
    
        if ('backend' == $enviroment) {
            $this->listadoAnuncios = $this->getAnunciosBackend();
        }
    }

    public function getAnuncioCssClass() {
        $cssClass = $this->getTotalAnuncios() > 0 ? 'anuncios-on':'';
        return $cssClass;
    }


    public function getTotalAnuncios() {
        return count($this->listadoAnuncios);
    }

    public function getListadoAnuncios() {
        return $this->listadoAnuncios;
    }

    static function getAnunciosFrontend() {
        $anuncios = Anuncio::where('active_on_front', true)->where('activo', true)->get();
        return $anuncios;
    }

    static function getAnunciosBackend() {
        return Anuncio::where('active_on_backend', true)->where('activo', true)->get();
    }
        
}