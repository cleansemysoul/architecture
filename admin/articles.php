<?php

if(isset($_SESSION['id_compte']))
	{
	if(isset($_GET['action']))
		{
		switch($_GET['action'])
			{
			//on traite le cas "ajouter" envoyé depuis le lien du menu admin.html via le parametre choix
			case "afficher_articles":
			
			$action_form="afficher_articles";								
								
			//si qq appuie sur le bouton enregistrer
			if(isset($_POST['submit']))
				{
				if(!empty($_POST['flux_article']))
					{
					$check=" checked=\"checked\"";
					}					
				else{
					$_POST['flux_article']=0;	
					}
				if(empty($_POST['titre_article']))
					{
					$message="<label class=\"pas_ok\">Vous devez saisir un titre</label>";
					$color['titre_article']=" class=\"avertissement\"";
					}	
				elseif(empty($_POST['contenu_article']))
					{
					$message="<label class=\"pas_ok\">Vous devez saisir un contenu</label>";
					$color['contenu_article']=" class=\"avertissement\"";
					}							
				elseif(empty($_POST['date_article']))
					{
					$message="<label class=\"pas_ok\">Vous devez saisir une date</label>";
					$color['date_article']=" class=\"avertissement\"";
					}							
				else
					{							
					//on calcule le rang a attribuer au nouvel item
					$requete="SELECT id_article FROM articles";
					$resultat=mysqli_query($connexion,$requete);
					
					//on compte le nombre de lignes trouvé par la requete
					$nb=mysqli_num_rows($resultat);
					$nouveau_rang=$nb+1;
				
					//on insere le nouvel article
					$requete="INSERT INTO articles SET titre_article='" . addslashes($_POST['titre_article']) . "',
																		 id_compte='" . $_SESSION['id_compte'] . "',
																		 contenu_article='" . addslashes($_POST['contenu_article']) . "',
																		 date_article='" . $_POST['date_article'] . "',
																		 rang_article='" . $nouveau_rang . "',
																		 flux_article='" . $_POST['flux_article'] . "'";
					$resultat=mysqli_query($connexion,$requete);
					
					//si le champ parcourir ne reste pas vide
					if(!empty($_FILES['fichier_article']['name']))
						{
						//on recupere la clé primaire de l'article qui vient d'être créé
						$cle=mysqli_insert_id($connexion);
						
						//on traite le cas du fichier uploadé : vérification de l'extension de type image
						if(fichier_type($_FILES['fichier_article']['name'])=="jpg" ||
						   fichier_type($_FILES['fichier_article']['name'])=="png" || 
						   fichier_type($_FILES['fichier_article']['name'])=="gif")
							{  
							$chemin_b="../medias/media" . $cle . "_b." . fichier_type($_FILES['fichier_article']['name']);
							$chemin_s="../medias/media" . $cle . "_s." . fichier_type($_FILES['fichier_article']['name']);													

							if(is_uploaded_file($_FILES['fichier_article']['tmp_name']))
							// tmp_name correspond au nom temporaire donné au fichier lors de sa copie sur le serveur
								{                                
								if(copy($_FILES['fichier_article']['tmp_name'], $chemin_b))
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

									$requete="UPDATE articles SET fichier_article='" . $chemin_b . "'  
													WHERE id_article='". $cle . "'";	
									$resultat=mysqli_query($connexion,$requete);
									$message="<label class=\"ok\">L'article a bien été créé</label>";
									}						  
								}
							}
						else
							{
							$message="<label class=\"pas_ok\">Le fichier doit être de type image ! (jpg/png/gif)</label>";
							$color['fichier_article']=" class=\"avertissement\"";	
							}
						}
					else
						{
						$message="<p class=\"ok\">L'article a bien été créé</p>";
						}
					//on vide les champs du formulaire
					foreach($_POST as $key => $value)
						{
						unset($_POST[$key]);
						}						
					}	
				}
				
			break;

			case "modifier_article":
			$action_form="modifier_article";
			//on va chercher les données dans la table articles pour
			//les réinjecter dans le formulaire (recharge le formulaire)
			if(isset($_GET['id_article']))
				{
				$requete="SELECT * FROM articles WHERE id_article='" . $_GET['id_article'] . "'";
				$resultat=mysqli_query($connexion,$requete);
				$ligne=mysqli_fetch_object($resultat);
				$_POST['titre_article']=$ligne->titre_article;
				$_POST['contenu_article']=$ligne->contenu_article;
				$_POST['date_article']=$ligne->date_article; 
				
				//on coche la checkbox si flux rss précedemment enregistré dans la table vaut 1
				if($ligne->flux_article==1)
					{
					$check=" checked=\"checked\"";					
					}
				//on stocke en session la variable id_article reçue
				//par la méthode GET (url)				
				$_SESSION['id_article']=$_GET['id_article'];
				}
			//si le bouton enregistrer du formulaire est activé
			if(isset($_POST['submit']))
				{
				if(empty($_POST['flux_article']))
					{
					$_POST['flux_article']=0;					
					}	
				if(empty($_POST['titre_article']))
					{
					$message="<label class=\"pas_ok\">Vous devez saisir un titre</label>";
					$color['titre_article']=" class=\"avertissement\"";
					}
				elseif(empty($_POST['contenu_article']))
					{
					$message="<label class=\"pas_ok\">Vous devez saisir un contenu</label>";
					$color['contenu_article']=" class=\"avertissement\"";
					}					
				elseif(empty($_POST['date_article']))
					{
					$message="<label class=\"pas_ok\">Vous devez saisir une date</label>";
					$color['date_article']=" class=\"avertissement\"";
					}					
				else
					{
					$requete="UPDATE articles SET 
					titre_article='" . addslashes($_POST['titre_article']) . "',
					contenu_article='" . addslashes($_POST['contenu_article']) . "',
					date_article='" . $_POST['date_article'] . "',
					flux_article='" . $_POST['flux_article'] . "' 
					WHERE id_article='" . $_SESSION['id_article'] . "'";
					$resultat=mysqli_query($connexion,$requete);
					
					//si le champ parcourie ne reste pas vide
					if(!empty($_FILES['fichier_article']['name']))
						{
						//on traite le cas du fichier uploadé : vérification de l'extension de type image
						if(fichier_type($_FILES['fichier_article']['name'])=="jpg" ||
						   fichier_type($_FILES['fichier_article']['name'])=="png" || 
						   fichier_type($_FILES['fichier_article']['name'])=="gif")
							{  
							$chemin_b="../medias/media" . $_SESSION['id_article']  . "_b." . fichier_type($_FILES['fichier_article']['name']);
							$chemin_s="../medias/media" . $_SESSION['id_article']  . "_s." . fichier_type($_FILES['fichier_article']['name']);													

							if(is_uploaded_file($_FILES['fichier_article']['tmp_name']))
							// tmp_name correspond au nom temporaire donné au fichier lors de sa copie sur le serveur
								{                                
								if(copy($_FILES['fichier_article']['tmp_name'], $chemin_b))
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

									$requete="UPDATE articles SET fichier_article='" . $chemin_b . "'  
													WHERE id_article='". $_SESSION['id_article']  . "'";	
									$resultat=mysqli_query($connexion,$requete);
									$message="<p class=\"ok\">L'article a bien été modifié</p>";
									}						  
								}
							}
						else
							{
							$message="<label class=\"pas_ok\">Le fichier doit être de type image ! (jpg/png/gif)</label>";
							$color['fichier_article']=" class=\"avertissement\"";	
							}
						}
					else
						{
						$message="<label class=\"ok\">L'article a bien été modifié</label>";
						}
					$action_form="afficher_articles";	
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
			if(isset($_GET['id_article']))
				{
				$requete="SELECT fichier_article FROM articles WHERE id_article='" . $_GET['id_article'] . "'";
				$resultat=mysqli_query($connexion,$requete);
				$ligne=mysqli_fetch_object($resultat);
				
				//on supprime les 2 fichiers image
				
				//petit fichier : ../medias/mediax_s.jpg
				@unlink(str_replace("_b","_s",$ligne->fichier_article));
				
				//grand fichier :  ../medias/mediax_b.jpg
				@unlink($ligne->fichier_article);
				
				$requete2="UPDATE articles SET fichier_article='' WHERE id_article='" . $_GET['id_article'] . "'";
				$resultat2=mysqli_query($connexion,$requete2);
				$message="<label class=\"ok\">Le fichier image a été supprimé</label>\n";
				}						
			
			break;

			case "supprimer_article":
			
			$action_form="afficher_articles";	
			
			//si on reçoit en méthode GET le parametre id_article (qq à appuyé sur la poubelle)
			if(isset($_GET['id_article']))
				{
				if(isset($_GET['ext']))
					{
					$extension="&ext=" . $_GET['ext'];	
					}
				else{
					$extension="";
					}					
				$entete="<h1 class=\"ouinon\">Vous-voulez vraiment supprimer cet article ? 
				<a href=\"admin.php?module=articles&action=supprimer_article&id_article=".$_GET['id_article']."&confirm=1".$extension."\">OUI</a>
				<a href=\"admin.php?module=articles&action=afficher_articles\">NON</a>
				</h1>";
				//si l'internaute à confirmer la suppression (bouton oui)
				if(isset($_GET['confirm']) && $_GET['confirm']==1)
					{
					$requete2="DELETE FROM articles WHERE id_article='".$_GET['id_article']."'";	
					$resultat2=mysqli_query($connexion,$requete2);
					
					$requete3="SELECT * FROM articles ORDER BY rang_article";
					$resultat3=mysqli_query($connexion, $requete3);
					$i=1;
					while($ligne3=mysqli_fetch_object($resultat3))
						{
						$requete4="UPDATE articles SET rang_article='" . $i . "' WHERE id_article='" . $ligne3->id_article . "'";
						$resultat4=mysqli_query($connexion, $requete4);
						$i++;
						}					
					
					if(isset($_GET['ext']))
						{
						//on calcule les chemins
						$chemin_a_supprimer_b="../medias/media_b" . $_GET['id_article'] . "." . $_GET['ext']; 
						$chemin_a_supprimer_s="../medias/media_s" . $_GET['id_article'] . "." . $_GET['ext']; 							
						
						//on supprime les fichiers
						unlink($chemin_a_supprimer_b);
						unlink($chemin_a_supprimer_s);
						}
					
					$entete="<h1 class=\"ok\">Article supprimé</h1>";
					//on réinitialise l'action du formulaire à afficher_articles					
					$action_form="afficher_articles";
					}						
				}
			break;

			
			case "trier_article":
			
			$action_form="ajouter";	
			
			if(isset($_GET['id_article']) && isset($_GET['tri']))
				{
				//1. On vérifie quel était le rang du id_menu à trier
				$requete="SELECT id_article, rang_article FROM articles WHERE id_article='" . $_GET['id_article'] . "'";
				$resultat=mysqli_query($connexion, $requete);
				$ligne=mysqli_fetch_object($resultat);
				
				switch($_GET['tri'])	
					{
					case "up":

					if($ligne->rang_article>1)
						{
						//on calcul le nouveau rang	
						$nouveau_rang=$ligne->rang_article-1;
							
						//2. On modifie le rang de la ligne qui a déja le $nouveau_rang
						$inversion_rang=$nouveau_rang+1;
						$requete2="UPDATE articles SET rang_article='" . $inversion_rang . "' WHERE rang_article='" . $nouveau_rang . "'";
						$resultat2=mysqli_query($connexion, $requete2);		

						//3. On attribue le nouveau rang au id_menu concerné
						$requete3="UPDATE articles SET rang_article='" . $nouveau_rang . "' WHERE id_article='" . $_GET['id_article'] . "'";
						$resultat3=mysqli_query($connexion, $requete3);										
						}
						
					break;
					
					case "down":

					//2. On compte le nombre de lignes de la table
					$requete2="SELECT id_article FROM articles";
					$resultat2=mysqli_query($connexion, $requete2);
					$nb_lignes=mysqli_num_rows($resultat2);							

					// si le rang de l'item à modifier est inférieur au nombre de lignes de la table
					if($ligne->rang_article<$nb_lignes)
						{
						//on calcul le nouveau rang	
						$nouveau_rang=$ligne->rang_article+1;
							
						//3. On modifie le rang de la ligne qui a déja le $nouveau_rang
						$inversion_rang=$nouveau_rang-1;
						$requete3="UPDATE articles SET rang_article='" . $inversion_rang . "' WHERE rang_article='" . $nouveau_rang . "'";
						$resultat3=mysqli_query($connexion, $requete3);		

						//4. On attribue le nouveau rang au id_menu concerné
						$requete4="UPDATE articles SET rang_article='" . $nouveau_rang . "' WHERE id_article='" . $_GET['id_article'] . "'";
						$resultat4=mysqli_query($connexion, $requete4);	
						}									
					
					break;
					}
				}
								
			break;

			}
		//on affiche les items du slider triés par rang_slider	
		//MÉTHODE 1
		$requete0="SELECT c.*,a.* FROM comptes c, articles a 
						WHERE c.id_compte=a.id_compte 
						ORDER BY a.id_compte, a.rang_article";
						
		$requete1="SELECT c.*,a.* FROM articles a 
					INNER JOIN comptes c 
					ON a.id_compte=c.id_compte 
					ORDER BY a.id_compte, a.rang_article";	
		//les requetes 0 et 1 donnent le même résultat
					
		//MÉTHODE 2
		//(INNER JOIN(jointure classique | LEFT JOIN(jointure gauche) | RIGHT JOIN(jointure droite)))
		$requete2="SELECT c.*,a.* FROM articles a 
					LEFT JOIN comptes c 
					ON a.id_compte=c.id_compte 
					ORDER BY a.id_compte, a.rang_article";
					
		$requete3="SELECT c.*,a.* FROM comptes c 
					RIGHT JOIN articles a 
					ON a.id_compte=c.id_compte 
					ORDER BY a.id_compte, a.rang_article";					
		
		//les requetes 2 et 3 donnent le même résultat
		$tab_resultats=afficher_articles($connexion,$requete2,"back");
		}
	}
else{
	header("Location:../index.php");	
	}		
?>