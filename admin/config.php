<?php
if(isset($_SESSION['id_compte']))
	{
	$entete="<h1>Gestion de la palette des couleurs</h1>";		
	if(isset($_POST['submit']))
		{
		if(empty($_POST['color_1']))
			{
			$message="<p class=\"pas_ok\">Vous devez choisir la couleur n°1</p>";
			$color['color_1']=" class=\"color_champ\"";						
			}
		elseif(empty($_POST['color_2']))
			{
			$message="<p class=\"pas_ok\">Vous devez choisir la couleur n°2</p>";
			$color['color_2']=" class=\"color_champ\"";						
			}
		elseif(empty($_POST['color_3']))
			{
			$message="<p class=\"pas_ok\">Vous devez choisir la couleur n°3</p>";
			$color['color_3']=" class=\"color_champ\"";						
			}
		else
			{
			$css=":root{\n--color_1:" . $_POST['color_1'] . ";\n--color_2:" . $_POST['color_2'] . ";\n--color_3:" . $_POST['color_3'] . ";\n}";
			$fichier_root=fopen("../css/color.css", "w");//le parametre w permet au curseur de démarrer en début de fichier et ouvre les droits en écriture seule
			// on écrit dans le fichier root.css
			fputs($fichier_root, "\n" . $css);
			//on referme le fichier
			fclose($fichier_root);							
			}				
		}
	else
		{
		// on lit le fichier color.css, et on exploite son contenu
		$fichier_root=fopen("../css/color.css", "r");//le parametre r permet au curseur de démarrer en début de fichier et ouvre les droits en lecture seule
		// on lit dans le fichier color.css tant qu'il y a du texte

		// si color.css est rempli, on lit color.css tant qu'il y a du texte
		if(filesize("../css/color.css")!=0)
			{
			$css=fread($fichier_root, filesize("../css/color.css"));
			//on referme le fichier
			fclose($fichier_root);	

			//on coupe la chaine lue sur les ";"
			$tab_proprietes=explode(";",$css);	
			$i=0;
			foreach($tab_proprietes as $cle => $valeur)
				{
				//on coupe ensuite chaque proprieté sur les ":"
				$tab_colors[$i]=explode(":",$valeur);	
				$i++;					
				}
			$_POST['color_1']=$tab_colors[0][2];//ATTENTION : [2] car il y a le : devant root qui est compté !!!!
			$_POST['color_2']=$tab_colors[1][1];			
			$_POST['color_3']=$tab_colors[2][1];						
			}
		}
	}
else{
	header("Location:../index.php");	
	}
?>