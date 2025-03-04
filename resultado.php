<!doctype html>
<html>
	<head>
		<style>
			.ok{width:20px;height:20px;border-radius:20px;background:green;}
			.ko{width:20px;height:20px;border-radius:20px;background:red;}
			span{display:inline-block;margin:3px;}
		</style>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<h1>jocarsa | deeppink</h1>
		<table>
		
				<?php
					if(isset($_POST['url'])){
						include "DeepPink.php";
						$web = new DeepPink($_POST['url']);
						$web->dameTitulo();
						$web->dameDescripcion();
						$web->damePalabras();
						$web->nubeDePalabras();
						for($i = 1;$i<=6;$i++){
							$web->dameTitulos($i);
						}
					}
				?>
		</table>
	</body>
</html>
