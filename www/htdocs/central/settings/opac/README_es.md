
# 📚 OPAC ABCD — Catálogo Público de Acceso en Línea

> Interfaz moderna y personalizable de ABCD para búsqueda, visualización e interacción con bases de datos bibliográficas.

-----

## ✨ Sobre el OPAC

El **OPAC de ABCD (Open Public Access Catalog)** es el módulo de acceso público del sistema **ABCD – Automatización de Bibliotecas y Centros de Documentación**.
Ofrece una interfaz dinámica y responsiva para la consulta de acervos, integración con múltiples bases de datos, visualización de registros, descarga de metadatos y recursos visuales configurables.

Desarrollado en PHP, el OPAC utiliza una arquitectura modular y soporte para temas, pudiendo ser fácilmente personalizado para bibliotecas, museos y centros de información.

-----

# 🆕 ¿Qué hay de nuevo?

## 🚀 Opac – v2.0.0 (2025-11-09)

### 🌟 Nuevas Funcionalidades y Mejoras de Interfaz

  - **Configuraciones:**
  - **Diagnóstico** para verificar la instalación correcta del OPAC. **[VER AQUÍ](javascript:EnviarForma('/central/settings/opac/diagnostico.php'))**
  - **Acceso restringido:** es posible restringir el acceso a la búsqueda, el OPAC puede ser restringido solo para usuarios autorizados a realizar las búsquedas. **[VER AQUÍ en el panel Seguridad](javascript:EnviarForma('/central/settings/opac/parametros.php'))**
  - **Registros restringidos:** para centros de documentación que necesitan ocultar o mostrar un registro mediante autenticación, ahora es posible. Basta seleccionar una base de datos y hacer clic en el menú superior en *Configuración Avanzada -\> Registros restringidos*.
  - **Relevancia de los datos:** en esta versión es posible definir el grado de relevancia de los campos para que el sistema puntúe. Los títulos tienen mayor puntuación que la información general, por lo que si el usuario busca un término que está en el título y la sintaxis de la frase está presente, este registro se muestra primero.

#### Sitio público

  - **Panel del Usuario (Mi Biblioteca / myabcd):**

      - **Reservas Modernizadas (AJAX):** El proceso de reserva ha sido totalmente rehecho. En lugar de una página de formulario, el usuario ahora hace clic en "Reservar" y aparece una ventana flotante (modal) para confirmar la acción.
      - **Confirmación de Reserva:** Esta nueva ventana muestra los detalles del ítem (como el Título) y solicita la confirmación del usuario.
      - **Nuevo Recurso (Días de Espera):** En la ventana de confirmación, el usuario ahora puede ingresar cuántos días está dispuesto a esperar por el ítem (el antiguo campo `v40` de la base `reserve`).
      - **Renovación y Cancelación vía Modal:** Las funciones de "Renovar Préstamo" y "Cancelar Reserva" dentro del panel del usuario ahora también usan el mismo sistema modal, mostrando mensajes claros de éxito o error (como "Límite de renovaciones alcanzado" o "Ítem ya reservado").
      - **Inicio de Sesión Inteligente:** Si un usuario no autenticado intenta reservar un ítem, el modal ahora muestra el mensaje "Usuario no autenticado" y muestra el botón de Iniciar Sesión, en lugar de simplemente fallar.

  - **Ocultar selección de base o colección:** El menú desplegable que aparece junto al campo de texto en la búsqueda libre de la página de inicio puede ocultarse en el menú Apariencia en la configuración del OPAC.

  - **Visualización Individual (Single View):**
    Nuevo **modal a pantalla completa** (`#recordDetailModal`) para mostrar detalles de registros sin perder la página de resultados.

  - **Selección de Formato en el Modal:**
    El antiguo `<select>` de formato ha sido reemplazado por un grupo de **botones interactivos** (Estándar, XML MARC, XML DC), que recargan el contenido vía AJAX.

  - **Visualización y Descarga de XML:**
    Ahora es posible visualizar los registros MARC y Dublin Core formateados con `<pre><code>` y descargarlos vía `sendtoxml.php`.

  - **Encabezado de Resultados:**
    Incluye totales de registros por base y el término de búsqueda limpio, inspirado en el estilo de Pergamum.

  - **Ordenamiento Avanzado:**
    Nuevo menú desplegable (`sort_dropdown.php`) permite clasificar por:

      - 🔹 Relevancia (predeterminado)
      - 🔹 Título (A–Z, Z–A)
      - 🔹 Autor (A–Z, Z–A)
      - 🔹 Más Nuevo (MFN ↓)
      - 🔹 Más Antiguo (MFN ↑)

  - **Paginación Doble:**
    Navegación mostrada en la **parte superior e inferior** de la lista de resultados.

  - **Selección de Registros (Cookies):**
    Casilla de verificación reactivada para selecciones múltiples, con barra flotante (`float_bar.php`) y opciones "Mostrar Selección" y "Limpiar".

  - **UI/CSS:**
    Diseño mejorado para modo oscuro y pie de página del modal en una sola línea.

-----

### 🧠 Cambios de Código y Lógica

  - Lógica de ordenamiento en `buscar_integrada.php` cambiada de `usort` a `array_multisort`.
  - `searchAndOrganizeResults()` ahora acepta `$base_selecionada` para filtros por base.
  - `submitMainSearch()` implementada para enviar correctamente la búsqueda libre.
  - Menú desplegable de bases (`dropdown_db.php`) ahora solo define `target_db`, sin ejecutar búsqueda inmediata.

