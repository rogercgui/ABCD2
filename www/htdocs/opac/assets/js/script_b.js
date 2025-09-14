/*
20210604 gascensio Added CruzarABCD
*/
accion=""

function showhide(what,what2){
	var what = document.getElementById(what);
	if (what.style.display=="none"){
		what.style.display="inline";
		if (arguments.length>1){
			what2.src="assets/images/buttonm.gif"
		}
	}else{
		what.style.display="none"
		if (arguments.length>1){
			what2.src="assets/images/buttonp.gif"
		}
		//document.getElementById(what2).src=Open.src
	}
}

function ProximaPagina(pagina,registro){
	document.continuar.desde.value=registro
	document.continuar.pagina.value=pagina
	document.continuar.submit()
}

function VerExistencias(base,kardex,inventario_r,inventario_k){
	document.continuar.existencias.value=base+"|"+kardex+"|"+inventario_r+"|"+inventario_k
	document.continuar.action="existencias.php"
	document.continuar.submit()
}


function BuscarBase(base){
	document.buscar.action="buscar_integrada.php"
	document.buscar.base.value=base
	document.buscar.desde.value=1
	document.buscar.count.value=25
	document.buscar.resaltar.value="S"
	document.buscar.Expresion.value=Expresion
	document.buscar.Opcion.value="integrada"
	document.buscar.submit()

}

function ProximaBase(base){
	document.buscar.action="buscar_integrada.php"
	document.buscar.desde.value=1
	document.buscar.base.value=base
	document.buscar.pagina.value=1
	document.buscar.facetas.value=""
	document.buscar.Formato.value=""
	document.buscar.submit()

}

function CambiarFormato(){
	Ctrl=document.getElementById("cambio_Pft")
	ix=Ctrl.selectedIndex
	Formato=Ctrl.options[ix].value
	document.continuar.Formato.value=Formato
	document.continuar.desde.value=1
	document.continuar.submit()

}

 function AbrirIndice(Letra){
  	document.diccionario.IR_A.value=Letra
  	NavegarDiccionario(this,3)
  }

function ObtenerTerminos(desde){
	Expresion=""
	Ctrl=eval("document.diccionario."+desde)
	ix=Ctrl.options.length
	if (Opcion=='libre')
		delimitador='"'
	else
		delimitador='"'

	for (i=0;i<ix;i++){
		if (Ctrl.options[i].selected || desde=="TerminosSeleccionados"){
			if (Expresion=="")
				Expresion=delimitador+Ctrl.options[i].value+delimitador
			else
				Expresion+=" "+delimitador+Ctrl.options[i].value+delimitador
		}
	}
	return Expresion

}


function CancelarDiccionario(retorno){
	switch (retorno){
		case 'A':
			document.diccionario.action="avanzada.php"
			document.diccionario.submit()
			break
		case 'B':
			document.diccionario.action="otras_busquedas.php"
			document.diccionario.submit()
			break;
		case 'C':
			document.diccionario.action="avanzada.php"
			document.diccionario.submit()
			break
		case 'D':
			document.diccionario.action="buscar_integrada.php"
			document.diccionario.submit()
			break
		default:
			document.diccionario.action=retorno
			document.diccionario.submit()
	}


}

function EjecutarBusquedaDiccionario(Accion){
	Expresion=""
	Seleccionados=ObtenerTerminos("TerminosSeleccionados")
	if (Seleccionados==""){
		alert(msgstr["sel_term"])
		return false
	}
	Expresion=Seleccionados
	document.diccionario.Seleccionados.value=Expresion;
	switch (Accion){
		case 0:
			document.diccionario.Opcion.value="buscar_diccionario"
			document.diccionario.Sub_Expresion.value=Expresion;
			document.diccionario.action="buscar_integrada.php"
			break
		case 1:
			document.diccionario.action="buscar_integrada.php"
			break
		case 2:
			document.diccionario.action="avanzada.php"
			break
	}
	document.diccionario.submit()
}

function NavegarDiccionario(F,desde){
	Seleccionados=""
 	Seleccionados=ObtenerTerminos("TerminosSeleccionados")
 	if (Seleccionados!=""){
 		document.diccionario.Seleccionados.value=Seleccionados
 	}
	switch (desde){
		case 4:
/* Más términos */
			document.diccionario.Navegacion.value="mas terminos"
			document.diccionario.submit()
			break
		case 3:
/* Ir a */
			document.diccionario.Navegacion.value="ir a"
			document.diccionario.LastKey.value=document.diccionario.IR_A.value
			document.diccionario.submit()
			break
	}
}



