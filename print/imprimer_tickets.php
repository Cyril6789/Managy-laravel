<?php @session_start();
include("../classes/mysql.class.php");
//include("../classes/mysql.class.php");
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
                                $db->Query('SELECT * FROM comptes_principaux WHERE id="'.$_SESSION['compte_principal'].'" ');
                                $row = $db->Row();
                                define(MAIL_CONTACT, $row->mail_contact);
                                define(NOM_SOCIETE, $row->nom_societe);
                                define(LOGO, $row->logo);
                                define(WEB, $row->web);
                                define(TEL, $row->tel);
                                define(EXP_SMS, $row->expediteur_sms);
                                define(SIGNATURE_SMS, $row->signature_sms);
                                define(ADRESSE, $row->adresse);
                                define(SIRET, $row->siret);
                                define(APE, $row->ape);

				$db->Query("SELECT interventions.prefix, titre, clients.nom, clients.prenom, external_link, fixe, clients.portable, clients.mail, clients.adresse, clients.cp, clients.ville, staffs.prenom AS prenom_s, panne, matos, id_inter, materiels.nom AS nom_materiel,  id_staff_ouverture, time_ouverture, urgente, tarif_estimatif
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
                                AND interventions.compte_principal='".$_SESSION['compte_principal']."' ");
				echo $db->Error();
				$data = $db->Row();
                                
                                
                                
				?>
				<div align="left">
				<table width="280" border="0" cellspacing="0" cellpadding="0">
					<tr>
											
						<td>
							<center>
								<img src="./print/images/logos/<?php echo $_SESSION['compte_principal'];?>.png" width="100%" alt="logo"/>
							</center>
						</td>
						<td width="50">&nbsp;</td>
					</tr>
					<tr>
												
						<td>
							<div align="center" class="mes_coordonnees">
								<?php echo SIGNATURE_SMS;?> - <?php echo NOM_SOCIETE;?><br />
								<?php echo ADRESSE;?><br />
								Tel : <?php echo TEL;?><br />
								E-mail : <span style="color:blue"><?php echo MAIL_CONTACT;?></span><br />
								Siret : <?php echo SIRET;?> - APE : <?php echo APE;?><br />
								<span style="color:blue"><?php echo WEB;?></span><br /><br />
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="ouverture">
								<strong>
									Date d'ouverture : <?php echo date('d/m/Y à H:i', $data->time_ouverture);?><BR />
									Hôte d'accueil : <?php echo $data->prenom_s;?>
								</strong>
							</div>
						</td>
					</tr>
					<tr><br />
						<td align="center">
							<br />
							<img src="https://www.managy.fr/barcode.php?id_inter=<?php echo $_GET['id_inter'];?>" alt=""/>
							<h2><?php echo 'Intervention <br />n°'.$data->prefix.$_GET['id_inter'];?></h2>
						</td>
					</tr> 
					<tr>
						
								<td>
									<div class="coord_client">
										<b>Client :</b> <?php echo $data->titre.' '.$data->nom.' '.$data->prenom;?><br />
										<b>Adresse :</b> <?php echo $data->adresse.', '.$data->cp.' '.$data->ville;?><br />
										<b>Tel :</b> <?php echo $data->fixe;?><br />
										<b>Port :</b> <?php echo $data->portable;?><br />
										<b>Email :</b> <?php echo $data->mail;?><br />
									
									
									</div>
								</td>
					</tr>


					<tr>
						<td >
							<b><center>Panne constatée :</center></b>
						</td>
					</tr>
					<tr>
						<td>
							<div class="panne">
								<?php echo stripslashes(nl2br($data->panne));
								if($data->urgente)
									$peci = 'Oui';
								else
									$peci = 'Non';?><br />
								Urgente : <?php echo $peci; ?>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<b><center>Materiel apporté :</center></b>
						</td>
					</tr>
					<tr>
									
						<td >
							<div class="materiel">
								<?php echo stripslashes(nl2br($data->matos));?>
							</div>
						</td>
					</tr>
					<?php
					if($data->tarif_estimatif)
					{
						?>
						<tr>
							<td>
								<b><center>Tarif estimé : <?php echo $data->tarif_estimatif;?> €</center></b>
							</td>
						</tr>
						<?php
					}
					?>
					<tr>
						<td><br /><br />
							<center>
								Suivez l'évolution de l'intervention en me scannant !<br /><br />

									<img height="80" src="./image.php?msg=<?php echo urlencode("http://www.mana.gy/".$data->external_link); ?>&amp;err=<?php echo urlencode("M"); ?>" alt="generation qr-code" />
								<br />
								<div style="font-size: 11px;">Ou sur : <i>http://www.mana.gy/<?php echo $data->external_link;?></i><br /></div>
							</center>
						</td>
					</tr>
					<tr>
								<td width="">
									<div class="charte"><br /><br />
										<?php
										$sql = 'SELECT entree FROM comptes_principaux WHERE id="'.$_SESSION['compte_principal'].'" ';
										$db->Query($sql);
										$r = $db->Row();
										if($r->entree == '')
										{
											$sql = 'SELECT entree FROM modalites';
											$db->Query($sql);
											$modalites = Array();
											$row = $db->Row();
											echo $row->entree;
										}
										else
											echo $r->entree;

										?>
									</div>
								</td>
							
					</tr>
					<tr>
						<td>Signature :<br />
							<table border="1" width="90%">
								<tr>
									<td>
										le <?php echo date('d/m/Y à H:i');?>
										<br /><br /><br /><br /><br />
									</td>
								</tr>
							</table>
						</td>
					</tr>
							
					</table>
                </div>
			<?php
			}
			?>
		
	</body>
</html>