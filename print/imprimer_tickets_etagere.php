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
			Impression Ticket retour
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

				$requete = "SELECT genre, client.nom, id_inter_bis, client.prenom, tel1, tel2, mail, adresse, cp, ville, pseudo, panne, materiel, type, cat_inter.nom AS nom_type, interventions.mdp, ouverture.id_technicien, DATE_FORMAT(ouverture.date, '%d/%m/%Y %H:%i') AS date, peci
				FROM client
				INNER JOIN interventions
				ON (interventions.id_client = client.id)
				INNER JOIN ouverture
				ON (ouverture.id_interventions = interventions.id)
				INNER JOIN technicien
				ON (technicien.id = ouverture.id_technicien)
				INNER JOIN cat_inter
				ON (interventions.type = cat_inter.id)
				WHERE interventions.id = '".$_GET['id_inter']."' ";
				$ret = new Requete($requete);
				$data = mysql_fetch_array($ret->retourner());
				?>
				<div align="center"> 
				<table border="0" width="200">
					<tr>
							<td>
								
								<center>
								<?php
									if($_GET['arrow'] == 'h')
										echo '<img src="./images/arrow-h.png" />';
								?>
									<h1>
											Intervention<br />
											<?php echo $_GET['id_inter'];?><br />
											
											
										
									</h1>
									<?php if($data['id_inter_bis'] > 0)
									{
										?>
										<h2>(<?php echo $data['id_inter_bis'];?>)</h2><br />
										<?php
									}
									?>
									<b>Client :</b>  <?php echo $data['genre'].' '.$data['nom'].' '.$data['prenom'];?><br />
									
									<b>
										Type de matériel : <?php echo $data['nom_type']; ?>
										
									</b>
										<p class="materiel">Materiel déposé : <?php echo stripslashes(nl2br($data['materiel']));?></p>
										
										<?php
									if($_GET['arrow'] == 'b')
										echo '<img src="./images/arrow-b.png" />';
								?>
								</center>
							</td>
											
					</tr>
				</table>
                </div>
			<?php
			}
			?>
		
	</body>
</html>