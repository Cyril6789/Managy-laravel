<?php session_start();

 //GESTION DES POSTS



if(!empty($_POST['nom_societe']))

{



    if($_POST['prefix'] == '0')

        $prefix = '';

    else

        $prefix = $db->SQLFix($_POST['prefix']);



    if($_POST['ask_mail'])

        $ask_mail = 1;

    else

        $ask_mail = 0;


    if($_POST['disable_recap_hebdo'])
      $drh = 1;
    else
      $drh = 0;

    $db = new MySQL();

    $sql = 'UPDATE comptes_principaux SET mail_contact="'.$db->SQLFix($_POST['mail_societe']).'", disable_recap_hebdo="'.$drh.'", ask_mail="'.$ask_mail.'", nom_societe="'.$db->SQLFix($_POST['nom_societe']).'", web="'.$db->SQLFix($_POST['web']).'", tel="'.$db->SQLFix($_POST['tel']).'", expediteur_sms="'.$db->SQLFix($_POST['expediteur_sms']).'", signature_sms="'.$db->SQLFix($_POST['signature_sms']).'", adresse="'.$db->SQLFix($_POST['adresse']).'", siret="'.$db->SQLFix($_POST['siret']).'", ape="'.$db->SQLFix($_POST['ape']).'", prefix="'.$prefix.'"  WHERE id="'.$_SESSION['compte_principal'].'" ';

    $db->Query($sql);

    $_SESSION['notify'] = 'majInfosSociete';

    header('location: ./admin');

    die();

}









//fichier

if(!empty($_FILES['docs']['name']) OR !empty($_FILES['mails']['name']))

{

    $extensions = Array('.jpg', '.jpeg', '.png');



    if(!empty($_FILES['docs']['name'])) {

        $extension1 = strtolower(strrchr($_FILES['docs']['name'], '.'));

        if (!in_array($extension1, $extensions)) {

            $_SESSION['notify'] = 'ErrorExtFile';

            header('Location: ./admin');

            die();

        } else {



            $dir = './print/images/logos/';



            if (is_file($dir . $_SESSION['compte_principal'] . '.png')) {

                unlink($dir . $_SESSION['compte_principal'] . '.png');

            }



            if (move_uploaded_file($_FILES['docs']['tmp_name'], $dir . $_SESSION['compte_principal'] . '.png')) {

                $_SESSION['notify'] = 'FileUploadSucces';

            } else {

                $_SESSION['notify'] = 'FileUploadError';

            }

        }

    }



    if(!empty($_FILES['mails']['name'])) {

        $extension2 = strtolower(strrchr($_FILES['mails']['name'], '.'));

        if (!in_array($extension2, $extensions)) {

            $_SESSION['notify'] = 'ErrorExtFile';

            header('Location: ./admin');

            die();

        } else {



            $dir = './images/logos_mails/';



            if (is_file($dir . $_SESSION['compte_principal'] . '.png')) {

                unlink($dir . $_SESSION['compte_principal'] . '.png');

            }



            if (move_uploaded_file($_FILES['mails']['tmp_name'], $dir . $_SESSION['compte_principal'] . '.png')) {

                $_SESSION['notify'] = 'FileUploadSucces';

            } else {

                $_SESSION['notify'] = 'FileUploadError';

            }

        }

    }

    header('Location: ./admin');

    die();

}



?>