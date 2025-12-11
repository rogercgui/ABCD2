# 📚 OPAC ABCD — Catálogo Público de Acesso Online
> Interface moderna e personalizável do ABCD para pesquisa, visualização e interação com bases de dados bibliográficas.

---

## ✨ Sobre o OPAC
O **OPAC do ABCD (Open Public Access Catalog)** é o módulo de acesso público do sistema **ABCD – Automação de Bibliotecas e Centros de Documentação**.  
Ele oferece uma interface dinâmica e responsiva para consulta a acervos, integração com múltiplas bases de dados, exibição de registros, download de metadados e recursos visuais configuráveis.

Desenvolvido em PHP, o OPAC utiliza arquitetura modular e suporte a temas, podendo ser facilmente customizado para bibliotecas, museus e centros de informação.

---
# 🆕 O que há de novo?


## 🚀 Opac – v2.1.0 (2025-12-10)

### 🌟 Novas Funcionalidades
- **Sistema Multi-Contexto:** Agora é possível servir múltiplas bibliotecas ou coleções independentes a partir de uma única instalação do OPAC.
  - Utilize o parâmetro `?ctx=apelido` na URL para alternar entre as pastas de bases.
  - Configuração centralizada no arquivo `config_opac.php`.
- **Modo Estrito (Strict Mode):** Nova configuração de segurança que bloqueia o acesso ao OPAC caso nenhum contexto de biblioteca seja informado, protegendo a instalação raiz.
- **Busca Detalhada:**
  - Lógica de JavaScript e PHP totalmente refatorada para suportar corretamente arrays de campos e operadores booleanos.
  - Correção na paginação e ordenação de resultados vindos da busca avançada.
  - Interface limpa usando Modais para dicionários e seletores.

---

## 🚀 Opac – v2.0.0 (2025-11-09)

### 🌟 Novas Funcionalidades e Melhorias de Interface

- **Configurações:**  
- **Diagnóstico** para verificar a instalação correta do OPAC. **[VER AQUI](javascript:EnviarForma('/central/settings/opac/diagnostico.php'))**
- **Acesso restrito** é possível restringir o acesso a pesquisa, o OPAC pode ser restrito apenas para usuários autorizados a realizarem as pesquisas. **[VER AQUI no painel Segurança](javascript:EnviarForma('/central/settings/opac/parametros.php'))**
- **Registros restritos** para centros de documentação que necessitam ocultar ou exibir mediante autenticação um registro, agora ficou possível. Basta selecionar uma base de dados e clicar no menu superior em *Configuração Avançada -> Registros restritos*.
- **Relevância dos dados** nesta versão é possível definir o grau de relevância dos campos para que o sistema pontue. Títulos possuem mais pontuação do que informações gerais, então se o usuário pesquisar um termo que está no título e a sintaxe da frase está presente, este registro é exibido primeiro.
 

#### Site público
- **Painel do Usuário (Minha Biblioteca / myabcd):**

    - **Reservas Modernizadas (AJAX):** O processo de reserva foi totalmente refeito. Em vez de uma página de formulário, o usuário agora clica em "Reservar" e uma janela flutuante (modal) aparece para confirmar a ação.
    - **Confirmação de Reserva:** Esta nova janela mostra os detalhes do item (como o Título) e pede a confirmação do usuário.
    - **Novo Recurso (Dias de Espera):** Na janela de confirmação, o usuário agora pode inserir por quantos dias está disposto a esperar pelo item (o antigo campo `v40` da base `reserve`).
    - **Renovação e Cancelamento via Modal:** As funções de "Renovar Empréstimo" e "Cancelar Reserva" dentro do painel do usuário agora também usam o mesmo sistema de modal, exibindo mensagens claras de sucesso ou erro (como "Limite de renovações atingido" ou "Item já reservado").
    - **Login Inteligente:** Se um usuário não logado tentar reservar um item, o modal agora exibe a mensagem "Usuário não autenticado" e mostra o botão de Login, em vez de apenas falhar.

- **Ocultar seleção de base ou coleção** O dropdown que aparece ao lado do campo de texto na pesquisa livre da página inicial pode ser ocultado no menu Aparência nas configurações do OPAC.

- **Visualização Individual (Single View):**  
  Novo **modal fullscreen** (`#recordDetailModal`) para exibir detalhes de registros sem perder a página de resultados.

- **Seleção de Formato no Modal:**  
  O antigo `<select>` de formato foi substituído por um grupo de **botões interativos** (Padrão, XML MARC, XML DC), que recarregam o conteúdo via AJAX.

- **Visualização e Download de XML:**  
  Agora é possível visualizar os registros MARC e Dublin Core formatados com `<pre><code>` e baixar via `sendtoxml.php`.

- **Cabeçalho de Resultados:**  
  Inclui totais de registros por base e o termo de busca limpo, inspirado no estilo do Pergamum.

- **Ordenação Avançada:**  
  Novo dropdown (`sort_dropdown.php`) permite classificar por:
  - 🔹 Relevância (padrão)
  - 🔹 Título (A–Z, Z–A)
  - 🔹 Autor (A–Z, Z–A)
  - 🔹 Mais Novo (MFN ↓)
  - 🔹 Mais Antigo (MFN ↑)

- **Paginação Dupla:**  
  Navegação exibida no **topo e rodapé** da lista de resultados.

- **Seleção de Registros (Cookies):**  
  Checkbox reativado para múltiplas seleções, com barra flutuante (`float_bar.php`) e opções “Mostrar Seleção” e “Limpar”.

- **UI/CSS:**  
  Layout aprimorado para modo escuro e rodapé do modal em linha única.

