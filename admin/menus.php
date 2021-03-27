<?php
if(isset($_SESSION['id_compte']))
	{
	if(isset($_GET['action']))
		{
		switch($_GET['action'])
			{
			case "afficher_menus":
			$entete="<h1>Gestion des menus</h1>";
			$action_form="afficher_menus";
			//2. on insert les champs dans la table comptes (modele : front.php)
			if(isset($_POST['submit']))
				{	
				//on gere la liste déroulante
				if(!empty($_POST['type_menu']))
					{
					$selected[$_POST['type_menu']]= "selected=\"selected\"";
					}			
				if(empty($_POST['intitule_menu']))
					{
					$message="<label class=\"pas_ok\">Mets un intitulé</label>";	
					$color['intitule_menu']="class=\"avertissement\" ";						
					}						
				elseif(empty($_POST['type_menu']))
					{
					$message="<label class=\"pas_ok\">Mets un type au menu</label>";	
					$color['type_menu']="class=\"avertissement\" ";						
					}
				else{
					//on calcule le rang a attribuer au nouvel item
					$requete="SELECT id_menu FROM menus";
					$resultat=mysqli_query($connexion,$requete);
					
					//on compte le nombre de lignes trouvé par la requete
					$nb=mysqli_num_rows($resultat);
					$nouveau_rang=$nb+1;					
					$requete="INSERT INTO menus SET intitule_menu='".addslashes($_POST['intitule_menu'])."',
													  lien_menu='".addslashes($_POST['lien_menu'])."',
													  rang_menu='".$nouveau_rang."',
													  type_menu='".$_POST['type_menu']."'";
					$resultat=mysqli_query($connexion,$requete);
					$dernier_id_menu=mysqli_insert_id($connexion);
					
					//on met à jour la table des droits
					$requete2="INSERT INTO droits SET id_menu='" . $dernier_id_menu . "'";
					$resultat2=mysqli_query($connexion,$requete2);
					
					$message="<label class=\"ok\">Nouvel item créé</label>";
					
					//on vide tous les champs du formulaire
					foreach($_POST AS $cle => $valeur)
						{
						unset($_POST[$cle]);	
						}					
					}
				}
			break;
			
			case "modifier_menu":
			
			//si qq valide le formulaire (appui sur le bouton ENVOYER)
			if(isset($_POST['submit']))
				{
				$requete="UPDATE menus SET intitule_menu='".addslashes($_POST['intitule_menu'])."',
										 lien_menu='".addslashes($_POST['lien_menu'])."',
										 type_menu='".addslashes($_POST['type_menu'])."'   
										 WHERE id_menu='".$_GET['id_menu']."'";
									 
				$resultat=mysqli_query($connexion,$requete);
				$message="<label class=\"ok\">L'item a été modifié</label>";
				
				//on se replace sur l'action afficher_comptes
				$action_form="afficher_menus";
				
				//on suprime la variable $_GET['id_menu']
				//afin de ne pas executer le if(isset($_GET['id_menu'])) qui suit
				unset($_GET['id_menu']);
				
				//on vide tous les champs du formulaire
				foreach($_POST AS $cle => $valeur)
					{
					unset($_POST[$cle]);	
					}		
				}
				
			if(isset($_GET['id_menu']))
				{
				$action_form="modifier_menu&id_menu=" . $_GET['id_menu'];
				
				//on récupere dans la table menus les infos du id_menu recu depuis l'url (methode GET)	
				$requete="SELECT * FROM menus WHERE id_menu='".$_GET['id_menu']."'";
				$resultat=mysqli_query($connexion,$requete);
				$ligne=mysqli_fetch_object($resultat);
				
				//on gere la récupération de la valeur "type_menu" dans la liste déoulante
				$selected[$ligne->type_menu]=" selected=\"selected\"";
				
				//on recharge les champs du formulaire avec les données stockées dans la table
				$_POST['intitule_menu']=$ligne->intitule_menu;
				$_POST['lien_menu']=$ligne->lien_menu;
				}			
			
			break;
			
			case "supprimer_menu":
			if(isset($_GET['id_menu']))
				{
				$entete="<h1 class=\"ouinon\">Vous-voulez vraiment supprimer cet item ? 
				<a href=\"admin.php?module=menus&action=supprimer_menu&id_menu=".$_GET['id_menu']."&confirm=1\">OUI</a>
				<a href=\"admin.php?module=menus&action=afficher_menus\">NON</a>
				</h1>";
				//si l'internaute à confirmer la suppression (bouton oui)
				if(isset($_GET['confirm']) && $_GET['confirm']==1)
					{
					$requete2="DELETE FROM menus WHERE id_menu='".$_GET['id_menu']."'";	
					$resultat2=mysqli_query($connexion,$requete2);
					
					$requete3="SELECT * FROM menus ORDER BY rang_menu";
					$resultat3=mysqli_query($connexion, $requete3);
					$i=1;
					while($ligne3=mysqli_fetch_object($resultat3))
						{
						$requete4="UPDATE menus SET rang_menu='" . $i . "' WHERE id_menu='" . $ligne3->id_menu . "'";
						$resultat4=mysqli_query($connexion, $requete4);
						$i++;
						}						
					$entete="<h1 class=\"ok\">Item supprimé</h1>";						
					}
				}
			break;

			case "trier_menu":
			
			if(isset($_GET['id_menu']) && isset($_GET['tri']) && isset($_GET['type_menu']))
				{
				//1. On vérifie quel était le rang du id_menu à trier
				$requete="SELECT * FROM menus WHERE id_menu='" . $_GET['id_menu'] . "'";
				$resultat=mysqli_query($connexion, $requete);
				$ligne=mysqli_fetch_object($resultat);
				
				switch($_GET['tri'])	
					{
					case "up":

					if($ligne->rang_menu>1)
						{
						//on calcul le nouveau rang	
						$nouveau_rang=$ligne->rang_menu-1;
							
						//2. On modifie le rang de la ligne qui a déja le $nouveau_rang
						$inversion_rang=$nouveau_rang+1;
						$requete2="UPDATE menus SET rang_menu='" . $inversion_rang . "' WHERE type_menu='" . $ligne->type_menu . "' AND rang_menu='" . $nouveau_rang . "'";
						$resultat2=mysqli_query($connexion, $requete2);		

						//3. On attribue le nouveau rang au id_menu concerné
						$requete3="UPDATE menus SET rang_menu='" . $nouveau_rang . "' WHERE id_menu='" . $_GET['id_menu'] . "'";
						$resultat3=mysqli_query($connexion, $requete3);										
						}
						
					break;
					
					case "down":

					//2. On compte le nombre de lignes de la table
					$requete2="SELECT id_menu FROM menus WHERE type_menu='" . $_GET['type_menu'] . "'";
					$resultat2=mysqli_query($connexion, $requete2);
					$nb_lignes=mysqli_num_rows($resultat2);							

					// si le rang de l'item à modifier est inférieur au nombre de lignes de la table
					if($ligne->rang_menu<$nb_lignes)
						{
						//on calcul le nouveau rang	
						$nouveau_rang=$ligne->rang_menu+1;
							
						//3. On modifie le rang de la ligne qui a déja le $nouveau_rang
						$inversion_rang=$nouveau_rang-1;
						$requete3="UPDATE menus SET rang_menu='" . $inversion_rang . "' WHERE type_menu='" . $ligne->type_menu . "' AND rang_menu='" . $nouveau_rang . "'";
						$resultat3=mysqli_query($connexion, $requete3);		

						//4. On attribue le nouveau rang au id_menu concerné
						$requete4="UPDATE menus SET rang_menu='" . $nouveau_rang . "' WHERE id_menu='" . $_GET['id_menu'] . "'";
						$resultat4=mysqli_query($connexion, $requete4);	
						}									
					
					break;
					}
				}
			break;
			}
			
		$requete="SELECT * FROM menus ORDER BY type_menu, rang_menu";
		$cas="back";
		$tab_resultats=afficher_menus($connexion,$requete,$cas);
		}
	}
else{
	header("Location:../index.php");	
	}		
?>