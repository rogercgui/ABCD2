/* * get_cookies.js - Gerenciamento de seleção de registros (Cookies)
 * Corrigido para manter a função original getCookie e suportar Multi-Contexto
 */

// --- FUNÇÃO ORIGINAL (Restaurada) ---
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
	var cookie = getCookie('ABCD');
	if (Ctrl.checked) {
		if (cookie != "") {
			var c = cookie + "|"
			if (c.indexOf(Ctrl.name + "|") == -1)
				cookie = cookie + "|" + Ctrl.name
		} else {
			cookie = Ctrl.name
		}
	} else {
		var sel = Ctrl.name + "|"
		var c = cookie + "|"
		var n = c.indexOf(sel)
		if (n != -1) {
			cookie = cookie.substr(0, n) + cookie.substr(n + sel.length)
		}
	}
	// Define o cookie (adicionada expiração de sessão e path para segurança)
	document.cookie = "ABCD=" + cookie + "; path=/; SameSite=Lax";

	// Mostra a barra flutuante
	var ctrlDiv = document.getElementById("cookie_div");
	if (ctrlDiv) {
		ctrlDiv.style.display = "block";
	}
}

function delCookie() {
	// 1. Limpa o cookie definindo data passada
	document.cookie = 'ABCD=; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/; SameSite=Lax';

	// 2. Esconde a barra
	var ctrlDiv = document.getElementById("cookie_div");
	if (ctrlDiv) {
		ctrlDiv.style.display = "none";
	}

	// 3. Desmarca visualmente todos os checkboxes da página atual
	var checkboxes = document.querySelectorAll('input[type="checkbox"][id^="c_"]');
	checkboxes.forEach(function (checkbox) {
		checkbox.checked = false;
	});
}

function showCookie(cname) {
	var cookie = getCookie(cname);
	if (cookie == "") {
		// Tenta usar a mensagem traduzida, senão usa fallback
		if (typeof msgstr !== 'undefined' && msgstr["rsel_no"]) {
			alert(msgstr["rsel_no"]);
		} else {
			alert("Não há registros selecionados.");
		}
		return;
	}

	// Se o formulário de busca principal existir, usamos ele
	if (document.buscar) {
		document.buscar.action = "index.php";
		document.buscar.cookie.value = cookie;

		// --- IMPORTANTE: Injeção do Contexto (Multi-Bases) ---
		// Se estivermos num contexto (ex: ?ctx=medicina), garantimos que ele seja enviado
		if (typeof OpacContext !== 'undefined' && OpacContext !== "") {
			if (!document.buscar.ctx) {
				// Se o input hidden 'ctx' não existir no form, cria agora
				var inputCtx = document.createElement("input");
				inputCtx.type = "hidden";
				inputCtx.name = "ctx";
				inputCtx.value = OpacContext;
				document.buscar.appendChild(inputCtx);
			} else {
				// Se já existir, atualiza o valor
				document.buscar.ctx.value = OpacContext;
			}
		}
		// -----------------------------------------------------

		document.buscar.submit();
	} else {
		// Fallback: Se não houver form 'buscar', cria um form temporário
		var form = document.createElement("form");
		form.method = "POST";
		form.action = "index.php";

		var inputCookie = document.createElement("input");
		inputCookie.type = "hidden";
		inputCookie.name = "cookie";
		inputCookie.value = cookie;
		form.appendChild(inputCookie);

		if (typeof OpacContext !== 'undefined' && OpacContext !== "") {
			var inputCtx = document.createElement("input");
			inputCtx.type = "hidden";
			inputCtx.name = "ctx";
			inputCtx.value = OpacContext;
			form.appendChild(inputCtx);
		}

		document.body.appendChild(form);
		form.submit();
	}
}