---

### 🧠 Alterações de Código e Lógica
- Lógica de ordenação em `buscar_integrada.php` alterada de `usort` para `array_multisort`.
- `searchAndOrganizeResults()` agora aceita `$base_selecionada` para filtros por base.
- `submitMainSearch()` implementada para submeter corretamente a busca livre.
- Dropdown de bases (`dropdown_db.php`) agora apenas define `target_db`, sem executar busca imediata.

---

### 🐞 Correções de Bugs Críticos
- **Facetas e Termos:**  
  Corrigido o bug que removia prefixos de `Expresion` e quebrava buscas refinadas.  
  `RefinF` e `removerTermo` foram reescritos para manter a estrutura correta das expressões booleanas.

- **Busca Livre e Acentos:**  
  `construir_expresion.php` e `limpar_termo` agora tratam corretamente acentos e caracteres especiais como `&` e `()`.

- **Destaque de Termos:**  
  `highlight.js` atualizado para ignorar palavras curtas e reconhecer o `div#results`.

- **Sugestão “Você quis dizer?”**  
  Lógica aprimorada para suportar frases completas e codificação ISO-8859-1 dos dicionários `.dic`.

- **Estabilidade:**  
  Função recursiva `pc_permute` limitada para evitar estouro de memória.  
  `get_record_details.php` agora verifica se a base está listada em `bases.dat`.

- **Consistência e Cookies:**  
  `sendtoxml.php` e `ToolButtons.php` padronizados (PFTs, caminhos, IDs).  
  `delCookie` corrigida para desmarcar checkboxes corretamente.

---

### ❌ Recursos Removidos
- `<select>` de formato antigo, substituído por botões AJAX.

---

## 🔍 Opac – v1.2.0-beta (2025-10-06)

### 🧩 Destaques
- Novo sistema **“Você quis dizer?”** baseado em dicionários (`ifkeys` ou WXIS).
- Implementação do **CAPTCHA invisível da Cloudflare Turnstile**.
- Registro automático de buscas (analytics) agrupado por ano e mês.
- Página inicial dinâmica, com HTML gerado a partir do editor administrativo.
- Novo checklist de configuração de bases e **configuração visual de botões de registro**.
- URLs de busca mais limpas e seguras.

---

## 🔤 Opac – v1.1.3-beta (2025-04-28)
- Introdução do **autocompletar** nas pesquisas (JSON dinâmico).  
- Novo parâmetro de ordenação de facetas: **A** (alfabética) ou **Q** (quantitativa).  
- Imagens com marca d’água gerada automaticamente.  
- Correções nas facetas para funcionar com múltiplas bases.

---

## ⚙️ Opac – v1.1.2-beta (2025-04-24)
- Reestruturação completa do sistema de **facetas por base**, com hierarquia configurável (`*_facetas.dat`).  
- Novo fluxo de pesquisa integrando múltiplas bases.

---

## 💡 Opac – v1.1.1-beta (2024-06-18)
- Melhorias gerais de configuração.  
- **Modo escuro** e metatags para SEO.  
- **Integração com Google Analytics** via parâmetro `GANALYTICS`.  
- Correções no formulário de busca avançada.  
- Substituição do antigo `dbName.def` por `bases.dat` centralizado.

---

## 🧰 Opac – v1.1.0-beta (2023-03-28)
- Integração do **Bootstrap** como base de layout.  
- Parâmetro `OpacHttp` torna-se obrigatório.  
- `select_record.pft` atualizado para padrão Bootstrap.

---

# 🗂️ Estrutura do Projeto

### 📁 Diretórios principais
        /bases/opac_conf/lang/

Arquivos necessários:
- `bases.dat`  
- `lang.tab`  
- `footer.info`  
- `menu.info`  
- `side_bar.info`  
- `sitio.info`

### 🧭 Formulários de Pesquisa
Os formulários devem respeitar o padrão de prefixos de cada base:
- `libre.tab` – Pesquisa livre (meta-pesquisa)
- `avanzada.tab` – Pesquisa avançada
- `colecciones.tab` – Subconjuntos de registros

Outros arquivos avaliados em desenvolvimento:
- `facetas.dat`  
- `formatos.dat`  
- `autoridades_opac.pft`  
- `indice.ix`  
- `opac.pft`  
- `opac_loanobjects.pft`  
- `select_record.pft`

### 🧩 Configuração por base
Cada base habilitada no OPAC deve conter:

        /bases/[dbName]/opac/lang/

Arquivos:
- `dbName.def`  
- `dbName.ix`  
- `dbName.lang`  
- `dbName_facetas.dat`  
- `dbName_formatos.dat`  
- `dbName_libre.tab`  
- `dbName_avanzada.tab`  
- `dbName_colecciones.tab`

---

# 🏗️ Características Gerais
- Pesquisa em até **3 níveis**:  
  1️⃣ Meta pesquisa  
  2️⃣ Pesquisa em base específica  
  3️⃣ Pesquisa em subconjuntos (via prefixo do FST)  
- Suporte multilíngue (`lang.tab`)  
- Layout baseado em **Bootstrap**  
- Suporte a modo **dark/light**  
- Exibição de resultados em múltiplos formatos (HTML, XML MARC, XML DC)

---

# 🌐 Créditos e Comunidade
Desenvolvido e mantido pela **ABCD Community**  
🔗 [https://abcd-community.org](https://abcd-community.org)

💬 Participe da comunidade, envie sugestões e contribua para a evolução do OPAC.

---

> © 2025 ABCD Community — Automação de Bibliotecas e Centros de Documentação  
> Projeto open-source mantido pela comunidade global do ABCD.
