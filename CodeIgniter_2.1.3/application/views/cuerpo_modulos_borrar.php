<script type="text/javascript">
	
	if(Number("<?php echo $mensaje_confirmacion?>") != 2){
		if(Number("<?php echo $mensaje_confirmacion?>") != -1){
				alert("Modulo eliminado correctamente");
				}
				else{
					alert("Error al eliminar");
				}
	}
</script>

<script type="text/javascript">
function detalleModulo(codigo_modulo,nombre_modulo,cod_equipo,descripcion){
	document.getElementById(nombre_modulo).innerHTML = nombre_modulo;
	document.getElementById(descripcion_modulo).innerHTML = descripcion;
	
	profesor_lider = document.getElementById(profesor_lider);
	var profesores_lideres = new Array();
	var cont;
	<?php
	$contadorE = 0;
	while($contadorE<count($profesor_lider)){
		echo 'profesores_lideres['.$contadorE.']=new Array();';
		echo 'profesores_lideres['.$contadorE.'][0] = "'.$profesor_lider[$contadorE][0].'";';
		echo 'profesores_lideres['.$contadorE.'][1] = "'.$profesor_lider[$contadorE][1].'";';
		echo 'profesores_lideres['.$contadorE.'][2] = "'.$profesor_lider[$contadorE][2].'";';
		echo 'profesores_lideres['.$contadorE.'][3] = "'.$profesor_lider[$contadorE][3].'";';
		echo 'profesores_lideres['.$contadorE.'][4] = "'.$profesor_lider[$contadorE][4].'";';
		echo 'profesores_lideres['.$contadorE.'][5] = "'.$profesor_lider[$contadorE][5].'";';
		$contadorE = $contadorE + 1;
	}
	?>
	for(cont=0;cont < profesores_lideres.length;cont++){
		if(cod_equipo == profesores_lideres[cont][0]){
			profesor_lider.innerHTML = profesores_lideres[cont][2]+" "+profesores_lideres[cont][3]+" "+profesores_lideres[cont][4]+" "+profesores_lideres[cont][5];
			cont = profesores_lideres.length;
		}
    }
	
	var arreglo = new Array();
	
	<?php
	$contadorE = 0;
	while($contadorE<count($modulos)){
		echo 'arreglo['.$contadorE.']=new Array();';
		echo 'arreglo['.$contadorE.'][0] = "'.$modulos[$contadorE][0].'";';//cod modulo tem
		echo 'arreglo['.$contadorE.'][1] = "'.$modulos[$contadorE][2].'";';//cod equipo
		$contadorE = $contadorE + 1;
	}
	?>
	for(cont=0;cont < arreglo.length;cont++){
		document.getElementById("tabla_sesion_"+arreglo[cont][0]).style.display='none';
		document.getElementById("tabla_profesor_"+arreglo[cont][2]).style.display='none';
		document.getElementById("tabla_requisito_"+arreglo[cont][0]).style.display='none';
    }

	document.getElementById("tabla_sesion_"+codigo_modulo).style.display='';
	document.getElementById("tabla_profesor_"+codigo_equipo).style.display='';
	document.getElementById("tabla_requisito_"+codigo_modulo).style.display='';
}
</script>

<script type="text/javascript">
	function eliminarModulo(){

		var answer = confirm("¿Está seguro de eliminar este módulo?")
		if (!answer){
			return false;
		}
		else{

		var borrar = document.getElementById("FormBorrar");
		borrar.action ="<?php echo site_url("Alumnos/hacerBorrarModulos/");?>"
		borrar.submit();
		}

	}
</script>

<div>
	<div class="span10">
		<fieldset>
		<legend>Borrar Módulo</legend>
		<form id="FormBorrar" type="post" onsubmit="eliminarModulo();return false" method="post">
			
	  		<div class="row-fluid">
				<div class="span6">
					<div class="row-fluid">
						<div class="span6">
							1. Escoja un módulo de la lista
						</div>
					</div>


				<div class="row-fluid" style="margin-left: 0%; width:90%">
					<thead>
						<tr>
							<th style="text-align:left;"><b>Nombre del módulo</b></th>
							
						</tr>
					</thead>


					<div style="border:#cccccc  1px solid;overflow-y:scroll;height:400px; -webkit-border-radius: 4px" ><!--  para el scroll-->
						<table class="table table-hover">
							<tbody>		
								<?php
								$contador=0;
								$comilla= "'";
								while ($contador<count($modulos)){
									
									echo '<tr>';
									echo	'<td  id="td_modulo_'.$modulos[$contador][0].'" onclick="DetalleModulo('.$comilla.$modulos[$contador][0].$comilla.','.$comilla. $modulos[$contador][3].$comilla.','.$comilla. $modulos[$contador][2].$comilla.','.$comilla. $modulos[$contador][4].$comilla.')" 
												  style="text-align:left;"><input required name="cod_modulo_eliminar" value="'.$modulos[$contador][0].'" type="checkbox"></input>
												  '. $modulos[$contador][3].'</td>';
									echo '</tr>';
																
									$contador = $contador + 1;
								}
								?>														
							</tbody>
						</table>
					</div>
				</div>	
			
		</div>
		
		<div class="span6" style="margin-left: 2%; padding: 0%;  ">
			2. Detalle Módulo Temático
			<div class ="row-fluid" style="margin-top:5%">
				<pre style="margin-top: 2%; padding: 2%; height:6%">
