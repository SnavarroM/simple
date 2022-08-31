<div class="form-group">
    <label for="{{$key}}">Ingresar Correo Electrónico</label>
    <input value="{{ !empty(Auth::user()->email) ? Auth::user()->email : '' }}" name="{{$key}}" id="{{$key}}"
           class="form-control{{ $errors->has($key) ? ' is-invalid' : '' }}">
    @if ($errors->has($key))
        <div class="invalid-feedback">
            <strong>{{ $errors->first($key) }}</strong>
        </div>
    @endif
</div>
<div class="form-group">
    <label for="{{$key}}_confirmation">Confirmar Correo Electrónico</label>
    <input value="{{ !empty(Auth::user()->email) ? Auth::user()->email : '' }}" name="{{$key}}_confirmation" id="{{$key}}_confirmation"
           class="form-control{{ $errors->has("{$key}_confirmation") ? ' is-invalid' : '' }}">
    @if ($errors->has("{$key}_confirmation"))
        <div class="invalid-feedback">
            <strong>{{ $errors->first("{$key}_confirmation") }}</strong>
        </div>
    @endif
</div>