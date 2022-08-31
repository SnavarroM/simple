@extends('layouts.tramite_informativo')

@section('content')
    <div class="container-fluid">
        <div class="row mt-5">
            <div class="col-md-12">
                <h4>Completar su perfil</h4>
                <hr>

                <form action="{{route('perfil.save')}}" method="post">
                    {{csrf_field()}}

                    <h5 class="col-12">
                    Ingrese el correo electrónico donde recibirá las notificaciones de sus trámites en los casos que corresponda.
                    </h5>
                    <h5 class="col-12">
                    Puede posteriormente modificar el correo electrónico en la sección Mi Cuenta.
                    </h5>
                    <br>
                    <div class="col-8">
                        @include('components.inputs.email_with_confirmation', ['key' => 'email'])
                    </div>

                    <hr>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <a href="{{route('home')}}" class="btn btn-light">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection