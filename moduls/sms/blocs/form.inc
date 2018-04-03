<?php session_start();

if($dialog)
    $style = 'style="display: none;"';
else
    $style = '';

?>


<script language="javascript">
        function setDestSms(dest)
        {
                var tab = dest.split('_');
                document.getElementById('num').value = tab[1];

                if(tab[0] > 0)
                {
                        var sms = document.getElementById('liste').value;
                        if(sms > 0)
                                setMessageSms(tab[0], sms)
                }
                else
                        document.getElementById('texte').value = '';
        }

        function setSms(sms)
        {

                if(sms > 0)
                {
                        var dest = document.getElementById('sms').value;
                        var tab = dest.split('_');
                        if(tab[0] > 0)
                                setMessageSms(tab[0], sms)
                }
                else
                        document.getElementById('texte').value = '';
        }

        function setMessageSms(dest, sms)
        {
            var messages = new Array();
            <?php
            
            
         
        foreach ($liste_smss AS $sms)
        {
            global $db;
            $db->Query('SELECT titre, nom, prenom FROM clients WHERE id="'.$sms['id'].'"  AND compte_principal="'.$_SESSION['compte_principal'].'" ');
            $row = $db->Row();
            $titre_c = addslashes($row->titre);
            $nom = addslashes($row->nom);
            $prenom = addslashes($row->prenom);
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
            echo '
                messages['.$sms['id'].'] = new Array();';
            
            foreach($sms_types AS $type)
            {
                $message = addslashes($type['message']);
                $message = str_replace('%id_inter%', $id_inter, $message);
                $message = str_replace('%titre%', $titre_c, $message);
                $message = str_replace('%nom%', $nom, $message);
                $message = str_replace('%prenom%', $prenom, $message);
                $message = addslashes(str_replace('%ref_chantier%', $ref_chantier, $message));
                $message = str_replace('%nom_couleur%', $nom_couleur, $message);
                $message = str_replace('%ral%', $ral, $message);
                echo '
                messages['.$sms['id'].']['.$type['id'].'] = \''.$message.'\'';
                
            }
        }
            ?>

                var message = messages[dest][sms];
                
                document.getElementById('texte').value = message.replace(/\\/g, '');
        }

</script>
<!--id=dialog_form-->

<div  <?php echo $style;?> id="<?php echo $id_dialog;?>"  title="<?php echo $titre;?>">
    <form action="" class="full validate" method="post">
        
                <?php
                    if($write_sms)
                    {
                        ?>
                        <div class="row">
                            <label for="sms">
                                    <strong>Destinataire</strong>
                            </label>
                            <div>
                                    <input class="required" class="MaskIntPhone" value="<?php echo $smss;?>" type=num name=num id=sms />
                            </div>
                        </div>
                        <?php
                    }
                    
                    if(!empty($liste_smss))
                    {
                        ?>
                        <input type="hidden" name="num" id="num" />
                        <div class="row">
                            <label for="sms">
                                    <strong>Destinataire</strong>
                            </label>
                            <div>
                                <select name="sms" id="sms" onchange="setDestSms(this.value);">
                                    <option value="">--</option>
                                    <?php
                                    foreach ($liste_smss AS $sms)
                                    {
                                        if($sms['sms']!= '')
                                        {
                                            ?>
                                    <option value="<?php echo $sms['id'];?>_<?php echo $sms['sms'];?>" ><?php echo $sms['nom'].' ('.$sms['sms'];?>)</option>
                                            <?php
                                        }
                                        else
                                        {
                                            ?>
                                    <option value="" disabled><?php echo $sms['nom'];?> (Aucune adresse e-sms)</option>
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
                            <select name="liste" id="liste" onchange="setSms(this.value);" >
                                <option value="">--</option>
                                <?php                                                    
                                foreach ($sms_types AS $sms)
                                {
                                    ?>
                                <option value="<?php echo $sms['id'];?>"><?php echo $sms['titre'];?></option>
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
                        <label for="text">
                                <strong>Message</strong>
                        </label>
                        <div>
                            <textarea  rows="7" cols="5" name="texte" id="texte" id="text">
                            </textarea>
                        </div>
                </div>
                
        </form>
    <?php if($dialog)
    {
        ?>
    
        <div class="actions">
                <div class="left">
                        <button class="grey cancel">Cancel</button>
                </div>
                <div class="right">
                    <button class="submit" onclick="">Submit</button>
                </div>
        </div>
        <?php
    }
    ?>
</div>