<?php
//===============================
// la fonction connecter() permet de choisir une
// base de données et de s'y connecter.

function connexion()
	{
	require_once("connect.php");
	//si numéro de port
	//$connexion = mysqli_connect(SERVEUR,LOGIN,PASSE,BASE,PORT) or die("Error " . mysqli_error($connexion));
	//si pas de numéro de port	
	$connexion = mysqli_connect(SERVEUR,LOGIN,PASSE,BASE) or die("Error " . mysqli_error($connexion));
	
	return $connexion;
	}


//================================================
function protocole()
	{
	if(isset($_SERVER['HTTPS']))
		{
		$protocole="https://";	
		}
	else
		{
		$protocole="http://";	
		}
	//$protocole="http://";
	return $protocole;	
	}
	
//================================
function security($chaine){
	$connexion=connexion();
	$security=addcslashes(mysqli_real_escape_string($connexion,$chaine), "%_");
	mysqli_close($connexion);
	return $security;
}

//===========================pour se loguer=======================================================
function login($login,$password)
{	
	$connexion=connexion();
	$login=security($login);
	$password=security($password);

	$requete="SELECT * FROM comptes WHERE login_compte= '" . $login . "' AND pass_compte=SHA1('" . $password . "')";
	$resultat=mysqli_query($connexion, $requete);
	$nb=mysqli_num_rows($resultat);
	
	if($nb==0)
		{
		return false;
		}
	else
		{ 
		$ligne=mysqli_fetch_object($resultat);
		
		//on stocke en mémoire de session les infos que l'on souhaite afficher sur l'accueil du back
		$_SESSION['id_compte']=$ligne->id_compte;
		$_SESSION['prenom_compte']=$ligne->prenom_compte;    
		$_SESSION['nom_compte']=$ligne->nom_compte;
		$_SESSION['statut_compte']=$ligne->statut_compte;
		if(!empty($ligne->fichier_compte))
			{
			$_SESSION['fichier_compte']="<img src=\"" . $ligne->fichier_compte . "\" alt=\"\" />";
			}
		else{
			$_SESSION['fichier_compte']="<span class=\"dashicons dashicons-admin-users\"></span>";
			}
		header("Location:../admin/admin.php");    
		return true;
		}		
	mysqli_close($connexion); 	
}


// ====détecter l'extension du fichier================
function fichier_type($uploadedFile)
{
$tabType = explode(".", $uploadedFile);
$nb=sizeof($tabType)-1;
$typeFichier=$tabType[$nb];
 if($typeFichier == "jpeg")
   {
   $typeFichier = "jpg";
   }
$extension=strtolower($typeFichier);
return $extension;
}


//============================================
function redimage($img_src,$img_dest,$dst_w,$dst_h,$quality)
{
if(!isset($quality))
	{
	$quality=100;
	}
   $extension=fichier_type($img_src);

   // Lit les dimensions de l'image
   $size = @GetImageSize($img_src);
   $src_w = $size[0];
   $src_h = $size[1];
   // Crée une image vierge aux bonnes dimensions   truecolor
   $dst_im = @ImageCreatetruecolor($dst_w,$dst_h);
   imagealphablending($dst_im, false);
   imagesavealpha($dst_im, true);      
    
   // Copie dedans l'image initiale redimensionnée  
   
   if($extension=="jpg")
     {
     $src_im = @ImageCreateFromJpeg($img_src);
     imagecopyresampled($dst_im,$src_im,0,0,0,0,$dst_w,$dst_h,$src_w,$src_h);
    
     // Sauve la nouvelle image
     @ImageJpeg($dst_im,$img_dest,$quality);     
     }
   if($extension=="png")
     {
     $src_im = @ImageCreateFromPng($img_src);    
     imagecopyresampled($dst_im,$src_im,0,0,0,0,$dst_w,$dst_h,$src_w,$src_h);     
     
     // Sauve la nouvelle image
     @ImagePng($dst_im,$img_dest,0);     
     }     
   if($extension=="gif")
     {
     $src_im = @ImageCreateFromGif($img_src);
     imagecopyresampled($dst_im,$src_im,0,0,0,0,$dst_w,$dst_h,$src_w,$src_h);
     
     // Sauve la nouvelle image
     @ImagePng($dst_im,$img_dest,0);     
     }

   // Détruis les tampons
   @ImageDestroy($dst_im);
   @ImageDestroy($src_im);
}

