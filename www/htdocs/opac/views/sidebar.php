<!-- Sidebar de facetas -->
<aside id="sidebar" class="collapse d-md-block col-12 col-md-3 flex-shrink-0 p-3 custom-sidebar">
		<?php
		if (!isset($_REQUEST["existencias"]) or trim($_REQUEST["existencias"]) == "") {
			if (isset($_GET["page"]) && $_GET["page"] == "startsearch") {
			include_once('components/facets.php');
			}
		}
		?>
</aside>