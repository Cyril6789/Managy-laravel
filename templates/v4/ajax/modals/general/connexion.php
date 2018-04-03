<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 12/08/2017
 * Time: 15:38
 */



if($_POST['title'])
    $modal = new jsonModal($_POST['title']);
else
    $modal = new jsonModal('Connexion');

$modal->hideButtons();
$modal->width('30%');

$connexion = new FormLayout('Entrez vos identifiants Managy');
$connexion->setFormControls('login_form_modal', 'login.html');
$danger = new Danger('Login / mot de passe incorrect', false);
$connexion->addLine('', $danger, false, 'error_line', 'display:none;');
$login = new Text('username');
$connexion->addLine('Utilisateur', $login);
$password = new Password('password');
$connexion->addLine('Mot de passe', $password);
$button = new Button('Connexion', 'javascript:void;');
$button->setClasse('btn-primary');
$button->onClick('login();');
$connexion->addLine('', $button);

$js = '
        <script>
            function login()
            {     
                 $.ajax({
                    url: \'./ajax/login.php\',
                    type: \'GET\',
                    data: \'username=\'+$("#username").val()+\'&password=\'+$("#password").val(),
                    dataType: \'html\',
                    complete: function (resultat, statut) {
                        if(resultat.responseText == "false")
                            $(\'#error_line\').show(\'slow\');
                        else ';
                if($_POST['close_modal'])
                    $js .= '
                        $(\'#general_connexion\').modal(\'hide\');
                    ';
                else
                        $js .=
                            $id_modal.'_modal_load();
                        ';

$js .= '
                    }
                });
            }
                
        </script>';

$modal->content($connexion.$js);
echo $modal;