//===============================
function format_date($date,$format)
{
if($format=="anglais")
   {
	$tab_date=explode("/",$date);
	$date_au_format=$tab_date[2] . "-" . $tab_date[1] . "-" . $tab_date[0];	
	 }
if($format=="francais")
   {
	$tab_date=explode("-",$date);
	$date_au_format=$tab_date[2] . "/" . $tab_date[1] . "/" . $tab_date[0];	
	 }
return $date_au_format;	
}

//===============================================

 function envoi_mel($destinataire,$sujet,$message_txt, $message_html,$expediteur)
  {
  if (!preg_match("#^[a-z0-9._-]+@(hotmail|live|msn).[a-z]{2,4}$#", $destinataire)) // On filtre les serveurs qui rencontrent des bogues.
    {
  	$passage_ligne = "\r\n";
    }
  else
    {
  	$passage_ligne = "\n";
    }
   
  //=====Création de la boundary
  $boundary = "-----=" . md5(rand());
  //==========
   
  //=====Création du header de l'email
  $header = "From: \"" . $_SESSION['expediteur'] . "\"<" . $expediteur . ">" . $passage_ligne;
  $header.= "Reply-to: \"" . $_SESSION['expediteur'] . "\" <" . $expediteur . ">" . $passage_ligne;
  $header.= "MIME-Version: 1.0" . $passage_ligne;
  $header.= "X-Priority: 3" . $passage_ligne;//1 : max et 5 : min
  $header.= "Content-Type: multipart/alternative;" . $passage_ligne . " boundary=\"" . $boundary . "\"" . $passage_ligne;
  //==========
   
  //=====Création du message
  $message = $passage_ligne . "--" . $boundary. $passage_ligne;
  //=====Ajout du message au format texte
  $message.= "Content-Type: text/plain; charset=\"UTF-8\"" . $passage_ligne;
  $message.= "Content-Transfer-Encoding: 8bit" . $passage_ligne;
  $message.= $passage_ligne . $message_txt . $passage_ligne;
  //==========
  $message.= $passage_ligne . "--" . $boundary . $passage_ligne;
  //=====Ajout du message au format HTML
  $message.= "Content-Type: text/html; charset=\"UTF-8\"" . $passage_ligne;
  $message.= "Content-Transfer-Encoding: 8bit" . $passage_ligne;
  $message.= $passage_ligne . $message_html . $passage_ligne;
  //==========
  $message.= $passage_ligne . "--" . $boundary."--" . $passage_ligne;
  $message.= $passage_ligne . "--" . $boundary."--" . $passage_ligne;
  //==========
   
  //=====Envoi de l'email
  mail($destinataire,$sujet,$message,$header);  
  }    
  
//=======================================
function afficher_contacts($connexion,$requete)
	{
	$resultat=mysqli_query($connexion,$requete);
	$i=0;
	$affichage="<table class=\"tab_resultats\">\n";
	$affichage.="<tr>\n";
	$affichage.="<th>Identité</th>\n";
	$affichage.="<th>Email</th>\n";
	$affichage.="<th>Date</th>\n";	
	$affichage.="<th>Action</th>\n";
	$affichage.="</tr>\n";		
	while($ligne=mysqli_fetch_object($resultat))
		{
		$affichage.="<tr>\n";
		$affichage.="<td>" . strtoupper($ligne->nom_contact) . " " . $ligne->prenom_contact . "</td>\n";
		$affichage.="<td>" . $ligne->mel_contact . "</td>\n";	
		$affichage.="<td>" . $ligne->date_contact . "</td>\n";	
		$affichage.="<td><a href=\"admin.php?action=contact&choix=supprimer&id_contact=" . $ligne->id_contact . "\"><span class=\"dashicons dashicons-trash\"></span></a></td>\n";						
		$affichage.="</tr>\n";
		$i++;					
		}
	$_SESSION['nb_contacts']=$i;
	$affichage.="</table>\n";

	return $affichage;
	}
