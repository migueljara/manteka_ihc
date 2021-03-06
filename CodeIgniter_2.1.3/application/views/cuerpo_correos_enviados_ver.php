<link rel="stylesheet" href="/<?php echo config_item('dir_alias') ?>/css/correosEnviados.css" type="text/css" media="all" />

<script type="text/javascript">

var id;
var extended=false;
var destinoaux;


/** 
* Esta función se llama al clickear un correo de la bandeja de correos enviados, En primera instancia muestra el detalle
* de dicho correo y a la vez ocultando la bandeja de correos mostrando sólo el detalle del correo seleccionado. 
* por convención las funciones que utilizan document.getElementById()
* deben ser definidas en la misma vista en que son utilizados para evitar conflictos de nombres.
* Para ver como se configura esto se debe ver en el evento onclick() en donde están contenidos los correos (bandeja) .
*/
function DetalleCorreo(hora,fecha,asunto,id,destino,codigo)
{		
	document.getElementById("fecha").innerHTML=fecha;
	document.getElementById("hora").innerHTML=hora;
	document.getElementById("asuntoDetalle").innerHTML=asunto;
	document.getElementById("cuerpoMail").innerHTML=document.getElementById("c"+id).value;
	
	this.id=id;

		destinoaux=destino.replace(/,/g,",<br>");
		document.getElementById("destinos").innerHTML=destino.split(",",1 );

	

	obtenerAdjunto(codigo);
	$('#cuadroEnviados').css({display:'none'});
	$('#cuadroDetalleCorreo').css({display:'block'});
}

/** 
* Esta función se llama al clickear el botón que se encuentra en el Detalle del Correo, para poder mostrar nuevamente la 
* bandeja de correos enviados y ocultar el detalle del correo que se estaba mostrando.
* Para ver como se configura esto se debe ver en el evento onclick() del botón que se encuentra en el Detalle de Correo.
*/
function volverCorreosEnviados()
{	$("[rel=details]").popover('hide');
	$('#cuadroDetalleCorreo').css({display:'none'});
	$('#cuadroEnviados').css({display:'block'});
}


/** 
* Esta función elimina los tags HTML
*/
function strip(html)
{
   var tmp = document.createElement("DIV");
   tmp.innerHTML = html;
   return tmp.textContent||tmp.innerText;
}
function obtenerAdjunto(codigo)
{
	$.ajax({
		type: "POST",
		url: "<?php echo site_url("Correo/obtenerAdjuntos") ?>",
		data: { codigo: codigo},
		success: function(respuesta){
			listaAdjuntos = JSON.parse(respuesta);
			if(listaAdjuntos.length>0)
			{
				$('#destinosAdjuntos').css({display:'none'});
				var tablaResultados=document.getElementById("files");
				$(tablaResultados).find('tbody').remove();

				tbody = document.createElement('tbody');
				tbody.setAttribute("style","height:auto;width:100%;");
				for(num=0;num<listaAdjuntos.length;num++){

					document.getElementById("attach").setAttribute("style","display:'';");
					var tr= document.createElement('tr'); 
					tr.setAttribute("id","f"+num);
					tr.setAttribute("style","margin-left:0px;display:block;");
					var td=document.createElement("td");
					td.setAttribute("style","width:95%;display:inline-table;font-size:10px;");
					var span;
					var iconClass='icon icon-'+listaAdjuntos[num].logico.substring(listaAdjuntos[num].logico.lastIndexOf(".")+1);
					span="<span  class='"+iconClass+"''></span>";	
					var link="<a href='"+listaAdjuntos[num].fisico+"' download='"+listaAdjuntos[num].logico+"'>"+listaAdjuntos[num].logico+"</a>";
					td.innerHTML=span+" "+link;
					tr.appendChild(td);
					tbody.appendChild(tr);
					tr=document.createElement("tr");
					tr.setAttribute("id",'b'+num);
					tr.setAttribute("style","display:none");
					tbody.appendChild(tr);
					var iconoCargado = document.getElementById("icono_cargando");
					
				}
				tablaResultados.appendChild(tbody);
			}
			else
			{

				$('#destinosAdjuntos').css({display:'inline-block'});
			}$(icono_cargando).hide();
		}
	});
	/* Muestro el div que indica que se está cargando... */
	var iconoCargado = document.getElementById("icono_cargando");
	$(icono_cargando).show();
}


