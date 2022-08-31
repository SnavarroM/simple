@extends('layouts.tramite_informativo')

@section('content')
    <div class="content-previsualizacion mt-lg-4 mb-lg-5">
        <h1 class="title">{{ $proceso->ficha_titulo }}</h1>
        <hr>
        <p class="mb-lg-5" style="white-space: pre-line;">
            {!! $proceso->ficha_contenido !!}
        </p>
        <div class="row justify-content-between">
            <div class="col-md-3">
                <a href="{{ url('home') }}" class="btn btn-light btn-block">Volver</a>
            </div>
            <div class="col-md-3">
                <a href="{{
                        $proceso->canUsuarioIniciarlo(Auth::user()->id) ? route('tramites.iniciar',  [$proceso->id]) :
                        (
                            $proceso->getTareaInicial()->acceso_modo == 'claveunica' ? route('login.claveunica').'?redirect='.route('tramites.iniciar', [$proceso->id]) :
                            route('login').'?redirect='.route('tramites.iniciar', $proceso->id)
                        )
                        }}"
                class="btn btn-primary {{$proceso->getTareaInicial()->acceso_modo == 'claveunica'? 'btn-cu btn-m btn-fw btn-color-estandar' : 'btn-block'}}">
                    @if ($proceso->canUsuarioIniciarlo(Auth::user()->id))
                        Iniciar trámite
                    @else
                        @if ($proceso->getTareaInicial()->acceso_modo == 'claveunica')
                            <span class="cl-claveunica"></span>
                            <span class="texto">{{__('auth.login_claveunica')}}</span>
                        @else
                            <i class="material-icons">person</i> Autenticarse
                            <span class="float-right">&#8594;</span>
                        @endif
                    @endif
                </a>
            </div>
        </div>
    </div>
@endsection