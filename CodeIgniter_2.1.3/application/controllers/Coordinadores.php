<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH.'controllers/Master.php'; //Carga el controlador master

class Coordinadores extends MasterManteka {
	
	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	public function index() //Esto hace que el index sea la vista que se desee
	{
		$this->verCoordinadores();
	}

	public function verCoordinadores()
	{
		$this->load->model('model_coordinadores');
		$ListaObjetosCoordinadores = $this->model_coordinadores->ObtenerTodosCoordinadores();
		
		$resultados = array();
		foreach ($ListaObjetosCoordinadores as $coordinador ) {
			$array_modulos = $this->model_coordinadores->GetModulos($coordinador['id']);
			$coordinador['modulos'] = "";
			$coordinador['secciones'] = "";
			
			foreach ($array_modulos as $mod) {
				$array_secciones = $this->model_coordinadores->GetSeccion($mod['COD_MODULO_TEM']);
				foreach ($array_secciones as $sec) {
					if($coordinador['secciones']=="")
						$coordinador['secciones']= $sec['COD_SECCION'];
					else
						$coordinador['secciones']= $coordinador['secciones']. " , ".$sec['COD_SECCION'];
				}

				if($coordinador['modulos']=="")
					$coordinador['modulos'] = $mod['COD_MODULO_TEM'];
				else
					$coordinador['modulos'] = $coordinador['modulos'] ." , ". $mod['COD_MODULO_TEM'];
			}
			
			array_push($resultados, $coordinador);
		}
		$datos_plantilla['listado_coordinadores'] = $resultados;
		
		

		$subMenuLateralAbierto = 'verCoordinadores'; //Para este ejemplo, los informes no tienen submenu lateral
		$muestraBarraProgreso = FALSE; //Indica si se muestra la barra que dice anterior - siguiente
		$tipos_usuarios_permitidos = array();
		$tipos_usuarios_permitidos[0] = TIPO_USR_COORDINADOR;
		$this->cargarTodo("Docentes", "cuerpo_coordinadores_ver", "barra_lateral_profesores", $datos_plantilla, $tipos_usuarios_permitidos, $subMenuLateralAbierto, $muestraBarraProgreso);

	}
    
    public function agregarCoordinadores()
    {
		if ($_SERVER['REQUEST_METHOD'] == 'POST'){
			$this->load->model('model_coordinadores');
			$this->model_coordinadores->agregarCoordinador($_POST['nombre'],$_POST['rut'],md5($_POST['contrasena']),$_POST['correo1'],$_POST['correo2'],$_POST['fono']);

			$datos_plantilla["titulo_msj"] = "Coordinador agregado";
			$datos_plantilla["cuerpo_msj"] = "El nuevo coordinador fue agregado correctamente.";
			$datos_plantilla["tipo_msj"] = "alert-success";
			$datos_plantilla["redirecTo"] = 'Coordinadores/agregarCoordinadores';
			$datos_plantilla["nombre_redirecTo"] = "Agregar Coordinador";
			$datos_plantilla["redirectAuto"] = TRUE;
			$tipos_usuarios_permitidos = array(); $tipos_usuarios_permitidos[0] = TIPO_USR_COORDINADOR;
			$this->cargarMsjLogueado($datos_plantilla, $tipos_usuarios_permitidos);

		}
		else {
			
			$datos_cuerpo = array(); //Cambiarlo por datos que provengan de los modelos para pasarsela a su vista_cuerpo
			//$datos_cuerpo["listado_de_algo"] = model->consultaSQL(); //Este es un ejemplo

			/* Se setea que usuarios pueden ver la vista, estos pueden ser las constantes: TIPO_USR_COORDINADOR y TIPO_USR_PROFESOR
			* se deben introducir en un array, para luego pasarlo como parámetro al método cargarTodo()
			*/
			$subMenuLateralAbierto = 'agregarCoordinadores'; //Para este ejemplo, los informes no tienen submenu lateral
			$muestraBarraProgreso = FALSE; //Indica si se muestra la barra que dice anterior - siguiente
			$tipos_usuarios_permitidos = array(); $tipos_usuarios_permitidos[0] = TIPO_USR_COORDINADOR;
			$this->cargarTodo("Docentes", "cuerpo_coordinadores_crear", "barra_lateral_profesores", $datos_cuerpo, $tipos_usuarios_permitidos, $subMenuLateralAbierto, $muestraBarraProgreso);
	
		}
    }
    