function BuscarPalabrasSide(){
	if (Trim(document.side.Expresion.value)=="")
		return
	document.side.submit()
}

function BuscarPalabrasTope(){
	if (Trim(document.SearchTope.Expresion.value)=="")
		return
	document.SearchTope.submit()
}

function Buscar(Ctrl){
	ix=Ctrl.selectedIndex
	document.buscar.Expresion.value=Ctrl.options[ix].value
	document.buscar.desde.value=1
	document.buscar.action="buscar_integrada.php"
	document.buscar.submit()
	Ctrl.selectedIndex=0
}

function CRUZARD(Prefijo,Termino,base){
	document.buscar.Expresion.value=""
	document.buscar.action="buscar_integrada.php"
	//document.buscar.base.value=""
	document.buscar.desde.value=1
	document.buscar.count.value=25
	document.buscar.resaltar.value="S"
	document.buscar.Opcion.value="detalle"
	document.buscar.prefijo.value=Prefijo
	document.buscar.Sub_Expresion.value=Termino
	document.buscar.submit()
}

function CruzarABCD(Termino,Prefijo){
	document.buscar.Expresion.value=""
	document.buscar.action="buscar_integrada.php"
	//document.buscar.base.value=""
	document.buscar.desde.value=1
	document.buscar.count.value=25
	document.buscar.resaltar.value="S"
	document.buscar.Opcion.value="detalle"
	document.buscar.prefijo.value=Prefijo
	document.buscar.Sub_Expresion.value=Termino
	document.buscar.submit()
}

//BUSQUEDA AVANZADA

Expresion=""
Operadores=""
Campos=""
function LimpiarBusqueda() {
  for (i=0; i<document.forma1.camp.length; i++){
      document.forma1.Sub_Expresiones[i].value=""
      }
}

function BusquedaAvanzada(){
	document.diccio.action="avanzada.php"
    document.diccio.Opcion.value="integrada"
	document.diccio.submit()
}


function DiccionarioLibre(Nivel){
	ix=document.getElementById(document.libre.coleccion)
	if (ix!=null){
		Ctrl=document.libre.coleccion
		ixc=Ctrl.length
		colec=""
		if (ixc){
			for (i=0;i<ixc;i++){
				if (Ctrl[i].checked){
					colec=Ctrl[i].value
					break
				}
			}
		}

		if (colec!=""){
			document.diccio_libre.coleccion.value=colec
		}
	}

	if (document.getElementById('and').checked)
		document.diccio_libre.alcance.value=document.getElementById('and').value
	else
		document.diccio_libre.alcance.value=document.getElementById('or').value
	document.diccio_libre.submit()
}

function Diccionario(jx){
    j=document.forma1.Sub_Expresiones.length
    if (j==undefined)
    	j=document.forma1.camp.selectedIndex
    else
		j=document.forma1.camp[jx].selectedIndex

	a=dt[j]
	diccio=a.split('|')
	nombrec=diccio[0]
	prefijo=diccio[1]
	ArmarBusqueda()
	document.diccio.Sub_Expresion.value=Expresion
	document.diccio.Campos.value=Campos
	document.diccio.Operadores.value=Operadores
	document.diccio.campo.value=nombrec
	document.diccio.prefijo.value=prefijo
	document.diccio.Diccio.value=jx
	document.diccio.submit()

}

function ArmarBusqueda(){
    Expresion=""
	Operadores=""
	Campos=""
	se = document.getElementById('tag900_0_n');
	j=document.forma1.Sub_Expresiones.length
	if (j==undefined){
		Expresion=document.forma1.Sub_Expresiones.value
		ixSel=document.forma1.camp.selectedIndex
		cc=document.forma1.camp.options[ixSel].value
		Campos=cc
		Operadores=""
		return
	}
	for (i=0;i<j;i++){
		if (document.forma1.Sub_Expresiones[i].value=="") document.forma1.Sub_Expresiones[i].value=" "
		if (Expresion==""){
			Expresion=document.forma1.Sub_Expresiones[i].value+" ~~~ "
		}else{
			Expresion=Expresion+document.forma1.Sub_Expresiones[i].value+" ~~~ "
		}

		ixSel=document.forma1.camp[i].selectedIndex
		cc=document.forma1.camp[i].options[ixSel].value
		if (Campos==""){
			Campos=cc
		}else{
			Campos=Campos+" ~~~ "+cc
		}
		if (i<j-1){
			icampo=document.getElementById('oper_'+i)
			if (icampo.type=="select-one"){
				ixSel=document.forma1.oper[i].selectedIndex
				cc=document.forma1.oper[i].options[ixSel].value
				if (Operadores==""){
					Operadores=cc
				}else{
					Operadores=Operadores+" ~~~ "+cc
				}
			}
		}
	}
}

