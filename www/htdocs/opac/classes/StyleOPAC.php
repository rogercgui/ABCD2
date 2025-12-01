<?php

use opac\classes\DesignOPAC;

class StyleOPAC extends DesignOPAC
{
    private $db_path;

    public function __construct($opac_global_style_def, $db_path)
    {
        parent::__construct($opac_global_style_def, $db_path);
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
            $this->getColorTXT()
        ];

        return "<style>\n" . implode("\n", $styles) . "\n</style>";
    }

    // --- MÉTODOS DE CORES (CSS) ---

    public function getTopBarBG()
    {
        $val = $this->getDesign('COLOR_TOPBAR_BG');
        return $val ? ".navbar-primary.bg-white { background: $val !important; } select.bg-white { background: $val !important; border: none;}" : "";
    }

    public function getTopBarTXT()
    {
        $val = $this->getDesign('COLOR_TOPBAR_TXT');
        return $val ? ".custom-top-link label, .custom-top-link a, .custom-top-link button, .custom-top-link select { color: $val !important; }" : "";
    }

    public function getColorBtnSubmit()
    {
        $bg = $this->getDesign('COLOR_BUTTONS_SUBMIT_BG');
        $txt = $this->getDesign('COLOR_BUTTONS_SUBMIT_TXT');
        return ($bg && $txt) ? ".btn-submit { background: $bg !important; border-color: rgba(0,0,0, 0.25) !important; color: $txt !important; }" : "";
    }

    public function getColorBtnLight()
    {
        $bg = $this->getDesign('COLOR_BUTTONS_LIGHT_BG');
        $txt = $this->getDesign('COLOR_BUTTONS_LIGHT_TXT');
        return ($bg && $txt) ? ".btn-light { background: $bg !important; border-color: rgba(0,0,0, 0.25) !important; color: $txt !important; }" : "";
    }

    public function getColorBtnPrimary()
    {
        $bg = $this->getDesign('COLOR_BUTTONS_PRIMARY_BG');
        $txt = $this->getDesign('COLOR_BUTTONS_PRIMARY_TXT');
        return ($bg && $txt) ? ".active>.page-link, .page-link.active, .btn-primary { background: $bg !important; border-color: rgba(0,0,0, 0.25) !important; color: $txt !important; }" : "";
    }

    public function getColorBtnSecondary()
    {
        $bg = $this->getDesign('COLOR_BUTTONS_SECONDARY_BG');
        $txt = $this->getDesign('COLOR_BUTTONS_SECONDARY_TXT');
        return ($bg && $txt) ? ".btn-secondary, .bg-secondary { background: $bg !important; border-color: rgba(0,0,0, 0.25) !important; color: $txt !important; }" : "";
    }

    public function getColorBG()
    {
        $val = $this->getDesign('COLOR_BG');
        return $val ? "body { background: $val !important; }" : "";
    }

    public function getColorSearchBoxBG()
    {
        $val = $this->getDesign('COLOR_SEARCHBOX_BG');
        return $val ? "#searchBox.card.bg-white, .card.text-bg-light, .card-block { background-color: $val !important; }" : "";
    }

    public function getColorResultsBG()
    {
        $val = $this->getDesign('COLOR_RESULTS_BG');
        return $val ? "#results > .registro-item { background-color: $val !important; }" : "";
    }

    public function getColorToTop()
    {
        $bg = $this->getDesign('COLOR_TOTOP_BG');
        $txt = $this->getDesign('COLOR_TOTOP_TXT');
        return ($bg && $txt) ? ".smoothscroll-top.show { background: $bg !important; color: $txt; }" : "";
    }

    public function getColorFooter()
    {
        $val = $this->getDesign('COLOR_FOOTER_BG');
        return $val ? ".custom-footer { background: $val !important; }" : "";
    }

    public function getColorFooterTXT()
    {
        $val = $this->getDesign('COLOR_FOOTER_TXT');
        return $val ? "footer.custom-footer, .custom-footer a, footer.custom-footer p, .custom-footer h1, .custom-footer h2 { color: $val !important; }" : "";
    }

    public function getColorA()
    {
        $val = $this->getDesign('COLOR_LINKS');
        return $val ? ".custom-sidebar .navbar-nav { color: $val; }" : "";
    }

    public function getColorTXT()
    {
        $val = $this->getDesign('COLOR_TEXT');
        if (!$val) return "";
        $css = ".custom-sidebar h6 { color: $val !important; }";
        $css .= ".custom-searchbox h6, .custom-searchbox label { color: $val !important; }";
        return $css;
    }

    // --- Métodos de Layout ---

    public function getLayoutData()
    {
        $sidebarVal = $this->getDesign('SIDEBAR');
        $sidebar = ($sidebarVal == 'N') ? 'SL' : 'R';

        return [
            'container'   => $this->getDesign('CONTAINER'),
            'sidebar'     => $sidebar,
            'hide_filter' => $this->getDesign('hideFILTER'),
            'num_pages'   => $this->getDesign('NUM_PAGES'),
            'topbar'      => $this->getDesign('TOPBAR')
        ];
    }
}

// --- INSTANCIAÇÃO E EXPORTAÇÃO DE VARIÁVEIS ---

if (isset($opac_global_style_def) && isset($db_path)) {
    $colorsOPAC = new StyleOPAC($opac_global_style_def, $db_path);

    // 1. Gera o CSS
    $CustomStyle = $colorsOPAC->inserStyle();

    // 2. Extrai Layout
    $layoutData = $colorsOPAC->getLayoutData();

    // 3. Define variáveis GLOBAIS para serem vistas pelo head.php
    global $container, $sidebar, $hide_filter, $num_pages, $topbar;

    $container = $layoutData['container'];
    $sidebar = $layoutData['sidebar'];
    $hide_filter = $layoutData['hide_filter'];
    $num_pages = $layoutData['num_pages'];
    $topbar = $layoutData['topbar'];
}