    public function editarCoordinadores()
    {
    	$rut = $this->session->userdata('rut'); //Se comprueba si el usuario tiene sesi?n iniciada
		if ($rut == FALSE) {
			redirect('/Login/', ''); //Se redirecciona a login si no tiene sesi?n iniciada
		}
		$datos_plantilla["rut_usuario"] = $this->session->userdata('rut');
		$datos_plantilla["nombre_usuario"] = $this->session->userdata('nombre_usuario');
		$datos_plantilla["tipo_usuario"] = $this->session->userdata('tipo_usuario');
		$datos_plantilla["title"] = "ManteKA";
		$datos_plantilla["head"] = $this->load->view('templates/head', $datos_plantilla, true);
		$datos_plantilla["barra_usuario"] = $this->load->view('templates/barra_usuario', $datos_plantilla, true);
		$datos_plantilla["banner_portada"] = $this->load->view('templates/banner_portada', '', true);
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST'){
			$this->load->model('model_coordinadores');
			
            //$this->model_coordinadores->modificarCoordinador($nombreActual,$rutActual,$nombreNuevo,$rutNuevo,$correo1Nuevo,$correo2Nuevo,$telefonoNuevo,$idNuevo,$tipoNuevo);
			if($_POST['contrasena']){
				$this->model_coordinadores->modificarPassword($_POST['id'],$_POST['contrasena']);
			}
			$this->model_coordinadores->modificarCoordinador($_POST['id'],$_POST['nombre'],$_POST['correo1'],$_POST['correo2'],$_POST['fono']);
			$datos_plantilla["titulo_msj"] = "Coordinador editado.";
			$datos_plantilla["cuerpo_msj"] = "El coordinador fue editado correctamente.";
			$datos_plantilla["tipo_msj"] = "success-error";
			$datos_plantilla["redirecTo"] = 'Coordinadores/editarCoordinadores';
			$datos_plantilla["nombre_redirecTo"] = "Editar Coordinador";
			$datos_plantilla["redirectAuto"] = TRUE;
			$this->load->view('templates/big_msj_deslogueado', $datos_plantilla);
			
		}else{
			$datos_plantilla["menuSuperiorAbierto"] = "Docentes";
			$datos_plantilla["barra_usuario"] = $this->load->view('templates/barra_usuario', $datos_plantilla, true);
			$datos_plantilla["menu_superior"] = $this->load->view('templates/menu_superior', $datos_plantilla, true);
			$datos_plantilla["barra_navegacion"] = $this->load->view('templates/barra_navegacion', '', true);
			$datos_plantilla["mostrarBarraProgreso"] = FALSE; //Cambiar en caso que no se necesite la barra de progreso
			$datos_plantilla["barra_progreso_atras_siguiente"] = $this->load->view('templates/barra_progreso_atras_siguiente', $datos_plantilla, true);
			$datos_plantilla["footer"] = $this->load->view('templates/footer', '', true);

			$this->load->model('model_coordinadores');
			$datos_cuerpo_central['listado_coordinadores'] = $this->model_coordinadores->ObtenerTodosCoordinadores();

			$datos_plantilla["cuerpo_central"] = $this->load->view('cuerpo_coordinadores_modificar', $datos_cuerpo_central, true); //Esta es la linea que cambia por cada controlador
			//Ahora se especifica que vista estÃ¡ abierta para mostrar correctamente el menu lateral
			$datos_plantilla["subVistaLateralAbierta"] = "editarCoordinadores"; //Usen el mismo nombre de la secciÃ³n donde debe estar
			$datos_plantilla["barra_lateral"] = $this->load->view('templates/barras_laterales/barra_lateral_profesores', $datos_plantilla, true); //Esta linea tambi?n cambia seg?n la vista como la anterior
			$this->load->view('templates/template_general', $datos_plantilla);
		}
		
    }

    public function borrarCoordinadores()
    {

		if ($_SERVER['REQUEST_METHOD'] == 'POST'){
			$this->load->model('model_coordinadores');
			$this->model_coordinadores->borrarCoordinadores(explode(',', str_replace('id','',$_POST['lista_eliminar'])));
			
			$datos_plantilla["titulo_msj"] = "Coordinador(es) eliminados(s)";
			$datos_plantilla["cuerpo_msj"] = "El(Los) coordinador(es) fueron eliminados correctamente.";
			$datos_plantilla["tipo_msj"] = "alert-success";
			$datos_plantilla["redirecTo"] = 'Coordinadores/borrarCoordinadores';
			$datos_plantilla["nombre_redirecTo"] = "Eliminar Coordinador";
			$datos_plantilla["redirectAuto"] = TRUE;
			$tipos_usuarios_permitidos = array(); $tipos_usuarios_permitidos[0] = TIPO_USR_COORDINADOR;
			$this->cargarMsjLogueado($datos_plantilla, $tipos_usuarios_permitidos);
			
		}else{
			$this->load->model('model_coordinadores');
			$datos_cuerpo_central['listado_coordinadores'] = $this->model_coordinadores->ObtenerTodosCoordinadores();

			/* Se setea que usuarios pueden ver la vista, estos pueden ser las constantes: TIPO_USR_COORDINADOR y TIPO_USR_PROFESOR
			* se deben introducir en un array, para luego pasarlo como parámetro al método cargarTodo()
			*/
			$subMenuLateralAbierto = 'borrarCoordinadores'; //Para este ejemplo, los informes no tienen submenu lateral
			$muestraBarraProgreso = FALSE; //Indica si se muestra la barra que dice anterior - siguiente
			$tipos_usuarios_permitidos = array(); $tipos_usuarios_permitidos[0] = TIPO_USR_COORDINADOR;
			$this->cargarTodo("Docentes", "cuerpo_coordinadores_eliminar", "barra_lateral_profesores", $datos_cuerpo_central, $tipos_usuarios_permitidos, $subMenuLateralAbierto, $muestraBarraProgreso);
	
		}
		
    }


}