function PrepararExpresion(Destino){
//	AbrirVentanaResultados()

	ArmarBusqueda()
	if (Expresion==""){
		alert(msgstr["miss_se"])
		return
	}else{
		document.diccio.Campos.value=Campos
	}
	var mensajes = document.getElementById("mensajes");
	mensajes.innerHTML ="<img src=assets/img/loading.gif>"
	document.diccio.Sub_Expresion.value=Expresion
	document.diccio.Operadores.value=Operadores
	document.diccio.action="buscar_integrada.php"
	document.diccio.submit()
}

//GALERIA DE IMAGENES

function Presentacion(base,Expresion,Pagina,Formato){
	if (document.getElementById("desde")){
		desde=document.continuar.desde.value-document.continuar.count.value
		if (desde<=0) desde=1
		document.buscar.desde.value=desde
	}
	document.buscar.base.value=base
	document.buscar.pagina.value=Pagina
	switch (Formato){
		case "galeria":
			document.buscar.action="slide_integrada.php";
			break
		case "ficha":
			document.buscar.action="buscar_integrada.php";
			break
	}
	//document.buscar.Opcion.value="integrada"
	document.buscar.Expresion.value=Expresion
	document.buscar.submit()

}

var i = 0;
iactual=0;
var image = new Array();
var link= new Array();
var titulo= new Array()
var ficha=new Array()

var k = image.length-1;

/*
function ProximaImagen(){
	iactual=iactual+1
	if (iactual>=image.length) iactual=0
	swapImage(iactual)

}

function AnteriorImagen(){
	iactual=iactual-1
	if (iactual<0) iactual=0
	swapImage(iactual)

}

function swapImage(i){
	iactual=i;
	var el = document.getElementById("mydiv");
	el.innerHTML=titulo[i];
	var img = document.getElementById("slide");
	img.src= image[i];
	var a = document.getElementById("link");
	a.href= link[i];
	var gal = document.getElementById("galeria");
	gal.innerHTML=ficha[i];
}
*/

function addLoadEvent(func) {
	var oldonload = window.onload;
	if (typeof window.onload != 'function') {
		window.onload = func;
	} else  {
		window.onload = function() {
			if (oldonload) {
				oldonload();
			}
			func();
		}
	}
}

addLoadEvent(function() {
		//swapImage(0);
	}
);

function ActivarIndice(titulo,columnas,Opcion,count,posting,prefijo,base){
	document.activarindice.titulo.value=titulo
	document.activarindice.columnas.value=columnas
	document.activarindice.Opcion.value=Opcion
	document.activarindice.count.value=count
	document.activarindice.posting.value=posting
	document.activarindice.prefijo.value=prefijo
	document.activarindice.base.value=base
	document.activarindice.submit()
}

function ValidarUsuario(){
	if (Trim(document.estado_de_cuenta.usuario.value)==""){
		alert("Debe ingresar su código de usuario")
		return
	}
	document.estado_de_cuenta.submit()
}

function SendTo(Accion,Data){
	switch (Accion){
		case "word":
			document.buscar.action ="components/sendtoword.php"
			break
		case "print_one":
			window.open("components/sendtoprint.php?cookie=" + Data, "", "width=600, height=600, resizable, scrollbars")
			//document.buscar.target = window.open("components/sendtoprint.php?base=suggestions&Expresion=CN_" + Expresion, "show", " width=550,height=400,resizable, scrollbars")
			//document.buscar.target = "_blank"
			break
		case "iso":
			window.open("components/sendtoiso.php?cookie=cookie=" + Data, "", "width=600, height=600, resizable, scrollbars")
			//document.buscar.target = window.open("components/sendtoprint.php?base=suggestions&Expresion=CN_" + Expresion, "show", " width=550,height=400,resizable, scrollbars")
			//document.buscar.target = "_blank"
			break
		case "mail_one":
			document.buscar.action="index.php"
			break
		case "reserve_one":
		    if (WEBRESERVATION!="Y"){
		    	alert(msgstr["reserv_no"])
		    	return
		    }
			document.buscar.action="index.php"
			break
		case "xml":
			document.buscar.action ="components/sendtoxml.php"
			document.buscar.target="_blank"
			break
	}
    document.buscar.Accion.value=Accion
	document.buscar.cookie.value=Data
	document.buscar.submit()
}

