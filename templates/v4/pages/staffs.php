<?php session_start();


//echo $gerant;


if(FREE_ACCESS)
    $info = new Info('Nous utilisons un système de licences pour les collaborateurs. Vous devez associer une licence à chacun de vos collaborateurs, sinon son accès ne sera pas valide. Nombre de licence gratuites disponibles : <strong>&infin;</strong>', false);
else
    $info = new Info('Nous utilisons un système de licences pour les collaborateurs. Vous devez associer une licence à chacun de vos collaborateurs, sinon son accès ne sera pas valide. Vous disposez de '.$max_licence.' licences gratuites sur votre abonnement. Les autres vous seront facturées '.$prix_staff_suppl.'€ HT/mois. Il vous faudra tout de même créer les liences gratuites pour les associer à un collaborateur.', false);
$col = new Col();
$col->setContent($info);

//echo $col;

$warning = new Warning('Il vous reste '.$reste_collabo.' accès sur '.$nb_licences.' pour les collaborateurs', false);
$col_gauche = new col(12);
$col_gauche->setContent($warning.$staffs);

if(FREE_ACCESS)
    $warning = new Warning('Il vous reste &infin; licences sur &infin; incluses à l\'abonnement', false);
else
    $warning = new Warning('Il vous reste '.$reste_licence.' licences sur '.$max_licence.' incluses à l\'abonnement', false);
$col_droite = new Col(12);
$col_droite->setContent($warning.$licences);

//echo $col_gauche.$col_droite;
/*if(FREE_ACCESS)
    echo new Row($staffs);
else*/
    echo  new Row($gerant.$col.$col_gauche.$col_droite);
