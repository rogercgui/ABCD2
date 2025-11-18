		document.cookie = 'ABCD; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/;SameSite=Lax'

		/* Marcado y presentación de registros*/
		function getCookie(cname) {
			var name = cname + "=";
			var decodedCookie = decodeURIComponent(document.cookie);
			var ca = decodedCookie.split(';');
			for (var i = 0; i < ca.length; i++) {
				var c = ca[i];
				while (c.charAt(0) == ' ') {
					c = c.substring(1);
				}
				if (c.indexOf(name) == 0) {
					return c.substring(name.length, c.length);
				}
			}
			return "";
		}

		function Seleccionar(Ctrl) {
			cookie = getCookie('ABCD')
			if (Ctrl.checked) {
				if (cookie != "") {
					c = cookie + "|"
					if (c.indexOf(Ctrl.name + "|") == -1)
						cookie = cookie + "|" + Ctrl.name
				} else {
					cookie = Ctrl.name
				}
			} else {
				sel = Ctrl.name + "|"
				c = cookie + "|"
				n = c.indexOf(sel)
				if (n != -1) {
					cookie = cookie.substr(0, n) + cookie.substr(n + sel.length)
				}

			}
			document.cookie = "ABCD=" + cookie
			Ctrl = document.getElementById("cookie_div")
			Ctrl.style.display = "inline-block"
		}

/* Marcado y presentación de registros*/
/*
function getCookie(cname) {
	var name = cname + "=";
	var decodedCookie = decodeURIComponent(document.cookie);
	var ca = decodedCookie.split(';');
	for (var i = 0; i < ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) == ' ') {
			c = c.substring(1);
		}
		if (c.indexOf(name) == 0) {
			return c.substring(name.length, c.length);
		}
	}
	return "";
}
*/
function Seleccionar(Ctrl) {
	cookie = getCookie('ABCD')
	if (Ctrl.checked) {
		if (cookie != "") {
			c = cookie + "|"
			if (c.indexOf(Ctrl.name + "|") == -1)
				cookie = cookie + "|" + Ctrl.name
		} else {
			cookie = Ctrl.name
		}
	} else {
		sel = Ctrl.name + "|"
		c = cookie + "|"
		n = c.indexOf(sel)
		if (n != -1) {
			cookie = cookie.substr(0, n) + cookie.substr(n + sel.length)
		}

	}
	document.cookie = "ABCD=" + cookie
	Ctrl = document.getElementById("cookie_div")
	Ctrl.style.display = "inline-block"
}

function delCookie() {

	// --- INÍCIO DA CORREÇÃO ---
	// Substituímos o loop 'for' que dependia do form 'continuar'
	// por 'querySelectorAll' que busca os checkboxes na página toda.

	// Seleciona todos os inputs tipo checkbox cujo ID começa com "c_"
	var checkboxes = document.querySelectorAll('input[type="checkbox"][id^="c_"]');

	// Itera sobre os checkboxes encontrados e desmarca cada um
	checkboxes.forEach(function (checkbox) {
		checkbox.checked = false;
	});
	// --- FIM DA CORREÇÃO ---

	// O resto da sua função original continua igual:
	document.cookie = 'ABCD=;';
	var Ctrl = document.getElementById("cookie_div"); // Use 'var' para declarar
	if (Ctrl) { // Boa prática: verificar se o elemento existe
		Ctrl.style.display = "none";
	}
}


function showCookie(cname) {
	cookie = getCookie(cname)
	if (cookie == "") {
		alert(msgstr["rsel_no"])
		return
	}
	//document.buscar.action = "views/view_selection.php"
	document.buscar.action = "index.php"
	document.buscar.cookie.value = cookie

	document.buscar.submit()
}


/*
		function delCookie() {
			document.cookie = 'ABCD=;';

		}
		
		var user = getCookie("ABCD");
		if (user != "") {
			alert("Welcome again " + user);
		} else {

		}
		*/