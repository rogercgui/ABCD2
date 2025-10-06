	</div>



	</div>


	</main>

	<!-- end #content -->

	</div>
	</div>


	<script type="text/javascript" src="<?php echo $OpacHttp; ?>assets/js/jquery-ui.min.js?<?php echo time(); ?>"></script>

	<?php include_once($Web_Dir . 'views/more_links.php'); ?>

	<!--ESTO SE CAMBIO PARA PODER INSERTAR EL NUEVO FOOTER -->
	<?php
	echo "<footer class=\"py-3 my-4 border-top pb-3 mb-3 custom-footer container" . $container . "\" id=\"footer\">\n";

	if (file_exists($db_path . "opac_conf/" . $lang . "/footer.info")) {
		$fp = file($db_path . "opac_conf/" . $lang . "/footer.info");
		foreach ($fp as $value) {
			$value = trim($value);
			if ($value != "") {
				if (substr($value, 0, 6) == "[LINK]") {
					$home_link = substr($value, 6);
					$hl = explode('|||', $home_link);
					$home_link = $hl[0];
					if (isset($hl[1]))
						$height_link = $hl[1];
					else
						$height_link = 800;
					$footer = "LINK";
				}
				if (substr($value, 0, 6) == "[TEXT]") {
					$home_text = substr($value, 6);
					$footer = "TEXT";
				}

				if (substr($value, 0, 6) == "[HTML]") {
					$home_text = substr($value, 6);
					$footer = "HTML";
				}
			}
		}
		switch ($footer) {
			case "LINK":

	?>
				<div>
					<iframe src="<?php echo $home_link ?>" frameborder="0" scrolling="no" width=100% height="<?php echo $height_link ?>" />
					</iframe>
				</div>
	<?php break;
			case "TEXT":
				$fp = file($db_path . "opac_conf/" . $lang . "/footer.info");
				foreach ($fp as $v) {
					echo str_replace("[TEXT]", "", $v);
				}
				break;
			case "HTML":
				$fp = file($db_path . "opac_conf/" . $lang . "/footer.info");
				foreach ($fp as $v) {
					echo str_replace("[HTML]", "", $v);
				}
				break;
		}
	} else {
		echo $footer;
		echo "\n";
	}
	?>
	<!-- end #footer -->
	</footer>
	</body>

	</html>

	<!-- Light Switch -->
	<script type="text/javascript" src="<?php echo $OpacHttp; ?>assets/js/switch.js?<?php echo time(); ?>"></script>
	<script type="text/javascript" src="<?php echo $OpacHttp; ?>assets/js/slick.min.js?<?php echo time(); ?>"></script>
	<script type="text/javascript" src="<?php echo $OpacHttp; ?>assets/js/script_f.js?<?php echo time(); ?>"></script>

	<?php
	toTop();
	include($Web_Dir . "forms.php");
	?>


	<div class="modal fade" id="permalinkModal" tabindex="-1" aria-labelledby="permalinkModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="permalinkModalLabel"><?php echo $msgstr["share_link"]; ?></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<p><?php echo $msgstr["copy_share_link"]; ?></p>
					<div class="input-group">
						<input type="text" id="permalinkInput" class="form-control" value="" readonly>
						<button class="btn btn-primary" type="button" id="copyPermalinkButton" onclick="copyPermalink()">Copiar</button>
					</div>
				</div>
			</div>
		</div>
	</div>