//=======================================
function afficher_comptes($connexion,$requete)
	{
	$resultat=mysqli_query($connexion,$requete);
	$i=0;
	$affichage="<table class=\"tab_resultats\">\n";
	//on calcule les entêtes des colonnes
	$affichage.="<tr>\n";
	$affichage.="<th>Identité</th>\n";
	$affichage.="<th>Login</th>\n";
	$affichage.="<th>Statut</th>\n";
	$affichage.="<th>Avatar</th>\n";	
	$affichage.="<th>Actions</th>\n";
	$affichage.="</tr>\n";	
	while($ligne=mysqli_fetch_object($resultat))
		{
		//on affiche le contenu de chaque uplet présent dans la table
		$affichage.="<tr>\n";
		$affichage.="<td>" . strtoupper($ligne->nom_compte) . " " . $ligne->prenom_compte . "</td>\n";
		$affichage.="<td style=\"text-align:center\">" . $ligne->login_compte . "</td>\n";	
		$affichage.="<td style=\"text-align:center\">" . $ligne->statut_compte . "</td>\n";	
		if(!empty($ligne->fichier_compte))
			{
			//on recupère l'extension du fichier pour calculer un parametre GET
			$extension="&ext=" . fichier_type($ligne->fichier_compte);
			$avatar="<img class=\"miniature\" src=\"" . $ligne->fichier_compte  . "\" alt=\"\" />";
			}
		else
			{
			$extension="";
			$avatar="<span class=\"dashicons dashicons-admin-users\"></span>";	
			}
		$affichage.="<td style=\"text-align:center\">" . $avatar . "</td>\n";		
		$affichage.="<td>";
		$affichage.="<a href=\"admin.php?module=comptes&action=modifier_compte&id_compte=" . $ligne->id_compte . "\"><span class=\"dashicons dashicons-edit\"></span></a>";
		$affichage.="&nbsp;&nbsp;&nbsp;";
		$affichage.="<a href=\"admin.php?module=comptes&action=supprimer_compte&statut_compte=" . $ligne->statut_compte . "&id_compte=" . $ligne->id_compte . $extension."\"><span class=\"dashicons dashicons-no-alt\"></span></a>";
		$affichage.="</td>\n";						
		$affichage.="</tr>\n";
		$i++;					
		}
	$affichage.="</table>\n";

	return $affichage;
	}
//======================================
function extrait($texte,$nb_mots,$tolerance)	
	{
	//on coupe le texte sur les espaces
	$tab_mots=explode(" ",$texte);
	
	//on compte le nombre de valeurs dans le tableau de variables $tab_mots
	$nb_mots_dans_texte=count($tab_mots);
	
	//si le nb de valeur est inférieur ou égal à $nb_mots
	if($nb_mots_dans_texte<=($nb_mots+$tolerance))
		{
		$extrait=$texte;	
		}
	else//alors il faut raccourcir le texte et fgarder seulement les $nb_mots premiers mots
		{
		//on fait une boucle qui tourne $nb_mots fois	
		$extrait="";
		for($i=0;$i<$nb_mots;$i++)
			{
			//au premier tour de boucle
			if($i==0)
				{
				$extrait.=$tab_mots[$i];	
				}
			else
				{
				$extrait.=" " . $tab_mots[$i];
				}
			}
		$extrait.="...";
		}
	return $extrait;
	}
	
