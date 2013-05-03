<!DOCTYPE html>
<html lang="en">
<?php
	echo $head						//Esta variable es pasada como parámetro a esta vista
?>
<script src="/<?php echo config_item('dir_alias') ?>/javascripts/verificadorRut.js"></script>
<body>
		
		<?php
			echo $banner_portada	//Esta variable es pasada como parámetro a esta vista
		?>
		<div class="row-fluid">
			<div class="span7 offset1">
				<h2>Bienvenido a ManteKA</h2>
						<p align="justify">
							ManteKA es un sistema que le permite mantener una comunicación precisa y fluida con los participantes de la asignatura de Comunicación Efectiva perteneciente al módulo básico de ingeniería. 							A través de ManteKA es posible enviar correos electrónicos masivos a las personas que usted requiere 
							Basta de enviar correos uno por uno!.
						</p>
			</div>
			<fieldset class="span3">
				<legend>Inicio de sesión</legend>
				<?php echo form_open('Login/LoginPost/'); ?>
						<?php /* Con esto hago que cambie la class del control-group a 'error' en caso de que exista un error en la validación */
							$inputRut = '';
							$inputPassword = '';
							if (form_error('inputPassword') != '') {
								$inputPassword = 'error';
							}
							if (form_error('inputRut') != '') {
								$inputRut = 'error';
							}
						?>
						<div class="control-group <?php echo $inputRut ?>">
							<label class="control-label" for="inputRut">Rut</label>
							<div class="controls">
							  	<input style="width:200px" type="text" name="inputRut" id="inputRut" placeholder=" Ingrese rut, ejemplo: 1756574" value="<?= set_value('inputRut'); ?>">
							 	<STRONG>-</STRONG>
							  	<input style="width:15px" type="text" name="inputGuionRut" maxlength="1" id="inputGuionRut"  placeholder="K" value="<?= set_value('inputGuionRut'); ?>">
								<?= form_error('inputRut', '<span class="help-inline">', '</span>');?>
							</div>
						</div>
						<div class="control-group <?php echo $inputPassword ?>">
							<label class="control-label" for="inputPassword">Contraseña</label>
							<div class="controls">
								<input style="width:242px" type="password" name="inputPassword" onclick="calculaDigitoVerificador()" id="inputPassword" placeholder="  Ingrese su contraseña" value="<?= set_value('inputPassword'); ?>">
								<?= form_error('inputPassword', '<span class="help-inline">', '</span>');?>
							</div>
						</div>
						<div class="control-group">
							<div class="controls">
								<label class="checkbox">
									<input type="checkbox"> Recordarme&nbsp
									<a href="<?php echo site_url("Login/olvidoPass")?>">¿Olvidó su contraseña?</a>
								</label>
								<button type="submit" class="btn btn-primary">
									Entrar
								</button>					
							</div>
						</div>
						<hr>
				<?php echo form_close(""); ?>
				<?php echo form_open('Login/signInGoogle/google'); ?>
						<div class="control-group text-center">
							<button type="submit" class="btn">
								<div class="pull-right">
									<img src="/<?php echo config_item('dir_alias') ?>/img/logo_gmail.png" alt="logo gmail" style="width: 50px; height: 20px;">
								</div>
								Entrar con&nbsp 
							</button>
						</div>
				<?php echo form_close(""); ?>
			</fieldset>
		</div>
		
</body>
</html>