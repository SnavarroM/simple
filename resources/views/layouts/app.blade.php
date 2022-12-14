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
    <link href="{{ asset('css/'.$estilo.'') }} " rel="stylesheet">

    <meta name="google" content="notranslate"/>

    <!-- fav and touch icons -->
    <link rel="shortcut icon" href="{{ asset(\Cuenta::getAccountFavicon()) }}">
    @yield('css')
    @yield('script_header')

    <style type="text/css">{{ $personalizacion }}</style>

    @if(env('RECAPTCHA3_SITE_KEY') != null)
        <script src="https://www.google.com/recaptcha/enterprise.js?render={{ env('RECAPTCHA3_SITE_KEY') }}"></script>
    @endif

</head>
<body>

@php
    $anunciosFront = new \App\Helpers\AnuncioHelper('frontend');
@endphp

@includeWhen((count($anunciosFront->getTotalAnuncios()) > 0), 'layouts.anuncios', ['anuncios' => $anunciosFront->getListadoAnuncios() ])

<div id="app" class="{{ $anunciosFront->getAnuncioCssClass() }}">

    @include($dominio_header)
    <div class="main-container container pb-5">
        @yield('content')
        {!! isset($content) ? $content : '' !!}
    </div>
    @include($dominio_footer, ['metadata' => $metadata_footer])
</div>

<!-- Scripts -->
<script src="{{ asset('js/app.js') }}"></script>

@yield('script')
</body>
</html>
