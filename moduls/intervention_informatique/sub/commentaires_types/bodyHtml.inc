<?php session_start();

    if(right('inter', 12))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/sub/commentaires_types.php'))
        {

            $commentaires_types = new DatabaseWorker('commentaires_types');
            $commentaires_types->displayedFields(Array('titre','texte'));
            $commentaires_types->labelsDisplayedFields(Array('Titre du commentaire type', 'Commentaire'));
            $commentaires_types->activeModify('', 'ModifyCommentaireType');
            $commentaires_types->activateAdd('', 'AddCommentaireType');
            $commentaires_types->activeDelete('', 'DeleteCommentaireType');



            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/sub/commentaires_types.php');
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