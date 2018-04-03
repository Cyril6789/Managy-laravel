<?php session_start();

    if(right('modules', 1))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/pages/moduls.php'))
        {

                global $db;

                $sql = 'SELECT fin_abo FROM comptes_principaux WHERE id="'.$_SESSION['compte_principal'].'" ';
                $db->Query($sql);
                $row = $db->Row();
                $fin_abo = $row->fin_abo;

                $db->Query('SELECT mc.id, mc.nom, mc.nom_propre, mc.prix, ma.date_activation, ma.fin_abo, dm.texte, dm.faq
                            FROM modules_comptes AS mc
                            LEFT JOIN modules_acces AS ma
                            ON (ma.nom = mc.nom)
                            AND (ma.compte_principal = "'.$_SESSION['compte_principal'].'")
                            LEFT JOIN descriptions_modules AS dm
                            ON (dm.nom = mc.nom)
                            WHERE type="2" AND mc.compte_principal="'.$_SESSION['compte_principal'].'" ');
                $tab_mods_active = Array();
                $tab_mods_not_active = Array();
                $i = 0;
                $j = 0;
                while($row = $db->Row())
                {
                    
                    if(in_array($row->nom,  $_SESSION['modules']))
                    {

                        $tab_mods_active[$i]['id'] = $row->id;
                        $tab_mods_active[$i]['link'] = $row->nom;
                        if($row->nom_propre)
                            $tab_mods_active[$i]['nom'] = $row->nom_propre;
                        else
                            $tab_mods_active[$i]['nom'] = $row->nom;
                        $tab_mods_active[$i]['date_activation'] = $row->date_activation;
                        $tab_mods_active[$i]['fin_abo'] = $row->fin_abo;
                        $tab_mods_active[$i]['prix'] = $row->prix;
                        $tab_mods_active[$i]['texte'] = $row->texte;
                        $tab_mods_active[$i]['faq'] = $row->faq;
                        $i++;
                    }
                    else
                    {
                        $tab_mods_not_active[$j]['id'] = $row->nom;
                        $tab_mods_not_active[$j]['link'] = $row->nom;
                        if($row->nom_propre)
                            $tab_mods_not_active[$j]['nom'] = $row->nom_propre;
                        else
                            $tab_mods_not_active[$j]['nom'] = $row->nom;
                        $tab_mods_not_active[$j]['prix'] = $row->prix;
                        $tab_mods_not_active[$j]['texte'] = $row->texte;
                        $tab_mods_not_active[$j]['faq'] = $row->faq;
                        $j++;
                    }
                }
            
            
                ob_start();
                include('./templates/'.TEMPLATE_NAME.'/pages/moduls.php');
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