/** 
* Esta función se llama al hacer click en los botones < y > para cambiar los correos mostrados
*/
function cambiarCorreos(direccion,offset)
{
	
	if (direccion=="ant") {
		offset=offset-20;
	}
	else if(direccion=="sig") {
		offset=offset+20;
	}
	
	var filtroBusqueda = document.getElementById("filtroLista");
	var textoBusqueda = $(filtroBusqueda).val();

	$.ajax({
		type: "POST",
		url: "<?php echo site_url("Correo/postEnviados") ?>",
		data: { offset: offset, textoBusqueda: textoBusqueda, textoFiltrosAvanzados: valorFiltrosJson},
		success: function(respuesta){
			var tablaResultados = document.getElementById('listadoResultados');
			var nodoTexto;
			$(tablaResultados).find('tbody').remove();
			listaRecibidos = JSON.parse(respuesta);

			
			tbody = document.createElement('tbody');
			tbody.setAttribute("style","overflow-y:scroll; height:295px;display:block;");
			if (listaRecibidos.length == 0) {
				tr = document.createElement('tr');
				td = document.createElement('td');
				$(td).html("No se encontraron resultados");
				$(td).attr('colspan',tiposFiltro.length);
				tr.appendChild(td);
				tbody.appendChild(tr);
			}
			for (var i = 0; i < listaRecibidos.length; i++) {
				tr = document.createElement('tr');
				tr.setAttribute("id","tr"+i);
				td = document.createElement('td');
				td.setAttribute("width", "5%");
				td.setAttribute("id", i);
				td.setAttribute("style","padding-top:4px;padding-bottom:8px;");
				td.setAttribute("align","center");				
				check = document.createElement('input');
				check.type='checkbox';
				check.setAttribute("name", prefijo_tipoDato + listaRecibidos[i].codigo);
				check.setAttribute("id", "check" + listaRecibidos[i].codigo);
				check.checked=false;
				td.appendChild(check);
				td.setAttribute("onclick","oscurecerFondo("+i+","+listaRecibidos[i].codigo+")");
				var cuerpo = listaRecibidos[i].cuerpo_email;
				tr.appendChild(td);
				td = document.createElement('td');
				td.setAttribute("width", "23%");
				td.setAttribute("id", i);
				td.setAttribute("style","text-align:left;padding-left:7px;");
				var de=listaRecibidos[i].nombre_destinatario;
				td.setAttribute("onclick","DetalleCorreo('"+listaRecibidos[i].hora+"','"+listaRecibidos[i].fecha+"','"+listaRecibidos[i].asunto+"',"+i+",'"+de+"',"+listaRecibidos[i].codigo+")");				if(de.replace(/,/g,", ").length>40)
					nodoTexto=document.createTextNode(de.replace(/,/g,", ").substr(0,40)+".....");
				else
					nodoTexto=document.createTextNode(de.replace(/,/g,", ").substr(0,40));	
				td.appendChild(nodoTexto);
				tr.appendChild(td);
				td = document.createElement('td');
				td.setAttribute("id", "m"+i);
				td.setAttribute("width", "27%");
				td.setAttribute("style","text-align:left;padding-left:7px;");
				td.setAttribute("onclick","DetalleCorreo('"+listaRecibidos[i].hora+"','"+listaRecibidos[i].fecha+"','"+listaRecibidos[i].asunto+"',"+i+",'"+de+"',"+listaRecibidos[i].codigo+")");
				var span="";
				
				if (listaRecibidos[i].adjuntos!=null)
					span="<span  style='width: 15px; height: 15px; float:right; margin-right:8px;'><img src='/manteka/img/icons/glyphicons_062_paperclip' alt=':' ></span>";	
				var largoAsunto=listaRecibidos[i].asunto.length; 
				if(listaRecibidos[i].asunto.length>30){
					var asuntoTmp = listaRecibidos[i].asunto.substr(0,30)+".....";	
					largoAsunto=30;
				}
				else
					var asuntoTmp = listaRecibidos[i].asunto;	
				if(strip(cuerpo+"<a>").length>40-largoAsunto)
					var cuerpoTmp = strip(cuerpo+"<a>").substr(0,40-largoAsunto)+".....";	
				else
					var cuerpoTmp = strip(cuerpo+"<a>");	
				td.innerHTML = asuntoTmp+" - <font color='#999999'>"+cuerpoTmp+"</font>"+span;
			
				tr.appendChild(td);
				td = document.createElement('td');
				td.setAttribute("width", "8%");
				td.setAttribute("id", i);
				td.setAttribute("style","text-align:left;padding-left:7px;");
				td.setAttribute("onclick","DetalleCorreo('"+listaRecibidos[i].hora+"','"+listaRecibidos[i].fecha+"','"+listaRecibidos[i].asunto+"',"+i+",'"+de+"',"+listaRecibidos[i].codigo+")");
				nodoTexto=document.createTextNode(listaRecibidos[i].fecha);
				td.appendChild(nodoTexto);
				tr.appendChild(td);
				td = document.createElement('td');
				td.setAttribute("width", "8%");
				td.setAttribute("id", i);
				td.setAttribute("style","text-align:left;padding-left:7px;");
				td.setAttribute("onclick","DetalleCorreo('"+listaRecibidos[i].hora+"','"+listaRecibidos[i].fecha+"','"+listaRecibidos[i].asunto+"',"+i+",'"+de+"',"+listaRecibidos[i].codigo+")");
				
				nodoTexto=document.createTextNode(listaRecibidos[i].hora);
				td.appendChild(nodoTexto);
				tr.appendChild(td);
				tbody.appendChild(tr);
				

				textarea=document.createElement('textarea');
				textarea.setAttribute("id","c"+i);
				textarea.setAttribute("style","display:none");
				textarea.value = cuerpo;
				tbody.appendChild(textarea);
			}

			tablaResultados.appendChild(tbody);

			var limite;
			if(<?php echo $cantidadCorreos;?><offset+20)
				limite=<?php echo $cantidadCorreos;?>;
			else
				limite=offset+20;

			
			
			document.getElementById("sig").setAttribute("onClick", "cambiarCorreos('sig',"+offset+")");
			document.getElementById("ant").setAttribute("onClick", "cambiarCorreos('ant',"+offset+")");
			if (direccion=="ant") {
					
					if(offset==0){
						document.getElementById("ant").className="disabled";
						document.getElementById("ant").removeAttribute('onClick');
					}
					document.getElementById("sig").removeAttribute('class');
			}else if(direccion=="sig"){
				
				if(offset+20>=<?php echo $cantidadCorreos;?>){
					document.getElementById("sig").className="disabled";
					document.getElementById("sig").removeAttribute('onClick');
				}
				document.getElementById("ant").removeAttribute('class');

			}else{
				if(offset+20>=<?php echo $cantidadCorreos;?>){
					document.getElementById("sig").className="disabled";
					document.getElementById("sig").removeAttribute('onClick');
				}
				if(offset==0)
					document.getElementById("ant").removeAttribute('onClick');
			}
			if (listaRecibidos.length == 0) {
				document.getElementById("mostrando").innerHTML="mostrando "+ (offset)+"-"+limite+ " de: "+<?php echo $cantidadCorreos;?>;
			}else

			document.getElementById("mostrando").innerHTML="mostrando "+ (offset+1)+"-"+limite+ " de: "+<?php echo $cantidadCorreos;?>;

			
			
			var iconoCargado = document.getElementById("icono_cargando");
					$(icono_cargando).hide();
		}
	});
	/* Muestro el div que indica que se está cargando... */
			var iconoCargado = document.getElementById("icono_cargando");
			$(icono_cargando).show();
	
}




