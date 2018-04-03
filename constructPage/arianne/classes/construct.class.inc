<?php session_start();
	Class Arianne {
		private $html;

		public function __construct($page_title)
		{
			$template_name = TEMPLATE_NAME;

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

			$db->Query('SELECT COUNT(*) AS nb FROM interventions WHERE time_cloture > "0" AND solde = "0" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
			$row = $db->Row();
			$nombre_inter_a_solder = $row->nb;

			if($nombre_inter_sst)
			{
				$db->Query('SELECT COUNT(*) AS nb FROM sous_traitances WHERE retour = "0" AND date_retour < "'.time().'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
				$row = $db->Row();
				$nombre_inter_sst_rouge = $row->nb;
			}


			//Nombre restitution
			$sql = 'SELECT COUNT(*) AS nb_restitution FROM interventions WHERE time_cloture > "0" AND time_restitution = "0" AND type_atelier_rdv = "1" AND compte_principal="'.$_SESSION['compte_principal'].'" ';
			$db->Query($sql);
			$row = $db->Row();
			$nb_restitutions = $row->nb_restitution;

			//nombre_facturation
			$sql = 'SELECT COUNT(*) AS nb_facturations
                    FROM interventions 
                    LEFT JOIN materiels
                    ON (interventions.id_materiel = materiels.id)
                    AND (materiels.compte_principal = "'.$_SESSION['compte_principal'].'")
                    WHERE facture = "0"
                    AND time_cloture>"0" 
                    AND interventions.compte_principal="'.$_SESSION['compte_principal'].'" 
                    AND rdv_annule = "0"
                    AND garantie = "0"
					AND (
							NOT EXISTS (
								SELECT NULL
								FROM maintenance
								WHERE maintenance.id_inter = interventions.id_inter
								AND compte_principal="' . $_SESSION['compte_principal'] . '"
								)     
							OR force_facturation = "1"	 
						)
					AND EXISTS (                    
                                SELECT NULL                                    
                                FROM prestations_effectuees                                                          
                                WHERE prestations_effectuees.id_inter = interventions.id_inter                                    
                                AND compte_principal="'.$_SESSION['compte_principal'].'"
                                )    
                    ';
            $db->Query($sql);
			if(0) //$_SESSION['id'] == '1')
            {
                echo $sql;
                echo $db->GetHTML();
                die();
            }
			$row = $db->Row();
			$nb_facturations = $row->nb_facturations;

			/*
			 * AND NOT EXISTS (
                                    SELECT NULL
                                    FROM maintenance
                                    WHERE maintenance.id_inter = interventions.id_inter
                                    AND compte_principal="'.$_SESSION['compte_principal'].'"
                                    )  
			 */

			if(is_file('./templates/'.TEMPLATE_NAME.'/includes/arianne.inc'))
				include('./templates/'.TEMPLATE_NAME.'/includes/arianne.inc');
			
		}

	}

?>