//=======================================
function afficher_articles($connexion,$requete,$cas)
	{
	$resultat=mysqli_query($connexion,$requete);
	//on calcule $nb pour détecter par la suite le dernier tour
	$nb=mysqli_num_rows($resultat);
	if(isset($cas))
		{
		switch($cas)
			{
			case "back":

			$i=0;
			$affichage="<div id=\"affichage\">\n";
			//on calcule les entêtes des colonnes

			$tab_compte=array();
			while($ligne=mysqli_fetch_object($resultat))
				{
				//on a besoin de stocker la valeur de id_compte à chaque tour de boucle
				$tab_compte[$i]=$ligne->id_compte;
				
				if($i==0 || ($i>0 && $tab_compte[$i]!=$tab_compte[$i-1]))
					{
					if($i>0)
						{
						$affichage.="</table>\n";//fermeture de la <table class=\"tab_resultats\">	
						$affichage.="</div>\n";//fermeture de la <div class="cat">	
						}	
					if($i<$nb)//tant que le dernier tour n'est pas atteint
						{
						$affichage.="<div class=\"cat\">\n";
						$affichage.="<label for=\"compte" . $ligne->id_compte . "\">" . $ligne->prenom_compte . " " . $ligne->nom_compte . "</label>\n";
						$affichage.="<input id=\"compte" . $ligne->id_compte . "\" type=\"checkbox\" name=\"compte\" />\n";
						$affichage.="<table class=\"tab_resultats\" cellspacing=\"0\">\n"; 
						$affichage.="<tr><th>Tri</th>\n<th>Titre</th>\n<th>Date</th>\n<th>Image</th>\n<th>Flux RSS</th>\n<th>Actions</th>\n</tr>\n";
						}			
					}
				
					
				//on affiche le contenu de chaque uplet présent dans la table
				$affichage.="<tr>\n";
				$affichage.="<td><a href=\"admin.php?module=articles&action=trier_article&id_article=" . $ligne->id_article . "&tri=up\"><span class=\"dashicons dashicons-arrow-up\"></span></a>&nbsp;&nbsp;<a href=\"admin.php?module=articles&action=trier_article&id_article=" . $ligne->id_article . "&tri=down\"><span class=\"dashicons dashicons-arrow-down\"></span></a></td>\n";	
				$affichage.="<td><a href=\"#\" title=\"" . extrait($ligne->contenu_article,8,4) . "\">" . $ligne->titre_article . "</a></td>\n";
				$affichage.="<td>" . $ligne->date_article . "</td>\n";	
				if(empty($ligne->fichier_article))
					{
					$affichage.="<td class=\"td_img\">pas d'image</td>";
					}
				else
					{
					$affichage.="<td class=\"td_img\">
					<img class=\"miniature\" src=\"" . str_replace("_b","_s",$ligne->fichier_article) . "\" alt=\"\" />
					<a class=\"suppr_img\" href=\"admin.php?module=articles&action=supprimer_image&id_article=". $ligne->id_article ."\">
					<span class=\"dashicons dashicons-no-alt\"></span></a>
					</td>\n";		
					}
				if($ligne->flux_article==1)
					{
					$flux_rss="<span class=\"dashicons dashicons-yes\"></span>";
					}
				else{
					$flux_rss="";
					}
				$affichage.="<td>" . $flux_rss . "</td>";		
				$affichage.="<td>";		
				$affichage.="<a href=\"admin.php?module=articles&action=modifier_article&id_article=" . $ligne->id_article . "\"><span class=\"dashicons dashicons-edit\"></span></a>";
				$affichage.="&nbsp;&nbsp;&nbsp;";
				$affichage.="<a href=\"admin.php?module=articles&action=supprimer_article&id_article=" . $ligne->id_article . "\"><span class=\"dashicons dashicons-no-alt\"></span></a>";
				$affichage.="</td>\n";						
				$affichage.="</tr>\n";
				$i++;					
				}
			$affichage.="</table>\n";//fermeture de la <table class=\"tab_resultats\">	
			$affichage.="</div>\n";//fermeture de la <div id=\"affichage\">
			break;

			case "front":
			
			$affichage="";
			$nom_mois=array("Jan","Fev","Mar","Avr","Mai","Juin","Juil","Aou","Sept","Oct","Nov","Dec");
			$i=0;
			while($ligne=mysqli_fetch_object($resultat))
				{				
				//calcul de la date en 3 morceaux
				$tab_date=explode("-",$ligne->date_article);
				$annee=$tab_date[0];
				$mois=$nom_mois[$tab_date[1]-1];
				$jour=$tab_date[2];

				$affichage.="<article>\n";
				$affichage.="<div class=\"date\">\n";
				$affichage.="<span class=\"jj \">" . $jour . "</span>\n";
				$affichage.="<span class=\"mm\">" . $mois . "</span>\n"; 
				$affichage.="<span class=\"aaaa\">" . $annee . "</span>\n";								
				$affichage.="</div>\n";
				if(!empty($ligne->fichier_article))
					{
					$affichage.="<figure><img src=\"". str_replace("_b","_s",$ligne->fichier_article) . "\" alt=\"" . $ligne->titre_article . "\" /></figure>\n";
					}
				$affichage.="<div class=\"text\">";	
				$affichage.="<h2>" . $ligne->titre_article . "</h2>\n";
				$affichage.="<p>" . $ligne->contenu_article . "</p>\n";
				$affichage.="</div>";
				$affichage.="</article>\n";
				$i++;				
				}				
			break;

			case "home":
			
			$affichage="";
			$i=0;
			while($ligne=mysqli_fetch_object($resultat))
				{
				if($i==0)
					{
					$affichage.="<article class=\"a_la_une\">\n";
					if(!empty($ligne->fichier_article))
						{
						$affichage.="<img src=\"" . $ligne->fichier_article . "\" alt=\"" . $ligne->titre_article . "\" />";
						}						
					}
				else{
					if($i==1)
						{
						$affichage.="<div>\n";		
						}
					$affichage.="<article>\n";
					}
				$affichage.="<h2>" . $ligne->titre_article . "</h2>\n";
				$affichage.="<p>" . extrait($ligne->contenu_article,20,5) . "</p>\n";
				$affichage.="<a href=\"front.php?page=single&id_article=" . $ligne->id_article . "\">voir la suite</a>\n";
				$affichage.="</article>\n";					
				$i++;
				}					
			$affichage.="</div>\n";
			break;

			case "single":
			
			//pas de boucle car on attend forcement un seul article à la fois
			$ligne=mysqli_fetch_object($resultat);
			$affichage="<h1>" . $ligne->titre_article . "</h1>\n";
			$affichage.="<article>\n";
			$affichage.="<p class=\"date_single\">PUBLIÉE LE " . $ligne->date_article . "</p>\n";
			if(!empty($ligne->fichier_article))
				{
				$affichage.="<img src=\"" . $ligne->fichier_article . "\" alt=\"" . $ligne->titre_article . "\" />";
				}						
			$affichage.="<p>" . $ligne->contenu_article . "</p>\n";
			$affichage.="</article>\n";					

			break;			
			}		
		}

	return $affichage;
	}
	
