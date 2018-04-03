<?php session_start();
 //GESTION DES POSTS
if(right('bl', 3))
{
    if(isset($_GET['id_customer']) AND !(empty($_GET['id_customer'])) AND is_numeric($_GET['id_customer'])) //c'est un nouveau
    {
        global $db;
        $id_customer = $db->SQLfix($_GET['id_customer']);

        $db->Query('SELECT COUNT(*) AS nb FROM clients WHERE id="'.$id_customer.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
        $row = $db->Row();

        if($row->nb) // client existant
        {
            $db->Query('SELECT COUNT(*) AS nb
                        FROM clients
                        WHERE clients.compte_principal = "'.$_SESSION['compte_principal'].'"
                        AND EXISTS (SELECT interventions.id AS id_inter 
                                    FROM interventions 
                                    WHERE interventions.id_client=clients.id
                                    AND interventions.time_cloture > 0
                                    AND interventions.compte_principal = "'.$_SESSION['compte_principal'].'"
                                    AND NOT EXISTS (SELECT NULL 
                                                    FROM bls_interventions 
                                                    WHERE id_inter = interventions.id_inter
                                                    AND bls_interventions.compte_principal = "'.$_SESSION['compte_principal'].'"
                                                    )
                                    )
                        AND clients.id="'.$id_customer.'" ');
            $row = $db->Row();

            if($row->nb) // client eligible
            {
                $db->Query('SELECT i.id_inter, i.projet, i.ref_chantier, c.ral, c.nom AS nom_c, a.nom AS nom_a, m.nom AS nom_m
                            FROM interventions AS i
                            LEFT JOIN couleurs AS c
                            ON (i.id_couleur=c.id)
                            AND (c.compte_principal = "'.$_SESSION['compte_principal'].'")
                            LEFT JOIN aspects AS a
                            ON (i.id_finition=a.id)
                            AND (a.compte_principal = "'.$_SESSION['compte_principal'].'")
                            LEFT JOIN matieres AS m
                            ON (i.id_matiere=m.id)
                            AND (m.compte_principal = "'.$_SESSION['compte_principal'].'")
                            WHERE i.compte_principal = "'.$_SESSION['compte_principal'].'"
                            AND NOT EXISTS (SELECT NULL 
                                            FROM bls_interventions 
                                            WHERE id_inter = i.id_inter
                                            AND bls_interventions.compte_principal = "'.$_SESSION['compte_principal'].'"
                                            )
                            AND i.id_client="'.$id_customer.'"
                            AND i.time_cloture > 0
                        ');
                
                $tab_inters = Array();
                
                while($row = $db->Row())
                {
                    if($_POST['inter'.$row->id_inter] == 'on')
                        $tab_inters[] = $row->id_inter;
                }
                if($tab_inters[0] > 0)
                {
                    
                    $db->Query('SELECT id_bl
                                FROM bls
                                WHERE compte_principal = "'.$_SESSION['compte_principal'].'"
                                ORDER BY id_bl DESC
                                LIMIT 1');
                    
                    $row = $db->Row();
                    $dernier_bl = $row->id_bl;
                    $nouveau_bl = $dernier_bl + 1;
                    $description = $db->SQLfix($_POST['commentaire']);
                    $db->Query('INSERT INTO bls (id_bl, id_client, timestamp, descriptif, compte_principal) VALUES ("'.$nouveau_bl.'", "'.$id_customer.'","'.time().'", "'.$description.'", "'.$_SESSION['compte_principal'].'")');
                    echo $db->Error();
                    foreach ($tab_inters AS $inter)
                    {
                        if($_POST['masquer_ref_chantier_'.$inter] == 'on')
                            $masquer = 1;
                        else
                            $masquer = 0;
                               
                        
                        
                        $db->Query('INSERT INTO bls_interventions (id_bl, id_inter, non_ref_chantier, compte_principal) VALUES ("'.$nouveau_bl.'", "'.$inter.'", "'.$masquer.'", "'.$_SESSION['compte_principal'].'") ');


                    }

                    /*dropbox
                    */

                    if(AccesActivableModul('dropbox'))
                    {

                        $isdropbox = true;
                        $_GET['id_bl'] = $nouveau_bl;
                        $nom_fichier = date('Y').$nouveau_bl.'.pdf';


                        echo $nom_fichier;
                        //die();
                        if(!is_dir('./dropbox_files/'))
                            mkdir('./dropbox_files/');
                        if(!is_dir('./dropbox_files/'.$_SESSION['compte_principal'].'/'))
                            mkdir('./dropbox_files/'.$_SESSION['compte_principal'].'/');
                        if(!is_dir('./dropbox_files/'.$_SESSION['compte_principal'].'/bl/'))
                            mkdir('./dropbox_files/'.$_SESSION['compte_principal'].'/bl/');

                        require('./pdf_bl.php');
                        $dropbox = new Dropbox();
                        $dropbox->upload_file('./dropbox_files/' . $_SESSION['compte_principal'] . '/bl/' . $nom_fichier, 'Bons de livraison');
                        @unlink('./dropbox_files/' . $_SESSION['compte_principal'] . '/bl/' . $nom_fichier);
                    }

                    
                    
                }
            }
        }
    }
    else //c'est peut etre une modif
    {
        if(isset($_POST['id_bl']) AND !(empty($_POST['id_bl'])) AND is_numeric($_POST['id_bl']))
        {
            
            global $db;
            $id_bl = $db->SQLfix($_POST['id_bl']);
            $db->Query('SELECT id_client, timestamp FROM bls WHERE id_bl="'.$id_bl.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
                $row = $db->Row();
                $id_client = $row->id_client;
                $annee = date('Y', $row->timestamp);
            $db->Query('SELECT COUNT(*) AS nb FROM bls WHERE id_bl="'.$id_bl.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
            $row = $db->Row();
               
            if($row->nb > 0)
            {
                
                $db->Query('SELECT i.id_inter, i.projet, i.ref_chantier, c.ral, c.nom AS nom_c, a.nom AS nom_a, m.nom AS nom_m
                                FROM interventions AS i
                                LEFT JOIN couleurs AS c
                                ON (i.id_couleur=c.id)
                                AND (c.compte_principal = "'.$_SESSION['compte_principal'].'")
                                LEFT JOIN aspects AS a
                                ON (i.id_finition=a.id)
                                AND (a.compte_principal = "'.$_SESSION['compte_principal'].'")
                                LEFT JOIN matieres AS m
                                ON (i.id_matiere=m.id)
                                AND (m.compte_principal = "'.$_SESSION['compte_principal'].'")
                                WHERE i.compte_principal = "'.$_SESSION['compte_principal'].'"
                                AND (
                                        NOT EXISTS (SELECT NULL 
                                                    FROM bls_interventions 
                                                    WHERE id_inter = i.id_inter
                                                    AND bls_interventions.compte_principal = "'.$_SESSION['compte_principal'].'"
                                                    )
                                        
                                        OR EXISTS   (SELECT NULL 
                                                    FROM bls_interventions 
                                                    WHERE id_inter = i.id_inter
                                                    AND bls_interventions.id_bl = "'.$id_bl.'"
                                                    AND bls_interventions.compte_principal = "'.$_SESSION['compte_principal'].'"
                                                    ) 
                                    )
                                AND i.time_cloture > 0
                            ');
                    $tab_inters = Array();
                    $i=0;
                    while($row = $db->Row())
                    {
                        $db2 = new MySQL();
                        $db2->Query('SELECT COUNT(*) AS nb FROM interventions WHERE id_inter="'.$row->id_inter.'" AND id_client="'.$id_client.'" AND compte_principal = "'.$_SESSION['compte_principal'].'"');
                        $row2 = $db2->Row();
                        if($row2->nb > 0)
                        {
                            if($_POST['inter'.$row->id_inter] == 'on')
                                $tab_inters[] = $row->id_inter;
                        }
                    }
                    
                    if($tab_inters[0] > 0)
                    {
                        
                        $description = $db->SQLfix($_POST['commentaire']);
                        $db->Query('UPDATE bls SET  descriptif = "'.$description.'" WHERE id_bl="'.$id_bl.'" AND compte_principal = "'.$_SESSION['compte_principal'].'" ');
                        
                        
                        $db->Query('DELETE FROM bls_interventions WHERE id_bl="'.$id_bl.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
                        foreach ($tab_inters AS $inter)
                        {
                            if($_POST['masquer_ref_chantier_'.$inter] == 'on')
                                $masquer = 1;
                            else
                                $masquer = 0;
                            $db->Query('INSERT INTO bls_interventions (id_bl, id_inter, non_ref_chantier, compte_principal) VALUES ("'.$id_bl.'", "'.$inter.'", "'.$masquer.'", "'.$_SESSION['compte_principal'].'") ');
                        }

                        /*dropbox
                        */
                        if(AccesActivableModul('dropbox'))
                        {
                            $dropbox = new Dropbox();
                            $isdropbox = true;
                            $_GET['id_bl'] = $id_bl;
                            $nom_fichier = $annee . $id_bl . '.pdf';


                            if (!is_dir('./dropbox_files/'))
                                mkdir('./dropbox_files/');
                            if (!is_dir('./dropbox_files/' . $_SESSION['compte_principal'] . '/'))
                                mkdir('./dropbox_files/' . $_SESSION['compte_principal'] . '/');
                            if (!is_dir('./dropbox_files/' . $_SESSION['compte_principal'] . '/bl/'))
                                mkdir('./dropbox_files/' . $_SESSION['compte_principal'] . '/bl/');

                            require('./pdf_bl.php');


                            $dropbox->upload_file('./dropbox_files/' . $_SESSION['compte_principal'] . '/bl/' . $nom_fichier, 'Bons de livraison');
                            @unlink('./dropbox_files/' . $_SESSION['compte_principal'] . '/bl/' . $nom_fichier);
                        }

                    }
                    else
                    {
                        $db->Query('DELETE FROM bls WHERE id_bl="'.$id_bl.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
                        $db->Query('DELETE FROM bls_interventions WHERE id_bl="'.$id_bl.'" AND compte_principal="'.$_SESSION['compte_principal'].'" ');
                        
                    }
            }
        }
    }
}
header('Location: ./bl');
?>