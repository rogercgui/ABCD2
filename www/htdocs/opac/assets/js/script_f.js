function BuscarIntegrada(base, modo, Opcion, Expresion, Coleccion, titulo_c, resaltar, submenu, Pft, mostrar_exp) {
	if (mostrar_exp != "") document.bi.action = "inicio_base.php"
	document.bi.base.value = base
	document.bi.Opcion.value = Opcion
	document.bi.modo.value = modo
	document.bi.home.value = mostrar_exp

	if (Opcion == "free") {
		document.bi.coleccion.value = Coleccion
		document.bi.Expresion.value = Expresion
	}

	if (Opcion == "directa") {
		document.bi.Expresion.value = Expresion
		document.bi.titulo_c.value = titulo_c
		document.bi.resaltar.value = resaltar
		document.bi.submenu.value = submenu
		document.bi.Pft.value = Pft
		document.bi.mostrar_exp.value = mostrar_exp
	}
	document.bi.submit()
}


function EnviarReserva() {
	hayerror = 0
	document.enviarreserva.items_por_reservar.value = items_por_reservar;
	if (Trim(document.enviarreserva.usuario.value) == '') {
		hayerror = 1
	}

	if (hayerror == 1) {
		return false
	} else {
		document.enviarreserva.submit()
	}
}


function validateForm() {
	return true; // Allow submission
}

function MarkExpr(term) {
	highlightSearchTerms(term);
	console.log(term);
}

// buscar_integrada.php - line 568
window.onload = function () {
	const highlightElements = document.querySelectorAll('.highlight-terms');
	highlightElements.forEach(element => {
		const expression = element.getAttribute('data-expression');
		if (expression) {
			highlightSearchTerms(expression);
			console.log('Expressão de busca: '+expression);
		}
	});
};


// To top
jQuery(function () {
	jQuery(document).on('scroll', function () {
		if (jQuery(window).scrollTop() > 100) {
			jQuery('.smoothscroll-top').addClass('show');
		} else {
			jQuery('.smoothscroll-top').removeClass('show');
		}
	});
	jQuery('.smoothscroll-top').on('click', scrollToTop);
});

function scrollToTop() {
	verticalOffset = typeof (verticalOffset) != 'undefined' ? verticalOffset : 0;
	element = jQuery('body');
	offset = element.offset();
	offsetTop = offset.top;
	jQuery('html, body').animate({
		scrollTop: offsetTop
	}, 600, 'linear').animate({
		scrollTop: 25
	}, 200).animate({
		scrollTop: 0
	}, 150).animate({
		scrollTop: 0
	}, 50);
}

// Adiciona o evento de clique APENAS se o elemento 'enviarDetalhes' existir na página
const enviarDetalhesButton = document.getElementById('enviarDetalhes');
if (enviarDetalhesButton) {
	enviarDetalhesButton.addEventListener('click', function () {
		document.detailed.submit();
	});
}


function handleCookieVisibility() {
	const cookie = getCookie('ABCD');
	const cookieDiv = document.getElementById('cookie_div');
	if (!cookieDiv) return; // evita erro se não existir

	if (cookie && cookie.trim() !== "") {
		cookieDiv.style.display = 'inline-block';
	} else {
		cookieDiv.style.display = 'none';
	}
}

document.addEventListener('DOMContentLoaded', function () {
	document.querySelectorAll('.facet-scroll-list').forEach(el => {
		let startY = 0,
			startScroll = 0;

		// Evento de início do toque
		el.addEventListener('touchstart', function (e) {
			if (e.touches.length !== 1) return;
			startY = e.touches[0].pageY;
			startScroll = el.scrollTop;
		}, {
			passive: true
		});

		// Evento de movimento do toque
		el.addEventListener('touchmove', function (e) {
			if (e.touches.length !== 1) return;
			const dy = startY - e.touches[0].pageY;
			// Apenas executa a rolagem se houver conteúdo para rolar
			if (el.scrollHeight > el.clientHeight) {
				el.scrollTop = startScroll + dy;
				e.stopPropagation(); // Evita que a página inteira role junto
				e.preventDefault();  // Previne o comportamento padrão de rolagem do navegador
			}
		}, {
			passive: false
		});
	});
});


