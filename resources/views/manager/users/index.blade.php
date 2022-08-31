<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page"><?=$title?></li>
    </ol>
</nav>

<div class="row">
    <div class="col-6">
        <a class="btn btn-primary" href="<?=url('manager/usuarios/editar')?>">Crear Usuario</a>
    </div>
    <div class="col-6 mb-4 mt-2">
        <form action="{{ route('manager.users.index') }}" class="form">
            <div class="row justify-content-end">
                <div class="col-5">
                    <input type="text" class="form-control" placeholder="Filtrar por email, nombre o apellidos"
                            name="user_search" value="{{ $user_search }}">
                </div>
                <div class="col-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="material-icons">search</i>
                        Buscar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table">
        <thead>
        <tr>
            <th>Estado</th>
            <th>Correo Electrónico</th>
            <th>Nombre</th>
            <th>Apellidos</th>
            <th>Cuenta</th>
            <th>Rol</th>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        @php
            $maxTimeOffLine = \Carbon\Carbon::now('America/Santiago')->subDays(env('OFFLINE_DAYS', 90));
        @endphp

        @foreach($usuarios as $usuario)
            @if(is_null($usuario->Cuenta->deleted_at))
                <tr>
                    <td>
                        <?php
                            $lastLoginDate = new \Carbon\Carbon($usuario->last_login);
                        ?>
                        @if($usuario->is_disabled || $lastLoginDate->isBefore($maxTimeOffLine))
                            <span class="badge badge-danger">Deshabilitado</span>
                        @else
                            <span class="badge badge-success">Habilitado</span>
                        @endif
                    </td>
                    <td>{{ $usuario->email }}</td>
                    <td>{{ $usuario->nombre }}</td>
                    <td>{{ $usuario->apellidos }}</td>
                    <td>{{ $usuario->Cuenta->nombre }}</td>
                    <td>{{ str_replace(",", ", ", $usuario->rol) }}</td>
                    <td style="width:250px;">
                        <a class="btn btn-primary" href="{{ url('manager/usuarios/editar/' . $usuario->id) }}">
                            <i class="material-icons">edit</i> Editar
                        </a>
                        <a class="btn btn-danger" href="{{ url('manager/usuarios/eliminar/' . $usuario->id) }}"
                        onclick="return confirm('¿Está seguro que desea desactivar a este usuario?')">
                            <i class="material-icons">delete</i> Desactivar
                        </a>
                    </td>
                </tr>
            @endif
        @endforeach
        </tbody>
    </table>
    @if(count($usuarios) <= 0)
        <div class="alert alert-primary" role="alert">
            No se encontraron usuarios.
        </div>
    @endif
    {{ $usuarios->appends(['user_search' => $user_search])->links('vendor.pagination.bootstrap-4') }}
</div>
