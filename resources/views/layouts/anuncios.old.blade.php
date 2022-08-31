@if(!is_null((new \App\Helpers\Utils())->get_anuncio_activo()))
    <div class="anuncios {{(new \App\Helpers\Utils())->get_anuncio_activo()->tipo}}">
        <p class="text-center" >
            <h1>{{ isset($origin) ? 'Si':'No'}}</h1>
            <img src="{{ asset('img/anuncios/alert-warning.svg') }}" class="icono-anuncio">
            {{(new \App\Helpers\Utils())->get_anuncio_activo()->texto}}
        </p>
    </div>
@endif



@if
@else
@endif