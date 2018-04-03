<?php session_start();

    if(right('customers', 1))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/pages/customers.php'))
        {
            global $db;
            $sql = 'SELECT c.id, c.nom, c.prenom, c.archive, c.mail, c.adresse, c.adresse_suite, c.cp, c.ville, c.fixe, c.portable, c.pro_part,
                      ( SELECT SUM(mouvements) AS solde
                        FROM maintenance
                        WHERE id_client = c.id
                        AND compte_principal = "'.$_SESSION['compte_principal'].'" ) AS solde,
                      ( SELECT count(*) AS nb_inter
                        FROM interventions
                        WHERE interventions.id_client = c.id
                        AND compte_principal = "'.$_SESSION['compte_principal'].'" ) AS nb_inter
                    FROM clients AS c
                    WHERE compte_principal="'.$_SESSION['compte_principal'].'" ORDER BY id ASC';
            
            $db->Query($sql);
            echo $db->Error();
            $i=0;
            $tab_clients = Array();
            while ($row = $db->Row()) 
            {
                $tab_clients[$i]['id'] = $row->id;
                $tab_clients[$i]['archive'] = $row->archive;
                $tab_clients[$i]['name'] = $row->nom;
                $tab_clients[$i]['lname'] = $row->prenom;
                $tab_clients[$i]['mail'] = $row->mail;
                $tab_clients[$i]['adresse'] = $row->adresse;
                $tab_clients[$i]['adresse_suite'] = $row->adresse_suite;
                $tab_clients[$i]['cp'] = $row->cp;
                $tab_clients[$i]['ville'] = $row->ville;
                $tab_clients[$i]['fixe'] = $row->fixe;
                $tab_clients[$i]['portable'] = $row->portable;
                $tab_clients[$i]['pro_part'] = $row->pro_part;
                $tab_clients[$i]['nb_inter'] = $row->nb_inter;

                if($row->solde)
                {
                    $heures = floor(abs($row->solde));
                    $minutes = (abs($row->solde) - $heures) * 100;
                    if($row->solde < 0)
                        $moins = '-';
                    else
                        $moins = '';

                    $tab_clients[$i]['solde'] = $moins.$heures.'.'.round($minutes);
                }
                else
                    $tab_clients[$i]['solde'] = '';

                $i++;
            }

                
            
            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/pages/customers.php');
            $ModulContent = ob_get_contents();
            ob_end_clean();
        }
    }
    else
    {
        if(is_dir('./templates/'.TEMPLATE_NAME.'/pages/'))
        {
                ob_start();
                include('./templates/'.TEMPLATE_NAME.'/pages/no_right.php');
                $ModulContent = ob_get_contents();
                ob_end_clean();
        }
    }
?>