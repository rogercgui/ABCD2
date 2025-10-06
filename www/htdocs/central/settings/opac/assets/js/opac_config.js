/*
 * JavaScript Unificado para a área de Configuração do OPAC
 */

// Função para controlar todos os elementos accordion na página
function initializeAccordions() {
    var acc = document.getElementsByClassName("accordion");
    for (var i = 0; i < acc.length; i++) {
        acc[i].addEventListener("click", function () {
            this.classList.toggle("active");
            var panel = this.nextElementSibling;
            if (panel) {
                panel.classList.toggle("show");
            }
        });
    }
}

// Função para destacar a seção ativa no menu lateral
function highlightActiveMenu() {
    if (typeof idPage !== 'undefined') {
        var activeMenuButton = document.getElementById(idPage);
        if (activeMenuButton) {
            var panel = activeMenuButton.nextElementSibling;
            if (panel) {
                panel.classList.add("show");
                activeMenuButton.classList.add("active");
            }
        }
    }
}

// Funções de navegação globais
function EnviarForma(Proceso) {
    document.opciones_menu.action = Proceso;
    document.opciones_menu.submit();
}

function SeleccionarBase(Base) {
    document.opciones_menu.action = "procesos_base.php";
    document.opciones_menu.base.value = Base;
    document.opciones_menu.submit();
}

function SeleccionarProceso(Proceso, Base, Conf) {
    document.opciones_menu.action = Proceso;
    document.opciones_menu.base.value = Base;
    document.opciones_menu.o_conf.value = Conf;
    document.opciones_menu.submit();
}


// Função para trocar o idioma via URL (GET)
function changeOpacLanguage(selectElement) {
    const newLang = selectElement.value;
    const url = new URL(window.location.href);
    url.searchParams.set('lang', newLang);
    window.location.href = url.toString();
}

// Executa as funções quando o DOM estiver pronto
document.addEventListener("DOMContentLoaded", function () {
    initializeAccordions();
    highlightActiveMenu();
});