function cambiarCorreos2(direccion,offset)
{
	
	if (direccion=="ant") {
		offset=offset-20;

		
	}else if (direccion=="sig"){
		offset=offset+20;
		

	}
	$.ajax({
		type: "POST",
		url: "<?php echo site_url("Correo/postEnviados") ?>",
		data: { offset: offset},
		success: function(respuesta){
			var tablaResultados = document.getElementById('tabla');
			var nodoTexto;
			$(tablaResultados).empty();		
			listaEnviados = JSON.parse(respuesta);
			listaEnviados.shift();


			for (var i = 0; i < listaEnviados.length; i++) {
				destino="";
				para="";
				if(typeof listaEnviados[i][1][0] != 'undefined')
				{  j=0;
					while(typeof listaEnviados[i][1][j] != 'undefined'){
						if(destino==""){
							destino=listaEnviados[i][1][j].nombre1_estudiante+' '+listaEnviados[i][1][j].apellido_paterno+' '+listaEnviados[i][1][j].apellido_materno+' &#60'+listaEnviados[i][1][j].correo_estudiante+'&#62';					
							para=listaEnviados[i][1][j].nombre1_estudiante+' '+listaEnviados[i][1][j].apellido_paterno+' '+listaEnviados[i][1][j].apellido_materno;
						}else{
							destino=destino+',<br>'+listaEnviados[i][1][j].nombre1_estudiante+' '+listaEnviados[i][1][j].apellido_paterno+' '+listaEnviados[i][1][j].apellido_materno+' &#60'+listaEnviados[i][1][j].correo_estudiante+'&#62';
							para=para+".....";
						}
						j++;	
					}
					
					
				}
				if(typeof listaEnviados[i][2][0] != 'undefined')
				{j=0;
					while(typeof listaEnviados[i][2][j] != 'undefined'){
						if(destino==""){
							destino=listaEnviados[i][2][j].nombre1_ayudante+' '+listaEnviados[i][2][j].apellido_paterno+' '+listaEnviados[i][2][j].apellido_materno+' &#60'+listaEnviados[i][2][j].correo_ayudante+'&#62';					
							para=listaEnviados[i][2][j].nombre1_ayudante+' '+listaEnviados[i][2][j].apellido_paterno+' '+listaEnviados[i][2][j].apellido_materno;
						}else{
							destino=destino+',<br>'+listaEnviados[i][2][j].nombre1_ayudante+' '+listaEnviados[i][2][j].apellido_paterno+' '+listaEnviados[i][2][j].apellido_materno+' &#60'+listaEnviados[i][2][j].correo_ayudante+'&#62';					
							para=para+".....";
						}
						j++;	
					}
					
				}
				if(typeof listaEnviados[i][3][0] != 'undefined')
				{j=0;
					while(typeof listaEnviados[i][3][j] != 'undefined'){
						if(destino==""){
							destino=listaEnviados[i][3][j].nombre1_profesor+' '+listaEnviados[i][3][j].apellido1_profesor+' '+listaEnviados[i][3][j].apellido2_profesor+' &#60'+listaEnviados[i][3][j].correo_profesor+'&#62';					
							para=listaEnviados[i][3][j].nombre1_profesor+' '+listaEnviados[i][3][j].apellido1_profesor+' '+listaEnviados[i][3][j].apellido2_profesor;
						}else{
							destino=destino+',<br>'+listaEnviados[i][3][j].nombre1_profesor+' '+listaEnviados[i][3][j].apellido1_profesor+' '+listaEnviados[i][3][j].apellido2_profesor+' &#60'+listaEnviados[i][3][j].correo_profesor+'&#62';					
							para=para+".....";
						}
						j++;	
					}
					
				}
				if(typeof listaEnviados[i][4][0] != 'undefined')
				{j=0;
					while(typeof listaEnviados[i][4][j] != 'undefined'){
						if(destino==""){
							destino=listaEnviados[i][4][j].nombre1_coordinador+' '+listaEnviados[i][4][j].apellido1_coordinador+' '+listaEnviados[i][4][j].apellido2_coordinador+' &#60'+listaEnviados[i][4][j].correo_coordinador+'&#62';					
							para=listaEnviados[i][4][j].nombre1_coordinador+' '+listaEnviados[i][4][j].apellido1_coordinador+' '+listaEnviados[i][4][j].apellido2_coordinador;
						}else{
							destino=destino+',<br>'+listaEnviados[i][4][j].nombre1_coordinador+' '+listaEnviados[i][4][j].apellido1_coordinador+' '+listaEnviados[i][4][j].apellido2_coordinador+' &#60'+listaEnviados[i][4][j].correo_coordinador+'&#62';					
							para=para+".....";
						}
						j++;	
					}
					
				}
				tr = document.createElement('tr');
				tr.setAttribute("id","tr"+i);
				td = document.createElement('td');
				td.setAttribute("width", "5%");
				td.setAttribute("id", i);
				td.setAttribute("style","padding-top:4px;padding-bottom:8px;");
				td.setAttribute("align","center");				
				check = document.createElement('input');
				check.type='checkbox';
				check.setAttribute("name",listaEnviados[i][0].cod_correo);
				check.setAttribute("id","check"+listaEnviados[i][0].cod_correo);
				check.checked=false;
				td.appendChild(check);
				td.setAttribute("onclick","oscurecerFondo("+i+","+listaEnviados[i][0].cod_correo+")");
				tr.appendChild(td);
				td = document.createElement('td');
				td.setAttribute("width", "22%");
				td.setAttribute("id", i);
				td.setAttribute("style","text-align:left;padding-left:7px;");
				td.setAttribute("onclick","DetalleCorreo('"+listaEnviados[i][0].hora+"','"+listaEnviados[i][0].fecha+"','"+listaEnviados[i][0].asunto+"',"+i+",'"+destino+"')");
				nodoTexto=document.createTextNode(para);
				td.appendChild(nodoTexto);
				tr.appendChild(td);
				td = document.createElement('td');
				td.setAttribute("id", "m"+i);
				td.setAttribute("width", "53%");
				td.setAttribute("style","text-align:left;padding-left:7px;");
				td.setAttribute("onclick","DetalleCorreo('"+listaEnviados[i][0].hora+"','"+listaEnviados[i][0].fecha+"','"+listaEnviados[i][0].asunto+"',"+i+",'"+destino+"')");
				bold =document.createElement('b');
				nodoTexto = document.createTextNode(listaEnviados[i][0].asunto);
				bold.appendChild(nodoTexto);
				td.appendChild(bold);

				nodoTexto = document.createTextNode(" "+listaEnviados[i][0].cuerpo_email);
				td.appendChild(nodoTexto);
				tr.appendChild(td);
				td = document.createElement('td');
				td.setAttribute("width", "10%");
				td.setAttribute("id", i);
				td.setAttribute("style","text-align:left;padding-left:7px;");
				td.setAttribute("onclick","DetalleCorreo('"+listaEnviados[i][0].hora+"','"+listaEnviados[i][0].fecha+"','"+listaEnviados[i][0].asunto+"',"+i+",'"+destino+"')");
				nodoTexto=document.createTextNode(listaEnviados[i][0].fecha);
				td.appendChild(nodoTexto);
				tr.appendChild(td);
				td = document.createElement('td');
				td.setAttribute("width", "10%");
				td.setAttribute("id", i);
				td.setAttribute("style","text-align:left;padding-left:7px;");
				td.setAttribute("onclick","DetalleCorreo('"+listaEnviados[i][0].hora+"','"+listaEnviados[i][0].fecha+"','"+listaEnviados[i][0].asunto+"',"+i+",'"+destino+"')");
				
				nodoTexto=document.createTextNode(listaEnviados[i][0].hora);
				td.appendChild(nodoTexto);
				tr.appendChild(td);
				tablaResultados.appendChild(tr);
				textarea=document.createElement('textarea');
				textarea.setAttribute("id","c"+i);
				textarea.setAttribute("style","display:none");
				tablaResultados.appendChild(textarea);
				var cuerpo=listaEnviados[i][0].cuerpo_email;
				document.getElementById("m"+i).innerHTML="<b>"+listaEnviados[i][0].asunto+"</b> - "+strip(cuerpo+".").substr(0,40-listaEnviados[i][0].asunto.length)+"......";
				document.getElementById("c"+i).value=cuerpo;
				
				
			}
			var limite;
			if(<?php echo $cantidadCorreos;?><offset+20)
				limite=<?php echo $cantidadCorreos;?>;
			else
				limite=offset+20;

			
			
			document.getElementById("sig").setAttribute("onClick", "cambiarCorreos('sig',"+offset+")");
			document.getElementById("ant").setAttribute("onClick", "cambiarCorreos('ant',"+offset+")");
			if (direccion=="ant") {
					
					if(offset==0){
						document.getElementById("ant").className="disabled";
						document.getElementById("ant").removeAttribute('onClick');
					}
					document.getElementById("sig").removeAttribute('class');
			}else if(direccion=="sig"){
				
				if(offset+20>=<?php echo $cantidadCorreos;?>){
					document.getElementById("sig").className="disabled";
					document.getElementById("sig").removeAttribute('onClick');
				}
				document.getElementById("ant").removeAttribute('class');

			}else if(offset+20>=<?php echo $cantidadCorreos;?>){
					document.getElementById("sig").className="disabled";
					document.getElementById("sig").removeAttribute('onClick');
				}
			document.getElementById("mostrando").innerHTML="mostrando "+ (offset+1)+"-"+limite+ " de: "+<?php echo $cantidadCorreos;?>;

			
			
			var iconoCargado = document.getElementById("icono_cargando");
					$(icono_cargando).hide();
		}
	});
	/* Muestro el div que indica que se está cargando... */
			var iconoCargado = document.getElementById("icono_cargando");
			$(icono_cargando).show();
	
}


