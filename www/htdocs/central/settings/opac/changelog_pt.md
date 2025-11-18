# Changelog
Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.  
O formato segue o padrão [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/)  
e o projeto adota a [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [2.0.0] - 2025-11-01
### Added
- Modal **fullscreen** (`#recordDetailModal`) para visualização individual de registros.
- Grupo de **botões de formato** (Padrão, XML MARC, XML DC) substituindo o antigo `<select>` de formato.
- Visualização de XML (MARC e Dublin Core) diretamente no modal com `<pre><code>`.
- Botão "Baixar XML" restaurado, chamando `sendtoxml.php` corretamente.
- Novo **cabeçalho de resultados** (`search_header.php`) exibindo totais por base e termo limpo.
- **Dropdown de ordenação** (`sort_dropdown.php`) na barra superior com suporte a:
  - Relevância (padrão)
  - Título (A-Z, Z-A)
  - Autor (A-Z, Z-A)
  - Mais Novo (MFN ↓)
  - Mais Antigo (MFN ↑)
- **Paginação dupla** no topo e rodapé dos resultados.
- **Seleção de registros** via cookies (`record_card.php` e `float_bar.php`).
- CSS para modo escuro e melhor adaptação de cabeçalhos (`alert-light`).

### Changed
- `buscar_integrada.php`: lógica de ordenação alterada de `usort` para `array_multisort`.
- Layout do rodapé do modal (`footer.php`) ajustado para botões na mesma linha.
- Fluxo do dropdown de busca: agora apenas define `target_db`, sem disparar busca imediata.
- Função `submitMainSearch()` implementada para submeter corretamente o formulário livre.
- Função `searchAndOrganizeResults()` agora aceita `$base_selecionada`.

### Fixed
- **Facetas e prefixos:** correção completa dos bugs em `RefinF` e `removerTermo`.
  - `PresentarExpresion` não remove mais prefixos da `Expresion` global.
  - URLs agora construídas corretamente com `Opcion=directa` e `Expresion` íntegra.
- **Busca livre:** ajuste em `construir_expresion.php` e `limpar_termo` para suportar acentos e caracteres especiais (`&`, `()`).
- **Highlight:** `highlight.js` corrigido para ignorar palavras curtas e detectar `#results`.
- **"Você quis dizer?":** melhorias na leitura e codificação dos arquivos `.dic`.
- **Memória:** limitação da função recursiva `pc_permute` para evitar estouro de memória.
- **Segurança:** `get_record_details.php` agora valida bases com `leer_bases.php`.
- **Consistência de ações:** `sendtoxml.php` e `ToolButtons.php` padronizados (PFTs, caminhos, IDs).
- **Checkboxes:** `delCookie` agora desmarca registros visíveis corretamente.

### Removed
- `<select>` de formato antigo que causava falhas em buscas integradas.

---

## [1.2.0-beta] - 2025-10-06
### Added
- Função **"Você quis dizer?"** com geração de dicionário (`ifkeys` e WXIS).
- CAPTCHA invisível da **Cloudflare Turnstile** no formulário de pesquisa.
- **Registro de analytics** por ano/mês.
- **Página inicial dinâmica** com editor HTML.
- **Checklist** de configuração das bases de dados.
- **Configuração dinâmica da barra de botões** por base.

### Changed
- URL de pesquisa mais limpa e segura (sem script explícito).
- Layout e comportamento do dicionário público mais dinâmico.

### Fixed
- Correção de paginação em buscas.
- Correções no carregamento da página inicial.

---

## [1.1.3-beta] - 2025-04-28
### Added
- Sistema de **autocompletar** no formulário de pesquisa (JSON dinâmico).
- Novo parâmetro de ordenação de facetas ("A" ou "Q").
- Marca d’água nas imagens servidas por `show_image.php`.

### Fixed
- Correções nas facetas e compatibilidade com múltiplas bases.

---

## [1.1.2-beta] - 2025-04-24
### Added
- Nova estrutura de **facetas** por base (`*_facetas.dat`) com hierarquia e prefixos configuráveis.

### Changed
- Fluxo de pesquisa aprimorado para suportar facetas por base.

---

## [1.1.1-beta] - 2024-06-18
### Added
- Melhorias gerais na configuração do OPAC.
- Google Analytics via parâmetro `GANALYTICS`.
- Modo escuro, meta tags para SEO e ajuste de fontes.

### Fixed
- Formulário de busca avançada e integração de funções de envio de termos.

### Removed
- Arquivo `dbName.def` substituído por `bases.dat` para centralizar descrições.

---

## [1.1.0-beta] - 2023-03-28
### Added
- Integração do **Bootstrap** ao layout do OPAC.
- Novo parâmetro `OpacHttp` obrigatório.
- Atualização de `select_record.pft` para padrão Bootstrap.