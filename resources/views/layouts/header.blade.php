<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="" href="{{ url('/') }}">
            <div class="media">
                <img class="align-self-center mr-3 logo"
                    src="{{Cuenta::cuentaSegunDominio() != 'localhost' ? Cuenta::cuentaSegunDominio()->logoADesplegar : asset('assets/img/logo.png') }}"
                    alt="{{Cuenta::cuentaSegunDominio() != 'localhost' ? Cuenta::cuentaSegunDominio()->nombre_largo : env('APP_NAME') }}"/>
                <div class="media-body align-self-center name-institution">
                    <h5 class="mt-1">{{Cuenta::cuentaSegunDominio() != 'localhost' ? Cuenta::cuentaSegunDominio()->nombre_largo : ''}}</h5>
                    <p>{{Cuenta::cuentaSegunDominio() != 'localhost' ? Cuenta::cuentaSegunDominio()->mensaje : ''}}</p>
                </div>
            </div>
        </a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
            <ul class="navbar-nav mt-2">
                @if (Auth::guest() || !Auth::user()->registrado)
                    <li class="nav-item login-default mr-3">
                        <a href="{{route('login')}}" class="nav-link">
                            Ingreso funcionarios
                        </a>
                    </li>
                    <li class="nav-item login">
                        <a href="{{route('login.claveunica')}}" class="btn-cu btn-m btn-color-estandar">
                            <span class="cl-claveunica"></span>
                            <span class="texto">{{__('auth.login_claveunica')}}</span>
                        </a>
                    </li>
                @else
                    <li class="nav-item dropdown login">
                        <a href="#" class="nav-link dropdown-toggle" id="navbarDropdownMenuLink"
                            data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            @if(Auth::user()->open_id===1)
                                <span class="icon-claveunica"></span>
                            @endif
                            Bienvenido/a, {{ Auth::user()->nombres }}
                        </a>
                        <div class="dropdown-menu dropdown-menu-right login" aria-labelledby="navbarDropdownMenuLink">
                            @if (Auth::user()->open_id===1)
                                <a href="{{ route('perfil') }}" class="dropdown-item" >
                                    <i class="material-icons">exit_to_app</i> {{__('auth.my_account')}}
                                </a>
                            @endif
                            <a href="{{ route('logout') }}" class="dropdown-item" id="cierreSesion" >
                                <i class="material-icons">exit_to_app</i> {{__('auth.close_session')}}
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                {{ csrf_field() }}
                            </form>
                        </div>
                    </li>
                @endif
            </ul>
            <ul class="simple-list-menu mt-1 list-group d-block d-sm-none">
                <a class="list-group-item list-group-item-action {{isBandejaCategoryActive('home')}}"
                    href="{{route('home')}}">
                    <i class="material-icons">insert_drive_file</i> Trámites disponibles
                </a>
                @if(Auth::user() && Auth::user()->registrado)
                    @php
                        $npendientes = \App\Helpers\Doctrine::getTable('Etapa')
                            ->findPendientes(Auth::user()->id, Cuenta::cuentaSegunDominio())->count();
                        $nsinasignar = count(\App\Helpers\Doctrine::getTable('Etapa')->findSinAsignar(Auth::user()->id, Cuenta::cuentaSegunDominio()));
                        $nparticipados = \App\Helpers\Doctrine::getTable('Tramite')->findParticipadosALL(Auth::user()->id, Cuenta::cuentaSegunDominio())->count();
                    @endphp
                    <a class="list-group-item list-group-item-action {{isBandejaCategoryActive('inbox')}}"
                        href="{{route('stage.inbox')}}">
                        <i class="material-icons">inbox</i> Bandeja de Entrada ({{$npendientes}})
                    </a>
                    @if ($nsinasignar)
                        <a class="list-group-item list-group-item-action {{isBandejaCategoryActive('sin_asignar')}}"
                            href="{{route('stage.unassigned')}}">
                            <i class="material-icons">assignment</i> Sin asignar @if ($nsinasignar) <img src="{{ asset('/img/cl-i-bell.png') }}"> @endif
                        </a>
                    @endif
                    <a class="list-group-item list-group-item-action {{isBandejaCategoryActive('historial')}}"
                        href="{{route('tramites.participados')}}">
                        <i class="material-icons">history</i> Historial de Trámites
                    </a>
                @endif
            </ul>
        </div>

    </div>
</nav>
