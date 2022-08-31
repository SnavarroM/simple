<div style="width:100%;" class="mt-4">
    @if ($errors->has('g-recaptcha-response'))
        <div class="alert alert-danger" role="alert">
        {{ $errors->first('g-recaptcha-response') }}
        </div>
    @endif
</div>