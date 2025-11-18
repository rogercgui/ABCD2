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

/**
 * Adiciona uma nova linha a uma tabela, baseada em um template.
 * @param {string} tableId - O ID da <tbody> da tabela.
 * @param {string} templateRowId - O ID da <tr> modelo (que está oculta).
 * @param {string} placeholder - O texto a ser substituído nos nomes dos inputs (ex: 'ROW_PLACEHOLDER').
 */
function addDynamicRow(tableId, templateRowId, placeholder = 'ROW_PLACEHOLDER') {
    var tableBody = document.getElementById(tableId);
    if (!tableBody) {
        console.error("Tabela não encontrada: " + tableId);
        return;
    }
    var templateRow = document.getElementById(templateRowId);
    if (!templateRow) {
        console.error("Linha de template não encontrada: " + templateRowId);
        return;
    }

    var newRow = templateRow.cloneNode(true);
    var newIndex = new Date().getTime(); // Gera um índice único

    newRow.style.display = ''; // Torna a linha visível
    newRow.id = ''; // Remove o ID do template

    var inputs = newRow.querySelectorAll('input, select, textarea');
    inputs.forEach(function (input) {
        input.name = input.name.replace(placeholder, newIndex);
        if (input.type === 'radio') {
            input.value = newIndex; // Garante que o radio button tenha um valor único
        }
    });

    tableBody.appendChild(newRow);
}

/**
 * Remove a linha da tabela (<tr>) mais próxima do botão que foi clicado.
 * @param {HTMLElement} buttonElement - O botão (this) que foi clicado.
 */
function removeDynamicRow(buttonElement) {
    var row = buttonElement.closest('tr');
    if (row) {
        row.parentNode.removeChild(row);
    }
}

/**
 * Adiciona um novo bloco de elementos, baseado em um template.
 * @param {string} containerId - O ID do <div> que contém os blocos.
 * @param {string} templateBlockId - O ID do <div> modelo (que está oculto).
 * @param {string} placeholder - O texto a ser substituído nos nomes dos inputs (ex: 'BLOCK_PLACEHOLDER').
 */
function addDynamicBlock(containerId, templateBlockId, placeholder = 'BLOCK_PLACEHOLDER') {
    var container = document.getElementById(containerId);
    var templateBlock = document.getElementById(templateBlockId);

    if (!container || !templateBlock) {
        console.error("Container ou template de bloco não encontrado.");
        return;
    }

    var newBlock = templateBlock.cloneNode(true);
    var newIndex = new Date().getTime();

    newBlock.style.display = 'block';
    newBlock.id = '';
    newBlock.classList.add('dynamic-block-container'); // Adiciona classe para remoção

    var inputs = newBlock.querySelectorAll('input, select, textarea');
    inputs.forEach(function (input) {
        input.name = input.name.replace(placeholder, newIndex);
    });

    container.appendChild(newBlock);
}

/**
 * Remove o bloco (identificado pela classe .dynamic-block-container) mais próximo do botão.
 * @param {HTMLElement} buttonElement - O botão (this) que foi clicado.
 */
function removeDynamicBlock(buttonElement) {
    var block = buttonElement.closest('.dynamic-block-container');
    if (block) {
        block.parentNode.removeChild(block);
    }
}

/**
 * Expande ou recolhe todos os accordions na página.
 * @param {boolean} expand - True para expandir, false para recolher.
 */
function toggleAllAccordions(expand = true) {
    var acc = document.getElementsByClassName("accordion");
    var i;
    for (i = 0; i < acc.length; i++) {
        var panel = acc[i].nextElementSibling;
        if (panel && panel.classList.contains("panel")) {
            if (expand) {
                acc[i].classList.add("active");
                panel.classList.add("show");
            } else {
                acc[i].classList.remove("active");
                panel.classList.remove("show");
            }
        }
    }
}

// Executa as funções quando o DOM estiver pronto
document.addEventListener("DOMContentLoaded", function () {
    initializeAccordions();
    highlightActiveMenu();
});