</script>
<script type="text/javascript">
/** 
* Esta función permite eliminar el correo que se encuentre marcado con su checkbox, también es posible la eliminación en 
* grupo (varios checkbox marcados).
*/
function eliminarCorreo()
{
	$("#seleccion").val("");
	var temp, idCorreo;
	$(':checkbox').each(function()
	{
		if (this.checked) {
			if (this.id != 'selectorTodos') { //Evito que se incluya el checkbox que los marca a todos
				temp = $(this).attr('name');
				idCorreo = temp.substring(prefijo_tipoDato.length, temp.length);
				if ($("#seleccion").val()== '') {
					$("#seleccion").val(idCorreo);
				}
				else {
					$("#seleccion").val($("#seleccion").val()+idCorreo+";");
				}
			}
		}
	});

	if ($("#seleccion").val() == '') {
		$('#modalSeleccioneAlgo').modal();
		return;
	}
	$('#modalConfirmacion').modal();
}
//funcion que resalta el correo seleccionado

function oscurecerFondo(i,codigo){
	
	if(document.getElementById("check"+codigo).checked==1){
		document.getElementById("tr"+i).setAttribute("bgcolor","#e5e5e5");	
	}
	
else
	document.getElementById("tr"+i).removeAttribute("bgcolor","#e5e5e5");
}
function limpiarFiltrosCorreo() {
	var tam = valorFiltrosJson.length;
	for (var i = 0; i < tam; i++) {
		valorFiltrosJson[i] = "";
	}
	var inputTextoFiltro = document.getElementById('filtroLista');
	$(inputTextoFiltro).val("");

	//Luego de limpiar los filtros, se debe iniciar una nueva búsqueda
	cambiarCorreos('inicial', 0);
}

