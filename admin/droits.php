<?php

if(isset($_SESSION['id_compte']))
	{
	$entete="<h1>Gestion des droits</h1>";
	if(isset($_GET['id_droit']) && isset($_GET['statut']))
		{
		$requete="UPDATE droits SET " . $_GET['statut'];
		if($_GET['valeur']=='oui')
			{
			$requete.="='non'";	
			}
		else
			{
			$requete.="='oui'";		
			}
		$requete.=" WHERE id_droit='" . $_GET['id_droit'] . "'";
		}
	$resultat=mysqli_query($connexion,$requete);


	$requete2="SELECT d.*,m.* FROM droits d 
				INNER JOIN menus m 
				ON d.id_menu=m.id_menu 
				WHERE m.type_menu='back' 
				ORDER BY m.rang_menu";		
	$tab_resultats=afficher_droits($connexion,$requete2);	
	}
else{
	header("Location:../index.php");	
	}
	
?>