//=======================================
function afficher_pages($connexion,$requete,$cas)
	{
	$resultat=mysqli_query($connexion,$requete);
	//on calcule $nb pour détecter par la suite le dernier tour
	$nb=mysqli_num_rows($resultat);
	if(isset($cas))
		{
		switch($cas)
			{
			case "back":

			$i=0;
			$affichage="<div id=\"affichage\">\n";
			//on calcule les entêtes des colonnes

			$tab_compte=array();
			while($ligne=mysqli_fetch_object($resultat))
				{
				//on a besoin de stocker la valeur de id_compte à chaque tour de boucle
				$tab_compte[$i]=$ligne->id_compte;
				
				if($i==0 || ($i>0 && $tab_compte[$i]!=$tab_compte[$i-1]))
					{
					if($i>0)
						{
						$affichage.="</table>\n";//fermeture de la <table class=\"tab_resultats\">	
						$affichage.="</div>\n";//fermeture de la <div class="cat">	
						}	
					if($i<$nb)//tant que le dernier tour n'est pas atteint
						{
						$affichage.="<div class=\"cat\">\n";
						$affichage.="<label for=\"compte" . $ligne->id_compte . "\">" . $ligne->prenom_compte . " " . $ligne->nom_compte . "</label>\n";
						$affichage.="<input id=\"compte" . $ligne->id_compte . "\" type=\"checkbox\" name=\"compte\" />\n";
						$affichage.="<table class=\"tab_resultats\" cellspacing=\"0\">\n"; 
						$affichage.="<tr>\n<th>Menu</th>\n<th>Titre</th>\n<th>Date</th>\n<th>Image</th>\n<th>Actions</th>\n</tr>\n";
						}			
					}

				//on affiche le contenu de chaque uplet présent dans la table
				$affichage.="<tr>\n";
				$affichage.="<td>" . $ligne->intitule_menu . "</td>\n";				
				$affichage.="<td><a href=\"#\" title=\"" . extrait($ligne->contenu_page,8,4) . "\">" . $ligne->titre_page . "</a></td>\n";
				$affichage.="<td>" . $ligne->date_page . "</td>\n";	
				if(empty($ligne->fichier_page))
					{
					$affichage.="<td class=\"td_img\">pas d'image</td>";
					}
				else
					{
					$affichage.="<td class=\"td_img\">
					<img class=\"miniature\" src=\"" . str_replace("_b","_s",$ligne->fichier_page) . "\" alt=\"\" />
					<a class=\"suppr_img\" href=\"admin.php?module=pages&action=supprimer_image&id_page=". $ligne->id_page ."\">
					<span class=\"dashicons dashicons-no-alt\"></span></a>
					</td>\n";		
					}
				$affichage.="<td>";		
				$affichage.="<a href=\"admin.php?module=pages&action=modifier_page&id_page=" . $ligne->id_page . "\"><span class=\"dashicons dashicons-edit\"></span></a>";
				$affichage.="&nbsp;&nbsp;&nbsp;";
				$affichage.="<a href=\"admin.php?module=pages&action=supprimer_page&id_page=" . $ligne->id_page . "\"><span class=\"dashicons dashicons-no-alt\"></span></a>";
				$affichage.="</td>\n";						
				$affichage.="</tr>\n";
				$i++;					
				}
			$affichage.="</table>\n";//fermeture de la <table class=\"tab_resultats\">	
			$affichage.="</div>\n";//fermeture de la <div id=\"affichage\">
			break;

			case "front":

			$ligne=mysqli_fetch_object($resultat);
			$affichage="<article id=\"page-" . $ligne->id_page . "\">\n";
			$affichage.="<h1>" . $ligne->titre_page . "</h1>\n";
			if(!empty($ligne->fichier_page))
				{
				$affichage.="<figure><img src=\"" . $ligne->fichier_page . "\" alt=\"" . $ligne->titre_page . "\" /></figure>\n";	
				}
			$affichage.="<div>" . $ligne->contenu_page . "</div>";
			$affichage.="</article>\n";
			break;		
			}		
		}

	return $affichage;
	}
	
