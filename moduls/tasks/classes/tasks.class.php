<?php session_start();

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 13/09/2016
 * Time: 11:33
 */
class tasks
{
    private $sql;
    private $statut;
    private $db;

    public function __construct($id_staff_assignation='*')
    {
        $this->sql = 'SELECT * FROM taches WHERE compte_principal = "'.$_SESSION['compte_principal'].'" ';
        if($id_staff_assignation != '*')
            $this->sql .= ' AND id_staff_assignation = "'.$id_staff_assignation.'" ';
        $this->statut = Array();

        $this->db = new MySQL();
    }

    public function setOpen()
    {
        $this->statut[] = 'open';
    }

    public function setWork()
    {
        $this->statut[] = 'work';
    }

    public function setDone()
    {
        $this->statut[] = 'done';
    }

    public function setClosed()
    {
        $this->statut[] = 'closed';
    }

    public function Query()
    {
        $this->genereSQL();
        $this->db->Query($this->sql);
        if($this->db->Error()) {
            $erreur = new Danger('Erreur MySql : ' . $this->db->Error(), false);
            $info = new Info('Requetes SQL : ' . $this->sql, true);
            $err = new Col('12');
            $err->setContent($erreur);
            $inf = new Col('12');
            $inf->setContent($info);
            echo $err . $inf;
        }

        return $this->db->RowCount();

    }

    public function getNewTaskModal($id_add)
    {
        $modal = $this->getModal($id_add);
        $tab_modal['html'] = $modal->getModalHtml();
        $tab_modal['link'] = $modal->getAhref();
        return $tab_modal;
    }

    public function getTable($id_add)
    {
        $tab_tasks = Array();
        $i=0;
        while($row =$this->db->Row())
        {
            $tab_tasks[$i]['id'] = $row->id;
            $tab_tasks[$i]['titre'] = $row->titre;
            $tab_tasks[$i]['statut'] = $row->statut;
            $tab_tasks[$i]['id_staff_ouverture'] = $row->id_staff_ouverture;
            $tab_tasks[$i]['id_staff_assignation'] = $row->id_staff_assignation;
            $tab_tasks[$i]['pourcentage'] = $row->pourcentage;
            $tab_tasks[$i]['commentaire'] = $row->commentaire;
            $tab_tasks[$i]['time_ouverture'] = $row->time_ouverture;

            $progress = new ProgressBar($row->pourcentage, '100');
            if($row->statut == 'work')
                $progress->forceAnimate();
            $progress->small();
            $tab_tasks[$i]['progress'] = (string) $progress;
            $modal = $this->getModal($id_add, $row->id, $row->titre, $row->statut, $row->pourcentage, $row->id_staff_assignation, $row->commentaire);
            $tab_tasks[$i]['modal_html'] = $modal->getModalHtml();
            $tab_tasks[$i]['modal_link'] = $modal->getAhref();

            $i++;
        }

        return $tab_tasks;
    }

    private function getModal($id_add, $id='', $titre='', $statut='', $pourcentage='', $id_staff_assignation='', $commentaire='')
    {
        if($id)
        {
            $modal_tache = new Modal('Modifier la tâche "'.$titre.'"', 'task_'.$id_add.'_'.$id);
            $modal_tache-> setOnclickButton('Modifier', 'form_'.$id_add.'_'.$id.'.submit();');
        }
        else
        {
            $id = 'new';
            $modal_tache = new Modal('Créer une nouvelle tâche', 'task_'.$id_add.'_'.$id);
            $modal_tache-> setOnclickButton('Ajouter', 'form_'.$id_add.'_'.$id.'.submit();');
        }

        $form_modal = new FormLayout('Saisie');
        $form_modal->setFormControls('form_'.$id_add.'_'.$id);

        $titre_f = new Text('titre');
        $titre_f->setValue($titre);
        $form_modal->addLine('Titre de la tache', $titre_f);

        $statut_f = new Select('statut');
        $statut_f->setSelected($statut);
        $statut_f->withSearch();
        $statut_f->addOption('open', 'Ouverte');
        $statut_f->addOption('work', 'En cours');
        $statut_f->addOption('done', 'Terminée');
        $statut_f->addOption('closed', 'Fermée');
        $form_modal->addLine('Statut', $statut_f);

        $pourcentage_f = new Select('pourcentage');
        $pourcentage_f->setSelected($pourcentage);
        $pourcentage_f->withSearch();
        for($p=0; $p<=100; $p += 10)
            $pourcentage_f->addOption($p, $p.'%');
        $form_modal->addLine('Pourcentage', $pourcentage_f);

        $staffs = new Select('staff');
        $staffs->withSearch();
        if($id_staff_assignation)
            $staffs->setSelected($id_staff_assignation);
        else
            $staffs->setSelected($_SESSION['id']);


        $sql = 'SELECT s.id, s.nom, s.prenom
                    FROM staffs AS s
                    INNER JOIN comptes_principaux
                    ON (comptes_principaux.id = s.compte_principal)
                    LEFT JOIN licences_staffs AS ls
                    ON (s.licence = ls.id)
                    WHERE s.compte_principal="'.$_SESSION['compte_principal'].'"
                    AND comptes_principaux.bloque = "0"
                    AND
                    ( ls.date_fin > "'.time().'" OR ls.incluse = "1" OR s.gerant = "1")
                    ';
        $db=new MySQL();
        $db->Query($sql);

        while($row = $db->Row())
            $staffs->addOption($row->id, $row->prenom.' '.$row->nom);
        $form_modal->addLine('Assigné à', $staffs);

        $commentaire_f = new Textarea('commentaire');
        $commentaire_f->Wysiwyg();
        $commentaire_f->setValue($commentaire);

        $hidden = new Hidden('task_id');
        $hidden->setValue($id);

        $form_modal->addLine('Commentaire', $commentaire_f.$hidden);

        $modal_tache->setContent($form_modal);

        return $modal_tache;
    }


    private function genereSQL()
    {
        if(count($this->statut))
            if(count($this->statut) == 1)
                $this->sql .= ' AND statut="'.$this->statut[0].'"';
            else
            {
                $this->sql .= 'AND (';
                $i=1;
                foreach($this->statut AS $stat) {
                    $this->sql .= 'statut="' . $stat . '"';
                    if($i<count($this->statut))
                        $this->sql .= ' OR ';
                    $i++;
                }
                $this->sql .= ')';
            }


        $this->sql;
    }

}