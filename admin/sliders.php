<?php

if(isset($_SESSION['id_compte']))
	{
	if(isset($_GET['action']))
		{
		switch($_GET['action'])
			{
			case "afficher_sliders":
			$entete="<h1>Gestion du slider</h1>";
			$action_form="afficher_sliders";
			//on insert les champs dans la table sliders
			if(isset($_POST['submit']))
				{
				if(empty($_POST['titre_slider']))
					{
					$message="<label class=\"pas_ok\">Mets ton titre</label>";	
					$color['titre_slider']="class=\"avertissement\" ";
					}
				elseif(empty($_FILES['fichier_slider']['name']))
					{
					$message="<label class=\"pas_ok\">Va chercher ton image</label>";	
					$color['fichier_slider']="class=\"avertissement\" ";
					}
				else{
					//on teste si le fichier a le bon format
					if(fichier_type($_FILES['fichier_slider']['name'])=="png" ||
					   fichier_type($_FILES['fichier_slider']['name'])=="jpg" ||
					   fichier_type($_FILES['fichier_slider']['name'])=="gif")
						{
						//on calcule le rang a attribuer au nouveau slider
						$requete="SELECT id_slider FROM sliders";
						$resultat=mysqli_query($connexion,$requete);
						
						//on compte le nombre de lignes trouvé par la requete
						$nb=mysqli_num_rows($resultat);
						$nouveau_rang=$nb+1;							
							

						//on insere dans la table sliders les champs autres que FILE
						$requete="INSERT INTO sliders 
						SET titre_slider='".addslashes($_POST['titre_slider'])."',
							descriptif_slider='".addslashes($_POST['descriptif_slider'])."',
							rang_slider='".$nouveau_rang."'";
						
						$resultat=mysqli_query($connexion, $requete);
						$dernier_id_cree=mysqli_insert_id($connexion);
						
						//on génère les 2 chemins des fichiers image : le big et le small
						$chemin_b="../medias/slider_b" . $dernier_id_cree . "." . fichier_type($_FILES['fichier_slider']['name']);
						$chemin_s="../medias/slider_s" . $dernier_id_cree . "." . fichier_type($_FILES['fichier_slider']['name']);						
						
						if(is_uploaded_file($_FILES['fichier_slider']['tmp_name']))
						//tmp_name correspond au nom temporaire donné au fichier lors de sa copie sur le serveur
							{                                
							if(copy($_FILES['fichier_slider']['tmp_name'], $chemin_b))
								{
								//On calcule les dimensions de l'image originelle
								$size=GetImageSize($chemin_b);
								$largeur=$size[0];
								$hauteur=$size[1];
								$rapport=$largeur/$hauteur;
								//si $rapport>1 alors image paysage
								//si $rapport<1 alors image portrait
								//si $rapport=1 alors image carrée
								
								//on genere une miniature en respectant l'homothétie
								$largeur_mini=100;
								$quality=80;
								redimage($chemin_b,$chemin_s,$largeur_mini,$largeur_mini/$rapport,$quality);
								
								//on met la jour la table sliders avec le chemin du fichier
								$requete2="UPDATE sliders 
											SET fichier_slider='" . $chemin_s . "' 
											WHERE id_slider='".$dernier_id_cree."'";
								$resultat2=mysqli_query($connexion, $requete2);			
								$message="<label class=\"ok\">Le fichier a bien été inséré</label>";			
								}									
							}
						}
					else{
						$message="<label class=\"pas_ok\">Seules les extensions png, gif et jpg sont autorisées</label>";	
						$color['fichier_slider']="class=\"avertissement\" ";
						}
					}
				//on vide tous les champs du formulaire
				foreach($_POST AS $cle => $valeur)
					{
					unset($_POST[$cle]);	
					}						
				}
			break;
			
			case "trier_slider":
			$entete="<h1>Gestion du slider</h1>";
			$action_form="afficher_sliders";

			if(isset($_GET['id_slider']) && isset($_GET['tri']))
				{
				//1. On vérifie quel était le rang du id_slider à trier
				$requete="SELECT id_slider, rang_slider FROM sliders WHERE id_slider='" . $_GET['id_slider'] . "'";
				$resultat=mysqli_query($connexion, $requete);
				$ligne=mysqli_fetch_object($resultat);
				
				switch($_GET['tri'])	
					{
					case "up":

					if($ligne->rang_slider>1)
						{
						//on calcul le nouveau rang	
						$nouveau_rang=$ligne->rang_slider-1;
							
						//2. On modifie le rang de la ligne qui a déja le $nouveau_rang
						$inversion_rang=$nouveau_rang+1;
						$requete2="UPDATE sliders SET rang_slider='" . $inversion_rang . "' WHERE rang_slider='" . $nouveau_rang . "'";
						$resultat2=mysqli_query($connexion, $requete2);		

						//3. On attribue le nouveau rang au id_slider concerné
						$requete3="UPDATE sliders SET rang_slider='" . $nouveau_rang . "' WHERE id_slider='" . $_GET['id_slider'] . "'";
						$resultat3=mysqli_query($connexion, $requete3);										
						}
						
					break;
					
					case "down":

					//2. On compte le nombre de lignes de la table
					$requete2="SELECT id_slider FROM sliders";
					$resultat2=mysqli_query($connexion, $requete2);
					$nb_lignes=mysqli_num_rows($resultat2);							

					// si le rang de l'item à modifier est inférieur au nombre de lignes de la table
					if($ligne->rang_slider<$nb_lignes)
						{
						//on calcul le nouveau rang	
						$nouveau_rang=$ligne->rang_slider+1;
							
						//3. On modifie le rang de la ligne qui a déja le $nouveau_rang
						$inversion_rang=$nouveau_rang-1;
						$requete3="UPDATE sliders SET rang_slider='" . $inversion_rang . "' WHERE rang_slider='" . $nouveau_rang . "'";
						$resultat3=mysqli_query($connexion, $requete3);		

						//4. On attribue le nouveau rang au id_menu concerné
						$requete4="UPDATE sliders SET rang_slider='" . $nouveau_rang . "' WHERE id_slider='" . $_GET['id_slider'] . "'";
						$resultat4=mysqli_query($connexion, $requete4);	
						}									
					
					break;
					}
				}
				
			break;			
			
			case "modifier_slider":
			
			//si qq valide le formulaire (appui sur le bouton ENVOYER)
			if(isset($_POST['submit']))
				{	
				if(empty($_POST['titre_slider']))
					{
					$message="<label class=\"pas_ok\">Mets ton titre</label>";	
					$color['titre_slider']="class=\"avertissement\" ";
					}					
				else{	
					$requete="UPDATE sliders SET titre_slider='".addslashes($_POST['titre_slider'])."',
										 descriptif_slider='".addslashes($_POST['descriptif_slider'])."' 
										WHERE id_slider='".$_GET['id_slider']."'";	
					$resultat=mysqli_query($connexion,$requete);
					
					//si une nouvelle image a été choisie
					if(!empty($_FILES['fichier_slider']['name']))
						{
						//on teste si le fichier a le bon format
						if(fichier_type($_FILES['fichier_slider']['name'])=="png" ||
						   fichier_type($_FILES['fichier_slider']['name'])=="jpg" ||
						   fichier_type($_FILES['fichier_slider']['name'])=="gif")
							{
							//on génère les 2 chemins des fichiers image : le big et le small
							$chemin_b="../medias/slider_b" . $_GET['id_slider'] . "." . fichier_type($_FILES['fichier_slider']['name']);
							$chemin_s="../medias/slider_s" . $_GET['id_slider'] . "." . fichier_type($_FILES['fichier_slider']['name']);						
							
							if(is_uploaded_file($_FILES['fichier_slider']['tmp_name']))
							//tmp_name correspond au nom temporaire donné au fichier lors de sa copie sur le serveur
								{                                
								if(copy($_FILES['fichier_slider']['tmp_name'], $chemin_b))
									{
									//On calcule les dimensions de l'image originelle
									$size=GetImageSize($chemin_b);
									$largeur=$size[0];
									$hauteur=$size[1];
									$rapport=$largeur/$hauteur;
									//si $rapport>1 alors image paysage
									//si $rapport<1 alors image portrait
									//si $rapport=1 alors image carrée
									
									//on genere une miniature en respectant l'homothétie
									$largeur_mini=60;
									$quality=80;
									redimage($chemin_b,$chemin_s,$largeur_mini,$largeur_mini/$rapport,$quality);
									
									//on met la jour la table sliders avec le chemin du fichier
									$requete2="UPDATE sliders 
												SET fichier_slider='" . $chemin_s . "' 
												WHERE id_slider='".$_GET['id_compte']."'";
									$resultat2=mysqli_query($connexion, $requete2);					
									}									
								}
							}
						else{
							$message="<label class=\"pas_ok\">Seules les extensions png, gif et jpg sont autorisées</label>";	
							$color['fichier_slider']="class=\"avertissement\" ";
							}					
						}
					$message="<label class=\"ok\">L'item du slider a été modifié</label>";
					
					//on se replace sur l'action afficher_comptes
					$action_form="afficher_sliders";
					
					//on suprime la variable $_GET['id_compte']
					//afin de ne pas executer le if(isset($_GET['id_slider'])) qui suit
					unset($_GET['id_slider']);
					
					//on vide tous les champs du formulaire
					foreach($_POST AS $cle => $valeur)
						{
						unset($_POST[$cle]);	
						}
					}					
				}
				
			if(isset($_GET['id_slider']))
				{
				$action_form="modifier_slider&id_slider=" . $_GET['id_slider'];
				
				//on récupere dans la table comptes les infos du id_compte recu depuis l'url (methode GET)	
				$requete="SELECT * FROM sliders WHERE id_slider='".$_GET['id_slider']."'";
				$resultat=mysqli_query($connexion,$requete);
				$ligne=mysqli_fetch_object($resultat);
				
				//on recharge le formulaire d'admin des comptes avec les données stockées dans la table
				$_POST['titre_slider']=$ligne->titre_slider;
				$_POST['descriptif_slider']=$ligne->descriptif_slider;
				}			
			
			break;
			
			case "supprimer_slider":
			if(isset($_GET['id_slider']))
				{
				if(isset($_GET['ext']))
					{
					$extension="&ext=" . $_GET['ext'];	
					}
				else{
					$extension="";
					}					
				$entete="<h1 class=\"ouinon\">Vous-voulez vraiment supprimer cet item du slider ? 
				<a href=\"admin.php?module=sliders&action=supprimer_slider&id_slider=".$_GET['id_slider']."&confirm=1" .$extension. "\">OUI</a>
				<a href=\"admin.php?module=sliders&action=afficher_sliders\">NON</a>
				</h1>";
				//si l'internaute à confirmer la suppression (bouton oui)
				if(isset($_GET['confirm']) && $_GET['confirm']==1)
					{
					$requete="DELETE FROM sliders WHERE id_slider='".$_GET['id_slider']."'";	
					$resultat=mysqli_query($connexion,$requete);
					if(isset($_GET['ext']))
						{
						//on calcule les chemins
						$chemin_a_supprimer_b="../medias/slider_b" . $_GET['id_slider'] . "." . $_GET['ext']; 
						$chemin_a_supprimer_s="../medias/slider_s" . $_GET['id_slider'] . "." . $_GET['ext']; 							
						
						//on supprime les fichiers
						unlink($chemin_a_supprimer_b);
						unlink($chemin_a_supprimer_s);
						}
					
					$entete="<h1 class=\"ok\">Item supprimé</h1>";
					//on réinitialise l'action du formulaire à afficher_comptes					
					$action_form="afficher_sliders";
					}
				}
			break;				
			}
		//on affiche les items du slider triés par rang_slider	
		$requete="SELECT * FROM sliders ORDER BY rang_slider";
		$tab_resultats=afficher_sliders($connexion,$requete,"back");
		}
	}
else{
	header("Location:../index.php");	
	}		
?>