//=======================================
function afficher_menus($connexion,$requete, $cas)
	{
	$resultat=mysqli_query($connexion,$requete);
	$nb=mysqli_num_rows($resultat);
	
	if(isset($cas))
		{
		switch($cas)
			{
			case "back":

			$i=0;
			$affichage="<div id=\"affichage\">\n";
			//on calcule les entêtes des colonnes

			$tab_menu=array();

			while($ligne=mysqli_fetch_object($resultat))
				{
				//on a besoin de stocker la valeur de id_compte à chaque tour de boucle
				$tab_menu[$i]=$ligne->type_menu;
				
				if($i==0 || ($i>0 && $tab_menu[$i]!=$tab_menu[$i-1]))
					{
					if($i>0)
						{
						$affichage.="</table>\n";//fermeture de la <table class=\"tab_resultats\">	
						$affichage.="</div>\n";//fermeture de la <div class="cat">	
						}	
					if($i<$nb)//tant que le dernier tour n'est pas atteint
						{
						$affichage.="<div class=\"cat\">\n";
						$affichage.="<label for=\"menu" . $ligne->id_menu . "\">" . $ligne->type_menu . "</label>\n";
						$affichage.="<input id=\"menu" . $ligne->id_menu . "\" type=\"checkbox\" name=\"menu\" />\n";
						$affichage.="<table class=\"tab_resultats\" cellspacing=\"0\">\n"; 
						$affichage.="<tr><th>Tri</th>\n<th>Intitulé</th>\n<th>Actions</th>\n</tr>\n";
						}			
					}					
				//on affiche le contenu de chaque uplet présent dans la table
				$affichage.="<tr>\n";
				$affichage.="<td><a href=\"admin.php?module=menus&action=trier_menu&id_menu=" . $ligne->id_menu . "&type_menu=" . $ligne->type_menu . "&tri=up\"><span class=\"dashicons dashicons-arrow-up\"></span></a>&nbsp;&nbsp;<a href=\"admin.php?module=menus&action=trier_menu&id_menu=" . $ligne->id_menu . "&type_menu=" . $ligne->type_menu . "&tri=down\"><span class=\"dashicons dashicons-arrow-down\"></span></a></td>\n";				
				$affichage.="<td><a href=\"" . $ligne->lien_menu . "\" target=\"_blank\">" . $ligne->intitule_menu . "</a></td>\n";			
				$affichage.="<td>";		
				$affichage.="<a href=\"admin.php?module=menus&action=modifier_menu&id_menu=" . $ligne->id_menu . "\"><span class=\"dashicons dashicons-edit\"></span></a>";
				$affichage.="&nbsp;&nbsp;&nbsp;";
				$affichage.="<a href=\"admin.php?module=menus&action=supprimer_menu&id_menu=" . $ligne->id_menu . "\"><span class=\"dashicons dashicons-trash\"></span></a>";
				$affichage.="</td>\n";						
				$affichage.="</tr>\n";						

				$i++;					
				}
			$affichage.="</table>\n";//fermeture de la <table class=\"tab_resultats\">	
			$affichage.="</div>\n";//fermeture de la <div id=\"affichage\">	
			
			break;	
			
			case "menu_back":	
			
			//on calcule les notifications des nouveaux messages
			$requete2="SELECT lu FROM contacts WHERE lu=0";
			$resultat2=mysqli_query($connexion,$requete2);
			$nb_lignes=mysqli_num_rows($resultat2);
			if($nb_lignes>0)
				{
				$notification=" <span class=\"notif\">".$nb_lignes."</span>";		
				}			
			$affichage="<nav id=\"menu_back\">\n<ul>\n";
			while($ligne=mysqli_fetch_object($resultat))
				{
				//gestion de la rour dentée
				if($ligne->lien_menu!="admin.php?module=config")
					{
					$affichage.="<li>\n";	
					if($nb_lignes>0 && $ligne->lien_menu=="admin.php?module=messages&action=afficher_messages")	
						{
						$affichage.="<a href=\"" . $ligne->lien_menu ."\">" . $ligne->intitule_menu . $notification . "</a>";											
						}
					else{
						$affichage.="<a href=\"" . $ligne->lien_menu ."\">" . $ligne->intitule_menu . "</a>";																	
						}
					$affichage.="</li>\n";	
					
					//on supprime la mémorisation de la rour dentée
					unset($_SESSION['parametres']);					
					}
				else{
					$_SESSION['parametres']="<a href=\"" . $ligne->lien_menu . "\"><span class=\"dashicons dashicons-admin-generic\"></span></a>";	
					}				
				}
			$affichage.="</ul>\n</nav\n";
			break;
			
			case "front":	
			$affichage="<nav id=\"menu_haut\">\n<ul>\n";
			while($ligne=mysqli_fetch_object($resultat))
				{
				//on regarde si il existe des pages associée à cet item de menu
				$requete2="SELECT * FROM pages WHERE id_menu='" . $ligne->id_menu . "'";
				$resultat2=mysqli_query($connexion,$requete2);
				$nb2=mysqli_num_rows($resultat2);

				if($nb2==0)
					{
					$affichage.="<li><a href=\"".$ligne->lien_menu."\">".$ligne->intitule_menu."</a></li>";	
					}
				elseif($nb2==1)//une seule page associée à l'item
					{
					$ligne2=mysqli_fetch_object($resultat2);
					$affichage.="<li><a href=\"front.php?page=content&id_page=". $ligne2->id_page ."\">".$ligne->intitule_menu."</a></li>";	
					}
				else{
					//alors il y a plusieurs pages associée à cet item de menu
					$affichage.="<li>";
					$affichage.="<label for=\"item" . $ligne->id_menu . "\">" . $ligne->intitule_menu . "</label>";	
					$affichage.="<input type=\"checkbox\" name=\"item\" id=\"item" . $ligne->id_menu . "\" />";
					$affichage.="<ul class=\"ss_menu\">";
					while($ligne2=mysqli_fetch_object($resultat2))
						{
						$affichage.="<li><a href=\"front.php?page=content&id_page=" . $ligne2->id_page . "\">" . $ligne2->titre_page . "</a></li>";		
						}
					$affichage.="</ul>";
					$affichage.="</li>";//fermeture du <li> ligne 691
					}				
				}
			$affichage.="</ul>\n</nav\n";
			break;			
			}
		}
		
	return $affichage;		
	}
	
