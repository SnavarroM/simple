<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?=url('manager/anuncios')?>">Anuncios</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?=$title?></li>
    </ol>
</nav>

<form class="ajaxForm" method="post" action="<?= url('manager/anuncios/editar_form/' . $anuncio->id) ?>">
    {{csrf_field()}}
    <fieldset>
        <legend><?= $title ?></legend>
        <hr>
        <div class="validacion"></div>
        <label>Texto</label>
        <textarea class="form-control col-6" type="text" name="texto"><?= $anuncio->texto ?></textarea><br>
        <label>Tipo</label>
        <select name="tipo" class="form-control col-3">
            <option value="">Seleccionar ...</option>
            <option value="critica" <?= ($anuncio->tipo == 'critica') ? 'selected' : '' ?>>Crítica</option>
            <option value="informativa" <?= ($anuncio->tipo == 'informativa') ? 'selected' : '' ?>>Informativa</option>
            <option value="warning" <?= ($anuncio->tipo == 'warning') ? 'selected' : '' ?>>Warning</option>
        </select>
        <br>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="active_on_front" id="active_on_front" {{ $anuncio->active_on_front ? 'checked' : ''}}>
            <label class="form-check-label" for="active_on_front">
                Activo para frontend
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="active_on_backend" id="active_on_backend" {{ $anuncio->active_on_backend ? 'checked' : ''}}>
            <label class="form-check-label" for="active_on_backend">
                Activo para backend
            </label>
        </div>

    </fieldset>
    <br>
    <div class="form-actions">
        <button class="btn btn-primary" type="submit">Guardar</button>
        <a class="btn btn-light" href="<?= url('manager/anuncios') ?>">Cancelar</a>
    </div>
</form>
