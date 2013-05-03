<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends CI_Controller {

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
	public function index()
	{
		$rut = $this->session->userdata('rut'); //Se comprueba si el usuario tiene sesión iniciada
	    if ($rut == TRUE) {
	      redirect('/Correo/', 'index');         // En dicho caso, se redirige a la interfaz principal
	    }
	    
		$datos_plantilla["title"] = "ManteKA login";
		$datos_plantilla["head"] = $this->load->view('templates/head', $datos_plantilla, true);
		$datos_plantilla["banner_portada"] = $this->load->view('templates/banner_portada', '', true);
		$this->load->view('login', $datos_plantilla);
		
	}

	public function olvidoPass()
	{
		$datos_plantilla["title"] = "ManteKA";
		$datos_plantilla["head"] = $this->load->view('templates/head', $datos_plantilla, true);
		$datos_plantilla["banner_portada"] = $this->load->view('templates/banner_portada', '', true);
		$this->load->view('olvidoPass', $datos_plantilla);
		
	}

	private function randomPassword() {
	    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
	    $pass = array(); //remember to declare $pass as an array
	    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
	    for ($i = 0; $i < 8; $i++) {
	        $n = rand(0, $alphaLength);
	        $pass[] = $alphabet[$n];
	    }
	    return implode($pass); //turn the array into a string
	}

	/*
		Esta función se usa sólo para enviar el correo de recuperación de contraseña
	*/
	private function enviarCorreo($destino, $subject, $mensaje) {
		try {
			$this->email->from('no-reply@manteka.cl', 'ManteKA');
			$this->email->to($destino);
			$this->email->subject($subject);
			$this->email->message($mensaje);


			$this->email->send();
			//echo $this->email->print_debugger();
			return TRUE;
		}
		catch (Exception $e) {
			return FALSE;
		}
	}

	public function check_mail_exist($email) {
		$this->load->model('model_usuario');
		if ($this->model_usuario->existe_mail($email)) {
			return TRUE;
		}
		else {
			$this->form_validation->set_message('check_mail_exist', 'El %s no existe en ManteKA, intente nuevamente.');
			return FALSE;
		}
	}

	public function LoginPost() {
		$Rut = $this->input->post('inputRut');
		$this->form_validation->set_rules('inputRut', 'usuario', "required|callback_check_userRUT");
		$this->form_validation->set_rules('inputPassword', 'contraseña', "required|callback_check_user_and_password[$Rut]");
		
		if ($this->form_validation->run() == FALSE)
		{
			$this->index(); //Vuelvo a llamar el cambio de contraseña si hubo un error
		}else{
			 $this->load->model('model_usuario');
            $ExisteUsuarioyPassoword=$this->model_usuario->ValidarUsuario($_POST['inputRut'],$_POST['inputPassword']);   //   comprobamos que el usuario exista en la base de datos y la password ingresada sea correcta
            if($ExisteUsuarioyPassoword){   // La variable $ExisteUsuarioyPassoword recibe valor TRUE si el usuario existe y FALSE en caso que no. Este valor lo determina el modelo.
				      $newdata = array(
                   'rut'  => $ExisteUsuarioyPassoword->RUT_USUARIO,
                   'email'     => $ExisteUsuarioyPassoword->CORREO1_USER,
                   'tipo_usuario' => $ExisteUsuarioyPassoword->ID_TIPO,
                   'logged_in' => TRUE
              );
				      $this->session->set_userdata($newdata);
				
				      redirect('/Correo/', 'index');
				    }
            else{   //   Si no logró validar
               //$data['error']="Rut o password incorrecta, por favor vuelva a intentar";
		          $this->session->unset_userdata('rut');
		          $this->session->unset_userdata('email');
              $this->session->unset_userdata('tipo_usuario');
		          $this->session->unset_userdata('loggued_in');
              redirect('/Login/', 'refresh'); //   Lo regresamos a la pantalla de login y pasamos como par?metro el mensaje de error a presentar en pantalla
            }
		}
	}

	public function recuperaPassPost() {
		$this->form_validation->set_rules('email', 'email', "required|email|xss_clean|callback_check_mail_exist");
		if ($this->form_validation->run() == FALSE)
		{
			$this->olvidoPass(); //Vuelvo a llamar el cambio de contraseña si hubo un error
		}
		else {

			/* Acá va la lógica de enviar un correo, etc */
			$destino = $this->input->post('email');
			$new_pass = $this->randomPassword();
			/* Seteo la nueva contraseña en el modelo y le doy un tiempo de validez */
			$this->load->model('model_usuario');
			$fechaValidez = date("Y-m-d", strtotime(date("Y-m-d")." +1 day"));
			$existeEmail = $this->model_usuario->setPassSecundaria($destino, $new_pass, $fechaValidez);
			if ($existeEmail) {
				$mensaje = "Su nueva contraseña es: ";
				$mensaje = $mensaje.$new_pass;
				$mensaje = $mensaje."\nEsta contraseña es válida hasta el día ".$fechaValidez.", luego no podrá utilizarla";
				$mensaje = $mensaje."\nA penas inicie sesión nuevamente cambie su contraseña.";
				$mensaje = $mensaje."\n\nEl equipo de ManteKA.";
				if ($this->enviarCorreo($destino, 'Recuperación de contraseña ManteKA', $mensaje) == FALSE) {
					$datos_plantilla["titulo_msj"] = "No se pudo enviar el correo";
					$datos_plantilla["cuerpo_msj"] = "Existe un problema con el servicio que envía correos electrónicos, comuniquese con el administrador.";
					$datos_plantilla["tipo_msj"] = "alert-error";

				}
				else {
					$datos_plantilla["titulo_msj"] = "Listo";
					$datos_plantilla["cuerpo_msj"] = "Se ha enviado un correo electrónico a la cuenta '".$destino."' con su nueva contraseña.";
					$datos_plantilla["tipo_msj"] = "alert-success";
				}
				


				/* Finalmente muestro la vista que indica que esto fue realizado correctamente */
				$datos_plantilla["title"] = "ManteKA";
				$datos_plantilla["head"] = $this->load->view('templates/head', $datos_plantilla, true);
				$datos_plantilla["banner_portada"] = $this->load->view('templates/banner_portada', '', true);
				
				$datos_plantilla["redirectAuto"] = FALSE; //Esto indica si por javascript se va a redireccionar luego de 5 segundos
				$datos_plantilla["redirecTo"] = "Login/index"; //Acá se pone el controlador/metodo hacia donde se redireccionará
				//$datos_plantilla["redirecFrom"] = "Login/olvidoPass"; //Acá se pone el controlador/metodo desde donde se llegó acá, no hago esto si no quiero que el usuario vuelva
				$datos_plantilla["nombre_redirecTo"] = "Inicio de sesión"; //Acá se pone el nombre del sitio hacia donde se va a redireccionar
				$this->load->view('templates/big_msj_deslogueado', $datos_plantilla);
			}
		}
	}

	
	/*
		Esta función muestra la vista para cambiar la contraseña, pero lleva un argumento que se setea por defecto 
		en un array vacio, de esta forma cuando el usuario abre esa vista por primera vez el array está vacio
		Cuando la vista es rellamada para mostrarla nuevamente pero con mensajes de error, warnings o success entonces 
		este array contiene el mensaje a ser mostrado (ver más abajo como se llama con el array)
	*/
	public function cambiarContrasegna($mensajes_alert = array())
	{
		$rut = $this->session->userdata('rut'); //Se comprueba si el usuario tiene sesión iniciada
		if ($rut == FALSE) {
			redirect('/Login/', ''); //Se redirecciona a login si no tiene sesi?n iniciada
		}
		
		
		//$this->form_validation->set_error_delimiters('<div class="error">', '</div>');
		$datos_plantilla["rut_usuario"] = $this->session->userdata('rut');
		$datos_plantilla["title"] = "ManteKA";

		/* Esta parte hace que se muestren los mensajes de error, warnings, etc */
		if (count($mensajes_alert) > 0) {
			$datos_plantilla["mensaje_alert"] = $this->load->view('templates/mensajes/mensajeError', $mensajes_alert, true);
		}
		$datos_plantilla["menuSuperiorAbierto"] = ""; //Ningún botón está presionado
		$datos_plantilla["head"] = $this->load->view('templates/head', $datos_plantilla, true);
		$datos_plantilla["barra_usuario"] = $this->load->view('templates/barra_usuario', $datos_plantilla, true);
		$datos_plantilla["banner_portada"] = $this->load->view('templates/banner_portada', '', true);
		$datos_plantilla["menu_superior"] = $this->load->view('templates/menu_superior', $datos_plantilla, true);
		$datos_plantilla["barra_navegacion"] = $this->load->view('templates/barra_navegacion', '', true);
		$datos_plantilla["mostrarBarraProgreso"] = FALSE; //Cambiar en caso que no se necesite la barra de progreso
		$datos_plantilla["barra_progreso_atras_siguiente"] = $this->load->view('templates/barra_progreso_atras_siguiente', $datos_plantilla, true);
		$datos_plantilla["footer"] = $this->load->view('templates/footer', '', true);
		$datos_plantilla["cuerpo_central"] = $this->load->view('cuerpo_cambio_contrasegna', '', true); //Esta es la linea que cambia por cada controlador
		$datos_plantilla["barra_lateral"] = ""; //$this->load->view('templates/barras_laterales/barra_lateral_planificacion', '', true); //Esta linea tambi?n cambia seg?n la vista como la anterior
		$this->load->view('templates/template_general', $datos_plantilla);
	
	}
	
	/*
		Por convención, las funciones que terminan en "Post" corresponden a las funciones que son llamadas cuando se envian datos
		al servidor a través de un formulario.
	*/
	public function cambiarContrasegnaPost() {
	
		$rut = $this->session->userdata('rut'); //Se comprueba si el usuario tiene sesión iniciada
		if ($rut == FALSE) {
			redirect('/Login/', ''); //Se redirecciona a login si no tiene sesión iniciada
		}
		
		$this->form_validation->set_rules('contrasegna_actual', 'Contraseña actual', "required|xss_clean|callback_check_user_and_password[$rut]");
		$this->form_validation->set_rules('nva_contrasegna_rep', 'Confirmación de contraseña', 'required|min_length[5]|max_length[100]|matches[nva_contrasegna]|xss_clean');
		$this->form_validation->set_rules('nva_contrasegna', 'Contraseña nueva', 'required|min_length[5]|max_length[100]|xss_clean');
		$this->form_validation->set_error_delimiters('<div class="error alert alert-error">', '</div>');
		if ($this->form_validation->run() == FALSE)
		{
			/* Se debe setear un array asociativo con 3 keys: "titulo_msj", "cuerpo_msj" y "tipo_msj"
			titulo_msj: puede ser cualquier texto que represente a grandes rasgos el mensaje
			cuerpo_msj: puede ser cualquier texto que represente a el detalle del mensaje
			tipo_msj: indica el tipo de mensaje, puede tomar los valores: "alert-error", "alert-warning", "alert-success", "alert-danger" y "alert-info"
			Luego se debe pasar este array como argumento al método del controlador que carga la vista con errores
			*/
			$mensaje_alert["titulo_msj"] = "Hay un problema para cambiar la contraseña";
			$mensaje_alert["cuerpo_msj"] = "Revise los campos señalados más abajo e intente nuevamente";
			$mensaje_alert['tipo_msj'] = "alert-error";
			$this->cambiarContrasegna($mensaje_alert); //Vuelvo a llamar el cambio de contraseña si hubo un error
		}
		else
		{
			$resultado = $this->model_usuario->cambiarContrasegna($rut ,md5($_POST['nva_contrasegna']));

			/* Cargo la vista que muestra el mensaje de que la operación se realizó correctamente */
			$datos_plantilla["rut_usuario"] = $this->session->userdata('rut');
			$datos_plantilla["title"] = "ManteKA";
			$datos_plantilla["menuSuperiorAbierto"] = "";
			$datos_plantilla["head"] = $this->load->view('templates/head', $datos_plantilla, true);
			$datos_plantilla["barra_usuario"] = $this->load->view('templates/barra_usuario', $datos_plantilla, true);
			$datos_plantilla["banner_portada"] = $this->load->view('templates/banner_portada', '', true);
			$datos_plantilla["menu_superior"] = $this->load->view('templates/menu_superior', $datos_plantilla, true);
			$datos_plantilla["barra_navegacion"] = $this->load->view('templates/barra_navegacion', '', true);
			$datos_plantilla["mostrarBarra_navegacion"] = FALSE;
			$datos_plantilla["mostrarBarraProgreso"] = FALSE; //Cambiar en caso que no se necesite la barra de progreso
			$datos_plantilla["barra_progreso_atras_siguiente"] = $this->load->view('templates/barra_progreso_atras_siguiente', $datos_plantilla, true);
			$datos_plantilla["footer"] = $this->load->view('templates/footer', '', true);
			$datos_plantilla["barra_lateral"] = "";
			$datos_plantilla["titulo_msj"] = "Listo";
			$datos_plantilla["cuerpo_msj"] = "Se ha cambiado su contraseña";
			$datos_plantilla["tipo_msj"] = "alert-success";
			$datos_plantilla["redirectAuto"] = FALSE; //Esto indica si por javascript se va a redireccionar luego de 5 segundos
			$datos_plantilla["redirecTo"] = "Correo/index"; //Acá se pone el controlador/metodo hacia donde se redireccionará
			//$datos_plantilla["redirecFrom"] = "Login/olvidoPass"; //Acá se pone el controlador/metodo desde donde se llegó acá, no hago esto si no quiero que el usuario vuelva
			$datos_plantilla["nombre_redirecTo"] = "vista principal"; //Acá se pone el nombre del sitio hacia donde se va a redireccionar
			$datos_plantilla["cuerpo_central"] = $this->load->view('templates/big_msj_logueado', $datos_plantilla, true); //Esta es la linea que cambia por cada controlador
			$this->load->view('templates/template_general', $datos_plantilla);
		}
	}
	
	public function check_user_and_password($current_password, $user) {
		$this->load->model('model_usuario');
		$logueo = $this->model_usuario->ValidarUsuario($user ,$current_password);
		if ($logueo) {
			return TRUE;
		}
		else {
			$this->form_validation->set_message('check_user_and_password', 'La %s es incorrecta');
			return FALSE;
		}
	}

	public function check_userRUT($user) {
		$this->load->model('model_usuario');
		$logueo = $this->model_usuario->ValidarRut($user);
		if ($logueo) {
			return TRUE;
		}
		else {
			$this->form_validation->set_message('check_userRUT', 'El %s no esta en el sistema');
			return FALSE;
		}
	}

	function logout() {
		$this->load->library('session');
		$this->load->helper('url');
		$this->session->unset_userdata('rut');
		$this->session->unset_userdata('email');
    $this->session->unset_userdata('loggued_in');
		redirect('/Login/', 'refresh'); //   Lo regresamos a la pantalla de login y pasamos como par?metro el mensaje de error a presentar en pantalla
   }


      /*
   *  Función para autentificarse en el sistema mediante una cuenta Google.
   *  El Controlador solicita la autentificación a Google.
   *  Una vez realizado esto, el usuario se autentifica normalmente.
   */
   function signInGoogle($provider){
    $rut = $this->session->userdata('rut'); //Se comprueba si el usuario tiene sesión iniciada
    if ($rut == TRUE) {
      redirect('/Correo/', 'index');         // En dicho caso, se redirige a la interfaz principal
    }

    $this -> load -> helper('url');
    $this->load->library('session');
    $this -> load -> spark('oauth2/0.4.0');
    //si el proveedor pasado es Google
    if($provider == 'google')
    {
        $provider = $this -> oauth2 -> provider($provider, array(
        'id' => '412900046548.apps.googleusercontent.com',
        'secret' => 'RN_R-d6BDT2XYwQdVHB5S9tO', ));
    }

    if (!$this -> input -> get('code')) {
        $url = $provider -> authorize();

        redirect($url);
    } else {
        try {
            // Se posee de un Token exitoso enviado por google
            $token = $provider -> access($this->input->get('code'));

            // Se obtiene la información del usuario (Nombre, mail, dirección, foto)
            $user = $provider->get_user_info($token); 

            // Correo del usuario ingresado
            $mail = $user['email'];
            
            //Cargando modelo para validar correo del usuario ingresado con algún registro en la db
            $this->load->model('model_usuario');
            
            /*
              * Verificando si existe algún usuario con dicho correo electrónico. 
              * Resultado de la consulta del modelo. Falso de no encontrar nada o
              * Las filas correspondientes a los usuarios que posean dicho mail.
            */
            $usuario = $this->model_usuario->existe_mail($mail);
            if ($usuario)
            {
              $newdata = array(
                   'rut'  => $usuario->RUT_USUARIO,
                   'email'     => $usuario->CORREO1_USER,
                   'tipo_usuario' => $usuario->ID_TIPO,
                   'logged_in' => TRUE
              );
              $this->session->set_userdata($newdata);
              redirect('/Correo/', 'index');         // En dicho caso, se redirige a la interfaz principal
            }
            else // En caso de no existir ningún usuario con correo = $mail
            {
              $datos_plantilla["titulo_msj"] = "Error";
              $datos_plantilla["cuerpo_msj"] = "El correo ingresado no se asocia a ningún usuario del sistema ManteKA";
              $datos_plantilla["tipo_msj"] = "alert-error";

              /* Finalmente muestro la vista que indica que esto fue realizado correctamente */
              $datos_plantilla["title"] = "ManteKA";
              $datos_plantilla["head"] = $this->load->view('templates/head', $datos_plantilla, true);
              $datos_plantilla["banner_portada"] = $this->load->view('templates/banner_portada', '', true);
              
              $datos_plantilla["redirectAuto"] = FALSE; //Esto indica si por javascript se va a redireccionar luego de 5 segundos
              $datos_plantilla["redirecTo"] = "Login/index"; //Acá se pone el controlador/metodo hacia donde se redireccionará
              $datos_plantilla["redirecFrom"] = "Login/signInGoogle/google"; //Acá se pone el controlador/metodo desde donde se llegó acá, no hago esto si no quiero que el usuario vuelva
              $datos_plantilla["nombre_redirecFrom"] = "Volver"; //Acá se pone el nombre del sitio hacia donde se va a redireccionar
              $datos_plantilla["nombre_redirecTo"] = "Inicio de sesión"; //Acá se pone el nombre del sitio hacia donde se va a redireccionar
              $this->load->view('templates/big_msj_deslogueado', $datos_plantilla);


            }

            
        } catch (OAuth2_Exception $e) {
            show_error('No se pudo loguear mediante Google :(: ' . $e);
        }
    }

   }

}

/* End of file Login.php */
/* Location: ./application/controllers/Login.php */