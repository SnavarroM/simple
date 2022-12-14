<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('layouts.ga')
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{  \Cuenta::seo_tags()->title }}</title>
    <meta name="description" content="{{ \Cuenta::seo_tags()->description }}">
    <meta name="keywords" content="{{ \Cuenta::seo_tags()->keywords }}">

    <!-- Styles -->
    <link href="{{ asset('css/'. getCuenta()['estilo']) }} " rel="stylesheet">

    <meta name="google" content="notranslate"/>

    <link rel="shortcut icon" href="{{ asset(\Cuenta::getAccountFavicon()) }}">
    <link href="{{ asset('css/component-chosen.css') }}" rel="stylesheet">

    @yield('css')

    <script src="{{ asset('js/app.js') }}"></script>
    <script type="text/javascript">
        var site_url = "";
        var base_url = "";

        var onloadCallback = function () {
            if ($('#form_captcha').length) {
                grecaptcha.enterprise.render("form_captcha", {
                    sitekey: "{{env('RECAPTCHA_SITE_KEY')}}"
                });
            }
        };

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    <style type="text/css">{{ getCuenta()['personalizacion'] }}</style>
</head>
<body class="h-100">

@php
    $anunciosFront = new \App\Helpers\AnuncioHelper('backend');
@endphp

@includeWhen((count($anunciosFront->getTotalAnuncios()) > 0), 'layouts.anuncios', ['anuncios' => $anunciosFront->getListadoAnuncios() ])

<div id="app" class="h-100 d-flex flex-column {{ $anunciosFront->getAnuncioCssClass() }}" >

@include(getCuenta()['header'])
    <div class="main-container container pb-5">
        <div class="row justify-content-md-center">
            <div class="col-xs-12 col-md-8">
                @include('components.messages')
                @yield('content')
                {!! isset($content) ? $content : '' !!}
            </div>

        </div>
    </div>
    @include(getCuenta()['footer'], ['metadata' => json_decode(getCuenta()['metadata'])])
</div>

@stack('script')

<!-- Scripts -->
<script src="https://www.google.com/recaptcha/enterprise.js?onload=onloadCallback&render=explicit&hl=es"></script>
<script src="{{ asset('js/helpers/grilla_datos_externos.js') }}"></script>

<script>
    $(function () {
        $(document).ready(function(){
            $('#cierreSesion').click(function (){
                $.ajax({ url: 'https://accounts.claveunica.gob.cl/api/v1/accounts/app/logout', dataType: 'script' }) .always(function() {
                    window.location.href = '/logout';
                });
            });
        });
    });
</script>
</body>
</html>