function cambioTipoFiltroCorreos(inputUsado) {
	if (inputUsado != undefined) {
		var idElem = inputUsado.id;
		var index = idElem.substring(prefijo_tipoFiltro.length, idElem.length);
		valorFiltrosJson[index] = inputUsado.value; //Copio el valor del input al array de filtros
	}
	cambiarCorreos("inicial", 0);
}

function evitarEnvioVacio() {
	$("#seleccion").val("");
	var temp, idCorreo;
	$(':checkbox').each(function()
	{
		if (this.checked) {
			if (this.id != 'selectorTodos') { //Evito que se incluya el checkbox que los marca a todos
				temp = $(this).attr('name');
				idCorreo = temp.substring(prefijo_tipoDato.length, temp.length);
				if ($("#seleccion").val() == '') {
					$("#seleccion").val(idCorreo);
				}
				else {
					$("#seleccion").val($("#seleccion").val()+";"+idCorreo);
				}
			}
		}
	});

	if ($("#seleccion").val() == '') {
		return false;
	}
	return true;
}

function escribirHeadTableCorreos() {

	var tablaResultados = document.getElementById("listadoResultados");
	$(tablaResultados).find('tbody').remove();
	var tr, td, th, thead, nodoTexto, nodoBtnFiltroAvanzado;
	thead = document.createElement('thead');
	thead.setAttribute('style', "cursor:default;width:100%;display:block;");
	tr = document.createElement('tr');
	tr.setAttribute("style","display:table;width:100%");
	//SE CREA LA CABECERA DE LA TABLA
	for (var i = 0; i < tiposFiltro.length; i++) {
			th = document.createElement('th');
			switch (i){
				case 0:
					th.setAttribute("style","width:7%");
					break;
				case 1:
					th.setAttribute("style","width:32%");
					break;
				case 2:
					th.setAttribute("style","width:37%");
					break;	
				case 3:
					th.setAttribute("style","width:12%");
					break;
				case 4:
					th.setAttribute("style","width:12%");
					break;							
			}


			if (tiposFiltro[i] != '') {
				nodoTexto = document.createTextNode(tiposFiltro[i]+" ");
				th.appendChild(nodoTexto);

				nodoBtnFiltroAvanzado = document.createElement('a');
				nodoBtnFiltroAvanzado.setAttribute('class', "btn btn-mini clickover");
				nodoBtnFiltroAvanzado.setAttribute('id', 'cabeceraTabla_'+tiposFiltro[i]);
				//$(nodoBtnFiltroAvanzado).attr('title', "Buscar por "+tiposFiltro[i]);
				nodoBtnFiltroAvanzado.setAttribute('style', "cursor:pointer;widht");
					span = document.createElement('i');
					span.setAttribute('class', "icon-filter clickover");
					//span.setAttribute('style', "vertical-align:middle;");
				nodoBtnFiltroAvanzado.appendChild(span);

				th.appendChild(nodoBtnFiltroAvanzado);

				// Se comprueba que existe un elemento para dicha posición del Array inputAllowedFiltro. En caso de que no, se setea en string vacío
				inputAllowedFiltro[i] = typeof(inputAllowedFiltro[i]) == 'undefined' ? "" : inputAllowedFiltro[i];
				/// Se asigna el valor del atributo pattern que tendrá el input.
				var inputPattern = inputAllowedFiltro[i] != "" ? 'pattern="'+inputAllowedFiltro[i]+'"' : "";

				var divBtnCerrar = '<div class="btn btn-mini" data-dismiss="clickover" data-toggle="clickover" data-clickover-open="1" style="position:absolute; margin-top:-40px; margin-left:180px;"><i class="icon-remove"></i></div>';
				
				var divs = divBtnCerrar+'<div class="input-append"><input class="span9 popovers" '+inputPattern+' id="'+ prefijo_tipoFiltro + i +'" type="text" onkeypress="getDataSource(this)" onChange="cambioTipoFiltroCorreos(this)" ><button class="btn" onClick="cambioTipoFiltroCorreos(this.parentNode.firstChild)" type="button"><i class="icon-search"></i></button></div>';
			
			}
			else { //Esto es para el caso de los checkbox que marcan toda la tabla
				nodoCheckeable = document.createElement('input');
				nodoCheckeable.setAttribute('data-previous', "false,true,false");
				nodoCheckeable.setAttribute('type', "checkbox");
				nodoCheckeable.setAttribute('id', "selectorTodos");
				nodoCheckeable.setAttribute('title', "Seleccionar todos");
				th.appendChild(nodoCheckeable);
			}
						
		tr.appendChild(th);
	}
	thead.appendChild(tr);
	
	tablaResultados.appendChild(thead);
}

