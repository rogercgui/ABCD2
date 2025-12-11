# 📚 ABCD OPAC — Online Public Access Catalog

> Modern and customizable ABCD interface for searching, viewing, and interacting with bibliographic databases.

-----

## ✨ About OPAC

The **ABCD OPAC (Open Public Access Catalog)** is the public access module of the **ABCD – Library and Documentation Centers Automation** system.
It offers a dynamic and responsive interface for consulting collections, integrating with multiple databases, displaying records, downloading metadata, and using configurable visual resources.

Developed in PHP, the OPAC uses a modular architecture with theme support, allowing it to be easily customized for libraries, museums, and information centers.

-----
# 🆕 What's New?

## 🚀 Opac – v2.1.0 (2025-12-10)

### 🌟 New Features
- **Multi-Context System:** It is now possible to serve multiple libraries or independent collections from a single OPAC installation.
  - Use the `?ctx=alias` parameter in the URL to switch between database folders.
  - Centralized configuration in the `config_opac.php` file.
- **Strict Mode:** New security setting that blocks access to the OPAC if no library context is provided, protecting the root installation.
- **Advanced Search:**
  - JavaScript and PHP logic completely refactored to correctly support field arrays and boolean operators.
  - Fixes in pagination and sorting of results from advanced search.
  - Clean interface using Modals for dictionaries and selectors.

-----

## 🚀 Opac – v2.0.0 (2025-11-09)

### 🌟 New Features and Interface Improvements

  - **Settings:**
  - **Diagnostics** to verify the correct installation of the OPAC. **[SEE HERE](javascript:EnviarForma('/central/settings/opac/diagnostico.php'))**
  - **Restricted Access:** it is possible to restrict search access; the OPAC can be restricted only to users authorized to perform searches. **[SEE HERE in Security panel](javascript:EnviarForma('/central/settings/opac/parametros.php'))**
  - **Restricted Records:** for documentation centers that need to hide a record or display it only upon authentication, this is now possible. Just select a database and click on the top menu at *Advanced Configuration -\> Restricted Records*.
  - **Data Relevance:** in this version, it is possible to define the relevance degree of fields for system scoring. Titles have a higher score than general information, so if the user searches for a term that is in the title and the phrase syntax is present, this record is displayed first.

#### Public Site

  - **User Dashboard (My Library / myabcd):**

      - **Modernized Reservations (AJAX):** The reservation process has been completely redone. Instead of a form page, the user now clicks "Reserve" and a floating window (modal) appears to confirm the action.
      - **Reservation Confirmation:** This new window shows the item details (such as the Title) and asks for user confirmation.
      - **New Feature (Wait Days):** In the confirmation window, the user can now input how many days they are willing to wait for the item (the old `v40` field from the `reserve` database).
      - **Renewal and Cancellation via Modal:** The "Renew Loan" and "Cancel Reservation" functions within the user dashboard now also use the same modal system, displaying clear success or error messages (such as "Renewal limit reached" or "Item already reserved").
      - **Smart Login:** If a non-logged-in user tries to reserve an item, the modal now displays the message "User not authenticated" and shows the Login button, instead of just failing.

  - **Hide base or collection selection:** The dropdown that appears next to the text field in the free search on the homepage can be hidden in the Appearance menu in the OPAC settings.

  - **Individual View (Single View):**
    New **fullscreen modal** (`#recordDetailModal`) to display record details without losing the results page.

  - **Format Selection in Modal:**
    The old format `<select>` has been replaced by a group of **interactive buttons** (Standard, XML MARC, XML DC), which reload content via AJAX.

  - **XML View and Download:**
    It is now possible to view MARC and Dublin Core records formatted with `<pre><code>` and download them via `sendtoxml.php`.

  - **Results Header:**
    Includes record totals per database and the clean search term, inspired by the Pergamum style.

  - **Advanced Sorting:**
    New dropdown (`sort_dropdown.php`) allows sorting by:

      - 🔹 Relevance (default)
      - 🔹 Title (A–Z, Z–A)
      - 🔹 Author (A–Z, Z–A)
      - 🔹 Newest (MFN ↓)
      - 🔹 Oldest (MFN ↑)

  - **Double Pagination:**
    Navigation displayed at the **top and bottom** of the results list.

  - **Record Selection (Cookies):**
    Checkbox reactivated for multiple selections, with a floating bar (`float_bar.php`) and "Show Selection" and "Clear" options.

  - **UI/CSS:**
    Improved layout for dark mode and single-line modal footer.

-----

### 🧠 Code and Logic Changes

  - Sorting logic in `buscar_integrada.php` changed from `usort` to `array_multisort`.
  - `searchAndOrganizeResults()` now accepts `$base_selecionada` for database filters.
  - `submitMainSearch()` implemented to correctly submit the free search.
  - Database dropdown (`dropdown_db.php`) now only sets `target_db`, without executing an immediate search.

