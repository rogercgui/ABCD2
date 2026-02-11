<?php

use opac\classes\DesignOPAC;

class StyleOPAC extends DesignOPAC
{
    private $db_path;

    public function __construct($opac_global_style_def, $db_path)
    {
        parent::__construct($opac_global_style_def, $db_path);

        // [CORREÇÃO CRÍTICA] Atribui o path recebido à variável privada da classe
        $this->db_path = $db_path;
    }

    // --- HELPER: Limpeza de Aspas (FIX para Gradientes) ---
    private function getCleanDesign($key)
    {
        $val = $this->getDesign($key);
        if ($val) {
            return trim($val, '"\''); // Remove " e ' do início e fim
        }
        return $val;
    }

    // --- 1. Gera o bloco CSS ---
    public function inserStyle()
    {
        $styles = [
            $this->getTopBarBG(),
            $this->getTopBarTXT(),
            $this->getColorBtnSubmit(),
            $this->getColorBtnLight(),
            $this->getColorBtnPrimary(),
            $this->getColorBtnSecondary(),
            $this->getColorBG(),
            $this->getColorSearchBoxBG(),
            $this->getColorResultsBG(),
            $this->getColorToTop(),
            $this->getColorFooter(),
            $this->getColorFooterTXT(),
            $this->getColorA(),
            $this->getColorTXT(),

            // ADICIONADO: CSS Personalizado por último
            $this->getCustomCSS()
        ];

        return "<style>\n" . implode("\n", $styles) . "\n</style>";
    }

    // --- MÉTODOS DE CORES (CSS) ---

    public function getTopBarBG()
    {
        $val = $this->getCleanDesign('COLOR_TOPBAR_BG');
        return $val ? ".navbar-primary.bg-white { background: $val !important; } select.bg-white { background: $val !important; border: none;}" : "";
    }

    public function getTopBarTXT()
    {
        $val = $this->getCleanDesign('COLOR_TOPBAR_TXT');
        return $val ? ".custom-top-link label, .custom-top-link a, .custom-top-link button, .custom-top-link select { color: $val !important; }" : "";
    }

    public function getColorBtnSubmit()
    {
        $bg = $this->getCleanDesign('COLOR_BUTTONS_SUBMIT_BG');
        $txt = $this->getCleanDesign('COLOR_BUTTONS_SUBMIT_TXT');
        return ($bg && $txt) ? ".btn-submit { background: $bg !important; border-color: rgba(0,0,0, 0.25) !important; color: $txt !important; }" : "";
    }

    public function getColorBtnLight()
    {
        $bg = $this->getCleanDesign('COLOR_BUTTONS_LIGHT_BG');
        $txt = $this->getCleanDesign('COLOR_BUTTONS_LIGHT_TXT');
        return ($bg && $txt) ? ".btn-light { background: $bg !important; border-color: rgba(0,0,0, 0.25) !important; color: $txt !important; }" : "";
    }

    public function getColorBtnPrimary()
    {
        $bg = $this->getCleanDesign('COLOR_BUTTONS_PRIMARY_BG');
        $txt = $this->getCleanDesign('COLOR_BUTTONS_PRIMARY_TXT');
        return ($bg && $txt) ? ".active>.page-link, .page-link.active, .btn-primary { background: $bg !important; border-color: rgba(0,0,0, 0.25) !important; color: $txt !important; }" : "";
    }

    public function getColorBtnSecondary()
    {
        $bg = $this->getCleanDesign('COLOR_BUTTONS_SECONDARY_BG');
        $txt = $this->getCleanDesign('COLOR_BUTTONS_SECONDARY_TXT');
        return ($bg && $txt) ? ".btn-secondary, .bg-secondary { background: $bg !important; border-color: rgba(0,0,0, 0.25) !important; color: $txt !important; }" : "";
    }

    public function getColorBG()
    {
        $val = $this->getCleanDesign('COLOR_BG');
        return $val ? "body { background: $val !important; }" : "";
    }

    public function getColorSearchBoxBG()
    {
        $val = $this->getCleanDesign('COLOR_SEARCHBOX_BG');
        return $val ? "#searchBox.card.bg-white, .card.text-bg-light, .card-block { background: $val !important; }" : "";
    }

    public function getColorResultsBG()
    {
        $val = $this->getCleanDesign('COLOR_RESULTS_BG');
        return $val ? "#results > .registro-item { background-color: $val !important; }" : "";
    }

    public function getColorToTop()
    {
        $bg = $this->getCleanDesign('COLOR_TOTOP_BG');
        $txt = $this->getCleanDesign('COLOR_TOTOP_TXT');
        return ($bg && $txt) ? ".smoothscroll-top.show { background: $bg !important; color: $txt; }" : "";
    }

    public function getColorFooter()
    {
        $val = $this->getCleanDesign('COLOR_FOOTER_BG');
        return $val ? ".custom-footer { background: $val !important; }" : "";
    }

    public function getColorFooterTXT()
    {
        $val = $this->getCleanDesign('COLOR_FOOTER_TXT');
        return $val ? "footer.custom-footer, .custom-footer a, footer.custom-footer p, .custom-footer h1, .custom-footer h2, .text-muted.custom-footer { color: $val !important; }" : "";
    }

    public function getColorA()
    {
        $val = $this->getCleanDesign('COLOR_LINKS');
        return $val ? ".custom-sidebar .navbar-nav { color: $val; }" : "";
    }

    public function getColorTXT()
    {
        $val = $this->getCleanDesign('COLOR_TEXT');
        if (!$val) return "";
        $css = ".custom-sidebar h6 { color: $val !important; }";
        $css .= ".custom-searchbox h6, .custom-searchbox label { color: $val !important; }";
        return $css;
    }

    // --- LEITURA DO ARQUIVO CUSTOM.CSS ---
    public function getCustomCSS()
    {
        // Agora $this->db_path terá valor correto
        $file = $this->db_path . "opac_conf/custom.css";

        if (file_exists($file)) {
            $css = file_get_contents($file);
            if (!empty($css)) {
                return "\n/* --- Custom CSS Injected --- */\n" . $css;
            }
        }
        return "";
    }

    // --- Métodos de Layout ---

    public function getLayoutData()
    {
        $sidebarVal = $this->getCleanDesign('SIDEBAR');
        $sidebar = ($sidebarVal == 'N') ? 'SL' : 'R';

        return [
            'container'   => $this->getCleanDesign('CONTAINER'),
            'sidebar'     => $sidebar,
            'hide_filter' => $this->getCleanDesign('hideFILTER'),
            'num_pages'   => $this->getCleanDesign('NUM_PAGES'),
            'topbar'      => $this->getCleanDesign('TOPBAR')
        ];
    }
}

// --- INSTANCIAÇÃO ---

if (isset($opac_global_style_def) && isset($db_path)) {
    $colorsOPAC = new StyleOPAC($opac_global_style_def, $db_path);

    // 1. Gera o CSS
    $CustomStyle = $colorsOPAC->inserStyle();

    // 2. Extrai Layout
    $layoutData = $colorsOPAC->getLayoutData();

    // 3. Define variáveis GLOBAIS
    global $container, $sidebar, $hide_filter, $num_pages, $topbar;

    $container = $layoutData['container'];
    $sidebar = $layoutData['sidebar'];
    $hide_filter = $layoutData['hide_filter'];
    $num_pages = $layoutData['num_pages'];
    $topbar = $layoutData['topbar'];
}
