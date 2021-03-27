<?php

if(isset($_SESSION['id_compte']))
	{
	if(isset($_GET['action']))
		{
		switch($_GET['action'])
			{
			//on traite le cas "ajouter" envoyé depuis le lien du menu admin.html via le parametre choix
			case "afficher_pages":
			
			$action_form="afficher_pages";

			//on construit la liste déroulante des langues
			$requete="SELECT * FROM menus WHERE type_menu='front' ORDER BY rang_menu";
			$resultat=mysqli_query($connexion, $requete);
			$ld_menu="<option value=\"\">Item de menu *</option>\n";
			while($ligne=mysqli_fetch_object($resultat))
				{
				if(isset($_POST['id_menu']) && $ligne->id_menu == $_POST['id_menu'])
					{
					$selected[$ligne->id_menu]=" selected=\"selected\"";  
					}                  
				else
					{
					$selected[$ligne->id_menu]="";
					}                     
				$ld_menu.="<option value=\"" . $ligne->id_menu . "\"" . $selected[$ligne->id_menu] . ">" . $ligne->intitule_menu . "</option>\n";          
				}
								
			//si qq appuie sur le bouton enregistrer
			if(isset($_POST['submit']))
				{
				if(empty($_POST['id_menu']))
					{
					$message="<label class=\"pas_ok\">Vous devez sélectionner un item de menu</label>";
					$color['id_menu']=" class=\"avertissement\"";
					}					
				if(empty($_POST['titre_page']))
					{
					$message="<label class=\"pas_ok\">Vous devez saisir un titre</label>";
					$color['titre_page']=" class=\"avertissement\"";
					}	
				elseif(empty($_POST['contenu_page']))
					{
					$message="<label class=\"pas_ok\">Vous devez saisir un contenu</label>";
					$color['contenu_page']=" class=\"avertissement\"";
					}													
				else
					{											
					//on insere la nouvelle page
					$requete="INSERT INTO pages SET titre_page='" . addslashes($_POST['titre_page']) . "',
																		 id_compte='" . $_SESSION['id_compte'] . "',
																		 id_menu='" . $_POST['id_menu'] . "',
																		 contenu_page='" . addslashes($_POST['contenu_page']) . "',
																		 date_page=NOW()";
					$resultat=mysqli_query($connexion,$requete);

					//si le champ parcourir ne reste pas vide
					if(!empty($_FILES['fichier_page']['name']))
						{
						//on récupere le id_page qui vient d'être créé
						$cle=mysqli_insert_id($connexion);	
						
						//on traite le cas du fichier uploadé : vérification de l'extension de type image
						if(fichier_type($_FILES['fichier_page']['name'])=="jpg" ||
						   fichier_type($_FILES['fichier_page']['name'])=="png" || 
						   fichier_type($_FILES['fichier_page']['name'])=="gif")
							{  
							$chemin_b="../medias/mediap" . $cle . "_b." . fichier_type($_FILES['fichier_page']['name']);
							$chemin_s="../medias/mediap" . $cle . "_s." . fichier_type($_FILES['fichier_page']['name']);													

							if(is_uploaded_file($_FILES['fichier_page']['tmp_name']))
							// tmp_name correspond au nom temporaire donné au fichier lors de sa copie sur le serveur
								{                                
								if(copy($_FILES['fichier_page']['tmp_name'], $chemin_b))
									{
									//on calcule la taille de l'image uploadée
									$size=GetImageSize($chemin_b);							
									$largeur=$size[0];
									$hauteur=$size[1];
									$rapport=$largeur/$hauteur;
									if($largeur>300)
										{
										$x=300;
										}
									else
										{
										$x=$largeur;
										}
									//permet de jouer sur la qualité des fichiers jpg uniquement	
									$quality=85;
									//on génère une miniature du fichier d'origine
									redimage($chemin_b,$chemin_s,$x,$x/$rapport,$quality);     											

									$requete="UPDATE pages SET fichier_page='" . $chemin_b . "'  
													WHERE id_page='". $cle . "'";	
									$resultat=mysqli_query($connexion,$requete);
									$message="<label class=\"ok\">La page a bien été créée</label>";
									}						  
								}
							}
						else
							{
							$message="<label class=\"pas_ok\">Le fichier doit être de type image ! (jpg/png/gif)</label>";
							$color['fichier_page']=" class=\"avertissement\"";	
							}
						}
					else
						{
						$message="<p class=\"ok\">La page a bien été créée</p>";
						}
					//on vide les champs du formulaire
					foreach($_POST as $key => $value)
						{
						unset($_POST[$key]);
						}						
					}	
				}
				
			break;

			case "modifier_page":
			$action_form="modifier_page";
			//on va chercher les données dans la table pages pour
			//les réinjecter dans le formulaire (recharge le formulaire)
			if(isset($_GET['id_page']))
				{
				$requete="SELECT * FROM pages WHERE id_page='" . $_GET['id_page'] . "'";
				$resultat=mysqli_query($connexion,$requete);
				$ligne=mysqli_fetch_object($resultat);
				$_POST['titre_page']=$ligne->titre_page;
				$_POST['contenu_page']=$ligne->contenu_page;			

				//on stocke en session la variable id_pages reçue
				//par la méthode GET (url)				
				$_SESSION['id_page']=$_GET['id_page'];
				
				//on construit la liste déroulante des langues
				$requete2="SELECT * FROM menus WHERE type_menu='front' ORDER BY rang_menu";
				$resultat2=mysqli_query($connexion, $requete2);
				$ld_menu="<option value=\"\">Item de menu *</option>\n";
				while($ligne2=mysqli_fetch_object($resultat2))
					{
					if($ligne->id_menu==$ligne2->id_menu)
						{
						$selected[$ligne2->id_menu]=" selected=\"selected\"";  
						}                  
					else
						{
						$selected[$ligne2->id_menu]="";
						}                     
					$ld_menu.="<option value=\"" . $ligne2->id_menu . "\"" . $selected[$ligne2->id_menu] . ">" . $ligne2->intitule_menu . "</option>\n";          
					}				
				}
			//si le bouton enregistrer du formulaire est activé
			if(isset($_POST['submit']))
				{
				if(empty($_POST['id_menu']))
					{
					$message="<label class=\"pas_ok\">Vous devez sélectionner un item de menu</label>";
					$color['id_menu']=" class=\"avertissement\"";
					}					
				elseif(empty($_POST['titre_page']))
					{
					$message="<label class=\"pas_ok\">Vous devez saisir un titre</label>";
					$color['titre_page']=" class=\"avertissement\"";
					}
				elseif(empty($_POST['contenu_page']))
					{
					$message="<label class=\"pas_ok\">Vous devez saisir un contenu</label>";
					$color['contenu_page']=" class=\"avertissement\"";
					}									
				else
					{
					$requete="UPDATE pages SET titre_page='" . addslashes($_POST['titre_page']) . "',
									id_compte='" . $_SESSION['id_compte'] . "',
									id_menu='" . $_POST['id_menu'] . "',
									contenu_page='" . addslashes($_POST['contenu_page']) . "',
									date_page=NOW()  
									WHERE id_page='" . $_SESSION['id_page'] . "'";
					$resultat=mysqli_query($connexion,$requete);
					
					//si le champ parcourie ne reste pas vide
					if(!empty($_FILES['fichier_page']['name']))
						{
						//on traite le cas du fichier uploadé : vérification de l'extension de type image
						if(fichier_type($_FILES['fichier_page']['name'])=="jpg" ||
						   fichier_type($_FILES['fichier_page']['name'])=="png" || 
						   fichier_type($_FILES['fichier_page']['name'])=="gif")
							{  
							$chemin_b="../medias/mediap" . $_SESSION['id_page']  . "_b." . fichier_type($_FILES['fichier_page']['name']);
							$chemin_s="../medias/mediap" . $_SESSION['id_page']  . "_s." . fichier_type($_FILES['fichier_page']['name']);													

							if(is_uploaded_file($_FILES['fichier_page']['tmp_name']))
							// tmp_name correspond au nom temporaire donné au fichier lors de sa copie sur le serveur
								{                                
								if(copy($_FILES['fichier_page']['tmp_name'], $chemin_b))
									{
									//on calcule la taille de l'image uploadée
									$size=GetImageSize($chemin_b);							
									$largeur=$size[0];
									$hauteur=$size[1];
									$rapport=$largeur/$hauteur;
									if($largeur>300)
										{
										$x=300;
										}
									else
										{
										$x=$largeur;
										}
									//permet de jouer sur la qualité des fichiers jpg uniquement	
									$quality=85;
									//on génère une miniature du fichier d'origine
									redimage($chemin_b,$chemin_s,$x,$x/$rapport,$quality);     											

									$requete="UPDATE pages SET fichier_page='" . $chemin_b . "'  
													WHERE id_page='". $_SESSION['id_page']  . "'";	
									$resultat=mysqli_query($connexion,$requete);
									$message="<p class=\"ok\">La page a bien été modifiée</p>";
									}						  
								}
							}
						else
							{
							$message="<label class=\"pas_ok\">Le fichier doit être de type image ! (jpg/png/gif)</label>";
							$color['fichier_pages']=" class=\"avertissement\"";	
							}
						}
					else
						{
						$message="<label class=\"ok\">La page a bien été modifiée</label>";
						}
					$action_form="afficher_pages";	
					//on vide les champs du formulaire
					foreach($_POST as $key => $value)
						{
						unset($_POST[$key]);
						}							
					}				
				}

			break;	
			
			case "supprimer_image":
			
			//si on reçoit en méthode GET le parametre id_contact (alors qq à appuyé sur la poubelle)
			if(isset($_GET['id_page']))
				{
				$requete="SELECT fichier_page FROM pages WHERE id_page='" . $_GET['id_page'] . "'";
				$resultat=mysqli_query($connexion,$requete);
				$ligne=mysqli_fetch_object($resultat);
				
				//on supprime les 2 fichiers image
				
				//petit fichier : ../medias/mediapx_s.jpg
				@unlink(str_replace("_b","_s",$ligne->fichier_page));
				
				//grand fichier :  ../medias/mediapx_b.jpg
				@unlink($ligne->fichier_page);
				
				$requete2="UPDATE pages SET fichier_page='' WHERE id_page='" . $_GET['id_page'] . "'";
				$resultat2=mysqli_query($connexion,$requete2);
				$message="<label class=\"ok\">Le fichier image a bien été supprimé</label>\n";
				}						
			
			break;

			case "supprimer_page":
			
			$action_form="afficher_pages";	
			
			//si on reçoit en méthode GET le parametre id_pages (qq à appuyé sur la poubelle)
			if(isset($_GET['id_page']))
				{
				if(isset($_GET['ext']))
					{
					$extension="&ext=" . $_GET['ext'];	
					}
				else{
					$extension="";
					}					
				$entete="<h1 class=\"ouinon\">Vous-voulez vraiment supprimer cette page ? 
				<a href=\"admin.php?module=pages&action=supprimer_page&id_page=".$_GET['id_page']."&confirm=1".$extension."\">OUI</a>
				<a href=\"admin.php?module=pages&action=afficher_pages\">NON</a>
				</h1>";
				//si l'internaute à confirmer la suppression (bouton oui)
				if(isset($_GET['confirm']) && $_GET['confirm']==1)
					{
					$requete2="DELETE FROM pages WHERE id_page='".$_GET['id_page']."'";	
					$resultat2=mysqli_query($connexion,$requete2);

					if(isset($_GET['ext']))
						{
						//on calcule les chemins
						$chemin_a_supprimer_b="../medias/mediap_b" . $_GET['id_page'] . "." . $_GET['ext']; 
						$chemin_a_supprimer_s="../medias/mediap_s" . $_GET['id_page'] . "." . $_GET['ext']; 							
						
						//on supprime les fichiers
						unlink($chemin_a_supprimer_b);
						unlink($chemin_a_supprimer_s);
						}
					
					$entete="<h1 class=\"ok\">Page supprimé</h1>";
					//on réinitialise l'action du formulaire à afficher_pages			
					$action_form="afficher_pages";
					}						
				}
			break;
			}
		$requete2="SELECT m.*,c.*,p.* FROM pages p 
					LEFT JOIN comptes c 
					ON p.id_compte=c.id_compte
					INNER JOIN menus m	
					ON p.id_menu=m.id_menu
					ORDER BY p.id_menu, p.date_page DESC";
				
		$tab_resultats=afficher_pages($connexion,$requete2,"back");
		}
	}
else{
	header("Location:../index.php");	
	}		
?>