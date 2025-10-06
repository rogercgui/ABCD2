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
			console.log(expression);
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