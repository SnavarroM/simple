<?php

use App\Helpers\Doctrine;

class Tarea extends Doctrine_Record
{

    function setTableDefinition()
    {
        $this->hasColumn('id');
        $this->hasColumn('identificador');
        $this->hasColumn('inicial');
        $this->hasColumn('es_final');
        $this->hasColumn('proceso_id');
        $this->hasColumn('nombre');
        $this->hasColumn('posx');
        $this->hasColumn('posy');
        $this->hasColumn('asignacion');                     //Modo de asignacion
        $this->hasColumn('asignacion_usuario');             //Id de usuario al que se le va a asignar en caso que modo de asignacion sea 'usuario'
        $this->hasColumn('asignacion_notificar');             //Indica si se le debe notificar via email al usuario que se le asigna esta tarea
        $this->hasColumn('almacenar_usuario');              //Se almacena el usuario o no
        $this->hasColumn('almacenar_usuario_variable');     //Nombre de la variable con que se debe almacenar
        $this->hasColumn('acceso_modo');                    //Quienes pueden acceder: grupos_usuarios, publico o registrados
        $this->hasColumn('grupos_usuarios');                //En caso que el modo de acceso sea grupos_usuarios, aqui se listan separados por coma los grupos.
        $this->hasColumn('activacion');                     //'si','no','entre_fechas'
        $this->hasColumn('activacion_inicio');              //Si es que la activacion es entre_fechas, esta seria la fecha de inicio
        $this->hasColumn('activacion_fin');                 //Si es que la activacion es entre_fechas, esta seria la fecha de fin
        $this->hasColumn('vencimiento');                    //Indica si tiene o no vencimiento.
        $this->hasColumn('vencimiento_valor');              //Entero que indica el valor del vencimiento.
        $this->hasColumn('vencimiento_unidad');             //String que indica la unidad del vencimiento. Ej: days, weeks, months, etc.
        $this->hasColumn('vencimiento_habiles');
        $this->hasColumn('vencimiento_notificar');          //Indica si se debe notificar en caso de que se acerque la fecha de vencimiento
        $this->hasColumn('vencimiento_notificar_dias');     //Indica desde cuantos dias de anticipacion se debe notificar la fecha de vencimiento
        $this->hasColumn('vencimiento_notificar_email');    //Cual es el email donde se debe notificar
        $this->hasColumn('paso_confirmacion');              //Boolean que indica si se debe incorporar una ultima pantalla de confirmacion antes de avanzar la tarea
        $this->hasColumn('previsualizacion');               //Texto de previsualizacion de la tarea al aparecer en las bandejas de entrada.
        $this->hasColumn('externa');                        //Indica si la tarea es externa para que pueda ejecutar servicios
        $this->hasColumn('exponer_tramite');                //Indica si la tarea es expuesta como servicio rest de simple
        $this->hasColumn('paso_confirmacion_titulo');       //Indica el titulo que se puede configurar para el paso de confirmaci??n
        $this->hasColumn('paso_confirmacion_contenido');    //Indica el contenido de texto que se puede configurar para el paso de confirmaci??n
        $this->hasColumn('paso_confirmacion_texto_boton_final'); //Indica el nombre del bot??n que se puede configurar para el paso de confirmaci??n
    }

    function setUp()
    {
        parent::setUp();

        $this->hasOne('Proceso', array(
            'local' => 'proceso_id',
            'foreign' => 'id'
        ));

        $this->hasMany('Etapa as Etapas', array(
            'local' => 'id',
            'foreign' => 'tarea_id'
        ));

        $this->hasMany('Conexion as ConexionesOrigen', array(
            'local' => 'id',
            'foreign' => 'tarea_id_origen'
        ));

        $this->hasMany('Conexion as ConexionesDestino', array(
            'local' => 'id',
            'foreign' => 'tarea_id_destino'
        ));

        $this->hasMany('Paso as Pasos', array(
            'local' => 'id',
            'foreign' => 'tarea_id',
            'orderBy' => 'orden'
        ));

        $this->hasMany('Evento as Eventos', array(
            'local' => 'id',
            'foreign' => 'tarea_id'
        ));

        $this->hasMany('GrupoUsuarios as GruposUsuarios', array(
            'local' => 'tarea_id',
            'foreign' => 'grupo_usuarios_id',
            'refClass' => 'TareaHasGrupoUsuarios'
        ));

        $this->hasMany('EventoExterno as EventosExternos', array(
            'local' => 'id',
            'foreign' => 'tarea_id'
        ));

    }

