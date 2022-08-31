<div class="anuncios-list" id="listado-anuncios">
    @foreach($anuncios as $anuncio)
        <div class="anuncios {{ $anuncio->tipo }}">
            <p class="text-center" >
                <img src="{{ asset('img/anuncios/alert-warning.svg') }}" class="icono-anuncio">
                {{ $anuncio->texto }}
            </p>
        </div>
    @endforeach
</div>