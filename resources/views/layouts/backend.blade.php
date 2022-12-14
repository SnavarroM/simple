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
    <link rel="stylesheet" href="{{asset('css/bootstrap-timepicker.css')}}">

    <link href="{{ asset('css/backend.css') }}" rel="stylesheet">

    <meta name="google" content="notranslate"/>

    <!-- fav and touch icons -->
    <link rel="shortcut icon" href="{{ asset(\Cuenta::getAccountFavicon()) }}">
    <link href="{{ asset('css/component-chosen.css') }}" rel="stylesheet">

    @yield('css')
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
    </script>
    <script src="{{ asset('js/backend.js') }}"></script>

</head>
<body >

@php
    $anunciosBackend = new \App\Helpers\AnuncioHelper('backend');
@endphp

@includeWhen((count($anunciosBackend->getTotalAnuncios()) > 0), 'layouts.anuncios', ['anuncios' => $anunciosBackend->getListadoAnuncios() ])

<div id="app" class="{{ $anunciosBackend->getAnuncioCssClass() }}">
    @include('layouts.backend.header')
    @include('components.messages')

    @yield('content')

    @include('layouts.footer', [
        'metadata' => \Cuenta::getAccountMetadata(),
        'footerCss' => 'footer-admin'
    ])
</div>

<!-- Scripts -->
@yield('script')
<script src="https://www.google.com/recaptcha/enterprise.js?onload=onloadCallback&render=explicit&hl=es"></script>
<script src="{{ asset('js/helpers/grilla_datos_externos.js') }}"></script>
</body>
</html>