-----

### 🐞 Correcciones de Errores Críticos

  - **Facetas y Términos:**
    Corregido el error que eliminaba prefijos de `Expresion` y rompía búsquedas refinadas.
    `RefinF` y `removerTermo` fueron reescritos para mantener la estructura correcta de las expresiones booleanas.

  - **Búsqueda Libre y Acentos:**
    `construir_expresion.php` y `limpar_termo` ahora tratan correctamente acentos y caracteres especiales como `&` y `()`.

  - **Resaltado de Términos:**
    `highlight.js` actualizado para ignorar palabras cortas y reconocer el `div#results`.

  - **Sugerencia "¿Quiso decir?"**
    Lógica mejorada para soportar frases completas y codificación ISO-8859-1 de los diccionarios `.dic`.

  - **Estabilidad:**
    Función recursiva `pc_permute` limitada para evitar desbordamiento de memoria.
    `get_record_details.php` ahora verifica si la base está listada en `bases.dat`.

  - **Consistencia y Cookies:**
    `sendtoxml.php` y `ToolButtons.php` estandarizados (PFTs, rutas, IDs).
    `delCookie` corregida para desmarcar casillas de verificación correctamente.

-----

### ❌ Recursos Eliminados

  - `<select>` de formato antiguo, reemplazado por botones AJAX.

-----

## 🔍 Opac – v1.2.0-beta (2025-10-06)

### 🧩 Destacados

  - Nuevo sistema **"¿Quiso decir?"** basado en diccionarios (`ifkeys` o WXIS).
  - Implementación del **CAPTCHA invisible de Cloudflare Turnstile**.
  - Registro automático de búsquedas (analytics) agrupado por año y mes.
  - Página de inicio dinámica, con HTML generado a partir del editor administrativo.
  - Nueva lista de verificación de configuración de bases y **configuración visual de botones de registro**.
  - URLs de búsqueda más limpias y seguras.

-----

## 🔤 Opac – v1.1.3-beta (2025-04-28)

  - Introducción de **autocompletar** en las búsquedas (JSON dinámico).
  - Nuevo parámetro de ordenamiento de facetas: **A** (alfabética) o **Q** (cuantitativa).
  - Imágenes con marca de agua generada automáticamente.
  - Correcciones en las facetas para funcionar con múltiples bases.

-----

## ⚙️ Opac – v1.1.2-beta (2025-04-24)

  - Reestructuración completa del sistema de **facetas por base**, con jerarquía configurable (`*_facetas.dat`).
  - Nuevo flujo de búsqueda integrando múltiples bases.

-----

## 💡 Opac – v1.1.1-beta (2024-06-18)

  - Mejoras generales de configuración.
  - **Modo oscuro** y metaetiquetas para SEO.
  - **Integración con Google Analytics** vía parámetro `GANALYTICS`.
  - Correcciones en el formulario de búsqueda avanzada.
  - Sustitución del antiguo `dbName.def` por `bases.dat` centralizado.

-----

## 🧰 Opac – v1.1.0-beta (2023-03-28)

  - Integración de **Bootstrap** como base de diseño.
  - Parámetro `OpacHttp` se vuelve obligatorio.
  - `select_record.pft` actualizado al estándar Bootstrap.

-----

# 🗂️ Estructura del Proyecto

### 📁 Directorios principales

```
    /bases/opac_conf/lang/
```

Archivos necesarios:

  - `bases.dat`
  - `lang.tab`
  - `footer.info`
  - `menu.info`
  - `side_bar.info`
  - `sitio.info`

### 🧭 Formularios de Búsqueda

Los formularios deben respetar el patrón de prefijos de cada base:

  - `libre.tab` – Búsqueda libre (meta-búsqueda)
  - `avanzada.tab` – Búsqueda avanzada
  - `colecciones.tab` – Subconjuntos de registros

Otros archivos evaluados en desarrollo:

  - `facetas.dat`
  - `formatos.dat`
  - `autoridades_opac.pft`
  - `indice.ix`
  - `opac.pft`
  - `opac_loanobjects.pft`
  - `select_record.pft`

### 🧩 Configuración por base

Cada base habilitada en el OPAC debe contener:

```
    /bases/[dbName]/opac/lang/
```

Archivos:

  - `dbName.def`
  - `dbName.ix`
  - `dbName.lang`
  - `dbName_facetas.dat`
  - `dbName_formatos.dat`
  - `dbName_libre.tab`
  - `dbName_avanzada.tab`
  - `dbName_colecciones.tab`

-----

# 🏗️ Características Generales

  - Búsqueda en hasta **3 niveles**:
    1️⃣ Meta búsqueda
    2️⃣ Búsqueda en base específica
    3️⃣ Búsqueda en subconjuntos (vía prefijo del FST)
  - Soporte multilingüe (`lang.tab`)
  - Diseño basado en **Bootstrap**
  - Soporte para modo **oscuro/claro** (dark/light)
  - Visualización de resultados en múltiples formatos (HTML, XML MARC, XML DC)

-----

# 🌐 Créditos y Comunidad

Desarrollado y mantenido por la **ABCD Community**
🔗 [https://abcd-community.org](https://abcd-community.org)

💬 Participe en la comunidad, envíe sugerencias y contribuya a la evolución del OPAC.

-----

> © 2025 ABCD Community — Automatización de Bibliotecas y Centros de Documentación
> Proyecto de código abierto mantenido por la comunidad global de ABCD.