//==============================================================
function afficher_droits($connexion,$requete)
	{
	$resultat=mysqli_query($connexion, $requete); 
	$affichage="<table class=\"tab_resultats\">\n";
	// on calcule les entêtes des colonnes
	$affichage.="<tr>\n";
	$affichage.="<th>Module</th>\n";
	$affichage.="<th>Admin</th>\n";	
	$affichage.="<th>User</th>\n";
	$affichage.="</tr>\n";	
	while($ligne=mysqli_fetch_object($resultat))
		{
		$affichage.="<tr>\n";
		$affichage.="<td>" . $ligne->intitule_menu . "</td>\n";		
		$affichage.="<td><a href=\"admin.php?module=droits&id_droit=" . $ligne->id_droit . "&statut=admin&valeur=" . $ligne->admin . "\"><img src=\"../images/" . $ligne->admin . ".png\" alt=\"\" /></a></td>";
		$affichage.="<td><a href=\"admin.php?module=droits&id_droit=" . $ligne->id_droit . "&statut=user&valeur=" . $ligne->user . "\"><img src=\"../images/" . $ligne->user . ".png\" alt=\"\" /></a></td>";		
		$affichage.="</tr>\n";	
		}
	$affichage.="</table>\n";	
	
	return $affichage;	
	}

//=======================================
function afficher_sliders($connexion,$requete,$cas)
	{
	$resultat=mysqli_query($connexion,$requete);
	if(isset($cas))
		{
		switch($cas)
			{
			case "back":	

			$affichage="<table class=\"tab_resultats\">\n";
			//on calcule les entêtes des colonnes
			$affichage.="<tr>\n";
			$affichage.="<th>Tri</th>\n";		
			$affichage.="<th>Titre image</th>\n";	
			$affichage.="<th>Image</th>\n";		
			$affichage.="<th>Actions</th>\n";
			$affichage.="</tr>\n";
			while($ligne=mysqli_fetch_object($resultat))
				{
				//on affiche le contenu de chaque uplet présent dans la table
				$affichage.="<tr>\n";
				$affichage.="<td><a href=\"admin.php?module=sliders&action=trier_slider&id_slider=" . $ligne->id_slider . "&tri=up\"><span class=\"dashicons dashicons-arrow-up\"></span></a>&nbsp;&nbsp;<a href=\"admin.php?module=sliders&action=trier_slider&id_slider=" . $ligne->id_slider . "&tri=down\"><span class=\"dashicons dashicons-arrow-down\"></span></a></td>\n";	
				$affichage.="<td><strong>" . $ligne->titre_slider . "</strong><br />" . extrait($ligne->descriptif_slider,5,0) . "</td>\n";				
				$affichage.="<td><a href=\"".str_replace("_s","_b",$ligne->fichier_slider)."\" target=\"_blank\"><img src=\"".$ligne->fichier_slider."\" alt=\"\" /></a></td>\n";
				$affichage.="<td>";		
				$affichage.="<a href=\"admin.php?module=sliders&action=modifier_slider&id_slider=" . $ligne->id_slider . "\"><span class=\"dashicons dashicons-edit\"></span></a>";
				$affichage.="&nbsp;&nbsp;&nbsp;";
				$affichage.="<a href=\"admin.php?module=sliders&action=supprimer_slider&id_slider=" . $ligne->id_slider . "\"><span class=\"dashicons dashicons-no-alt\"></span></a>";
				$affichage.="</td>\n";						
				$affichage.="</tr>\n";					
				}
			$affichage.="</table>\n";

			break;
			
			case "front":
			
			$affichage="";
			while($ligne=mysqli_fetch_object($resultat))
				{
				$affichage.="<figure>\n";
				$affichage.="<img src=\"" . str_replace("_s","_b",$ligne->fichier_slider) . "\" alt=\"" . $ligne->titre_slider . "\" />";
				$affichage.="<figcaption class=\"caption\">\n";
				$affichage.="<h1>" . $ligne->titre_slider . "</h1>\n";
				$affichage.="<p>" . $ligne->descriptif_slider . "</p>\n";
				$affichage.="</figcaption>\n";
				$affichage.="</figure>\n";
				}					
			
			break;
			}
		}
	return $affichage;
	}	
