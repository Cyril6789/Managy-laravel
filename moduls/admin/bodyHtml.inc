<?php session_start();

	if($_SESSION['gerant'])
	{
		if(is_file('./templates/'.TEMPLATE_NAME.'/pages/admin.php'))
		{

			$db = new MySQL();

			$sql = 'SELECT * FROM comptes_principaux WHERE id="'.$_SESSION['compte_principal'].'"';

			$db->Query($sql);
			$infos_societe = Array();
			$row = $db->Row();
			
			$infos_societe['mail'] = $row->mail_contact;
			$infos_societe['societe'] = $row->nom_societe;
			$infos_societe['web'] = $row->web;
			$infos_societe['tel'] = $row->tel;
			$infos_societe['adresse'] = $row->adresse;
			$infos_societe['siret'] = $row->siret;
			$infos_societe['ape'] = $row->ape;
			$infos_societe['expediteur_sms'] = $row->expediteur_sms;
			$infos_societe['signature_sms'] = $row->signature_sms;
			$infos_societe['cal'] = $row->cal;
			$infos_societe['prefix'] = $row->prefix;
			$infos_societe['mail_notification_cloture'] = $row->mail_notification_cloture;
			$infos_societe['mail_notification_sms'] = $row->mail_notification_sms;
			$infos_societe['sellsy_oauth_access_token'] = $row->sellsy_oauth_access_token;
			$infos_societe['sellsy_oauth_access_token_secret'] = $row->sellsy_oauth_access_token_secret;
			$infos_societe['sellsy_oauth_consumer_key'] = $row->sellsy_oauth_consumer_key;
			$infos_societe['sellsy_oauth_consumer_secret'] = $row->sellsy_oauth_consumer_secret;
			$infos_societe['ask_mail'] = $row->ask_mail;
      $infos_societe['disable_recap_hebdo'] = $row->disable_recap_hebdo;



			$sql = 'SELECT * FROM modalites';
			$db->Query($sql);
			$modalites = Array();
			$row = $db->Row();
			$modalites['entree'] = $row->entree;
			$modalites['sortie'] = $row->sortie;
			$modalites['site'] = $row->site;


			ob_start();
			include('./templates/'.TEMPLATE_NAME.'/pages/admin.php');
			$ModulContent = ob_get_contents();
			ob_end_clean();
		}
	}
	else
	{
		if(is_file('./templates/'.TEMPLATE_NAME.'/pages/no_right.php'))
		{
			ob_start();
			include('./templates/'.TEMPLATE_NAME.'/pages/no_right.php');
			$ModulContent = ob_get_contents();
			ob_end_clean();
		}
	}

?>