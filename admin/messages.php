<?php
if(isset($_SESSION['id_compte']))
	{
	$open=array();
	if(isset($_GET['action']))
		{
		switch($_GET['action'])
			{
			case "afficher_messages":
			$entete="<h1>Messagerie</h1>";
			unset($_SESSION['id_contact']);
			break;
			
			case "marquer_message":
			$entete="<h1>Messagerie</h1>";
			if(isset($_GET['id_contact']))
				{
				$requete="UPDATE contacts SET lu='1' 
							WHERE id_contact='".$_GET['id_contact']."'";
				$resultat=mysqli_query($connexion,$requete);
				//mémorise en variable de session la valeur du parametre id_contact
				$_SESSION['id_contact']=$_GET['id_contact'];
				}				
			break;
			
			case "supprimer_message":
			
			if(isset($_GET['id_contact']))
				{
				$entete="<h1 class=\"ouinon\">Vous-voulez vraiment supprimer ce message ? 
				<a href=\"admin.php?module=messages&action=supprimer_message&id_contact=".$_GET['id_contact']."&confirm=1\">OUI</a>
				<a href=\"admin.php?module=messages&action=afficher_message\">NON</a>
				</h1>";
				//si l'internaute à confirmer la suppression (bouton oui)
				if(isset($_GET['confirm']) && $_GET['confirm']==1)
					{
					$requete="DELETE FROM contacts WHERE id_contact='".$_GET['id_contact']."'";	
					$resultat=mysqli_query($connexion,$requete);
					$entete="<h1 class=\"ok\">Message supprimé</h1>";
					}
				}

			break;			
			}
		
		//on construit un tableau qui affiche tous
		//les messages reçus depuis le front
		$tab_resultats="<table class=\"tab_resultats\">\n";
		$requete="SELECT * FROM contacts ORDER BY date_contact DESC";
		$resultat=mysqli_query($connexion,$requete);
		//tant que dans la variable $resultat il y a des lignes 
		//je vais exploiter chaque champ de chaque ligne sous forme d'objets
		$i=1;
		while($ligne=mysqli_fetch_object($resultat))
			{
			//si le message n'a pas été lu
			if($ligne->lu==0)
				{
				$class="non_lu";	
				}
			else{
				$class="lu";			
				}
			if(isset($_SESSION['id_contact']) && $_SESSION['id_contact']==$ligne->id_contact)
				{
				$open=" open";	
				}
			else{
				$open="";
				}			
			//premiere ligne visible
			$tab_resultats.="<tr class=\"".$open."\">\n";	
			$tab_resultats.="<td class=\"".$class."\"><a href=\"admin.php?module=messages&action=marquer_message&id_contact=".$ligne->id_contact."\">".$ligne->nom_contact." ".$ligne->prenom_contact."</a></td>\n";
			$tab_resultats.="<td>".$ligne->date_contact."</td>\n";
			$tab_resultats.="<td>
			<a href=\"admin.php?module=messages&action=supprimer_message&id_contact=".$ligne->id_contact."\">
			<span class=\"dashicons dashicons-no-alt\"></span>
			</a></td>\n";
			$tab_resultats.="</tr>\n";

			//deuxieme ligne visible si clic
			$tab_resultats.="<tr>\n";

			$tab_resultats.="<td class=\"".$open."\" colspan=\"3\">
							<strong>Expediteur : </strong>" . $ligne->mel_contact . "<br />";
			$tab_resultats.="<strong>Message</strong>
							<br />" . $ligne->message_contact . "</td>\n";
			$tab_resultats.="</tr>\n";
			$i++;
			}
		$tab_resultats.="</table>\n";		
		}	
	}
else{
	header("Location:../index.php");	
	}
?>