</script>

<script>
	var tiposFiltro = ["", "Para", "Mensaje", "Fecha", "Hora"]; //Debe ser escrito con PHP
	var valorFiltrosJson = ["", "", "", "", ""]; //Esta es variable global que almacena el valor de los input de búsqueda en específico
	var inputAllowedFiltro = ["[A-Za-z]+", "[A-Za-z]+", "[A-Za-z]+","([1-9][0-9]{3}-(0\\d|1[0-2])-([0-2]\\d|3[0-1])|[1-9][0-9]{3}-(0\\d|1[0-2])|[1-9][0-9]{3}|(0\\d|1[0-2])|([0-2]\\d|3[0-1]))","(^(0{0,1}\\d|1\\d|2[0-3]):([0-5]\\d):([0-5]\\d)$|([0-5]\\d):([0-5]\\d)$|([0-5]\\d)$)"];
	var prefijo_tipoDato = "correo_rec_";
	var prefijo_tipoFiltro = "tipo_filtro_";
	var url_post_busquedas = "<?php echo site_url("Correos/postEnviados") ?>";
	var url_post_historial = "<?php echo site_url("HistorialBusqueda/buscar/correos") ?>";

	//Se cargan por ajax
	$(document).ready(function() {
		escribirHeadTableCorreos();
		cambiarCorreos('inicial', 0);

		//Hace que se seleccionen todos los checkbox al presionar el selectorTodos
		$("#selectorTodos").click(function()				
		{
			var checked_status = this.checked;
			$(':checkbox').each(function()
			{
				this.checked = checked_status;
			});
		});
	});