    public function hasGrupoUsuarios($grupo_id)
    {
        foreach ($this->GruposUsuarios as $g)
            if ($g->id == $grupo_id)
                return true;

        return false;
    }


    //Obtiene el listado de usuarios que tienen acceso a esta tarea y que esten disponibles (no en vacaciones).
    //$etapa_id indica la etapa hasta la cual se debe calcular la variable para obtener el grupo de usuario.
    public function getUsuarios($etapa_id)
    {
        /*if ($this->acceso_modo == 'publico')
            return Doctrine::getTable('Usuario')->findByVacaciones(0);
        else if ($this->acceso_modo == 'registrados')
            return Doctrine::getTable('Usuario')->findByRegistradoAndVacaciones(1, 0);
        else if ($this->acceso_modo == 'claveunica')
            return Doctrine::getTable('Usuario')->findByOpenIdAndVacaciones(1, 0);*/


        //Convertimos las variables e ids, separados por coma, en una arreglo de grupos de usuarios.
        $grupos_arr = array(-1);
        $grupos = explode(',', $this->grupos_usuarios);
        foreach ($grupos as $key => $g) {
            $r = new Regla($g);
            $var = $r->evaluar($etapa_id);
            if (is_numeric($var))
                $grupos_arr[] = $var;
            else if (is_array($var))
                foreach ($var as $v)
                    if (is_numeric($v))
                        $grupos_arr[] = $v;
        }
        return Doctrine_Query::create()
            ->from('Usuario u, u.GruposUsuarios g, g.Cuenta c')
            ->where('u.vacaciones = 0')
            ->andWhere('c.id = ?', $this->Proceso->Cuenta->id)
            ->andWhereIn('g.id', $grupos_arr)
            ->execute();
    }

    //Obtiene el listado de usuarios que tienen acceso a esta tarea y que esten disponibles (no en vacaciones).
    //Ademas, deben pertenecer a alguno de los grupos de usuarios definidos en la cuenta
    public function getUsuariosFromGruposDeUsuarioDeCuenta($etapa_id)
    {
        $query = Doctrine_Query::create()
            ->from('Usuario u, u.GruposUsuarios g, g.Cuenta c')
            ->where('u.vacaciones = 0')
            ->andWhere('c.id = ?', $this->Proceso->Cuenta->id);

        if ($this->acceso_modo == 'publico' || $this->acceso_modo == 'anonimo') {
            ;
        } else if ($this->acceso_modo == 'registrados') {
            $query->andWhere('u.registrado = 1');
        } else if ($this->acceso_modo == 'claveunica') {
            $query->andWhere('u.open_id = 1');
        } else {
            //Convertimos las variables e ids, separados por coma, en una arreglo de grupos de usuarios.
            $grupos_arr = array(-1);
            $grupos = explode(',', $this->grupos_usuarios);
            foreach ($grupos as $key => $g) {
                $r = new Regla($g);
                $var = $r->evaluar($etapa_id);
                if (is_numeric($var))
                    $grupos_arr[] = $var;
                else if (is_array($var))
                    foreach ($var as $v)
                        if (is_numeric($v))
                            $grupos_arr[] = $v;
            }
            $query->andWhereIn('g.id', $grupos_arr);
        }

        return $query->execute();
    }


