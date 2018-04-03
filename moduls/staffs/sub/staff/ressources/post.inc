<?php session_start();
 //GESTION DES POSTS
$maj = false;
if(isset($_POST['settings-name']) AND !empty($_POST['settings-name']))
{
    if(right('staffs', 1))
    {
        if($_GET['id_staff'] AND !empty($_GET['id_staff']) AND is_numeric($_GET['id_staff'])) //Un employé séléctionné
        {
            global $db;
            $id_staff = $db->SQLfix($_GET['id_staff']);
            
            $db->QUERY('SELECT COUNT(*) AS nb FROM staffs WHERE id ="'.$id_staff.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
            $row = $db->Row();
            
            if($row->nb) //employé existant
            {
                if($_GET['id_staff'] == 1) //cyril
                {
                    if(right('empty', -1)) //C'est cyril, seul moi peux voir
                    {
                        $maj = true;
                        
                    }

                }
                else
                {
                    $maj=TRUE;
                }
            }
            
        }
    }
}

if($maj)
{
    global $db;
    $nom = $db->SQLfix($_POST['settings-name']);
    $prenom = $db->SQLfix($_POST['settings-lastname']);
    $mail = $db->SQLfix($_POST['settings-email']);
    $pseudo = $db->SQLfix($_POST['settings-pseudo']);
    $pass = sha1($db->SQLfix($_POST['settings-password']));
    
    $sql = 'UPDATE staffs SET nom = "'.$nom.'"';
    if(!empty($prenom))
        $sql .= ', prenom = "'.$prenom.'"';
    if(!empty($pseudo))
        $sql .= ', pseudo = "'.$pseudo.'"';
    if(!empty($pass))
        $sql .= ', pass = "'.$pass.'"';
    $sql .= ', mail = "'.$mail.'" WHERE id = "'.$id_staff.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ';
    
    
    $db->Query($sql);
    
    
    if($id_staff == $_SESSION['id']) //on redéfini les superglobales si c'est nous même qu'on a changé
    {
        $db->Query('SELECT prenom, nom, mail FROM staffs WHERE id="'.$id_staff.'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
        echo $db->Error();
        $row = $db->Row();
        $_SESSION['prenom'] = $row->prenom;
        $_SESSION['nom'] = $row->nom;
        $_SESSION['mail'] = $row->mail;
    }
}
?>