</script>

<?php
if(isset($msj))
{
	if($msj==1)
	{
		?>
		    <div class="alert alert-success">
    			<button type="button" class="close" data-dismiss="alert">&times;</button>
    			 Eliminación de correo(s) realizada satisfactoriamente !!!
    		</div>	
		<?php
	}
	else if($msj==0)
	{
		?>
		<div class="alert alert-error">
    			<button type="button" class="close" data-dismiss="alert">&times;</button>
    			 La eliminación de correo(s) no se pudo realizar. Contacta al administrador del sistema.
    		</div>		

		<?php
	}
	unset($msj);
}
?>

<fieldset id="cuadroEnviados">
	<legend>Correos enviados</legend>
	<?php
		$contador=0;
		$offset=0;

		if($cantidadCorreos<$offset+20)
			$limite=$cantidadCorreos;
		else
			$limite=$offset+20;

		$comilla= "'";

	?>

	<div class="row-fluid">
		<div class="span6">
			<div class="controls controls-row">
			    <div class="input-append span7">
					<input id="filtroLista" type="text" onkeypress="getDataSource(this)" onChange="cambiarCorreos('inicial', 0)" placeholder="Filtro búsqueda">
					<button class="btn" onClick="cambiarCorreos('inicial', 0)" title="Iniciar una búsqueda considerando todos los atributos" type="button"><i class="icon-search"></i></button>
				</div>
				<button class="btn" onClick="limpiarFiltrosCorreo()" title="Limpiar todos los filtros de búsqueda" type="button"><i class="caca-clear-filters"></i></button>
			</div>
		</div>
		<div class="span6" >
			<button class ="btn pull-right" onclick="eliminarCorreo() "><div class="btn_with_icon_solo">Ë</div> Eliminar seleccionados</button><br><br>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span6" >
			
		</div>
		<div class="span6" >
			<ul id="pager" class="pager" style="text-align:right; margin:0px" >
				<span id="mostrando">  mostrando <?php echo ($offset+1)."-".$limite. " de: ".$cantidadCorreos; ?></span>
				<li id="ant" class="disabled" ><a href="#"><div class="btn_with_icon_solo"><</div></a></li>
				<?php 
				if ($limite<$cantidadCorreos) {
					?>
					<li id ="sig" onClick="cambiarCorreos('sig',<?php echo $offset; ?>)"><a href="#"><div class="btn_with_icon_solo">=</div></a></li>
					<?php
				}
				else {
					?>
					<li id ="sig" onClick="cambiarCorreos('sig',<?php echo $offset; ?>)" class="disabled"><a href="#"><div class="btn_with_icon_solo">=</div></a></li>
					<?php
				}
				?>
			</ul>
		</div>
	</div>

	<?php
		
		$attributes = array('onsubmit' => 'return evitarEnvioVacio()', 'id' => 'formu', 'name' => "formulario");
		echo form_open('Correo/EliminarCorreo', $attributes);
		
	?>
		<div class="row-fluid">
			<div class="span12" style="border:#cccccc 1px solid;  height:400px; -webkit-border-radius: 4px;">
				<table id="listadoResultados" class="table table-hover" style="height:400px;width:100%; display:block;">
					
					<!-- Acá va el tbody cargado por ajax -->

				</table>
			</div>
		</div>
		

		<!-- Modal de confirmación -->
		<div id="modalConfirmacion" class="modal hide fade">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Confirmación</h3>
			</div>
			<div class="modal-body">
				<p>Se van a eliminar los correos seleccionados ¿Está seguro?</p>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn"><div class="btn_with_icon_solo">Ã</div>&nbsp; Aceptar</button>
				<button class="btn" type="button" data-dismiss="modal"><div class="btn_with_icon_solo">Â</div>&nbsp; Cancelar</button>
			</div>
		</div>

		<!-- Modal de no ha seleccionado a nadie -->
		<div id="modalSeleccioneAlgo" class="modal hide fade">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>No ha seleccionado un correo</h3>
			</div>
			<div class="modal-body">
				<p>Por favor seleccione un correo para eliminar y vuelva a intentarlo</p>
			</div>
			<div class="modal-footer">
				<button class="btn" type="button" data-dismiss="modal">Cerrar</button>
			</div>
		</div>

		<input type="hidden" id="seleccion" name="seleccion" value="">
	</form>
