<?php session_start();


$dropbox = new Dropbox();
if($dropbox->isAccessible())
{
    echo 'Vous êtes connecté à Dropbox avec le compte <b>'.$dropbox->getInfoAccount()->email.'</b>.<br />
    Vous pouvez <a href="?action=deco">vous déconnecter ici</a>';
}
else
{
    echo 'Vous n\'êtes pas connecté à Dropbox.<br />
    <a href="'.$dropbox->GetUrlToDropboxConnect().'">Cliquez ici pour vous connecter</a>';
}

?> 