<?php session_start();
	Class Head{
		private $html;

		public function __construct()
		{
                    
            global $db;
            $db->Query('SELECT COUNT(*) AS nb FROM commandes WHERE cde_recue = "0" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
            $row = $db->Row();
            $nombre_inter_cde = $row->nb;

            if($nombre_inter_cde)
            {
                $db->Query('SELECT COUNT(*) AS nb FROM commandes WHERE cde_recue = "0" AND date_reception < "'.time().'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
                $row = $db->Row();
                $nombre_inter_cde_rouge = $row->nb;
            }


            $db->Query('SELECT COUNT(*) AS nb FROM sous_traitances WHERE retour = "0" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
            $row = $db->Row();
            $nombre_inter_sst = $row->nb;

            if($nombre_inter_sst)
            {
                $db->Query('SELECT COUNT(*) AS nb FROM sous_traitances WHERE retour = "0" AND date_retour < "'.time().'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
                $row = $db->Row();
                $nombre_inter_sst_rouge = $row->nb;
            }


            $db->Query('SELECT COUNT(*) AS nb FROM interventions WHERE time_cloture > "0" AND solde = "0" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
            $row = $db->Row();
            $nombre_inter_a_solder = $row->nb;




            //switch account
            if(is_file('./moduls/switch_account/blocs/switch_account.inc') AND right('switch_account', -1))
            {

                $db->Query('SELECT id, nom_societe FROM comptes_principaux WHERE id="' . $_SESSION['compte_principal'] . '" ');
                $row = $db->Row();
                $compte_actuel = $row->nom_societe;
                $id_compte_actuel = $row->id;
                $tab_comptes = Array();
                $i = 0;
                $db->Query('SELECT nom_societe, id FROM comptes_principaux WHERE bloque="0"');
                while ($row = $db->Row()) {
                    $tab_comptes[$i]['id'] = $row->id;
                    $tab_comptes[$i]['nom'] = $row->nom_societe;
                    $i++;
                }
            }


            //sous traitance en cours
            $tab_sstts = Array();
            if(right('soustraitances', 6) AND (AccesActivableModul('intervention_informatique')) AND $nombre_inter_sst)
            {
                $db->Query('SELECT st.id, st.id_inter, st.devis, st.num_cde, st.nom AS nom_st, colis_alle, colis_retour, date_sst, date_retour, clients.titre, clients.nom, clients.prenom
                    FROM sous_traitances AS st
                    INNER JOIN interventions
                    ON (interventions.id_inter = st.id_inter)
                    AND (interventions.compte_principal="' . $_SESSION['compte_principal'] . '")
                    INNER JOIN clients
                    ON (clients.id = interventions.id_client)
                    AND (clients.compte_principal = "' . $_SESSION['compte_principal'] . '")
                    WHERE retour="0" AND st.compte_principal="' . $_SESSION['compte_principal'] . '"
                    ORDER BY date_retour ASC');



                $i = 0;
                while ($row = $db->Row())
                {
                    $tab_sstts[$i]['id'] = $row->id;
                    $tab_sstts[$i]['id_inter'] = $row->id_inter;
                    $tab_sstts[$i]['devis'] = $row->devis;
                    $tab_sstts[$i]['nom_st'] = $row->nom_st;
                    $tab_sstts[$i]['num_cde'] = $row->num_cde;
                    $tab_sstts[$i]['colis_alle'] = $row->colis_alle;
                    $tab_sstts[$i]['colis_retour'] = $row->colis_retour;
                    $tab_sstts[$i]['date_sst'] = date('d/m/Y', $row->date_sst);
                    $tab_sstts[$i]['date_retour'] = date('d/m/Y', $row->date_retour);
                    $tab_sstts[$i]['date_retour_t'] = $row->date_retour;
                    $tab_sstts[$i]['titre'] = $row->titre;
                    $tab_sstts[$i]['nom'] = $row->nom;
                    $tab_sstts[$i]['prenom'] = $row->prenom;
                    $i++;
                }

                $nb_sst = $i;
                $db->Query('SELECT COUNT(*) AS nb FROM sous_traitances WHERE retour = "0" AND date_retour < "'.time().'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
                $row = $db->Row();
                $nombre_inter_sst_retard = $row->nb;
            }

            /* liste des module activés*/
            $db->Query('SELECT nom, nom_propre, prix FROM modules_comptes WHERE type="2" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
            $tab_mods_active = Array();
            $tab_mods_not_active = Array();
            $i = 0;
            $j = 0;
            while($row = $db->Row())
            {

                if(in_array($row->nom,  $_SESSION['modules']) AND $row->prix > 0)
                {
                    $tab_mods_active[$i]['link'] = $row->nom;
                    if($row->nom_propre)
                        $tab_mods_active[$i]['nom'] = $row->nom_propre;
                    else
                        $tab_mods_active[$i]['nom'] = $row->nom;
                    $tab_mods_active[$i]['prix'] = $row->prix;
                    $i++;
                }
            }

            if(TEMPLATE_NAME == 'melon') {
                /* NB licences staffs*/
                $sql = 'SELECT COUNT(*) AS nb FROM licences_staffs WHERE compte_principal = "' . $_SESSION['compte_principal'] . '" AND incluse="0"';
                $db->Query($sql);
                $row = $db->Row();
                $nb_licences_suppl = $row->nb;

                $sql = 'SELECT prix_staff_suppl FROM comptes_principaux WHERE id = "' . $_SESSION['compte_principal'] . '" ';
                $db->Query($sql);
                $row = $db->Row();
                $prix_staff_suppl = $row->prix_staff_suppl;

                $sql = 'SELECT nom FROM licences_staffs WHERE compte_principal = "' . $_SESSION['compte_principal'] . '" AND incluse="0"';
                $db->Query($sql);
                $tab_licences_suppl = Array();
                while ($row = $db->Row()) {
                    $tab_licences_suppl[] = $row->nom;
                }

                /*
                             * Choix abo
                             */

                $sql = 'SELECT mois, coeff FROM choix_abo ORDER BY mois DESC';
                $tab_choix_abo = Array();
                $i = 0;
                $db->Query($sql);
                while ($row = $db->Row()) {
                    $tab_choix_abo[$i]['mois'] = $row->mois;
                    $tab_choix_abo[$i]['coeff'] = $row->coeff;
                    $i++;
                }
                $nb_choix = $i;

            }
            //commandes
            $tab_cdes = Array();
            if(right('commandes', 6) AND (AccesActivableModul('intervention_informatique')) AND $nombre_inter_cde)
            {
                global $db;
                $db->Query('SELECT commandes.id, commandes.id_inter, interventions.prefix, num_cde, bdc, fournisseur, colis, date_cde, date_reception, clients.titre, clients.nom, clients.prenom
                    FROM commandes
                    INNER JOIN interventions
                    ON (interventions.id_inter = commandes.id_inter)
                    AND (interventions.compte_principal="' . $_SESSION['compte_principal'] . '")
                    INNER JOIN clients
                    ON (clients.id = interventions.id_client)
                    AND (clients.compte_principal = "' . $_SESSION['compte_principal'] . '")
                    WHERE cde_recue="0" AND commandes.compte_principal="' . $_SESSION['compte_principal'] . '"
                    ORDER BY date_reception ASC');


                $i = 0;
                while ($row = $db->Row())
                {
                    $tab_cdes[$i]['id'] = $row->id;
                    $tab_cdes[$i]['prefix'] = $row->prefix;
                    $tab_cdes[$i]['id_inter'] = $row->id_inter;
                    $tab_cdes[$i]['bdc'] = $row->bdc;
                    $tab_cdes[$i]['fournisseur'] = $row->fournisseur;
                    $tab_cdes[$i]['num_cde'] = $row->num_cde;
                    $tab_cdes[$i]['colis'] = $row->colis;
                    $tab_cdes[$i]['date_cde'] = date('d/m/Y', $row->date_cde);
                    $tab_cdes[$i]['date_reception'] = date('d/m/Y', $row->date_reception);
                    $tab_cdes[$i]['date_reception_t'] = $row->date_reception;
                    $tab_cdes[$i]['titre'] = $row->titre;
                    $tab_cdes[$i]['nom'] = $row->nom;
                    $tab_cdes[$i]['prenom'] = $row->prenom;
                    $i++;
                }
                $nb_cdes = $i;
                $db->Query('SELECT COUNT(*) AS nb FROM commandes WHERE cde_recue = "0" AND date_reception < "'.time().'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
                $row = $db->Row();
                $nombre_inter_cde_retard = $row->nb;
            }






            $sql = 'SELECT * FROM stocks_sms WHERE compte_principal="'.$_SESSION['compte_principal'].'"';
            $db->Query($sql);
            $row = $db->Row();
            $conso = $row->conso;
            $commande = $row->commande;


            /*
             * CGU
             */
            if($_SESSION['gerant']) {
                $sql = 'SELECT * FROM cgu ORDER BY id DESC LIMIT 1';
                $db->Query($sql);

                $row = $db->Row();
                $id_cgu = $row->id;
                $texte_cgu = $row->texte;
            }

            /*
             * Changement interventons
             */

            $sql = 'SELECT id_staff_c, id_inter 
                    FROM changements_interventions 
                    WHERE id_staff_v ="'.$_SESSION['id'].'" AND compte_principal="'.$_SESSION['compte_principal'].'" ORDER BY id ASC';
            $db->Query($sql);
            $i = 0;
            $tab_changement = Array();
            while($row = $db->Row())
            {
                $tab_changement[$i]['id_staff'] = $row->id_staff_c;
                $tab_changement[$i]['id_inter'] = $row->id_inter;
                $i++;
            }


            $sql = 'DELETE FROM changements_interventions WHERE  id_staff_v ="'.$_SESSION['id'].'" AND compte_principal="'.$_SESSION['compte_principal'].'"';
            $db->Query($sql);


            /*
             * Statut en attente
             */

            $sql = 'SELECT i.id_inter,
                    c.titre, c.nom, c.prenom,
                    s.nom AS nom_statut
                    FROM interventions AS i 
                    INNER JOIN clients AS c
                    ON (c.id = i.id_client)
                    AND (c.compte_principal = "'.$_SESSION['compte_principal'].'")
                    LEFT JOIN statuts AS s
                    ON (s.id = i.id_statut)
                    AND (s.compte_principal = "'.$_SESSION['compte_principal'].'")
                    WHERE i. compte_principal  = "'.$_SESSION['compte_principal'].'"
                    AND s.intervention_occupee = "1"                    
                    ';
            $tab_inter_occupees = Array();
            $i=0;
            $db->Query($sql);
            echo $db->Error();
            while($row = $db->Row())
            {
                $tab_inter_occupees[$i]['id'] = $row->id_inter;
                $tab_inter_occupees[$i]['titre'] = $row->titre;
                $tab_inter_occupees[$i]['nom'] = $row->nom;
                $tab_inter_occupees[$i]['prenom'] = $row->prenom;
                $tab_inter_occupees[$i]['statut'] = $row->nom_statut;
                $i++;
            }

            $nb_inter_busy = $i;






            
			$template_name = TEMPLATE_NAME;
			if(is_file('./templates/'.TEMPLATE_NAME.'/includes/head.inc'))
				include('./templates/'.TEMPLATE_NAME.'/includes/head.inc');
		}

	}

?>