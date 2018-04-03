<?php session_start();

    if(right('admin', -1))
    {
        if(is_file('./templates/'.TEMPLATE_NAME.'/sub/notifys_mail.php'))
        {
            
            $notify = new DatabaseWorker('notifications_mail', false);
            $notify->setWidget('Tableau des notifications mail', 6);
            $notify->displayedFields(Array('id', 'nom', 'sujet', 'titre', 'message', 'id_categorie'));
            $notify->labelsDisplayedFields(Array('Numéro', 'Titre de la notification', 'Sujet du mail', 'Titre du mail', 'Corps du message', 'Catégorie'));
            $notify->innerJoin('categories_notifications_mail', 'id_categorie', 'nom', false);
            $notify->noModifyFields('id');
            $notify->activateAdd('', 'addNotifyMail');
            $notify->activeModify('', 'modifyNotifyMail');
            $notify->activeDelete('', 'delterNotifyMail');


            $cat_notify = new DatabaseWorker('categories_notifications_mail', false);
            $cat_notify->setWidget('Tableau des catégories de notifications mail', 6);
            $cat_notify->displayedFields('nom');
            $cat_notify->labelsDisplayedFields('Nom de la catégorie');
            $cat_notify->activateAdd();
            $cat_notify->activeModify();
            $cat_notify->activeDelete();

            
            ob_start();
            include('./templates/'.TEMPLATE_NAME.'/sub/notifys_mail.php');
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