function ChangeLanguage() {
	var langSelect = document.getElementById("lang");
	var langcode = langSelect.options[langSelect.selectedIndex].value;
	var actualScript = window.location.pathname;

	// Atualiza todos os formulários que tenham campo "lang"
	for (var i = 0; i < document.forms.length; i++) {
		if (document.forms[i].elements["lang"]) {
			document.forms[i].elements["lang"].value = langcode;
		}
	}

	// Verifica se o formulário changelanguage existe
	if (document.forms["changelanguage"]) {
		document.forms["changelanguage"].elements["lang"].value = langcode;
		document.forms["changelanguage"].action = actualScript;
		document.forms["changelanguage"].submit();
		console.log("The OPAC is in language: " + langcode);
	} else {
		console.warn("Formulário 'changelanguage' não encontrado.");
	}
}

/*
function openNavFacetas() {
  document.getElementById("SidenavFacetas").style.width = "200px";
}

function closeNavFacetas() {
  document.getElementById("SidenavFacetas").style.width = "0";
}
*/

function closeNav() {
    document.getElementById("sidebar").style.width = "0";
    document.getElementById("sidebar").style.marginLeft = "-30px";
    document.getElementById("page").style.marginLeft = "0px"
    document.getElementById("page").style.width = "95%";
}

function Facetas(Expresion){
	document.buscar.facetas.value=Expresion
	document.buscar.submit()
}

function SolicitarPrestamo(CN,Base,otro){
	document.buscar.action="../prestamos/prestamos.php"
	document.buscar.cookie.value=CN
	document.buscar.submit()

}

/** BUTTONS **/

function SendToWord(){
		document.regresar.action="components/sendtoword.php"
		document.regresar.target=""
		document.regresar.submit()
		document.regresar.action="buscar_integrada.php"
	}

function SendToXML(seleccion){
		cookie=document.regresar.cookie.value
		document.regresar.cookie.value=seleccion
		document.regresar.action="components/sendtoxml.php"
		document.regresar.target="_blank"
		document.regresar.submit()
		document.regresar.action="buscar_integrada.php"
		document.regresar.target=""
		document.regresar.cookie=cookie
	}

function SendToISO(){
		document.regresar.action="components/sendtoiso.php"
		document.regresar.cookie = cookie
		document.regresar.submit()
		document.regresar.action="buscar_integrada.php"
		document.regresar.target=""

	}

function EnviarCorreo() {
	hayerror = 0
	document.correo.comentario.value = escape(document.correo.comentario.value)
	if (Trim(document.correo.email.value) == '' || document.correo.email.value.indexOf('@') == -1) {
		alert('Correo inválido')
		hayerror = 1
	}

	if (hayerror == 1) {
		return false
	} else {
		document.correo.submit()
	}
}

function SendToPrint(){
		cookie = document.regresar.cookie.value
		window.open("components/sendtoprint.php?cookie=" + cookie, "", "width=600, height=600, resizable, scrollbars");

		//cookie = document.regresar.cookie.value
		//document.regresar.cookie.value = seleccion
		//document.regresar.action = "components/sendtoprint.php"
		//document.regresar.target = "_blank"
		//document.regresar.submit()
	}

  	function ShowHide(myDiv) {
  		if (myDiv=="myMail"){
  			document.getElementById("myReserve").style.display="none"
  		}else{
  			document.getElementById("myMail").style.display="none"
  			if (Total_No>=contador){
  				//alert("<?php echo $msgstr["front_nothing_to_reserve"]?>")
  				return
  			}
  		}
  		var x = document.getElementById(myDiv);
  		if (x.style.display === "none") {
    		x.style.display = "block";
  		} else {
    		x.style.display = "none";
  		}
	}

/* Clears cookies and returns to the home page */
function deleteAllCookies() {
	var cookies = document.cookie.split(";");

	for (var i = 0; i < cookies.length; i++) {
		var cookie = cookies[i];
		var eqPos = cookie.indexOf("=");
		var name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
		document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT";
	}
}



// Importado do alfabetico.php

function Localizar(Expresion, Existencias) {
	Expr = Expresion.split('$#$')
	document.indiceAlfa.Sub_Expresion.value = Expresion
	//document.indiceAlfa.letra.value = primero
	document.indiceAlfa.Opcion.value = "detalle"
	document.indiceAlfa.action = "buscar_integrada.php"
	document.indiceAlfa.Existencias.value = Existencias
	document.indiceAlfa.submit()
}