// Busca por coleção
$(document).ready(function () {

	/**
	 * Parte 1: Atualizar o dropdown e o input hidden (Sem alteração)
	 */
	$(document).on('click', '.dropdown-item-select', function (e) {
		e.preventDefault();

		var selectedValue = $(this).data('value');
		var selectedText = $(this).data('text');

		$(this).closest('.dropdown').find('.dropdown-toggle').text(selectedText);
		$('#target_db_input').val(selectedValue);
		$(this).closest('.dropdown-menu').find('.dropdown-item-select').removeClass('active');
		$(this).addClass('active');
	});


	/**
	 * Parte 2: Lógica de submissão do formulário (COM CORREÇÕES)
	 */

	// Localiza o formulário de busca
	var $searchForm = $('#termo-busca').closest('form');

	// Armazena a URL de 'action' original (provavelmente "buscar_integrada.php")
	var originalFormAction = '';
	if ($searchForm.length > 0) {
		originalFormAction = $searchForm.attr('action');
	}

	if ($searchForm.length > 0) {
		$searchForm.on('submit', function (e) {

			var targetDb = $('#target_db_input').val(); // O valor selecionado (ex: "", "biblio", "col:...")
			var searchTerm = $('#termo-busca').val(); // O termo digitado

			// Garante que os valores padrão sejam restaurados antes de cada envio
			$('#target_db_input').prop('disabled', false).attr('name', 'target_db');
			$(this).attr('action', originalFormAction); // Restaura a action original

			// Caso 3: Se uma COLEÇÃO foi selecionada (valor começa com "col:")
			if (targetDb.startsWith('col:')) {

				e.preventDefault(); // Impede a submissão padrão

				// **** CORREÇÃO 1: Remover o prefixo "col:" ****
				// O wxis espera "TPR_a" e não "col:TPR_a"
				var coleccion = targetDb.replace('col:', '');

				var formAction = $(this).attr('action'); // Pega a action (buscar_integrada.php)
				var params = [];

				// Pega todos os outros inputs do formulário (lang, modo, etc.)
				$(this).find('input, select').each(function () {
					var $input = $(this);
					var name = $input.attr('name');

					if (!name || name === 'Sub_Expresion' || name === 'Expresion' || name === 'alcance' || name === 'Opcion' || name === 'target_db') {
						return;
					}
					params.push(encodeURIComponent(name) + '=' + encodeURIComponent($input.val()));
				});

				// Constrói a expressão com a variável 'coleccion' (sem o prefixo)
				var expresion = "TW_" + searchTerm + " and " + coleccion;

				// Adiciona os parâmetros específicos
				params.push("Expresion=" + encodeURIComponent(expresion));
				params.push("alcance=and");
				params.push("Opcion=directa");

				// Redireciona para a URL de busca montada
				window.location.href = formAction + '?' + params.join('&');

			}
			// Caso 2: Se uma BASE DE DADOS foi selecionada (valor não é "" e não começa com "col:")
			else if (targetDb !== "") {

				// 1. Renomeia o input para 'base'
				//    (O backend 'buscar_integrada.php' deve estar esperando por $_REQUEST['base'])
				$('#target_db_input').attr('name', 'base');

				// 2. NÃO MUDA O ACTION. 
				//    O formulário será submetido para "buscar_integrada.php"
				//    enviando: ?Sub_Expresion=... & base=... & lang=...
				// (Sem e.preventDefault(), deixa o formulário enviar)
			}
			// Caso 1: Se NADA foi selecionado (valor é "")
			else {

				// A 'action' original ("buscar_integrada.php") já foi restaurada
				// Apenas desabilitamos o input hidden para não sujar a URL

				$('#target_db_input').prop('disabled', true);
				
				// Destino: buscar_integrada.php
				// Parâmetros: Sub_Expresion=... (e outros, como 'lang')
			}
		});
	}
});