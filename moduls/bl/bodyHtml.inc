<?php session_start();

    if(right('bl', 3))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/pages/bls.php'))
        {
        
            global $db;


            $db->Query('SELECT id, titre, nom, prenom
                        FROM clients
                        WHERE compte_principal = "'.$_SESSION['compte_principal'].'"
                        ORDER BY nom ASC
                        ');
            $tab_clients = Array();
            $i=0;
            while($row3 = $db->Row())
            {
                $tab_clients[$i]['id'] = $row3->id;
                $tab_clients[$i]['titre'] = $row3->titre;
                $tab_clients[$i]['nom'] = $row3->nom;
                $tab_clients[$i]['prenom'] = $row3->prenom;
                $i++;
            }


            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/pages/bls.php');
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