</fieldset>


<fieldset id="cuadroDetalleCorreo" style="display:none;">
	<legend>Correos enviados ::: detalles</legend>
	<div class="row-fluid">
		<div class="span6">
			Detalles del correo seleccionado
		</div>
		<div class="span6">
			<button class="btn pull-right" onclick="volverCorreosEnviados()" ><div class="btn_with_icon_solo"><</div> Volver</button>
		</div>
		
	</div>
	</br>
	<pre class="detallesEmail">
<div style="text-align:right; margin-bottom:0px;">Fecha: <b  id="fecha"> </b>  <b style="text-align:right;" id="hora"></b></div>
  Para:     <b  class="txt"  id="destinos"></b> <div href="#" rel="details"  class="btn btn_with_icon_solo" style="width: 15px; height: 15px; align:left;"><img src="/<?php echo config_item('dir_alias') ?>/img/icons/glyphicons_367_expand.png" alt=":" ></div>
  Asunto:   <b  id="asuntoDetalle"></b>
  Adjuntos: <b  class="txt"  style="display:none;" id="destinosAdjuntos">Sin archivos adjuntos</b> <!--<div id="xxx" href="#" rel="details2"  class="btn btn_with_icon_solo" style="width: 15px; height: 15px; align:left;"><img src="/<?php echo config_item('dir_alias') ?>/img/icons/glyphicons_062_paperclip.png" alt=":" ></div>-->
<fieldset id="attach" style="display:none"><table id="files" class="files" style="height:auto;width:100%;"><tbody  style="height:auto;width:100%;"></tbody></table></fieldset>
  Cuerpo:<fieldset id="cuerpoMail" style=" min-height:250px;"></fieldset></pre>
</fieldset>
<script type="text/javascript">
  $(document).ready(function() {
  	$("[rel=details]").tooltip({
  		placement : 'bottom', 
  		html: 'true', 
  		title : '<div style="text-color:white;"><strong>Muestra detalles</strong></div>',
  		trigger:'hover',
  	});
  	
  });

    $(window).load(function() {
  	  $("[rel=details]").popover({
	placement : 'bottom', 
    content: get_popover_content,
    html: true,
    trigger: 'click'
});
  	
  });

function get_popover_content() {
	fecha=document.getElementById("fecha").innerHTML;
	hora=document.getElementById("hora").innerHTML;
	asunto=document.getElementById("asuntoDetalle").innerHTML;
	content='<table class="pop" style="  width:100%;"><tr ><td >Para:</td><td><strong>'+destinoaux+'</strong></td></tr><tr><td>Asunto: </td><td><strong>'+asunto+'</strong></td></tr><tr><td>Fecha:  </td><td><strong>'+fecha  +'</strong></td><tr><td>Hora:   </td><td><strong>'+    hora+'</strong></td></tr></table>';
        return content;
}
  

  	</script>