-----

### 🐞 Critical Bug Fixes

  - **Facets and Terms:**
    Fixed the bug that removed prefixes from `Expresion` and broke refined searches.
    `RefinF` and `removerTermo` were rewritten to maintain the correct structure of boolean expressions.

  - **Free Search and Accents:**
    `construir_expresion.php` and `limpar_termo` now correctly handle accents and special characters like `&` and `()`.

  - **Term Highlighting:**
    `highlight.js` updated to ignore short words and recognize `div#results`.

  - **"Did you mean?" Suggestion:**
    Improved logic to support complete phrases and ISO-8859-1 encoding of `.dic` dictionaries.

  - **Stability:**
    Recursive function `pc_permute` limited to prevent memory overflow.
    `get_record_details.php` now checks if the database is listed in `bases.dat`.

  - **Consistency and Cookies:**
    `sendtoxml.php` and `ToolButtons.php` standardized (PFTs, paths, IDs).
    `delCookie` fixed to correctly uncheck checkboxes.

-----

### ❌ Removed Features

  - Old format `<select>`, replaced by AJAX buttons.

-----

## 🔍 Opac – v1.2.0-beta (2025-10-06)

### 🧩 Highlights

  - New **"Did you mean?"** system based on dictionaries (`ifkeys` or WXIS).
  - Implementation of **Cloudflare Turnstile invisible CAPTCHA**.
  - Automatic search logging (analytics) grouped by year and month.
  - Dynamic homepage, with HTML generated from the administrative editor.
  - New database configuration checklist and **visual configuration for record buttons**.
  - Cleaner and more secure search URLs.

-----

## 🔤 Opac – v1.1.3-beta (2025-04-28)

  - Introduction of **autocomplete** in searches (dynamic JSON).
  - New facet sorting parameter: **A** (alphabetic) or **Q** (quantitative).
  - Images with automatically generated watermarks.
  - Fixes in facets to work with multiple databases.

-----

## ⚙️ Opac – v1.1.2-beta (2025-04-24)

  - Complete restructuring of the **per-database facet system**, with configurable hierarchy (`*_facetas.dat`).
  - New search flow integrating multiple databases.

-----

## 💡 Opac – v1.1.1-beta (2024-06-18)

  - General configuration improvements.
  - **Dark mode** and SEO metatags.
  - **Google Analytics integration** via `GANALYTICS` parameter.
  - Fixes in the advanced search form.
  - Replacement of the old `dbName.def` with centralized `bases.dat`.

-----

## 🧰 Opac – v1.1.0-beta (2023-03-28)

  - Integration of **Bootstrap** as layout base.
  - `OpacHttp` parameter becomes mandatory.
  - `select_record.pft` updated to Bootstrap standard.

-----

# 🗂️ Project Structure

### 📁 Main Directories

```
    /bases/opac_conf/lang/
```

Required files:

  - `bases.dat`
  - `lang.tab`
  - `footer.info`
  - `menu.info`
  - `side_bar.info`
  - `sitio.info`

### 🧭 Search Forms

Forms must respect the prefix pattern of each database:

  - `libre.tab` – Free search (meta-search)
  - `avanzada.tab` – Advanced search
  - `colecciones.tab` – Record subsets

Other files evaluated in development:

  - `facetas.dat`
  - `formatos.dat`
  - `autoridades_opac.pft`
  - `indice.ix`
  - `opac.pft`
  - `opac_loanobjects.pft`
  - `select_record.pft`

### 🧩 Configuration per database

Each database enabled in the OPAC must contain:

```
    /bases/[dbName]/opac/lang/
```

Files:

  - `dbName.def`
  - `dbName.ix`
  - `dbName.lang`
  - `dbName_facetas.dat`
  - `dbName_formatos.dat`
  - `dbName_libre.tab`
  - `dbName_avanzada.tab`
  - `dbName_colecciones.tab`

-----

# 🏗️ General Features

  - Search in up to **3 levels**:
    1️⃣ Meta search
    2️⃣ Specific database search
    3️⃣ Search in subsets (via FST prefix)
  - Multilingual support (`lang.tab`)
  - **Bootstrap** based layout
  - **Dark/light** mode support
  - Result display in multiple formats (HTML, XML MARC, XML DC)

-----

# 🌐 Credits and Community

Developed and maintained by the **ABCD Community**
🔗 [https://abcd-community.org](https://abcd-community.org)

💬 Join the community, send suggestions, and contribute to the evolution of the OPAC.

-----

> © 2025 ABCD Community — Library and Documentation Centers Automation
> Open-source project maintained by the global ABCD community.

-----