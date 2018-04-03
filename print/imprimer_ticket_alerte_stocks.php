<?php @session_start();
include("./classes/requete.php");
include("./classes/requeteCount.php");
require_once './fonctions/droit_dacces.php';

//if($_SERVER["REMOTE_ADDR"]!="88.178.4.193")
	//if( ((!droit_dacces(11)) AND (!droit_dacces(12))) )
		//die();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
	<head>
		<title>
			Impression des stocks
		</title>
		<meta charset="utf-8" />
		<script language="javascript">
			function imprimer()
			{
				
                                    window.print();
                                
											
			}
		</script>
            <link rel="stylesheet" media="print" type="text/css" title="design" href="./css/imprimer_tickets.css" />
            <link rel="stylesheet" media="screen" type="text/css" title="design" href="./css/imprimer_tickets.css" />
	</head>
	<?php 
	if($_GET['noprint'] == '1')
		echo '<body>';
	else
		echo '<body onload="imprimer()">';
		
		
			if(1);//$_GET['type'] == "entree")
			{

				$html .= '
				Impression des alertes des stocks <br />
				Au '.date('d/m/Y \a H:i').'
				<table  width="200px" border="0">';
	
/*les catégorie des produits*/
	$req = "SELECT cat_stock.id, cat_stock.nom 
			FROM cat_stock
			INNER JOIN classement_stock
			ON (classement_stock.id_cat_stock = cat_stock.id)
			INNER JOIN stock
			ON (classement_stock.id_stock = stock.id)
			GROUP BY cat_stock.nom";
	$ret = new Requete($req);
	while($d_cat = mysql_fetch_array($ret->retourner()))
	{
		
		$html .= '<tr><th colspan="2"><center>'.$d_cat['nom'].'</center></th></tr>';
		/*Fin les catégorie de produits*/
		
		/*Les produits */
		$requete = "SELECT stock.id, titre, ref, descr, qte
		FROM stock
		INNER JOIN classement_stock
		ON (classement_stock.id_stock = stock.id)
		WHERE classement_stock.id_cat_stock ='".$d_cat['id']."'
		AND qte <= lim_al
		ORDER BY qte ASC";
		$retour = new Requete($requete);

		while($data = mysql_fetch_array($retour->retourner()))
		{
			
			
		
			
			
			$html .= '<tr>
						<td>
							'.$data['titre'].'
						</td>
						<td>
							'.$data['qte'].'
						</td>
					</tr>
							';
		}
		/*fin les produits */
		
		$options .= '';
		
	}
	
	$html .= '</table>';
			}
		echo '<div align="center"> '.
		$html.'</div>';
			?>
	</body>
</html>