Nombre del módulo:	<b id="nombre_modulo"></b>
Profesor lider: 	<b id="profesor_lider"></b>
Descripción módulo: <b id="descripcion_modulo"></b></pre>
				
			</div>
			<div class="row-fluid">
				<div class="row-fluid">
						<div class="span6">
							3. Sesiones del Módulo Temático
						</div>
				</div>
			</div>
			<div class="row-fluid">
				<div style="border:#cccccc 1px solid;overflow-y:scroll;height:100px; -webkit-border-radius: 4px" >																		
					<?php
					$contador=0;
					while ($contador<count($sesiones)){
					$codigo_actual = $sesiones[$contador][1];
					echo  '<table id="tabla_sesion_'.$sesiones[$contador][1].'" class="table table-hover" style="display:none">';
					echo 	'<thead>';
					echo	'</thead>';
					echo	'<tbody>';	
					
						while($contador<count($sesiones) && $codigo_actual == $sesiones[$contador][1]){
							echo '<tr>';
							echo	'<td  title="'.$sesiones[$contador][2].'" style="text-align:left;">'.$sesiones[$contador][0].'</td>';
							echo '</tr>';
							$contador = $contador + 1;
						}
					echo 	'</tbody>';
					echo '</table>';
					}
					?>
				</div>
			</div>
			<div class="row-fluid">
				<div class="row-fluid" style="margin-top:2%">
						<div class="span7">
							4. Profesores del Módulo Temático
						</div>
				</div>
			</div>
			<div class="row-fluid">
				<div style="border:#cccccc 1px solid;overflow-y:scroll;height:100px; -webkit-border-radius: 4px" >
									
						<?php
						$contador=0;
						while ($contador<count($equipos)){
						$codigo_actual = $equipos[$contador][0];
						echo  '<table id="tabla_profesor_'.$equipos[$contador][0].'" class="table table-hover" style="display:none">';
						echo 	'<thead>';
						echo	'</thead>';
						echo	'<tbody>';	
						
							while($contador<count($equipos) && $codigo_actual == $equipos[$contador][0]){
								echo '<tr>';
								echo	'<td  style="text-align:left;">
							  '.$equipos[$contador][1].' :'.$equipos[$contador][2].' '.$equipos[$contador][3].' '.$equipos[$contador][4].' '.$equipos[$contador][5].'</td>';
								echo '</tr>';
							$contador = $contador + 1;
							}
						echo 	'</tbody>';
						echo '</table>';
						}
						?>
					</table>
				</div>
			</div>
			
			
			<div class="row-fluid">
				<div class="row-fluid" style="margin-top:2%">
						<div class="span7">
							5. Requisitos del Módulo Temático
						</div>
				</div>
			</div>
			<div class="row-fluid">
				<div style="border:#cccccc 1px solid;overflow-y:scroll;height:100px; -webkit-border-radius: 4px" >
									
					<?php	
					$contador=0;
					while ($contador<count($requisitos)){
					$codigo_actual = $requisitos[$contador][0];
					echo  '<table id="tabla_requisito_'.$requisitos[$contador][0].'" class="table table-hover" style="display:none">';
					echo 	'<thead>';
					echo	'</thead>';
					echo	'<tbody>';	
					
						while($contador<count($requisitos) && $codigo_actual == $requisitos[$contador][0]){
							echo '<tr>';
							echo	'<td  style="text-align:left;">'.$requisitos[$contador][2].' , código: '.$requisitos[$contador][1].'</td>';
							echo '</tr>';
							$contador = $contador + 1;
						}
					echo 	'</tbody>';
					echo '</table>';
					}
					?>	
				</div>
			</div>
			
			<div class="row" style="margin-top: 2%">

					<div class="span3 offset6">
						<button class="btn" type="submit" style="width: 93px">
							<div class= "btn_with_icon_solo">b</div>
							&nbsp Borrar
						</button>
					</div>

					<div class = "span3 ">
						<button  class ="btn" type="reset" style="width: 105px">
							<div class= "btn_with_icon_solo">Â</div>
							&nbsp Cancelar
						</button>
					</div>

			</div>
			
		</div>
	    </div>

		</form>
		</fieldset>
	</div>
</div>