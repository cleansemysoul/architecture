<?php
session_start();
//si la personne est autorisée à acceder au back
if(isset($_SESSION['id_compte']))
	{
	//on calcule une phrase de bienvenue
	$bienvenue=$_SESSION['fichier_compte']." " . $_SESSION['prenom_compte'] . " " . substr($_SESSION['nom_compte'],0,1) . " [Statut:" . $_SESSION['statut_compte'] . "]";
		
	//je connecte la librairie de fonctions php
	require_once("../outils/fonctions.php");
	//je stocke dans une variable ($connexion)
	//le résultat de la fonction connexion()
	$connexion=connexion();
	
	//on calcul le menu dynamiquement coté back	
	$requete="SELECT d.*,m.* FROM droits d 
			INNER JOIN menus m ON d.id_menu=m.id_menu 
			WHERE d." . $_SESSION['statut_compte'] . "='oui' 
			AND m.type_menu='back' 
			ORDER BY m.rang_menu";	

	$cas="menu_back";
	$menu_back=afficher_menus($connexion,$requete,$cas);

	//si admin.php reçoit le parametre action (si un client a cliqué sur un bouton)
	if(isset($_GET['module']))
		{
		$contenu="form_" . $_GET['module'] . ".html";	
		switch($_GET['module'])
			{
			case "deconnecter":
			//permet de détruire l'ensemble des variables de session
			session_destroy();
			header("Location:../log");
			break;	
			
			case "config":
			include_once("config.php");
			break;

			case "droits":
			include_once("droits.php");
			break;
						
			case "menus":
			include_once("menus.php");
			break;	
			
			case "comptes":
			include_once("comptes.php");
			break;	
			
			case "articles":
			include_once("articles.php");
			break;	
			
			case "pages":
			include_once("pages.php");
			break;			
			
			case "sliders":
			include_once("sliders.php");
			break;

			case "messages":
			include_once("messages.php");
			break;		
			}	
		}
	else//personne n'a cliqué sur un bouton ( à l'arrivée sur le tableau de bord)
		{
		$contenu="intro.html";
		}
		
	mysqli_close($connexion);
	include("admin.html");
	}
else
	{
	header("Location:../index.php");
	}
?>