//==============================================================
function generer_flux_rss($requete,$connexion)
	{
	$resultat=mysqli_query($connexion, $requete); 
	
	//on calcule l'entete du flux RSS
	$flux_rss="<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
	$flux_rss.="<rss xmlns:atom=\"http://www.w3.org/2005/Atom\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\" version=\"2.0\" xml:base=\"http://localhost/archi_v5\">\n";
	$flux_rss.="<channel>\n";
	$flux_rss.="<atom:link rel=\"self\" href=\"http://localhost/archi_v5/feed/rss.xml\"/>\n";	
	$flux_rss.="<title>Nos actus</title>\n";	
	$flux_rss.="<description>Le petit journal hebdo de Archi</description>\n";
	$flux_rss.="<lastBuildDate>" . date("D") . ", " . date("d M Y") . " " .  date("H:i:s") . " " . date("O") . "</lastBuildDate>\n";	
	$flux_rss.="<link>http://localhost/archi_v5</link>\n";
	$flux_rss.="<language>fr</language>\n";
	$flux_rss.="<copyright>Copyright " . date("Y") . "</copyright>\n";	
	$flux_rss.="<image>\n";
	$flux_rss.="<title>Archi : construction et rénovation de maisons individuelles</title>\n";
	$flux_rss.="<url>http://localhost/archi_v5/images/logo.png</url>\n";
	$flux_rss.="<link>http://localhost/archi_v5</link>\n";
	$flux_rss.="<width>185</width>\n"; 
	$flux_rss.="<height>99</height>\n"; 
	$flux_rss.="</image>\n";  	
	
	
	$car_replace=array("<br>","<br />");  
	
	//on calcul chaque item du flux (1 item=1 article avec RSS=1)
	$i=0;
	while($ligne=mysqli_fetch_object($resultat))
		{
		$flux_rss.="\n<item>\n";
		$flux_rss.="<title><![CDATA[" . $ligne->titre_article . "]]></title>\n";
		$contenu_flux=str_replace($car_replace,"\n",$ligne->contenu_article);
		$flux_rss.="<description><![CDATA[" . str_replace("&","&amp;",strip_tags($contenu_flux)) . "]]></description>\n";
		$date_flux=date("r",strtotime($ligne->date_article));
		$flux_rss.="<pubDate>" . $date_flux . "</pubDate>\n";	
		$flux_rss.="<link>http://localhost/archi_v5/front/front.php?page=single&amp;id_article=" . $ligne->id_article . "</link>\n";
		$flux_rss.="<guid isPermaLink=\"false\">" . $ligne->id_article . "</guid>\n";
		if(!empty($ligne->fichier_article))
			{
			$lien_image[$i]=$ligne->fichier_article;
			$taille_image[$i]=filesize($ligne->fichier_article);
			$flux_rss.="<enclosure length=\"". $taille_image[$i] . "\" url=\"" . $lien_image[$i] . "\"  type=\"image/" . str_replace("jpg","jpeg",fichier_type($ligne->fichier_article)) . "\" />\n";				
			}
		$flux_rss.="</item>\n";	
		$i++;
		}
	
	$flux_rss.="</channel>\n";
	$flux_rss.="</rss>\n";
	return $flux_rss;	
	}
	
?>





