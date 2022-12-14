<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{  \Cuenta::seo_tags()->title .' - Autenticaci&oacute;n' }}</title>
    <meta name="description" content="{{ \Cuenta::seo_tags()->description }}">
    <meta name="keywords" content="{{ \Cuenta::seo_tags()->keywords }}">
    
    <!-- Styles -->
    <link href="{{ asset('css/backend.css') }}" rel="stylesheet">

    <meta name="google" content="notranslate"/>

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="{{asset('/img/favicon.png')}}">

    @yield('css')
    @if(env('RECAPTCHA_SITE_KEY') != null)
        @if($errors->any())
        <script type="text/javascript">
            var onloadCallback = function() {
                grecaptcha.enterprise.render('token_recaptcha', {
                    'sitekey' : "{{ env('RECAPTCHA_SITE_KEY') }}",
                    'theme' : 'light'
                });
            };
        </script>
        @endif
    @endif

</head>
<body>

@php
    $anunciosBackend = new \App\Helpers\AnuncioHelper('backend');
@endphp

@includeWhen((count($anunciosBackend->getTotalAnuncios()) > 0), 'layouts.anuncios', ['anuncios' => $anunciosBackend->getListadoAnuncios() ])

<div class="container">
    <div class="row justify-content-md-center mt-5">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">BACKEND {{__('auth.login')}}</div>
                <div class="card-body">
                    <form class="form-horizontal" method="POST" action="{{ route('backend.login.submit') }}" id="form-login">
                        {{ csrf_field() }}
                        <div class="form-group row">
                            <label for="email"
                                   class="col-lg-4 col-form-label text-lg-right">{{__('auth.email')}}</label>

                            <div class="col-lg-6">
                                <input
                                        id="email"
                                        type="email"
                                        class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                        name="email"
                                        value="{{ old('email') }}"
                                        required
                                        autofocus
                                >

                                @if ($errors->has('email'))
                                    <div class="invalid-feedback">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password"
                                   class="col-lg-4 col-form-label text-lg-right">{{__('auth.password')}}</label>

                            <div class="col-lg-6">
                                <input
                                        id="password"
                                        type="password"
                                        class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                                        name="password"
                                        required
                                >

                                @if ($errors->has('password'))
                                    <div class="invalid-feedback">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-lg-6 offset-lg-4">
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input"
                                               name="remember" {{ old('remember') ? 'checked' : '' }}> {{__('auth.remember_me')}}
                                    </label>
                                </div>
                            </div>
                        </div>

                        @if($errors->any())
                            <div class="form-group row">
                                <div class="col-lg-6 offset-lg-4">
                                <div id="token_recaptcha"></div>
                                @if (env('RECAPTCHA_SITE_KEY') != null)
                                    @include('shared.captcha')
                                @endif
                                </div>
                            </div>
                        @endif


                        <div class="form-group row">
                            <div class="col-lg-8 offset-lg-4">
                                <button type="submit" class="btn btn-primary">
                                    {{__('auth.login')}}
                                </button>

                                <a class="btn btn-link" href="{{ route('backend.password.email') }}">
                                    {{__('auth.forgot_password')}}
                                </a>
                            </div>
                        </div>
                    </form>
                    @if(env('RECAPTCHA_SITE_KEY') != null)
                        @if($errors->any())
                            <script src="https://www.google.com/recaptcha/enterprise.js?onload=onloadCallback&render=explicit"
                                async defer>
                            </script>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>