<?php session_start();

    if(right('inter', 15))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/sub/materiels_ajoutes_types.php'))
        {

            $materiel = new DatabaseWorker('materiels_ajoutes_types');
            $materiel->setWidget('Listes des matéirels ajoutés types');
            $materiel->displayedFields(Array('nom', 'texte'));
            $materiel->labelsDisplayedFields(Array('Titre', 'Contenu'));
            $materiel->activateAdd('./intervention-materiels_ajoutes_types', 'MaterielAjouteTypeAdd');
            $materiel->activeDelete('./intervention-materiels_ajoutes_types', 'MaterielAjouteTypeDelete');
            $materiel->activeModify('./intervention-materiels_ajoutes_types', 'MaterielAjouteTypeModify');

            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/sub/materiels_ajoutes_types.php');
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