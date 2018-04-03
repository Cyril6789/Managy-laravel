<?php session_start()
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 27/03/2016
 * Time: 16:17
 */
?>


<script language="javascript">
    function setDestMail(dest)
    {
        var tab = dest.split('_');
        document.getElementById('mail').value = tab[1];


        if(tab[0] > 0)
        {

            var mail = document.getElementById('liste_messages').value;
            if(mail > 0)
                setMessageMail(tab[0], mail)
        }
        else
        {

            document.getElementById('message').value = '';
            document.getElementById('sujet').value = '';
            document.getElementById('titre').value = '';
        }
    }

    function setMail(mail)
    {

        if(mail > 0)
        {
            var dest = document.getElementById('mail_liste').value;
            var tab = dest.split('_');
            if(tab[0] > 0)
                setMessageMail(tab[0], mail)
        }
        else
        {
            document.getElementById('message').value = '';
            document.getElementById('sujet').value = '';
            document.getElementById('titre').value = '';
        }
    }

    function setMessageMail(dest, mail)
    {
        var messages = new Array();
        <?php


    foreach ($liste_mails AS $mail)
    {
        global $db;
        $db->Query('SELECT titre, nom, prenom FROM clients WHERE id="'.$mail['id'].'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
        $row = $db->Row();
        $titre_c = addslashes($row->titre);
        $nom = addslashes($row->nom);
        $prenom = addslashes($row->prenom);
        echo '
            messages['.$mail['id'].'] = new Array();';

        $db->Query('SELECT c.nom, c.ral
                    FROM couleurs AS c
                    INNER JOIN interventions AS i
                    ON (i.id_couleur = c.id)
                    WHERE i.compte_principal="'.$_SESSION['compte_principal'].'"
                    AND c.compte_principal="'.$_SESSION['compte_principal'].'"
                    AND i.id_inter = "'.$id_inter.'"    ');
        $row = $db->Row();
        $ral = addslashes($row->ral);
        $nom_couleur = addslashes($row->nom);
        foreach($mails_types AS $type)
        {
            $message = addslashes($type['message']);
            $message = str_replace('%id_inter%', $id_inter, $message);
            $message = str_replace('%titre%', $titre_c, $message);
            $message = str_replace('%nom%', $nom, $message);
            $message = str_replace('%prenom%', $prenom, $message);
            $message = str_replace('%nom_couleur%', $nom_couleur, $message);
            $message = str_replace('%ral%', $ral, $message);
            $message = addslashes(str_replace('%ref_chantier%', $ref_chantier, $message));
            $sujet = $type['sujet'];
            $sujet = str_replace('%id_inter%', $id_inter, $sujet);
            $sujet = addslashes(str_replace('%ref_chantier%', $ref_chantier, $sujet));
            $sujet = str_replace('%nom_couleur%', $nom_couleur, $sujet);
            $sujet = str_replace('%ral%', $ral, $sujet);
            $titre = $type['titre'];
            $titre = str_replace('%id_inter%', $id_inter, $titre);
            $titre = addslashes(str_replace('%ref_chantier%', $ref_chantier, $titre));
            $titre = str_replace('%nom_couleur%', $nom_couleur, $titre);
            $titre = str_replace('%ral%', $ral, $titre);


            echo '
            messages['.$mail['id'].']['.$type['id'].'] = new Array();
            messages['.$mail['id'].']['.$type['id'].'][\'message\'] = \''.$message.'\';
            messages['.$mail['id'].']['.$type['id'].'][\'sujet\'] = \''.$sujet.'\';
            messages['.$mail['id'].']['.$type['id'].'][\'titre\'] = \''.$titre.'\';

                ';

        }
    }
        ?>

        var message = messages[dest][mail]['message'];
        var sujet = messages[dest][mail]['sujet'];
        var titre = messages[dest][mail]['titre'];
        $('#message').val(message.replace(/\\/g, '')).blur();
        //document.getElementById('message').value = message.replace(/\\/g, '');
        document.getElementById('sujet').value = sujet.replace(/\\/g, '');
        document.getElementById('titre').value = titre.replace(/\\/g, '');
    }

</script>
<!--id=dialog_form-->


<?php
$modal_mail = new Modal($titre_dialog, $id_dialog)
?>

<div  <?php echo $style;?> id="<?php echo $id_dialog;?>"  title="<?php echo $titre_dialog;?>">
    <form action="" class="full validate" method="post">

        <?php
        if($write_mail)
        {
            ?>
            <div class="row">
                <label for="mail">
                    <strong>Destinataire</strong>
                </label>
                <div>
                    <input class="required" value="<?php echo $emails;?>" type=mail name=mail id=mail />
                </div>
            </div>
            <?php
        }

        if(!empty($liste_mails))
        {
            ?>

            <div class="row">
                <label for="mail">
                    <strong>Destinataire</strong>
                </label>
                <div><input type="hidden" name="mail" id="mail" /><br />
                    <select name="mail_liste" id="mail_liste" onchange="setDestMail(this.value);">
                        <option value="">--</option>
                        <?php
                        foreach ($liste_mails AS $mail)
                        {
                            if($mail['mail']!= '')
                            {
                                ?>
                                <option value="<?php echo $mail['id'];?>_<?php echo $mail['mail'];?>" ><?php echo $mail['nom'].' ('.$mail['mail'];?>)</option>
                                <?php
                            }
                            else
                            {
                                ?>
                                <option value="" disabled><?php echo $mail['nom'];?> (Aucune adresse e-mail)</option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <?php
        }
        if($liste_messages)
        {
            ?>
            <div class="row">
                <label for="text">
                    <strong>Liste</strong>
                </label>
                <div>
                    <select name="liste_messages" id="liste_messages" onchange="setMail(this.value);" >
                        <option value="">--</option>
                        <?php
                        foreach ($mails_types AS $mail)
                        {
                            ?>
                            <option value="<?php echo $mail['id'];?>"><?php echo $mail['nom'];?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
            <?php
        }
        ?>
        <div class="row">
            <label for="sujet">
                <strong>Sujet</strong>
            </label>
            <div>
                <input class="required" type=text name=sujet id=sujet value="<?php echo $sujet;?>" />
            </div>
        </div>
        <div class="row">
            <label for="titre">
                <strong>Titre</strong>
            </label>
            <div>
                <input class="required" type=text name=titre id=titre value="<?php echo $titre_m;?>"/>
            </div>
        </div>
        <div class="row">
            <label for="text">
                <strong>Texte</strong>
            </label>
            <div>
                <textarea rows=5 cols="5" name="message" id="message" ><?php echo $message;?></textarea>
            </div>
        </div>

    </form>
    <?php if($dialog)
    {
        ?>

        <div class="actions">
            <div class="left">
                <button class="grey cancel">Annuler</button>
            </div>
            <div class="right">
                <button class="submit" onclick="">Envoyer</button>
            </div>
        </div>
        <?php
    }
    ?>
</div>
