<?php @session_start();
include("../classes/mysql.class.php");
$db = new MySQL();


//if($_SERVER["REMOTE_ADDR"]!="88.178.4.193")
	//if( ((!droit_dacces(11)) AND (!droit_dacces(12))) )
		//die();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
	<head>
		<title>
			Impression d'une fiche d'intervention
		</title>
		<meta charset="utf-8" />
		<script language="javascript">
			function imprimer()
			{
				
                                   window.print();
                                
											
			}
		</script>
            <link rel="stylesheet" media="print" type="text/css" title="design" href="./print/css/imprimer_tickets.css" />
            <link rel="stylesheet" media="screen" type="text/css" title="design" href="./print/css/imprimer_tickets.css" />
	</head>
	<?php 
	if($_GET['noprint'] == '1')
		echo '<body>';
	else
		echo '<body onload="imprimer()">';
		
		
			if(1);//$_GET['type'] == "entree")
			{

				$db->Query("SELECT interventions.prefix, titre, clients.nom, clients.prenom, fixe, clients.portable, interventions.mdp, clients.mail, clients.adresse, clients.cp, clients.ville, staffs.prenom AS prenom_s, panne, matos, id_inter, materiels.nom AS nom_materiel,  id_staff_ouverture, time_ouverture, urgente
				FROM clients
				LEFT JOIN interventions
				ON (interventions.id_client = clients.id)
                                AND (clients.compte_principal='".$_SESSION['compte_principal']."')
				LEFT JOIN staffs
				ON (staffs.id = interventions.id_staff_ouverture)
                                AND (staffs.compte_principal='".$_SESSION['compte_principal']."')
				LEFT JOIN materiels
				ON (interventions.id_materiel = materiels.id)
                                AND (materiels.compte_principal='".$_SESSION['compte_principal']."')
				WHERE interventions.id_inter = '".$db->SQLfix($_GET['id_inter'])."'
                                AND interventions.compte_principal='".$_SESSION['compte_principal']."'");
				echo $db->Error();
				$data = $db->Row();
				?>
				<div align="center"> 
				<table border="0" width="160">
					<tr>
							<td>
								<table border="0" width="100%" valign="top">
									<tr>
										<td>
											<div style="font-size: 9pt;" >
												Type de matériel : <?php echo $data->nom_materiel; ?>
											</div>
										<td>
										<td style="text-align: right;">
											<div style="font-size: 9pt;" >
											
												Urgente : <?php 
												if($data->urgente)
													$peci = 'Oui';
												else
													$peci = 'Non';
												echo $peci; ?>
											</div>
										</td>
									</tr>
								</table>
								<center>
									<h1>
                                        <br />
                                        <img src="https://www.managy.fr/barcode.php?id_inter=<?php echo $_GET['id_inter'];?>" alt=""/>
										
											<?php echo $data->prefix.$_GET['id_inter'];?><br />
											<!--<img width="100%" src='./images/codebarre.php?numero=<?php echo $_GET['id_inter'];?>&amp;control=1'/>
											<br />-->
										
									</h1>									<?php if(!$_GET['no_client'])									{										?>
									<b>Client :</b>  <?php echo $data->titre.' '.$data->nom.' '.$data->prenom?><br />
									<?php																		
                                                                        if($data->fixe)
                                                                            echo '<b>Fixe :</b> '.$data->fixe.'<br />';
                                                                        if($data->portable)
                                                                            echo '<b>Portable :</b> '.$data->portable.'<br />';
									}
									?>
                                                                        <br />
									<b>
										Panne : <?php echo stripslashes(nl2br($data->panne));?>
										
									</b>
										<p class="materiel">Materiel déposé : <?php echo stripslashes(nl2br($data->matos));?></p>
																				<p class="materiel"><b>Codes : </b><?php echo stripslashes(nl2br($data->mdp));?></p>
										
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