function RefinF(Expresion, ExprArm, Base) {
	// Assembles the combined expression with the boolean AND operator
	const novaExpressao = `(${Expresion}) and (${ExprArm})`;

	// Defines the values in the form
	const form = document.forms['filtro'];
	if (!form) {
		console.error("Form 'filter' not found.");
		return;
	}

	form.base.value = Base;
	form.Expresion.value = novaExpressao;
	form.Opcion.value = "directa";
	form.action = "buscar_integrada.php";
	form.submit();
}

function processarTermosLivres() {
	const input = document.getElementById('termo-busca');
	const termos = input.value.trim();

	if (!termos) {
		alert("Type in at least one term.");
		return;
	}

	const termosArray = termos.split(/\s+/).map(t => `"TW_${t}"`);
	const novaExpressao = '(' + termosArray.join(' and ') + ')';

	const inputExpresion = document.getElementById('Expresion');
	let expresionAtual = inputExpresion.value.trim();

	// If you have a previous expression, add it with AND
	if (expresionAtual) {
		expresionAtual = `(${expresionAtual}) and ${novaExpressao}`;
	} else {
		expresionAtual = novaExpressao;
	}

	inputExpresion.value = expresionAtual;

	// Submit the form
	document.getElementById('facetasForm').submit();
}


function removerTermo(termoRemover) {
	let campoExp = document.getElementById('Expresion');
	let expresion = campoExp.value;
	const termosAtivosDiv = document.getElementById('termosAtivos');
	const botoesTermo = termosAtivosDiv.getElementsByClassName('termo');
	const linkPaginaInicial = termosAtivosDiv.dataset.linkInicial;

	// Standardising spaces
	expresion = expresion.replace(/\s+/g, ' ').trim();

	// Remove the term (with or without brackets and AND before/after)
	const termoRegex = new RegExp(`(\\s+and\\s+)?\\(?${termoRemover.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}\\)?(\\s+and\\s+)?`, 'i');

	expresion = expresion.replace(termoRegex, (match, andBefore, andAfter) => {
		if (andBefore && andAfter) return ' and ';
		return '';
	});

	// Clean up extra ANDs and edges
	expresion = expresion.replace(/^\s*and\s*|\s*and\s*$/gi, '').replace(/\s+and\s+/gi, ' and ').trim();

	// Reapply brackets only around terms
	if (expresion) {
		let termos = expresion
			.split(/\s+and\s+/i)
			.map(t => t.replace(/[()"]/g, '').trim()) // remove brackets and inverted commas
			.filter(t => t !== '') // eliminates empty terms
			.map(t => `(${t})`); // reapply brackets correctly

		expresion = termos.join(' AND ');
	}

	// Updates the field
	campoExp.value = expresion;

	if (botoesTermo.length <= 1) {
		window.location.href = linkPaginaInicial;
	} else {
		const url = new URL(window.location.href);
		url.searchParams.set('Expresion', expresion);
		window.location.href = url.toString();
	}
}


function clearAndRedirect(link) {
	deleteAllCookies();

	// Tenta obter o parâmetro lang da URL atual
	const urlParams = new URLSearchParams(window.location.search);
	const lang = urlParams.get("lang");

	const hasQuery = link.includes("?");
	const separator = hasQuery ? "&" : "?";

	// Redireciona com o parâmetro lang atual (se houver)
	const finalLink = lang ? link + separator + "lang=" + encodeURIComponent(lang) : link;
	document.location = finalLink;
}


// MODAL PERMALINK

document.addEventListener('DOMContentLoaded', function () {
	var modalElement = document.getElementById('registroModal');
	if (modalElement) {
		var modal = new bootstrap.Modal(modalElement);
		modal.show();
	}
});


	function copiarLink() {
        const url = window.location.href;
	const btn = document.getElementById('btnCopiar');
	const originalHTML = btn.innerHTML;

        navigator.clipboard.writeText(url).then(() => {
		// Feedback visual com ícone de check
			btn.innerHTML = '<i class="fas fa-clipboard"></i> Copiado!';
			btn.classList.remove('btn-outline-primary');
			btn.classList.add('btn-success');

            setTimeout(() => {
				btn.innerHTML = '<i class="far fa-clipboard"></i> Copiar link direto';
				btn.classList.remove('btn-success');
				btn.classList.add('btn-outline-primary');
            }, 2000);
        }).catch(err => {
		alert("Erro ao copiar: " + err);
        });
    }