    //Obtiene el ultimo usuario que fue a asignado a esta tarea dentro del tramite tramite_id
    public function getUltimoUsuarioAsignado($proceso_id)
    {
        return Doctrine_Query::create()
            ->from('Usuario u, u.Etapas e, e.Tarea t, e.Tramite.Proceso p')
            ->where('t.id = ? AND p.activo=1 AND p.id = ?', array($this->id, $proceso_id))
            ->orderBy('e.created_at DESC')
            ->fetchOne();
    }

    public function setConexionesFromArray($conexiones_array)
    {
        //Limpiamos la lista antigua
        foreach ($this->ConexionesOrigen as $key => $c)
            unset($this->ConexionesOrigen[$key]);

        //Agregamos los nuevos
        if (is_array($conexiones_array)) {
            $tipo = $conexiones_array[0]['tipo'];     //Todas deben ser del mismo tipo si vienen de un origen
            foreach ($conexiones_array as $key => $p) {
                $conexion = new Conexion();
                $conexion->tipo = $tipo;
                $conexion->tarea_id_destino = $p['tarea_id_destino'] ? $p['tarea_id_destino'] : null;
                $conexion->regla = isset($p['regla']) ? $p['regla'] : null;
                $this->ConexionesOrigen[] = $conexion;
            }
        }
    }

    public function setPasosFromArray($pasos_array)
    {
        //Limpiamos la lista antigua
        foreach ($this->Pasos as $key => $val)
            unset($this->Pasos[$key]);

        //Agregamos los nuevos
        if (is_array($pasos_array)) {
            foreach ($pasos_array as $key => $p) {

                //Guardamos el paso solamente si el formulario_id corresponde a un formulario existente.
                $formulario_id = null;
                if(array_key_exists('formulario_id', $p)){
                    foreach ($this->Proceso->Formularios as $f)
                        if ($f->id == $p['formulario_id'])
                            $formulario_id = $p['formulario_id'];

                    if ($formulario_id) {
                        $paso = new Paso();
                        if(isset($p['id']))
                            $paso->id = $p['id'];
                        $paso->orden = $key;
                        $paso->regla = $p['regla'];
                        $paso->modo = $p['modo'];
                        $paso->formulario_id = $formulario_id;
                        $this->Pasos[] = $paso;
                    }
                }
            }
        }
    }

    public function setGruposUsuariosFromArray($grupos_array)
    {
        if (is_array($grupos_array))
            $this->grupos_usuarios = implode(',', $grupos_array);
        else
            $this->grupos_usuarios = '';
    }

    public function setEventosFromArray($eventos_array)
    {
        //Limpiamos la lista antigua
        foreach ($this->Eventos as $key => $val)
            unset($this->Eventos[$key]);

        //Agregamos los nuevos
        if (is_array($eventos_array)) {
            ksort($eventos_array);
            foreach ($eventos_array as $key => $p) {
                //Seteamos el paso_id solamente si el paso es parte de esta tarea.
                $paso_id = null;
                foreach ($this->Pasos as $paso)
                    if ($paso->id == $p['paso_id'])
                        $paso_id = $p['paso_id'];

                $evento_externo_id = null;
                foreach ($this->EventosExternos as $evento_externo)
                    if ($evento_externo->id == $p['paso_id'])
                        $evento_externo_id = $p['paso_id'];

                $evento = new Evento();
                $evento->regla = $p['regla'];
                $evento->instante = $p['instante'];
                $evento->accion_id = $p['accion_id'];
                $evento->paso_id = $paso_id;
                $evento->evento_externo_id = $evento_externo_id;
                $c_asociado = str_replace('@', '', $p['campo_asociado']);
                if(trim($c_asociado) != '')
                    $campo_asociado = '@@'.$c_asociado;
                else
                    $campo_asociado = '';
                $evento->campo_asociado = $campo_asociado;
                $this->Eventos[] = $evento;

            }
        }
    }


    //Setea esta tarea como final
    public function setFinal($final)
    {
        if ($final && !$this->final) {
            //Limpiamos las conexiones antiguas
            foreach ($this->ConexionesOrigen as $key => $c)
                unset($this->ConexionesOrigen[$key]);

            $conexion = new Conexion();
            $conexion->tipo = 'secuencial';
            $conexion->tarea_id_destino = null;
            $conexion->regla = null;
            $this->ConexionesOrigen[] = $conexion;
            $this->es_final = 1;
        } else if (!$final && $this->final) {
            //Limpiamos las conexiones antiguas
            foreach ($this->ConexionesOrigen as $key => $c)
                unset($this->ConexionesOrigen[$key]);
            $this->es_final = 0;
        }
    }


    //Retorna true si es una tarea final.
    public function getFinal()
    {
        if ($this->es_final || ($this->ConexionesOrigen->count() == 1 && $this->ConexionesOrigen[0]->tipo == 'secuencial' && !$this->ConexionesOrigen[0]->tarea_id_destino))
            return true;

        return false;
    }

    //Indica si esta tarea se encuentra activa. Es decir, se puede ejecutar.
    public function activa()
    {
        if ($this->activacion == 'no')
            return FALSE;

        if ($this->activacion == 'entre_fechas') {
            $fecha_actual = \Carbon\Carbon::today();
            $fecha_activacion_inicio = \Carbon\Carbon::parse($this->activacion_inicio)->format('Y-m-d H:i:s');
            $fecha_activacion_fin = \Carbon\Carbon::parse($this->activacion_fin)->format('Y-m-d H:i:s');
            if (isset($fecha_activacion_inicio) && $fecha_actual->lessThan($fecha_activacion_inicio))
                return FALSE;
            if (isset($fecha_activacion_fin) && $fecha_actual->greaterThan($fecha_activacion_fin))
                return FALSE;
        }

        return TRUE;
    }

    public function setActivacionInicio($date)
    {
        if ($date)
            $this->_set('activacion_inicio', date('Y-m-d', strtotime($date)));
        else
            $this->_set('activacion_inicio', NULL);
    }

    public function setActivacionFin($date)
    {
        if ($date)
            $this->_set('activacion_fin', date('Y-m-d', strtotime($date)));
        else
            $this->_set('activacion_fin', NULL);
    }

    public function canUsuarioIniciarla($usuario_id)
    {
        $usuario = Doctrine::getTable('Usuario')->find($usuario_id);

        if ($this->acceso_modo == 'publico' || $this->acceso_modo == 'anonimo')
            return true;

        if ($this->acceso_modo == 'claveunica' && $usuario->open_id)
            return true;

        if ($this->acceso_modo == 'registrados' && $usuario->registrado)
            return true;

        if ($this->acceso_modo == 'grupos_usuarios') {
            $grupos_arr = explode(',', $this->grupos_usuarios);
            $u = Doctrine_Query::create()
                ->from('Usuario u, u.GruposUsuarios g')
                ->where('u.id = ?', $usuario->id)
                ->andWhereIn('g.id', $grupos_arr)
                ->fetchOne();
            if ($u)
                return true;
        }

        return false;
    }

    public function toPublicArray()
    {
        $publicArray = array(
            'id' => (int)$this->id,
            'nombre' => $this->nombre,
        );

        return $publicArray;
    }

    public function setEventosExternosFromArray($eventos_externos_array)
    {
        //Limpiamos la lista antigua
        foreach ($this->EventosExternos as $key => $val)
            unset($this->EventosExternos[$key]);

        //Agregamos los nuevos
        if (is_array($eventos_externos_array)) {
            foreach ($eventos_externos_array as $key => $p) {
                $evento_externo = new EventoExterno();
                $evento_externo->id = $p['id'];
                $evento_externo->nombre = $p['nombre'];
                $evento_externo->metodo = $p['metodo'] == 'Ninguno' ? NULL : $p['metodo'];
                $evento_externo->url = $p['url'];
                $evento_externo->mensaje = trim($p['mensaje']);
                $evento_externo->regla = $p['regla'] ? trim($p['regla']) : NULL;
                $evento_externo->opciones = $p['opciones'] ? trim($p['opciones']) : NULL;
                $this->EventosExternos[] = $evento_externo;
            }
        }
    }
}
