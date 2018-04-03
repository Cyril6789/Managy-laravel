<?php session_start();
function scan($dossier)
{
    $texte=0;
    $Directory = $dossier;
    $tab_exclu = Array('.', '..', '_less', 'php', 'stammtec', 'landing_old', 'mango', 'colis', 'v4', 'bootstrap', 'PhpMailer', 'data', 'letters', 'landing', 'LibPdf', 'js', 'Photos_immo', 'sellsy', 'images', 'images_maj', 'backup_bdd', 'extract',  'dropbox_files', 'files', 'css', 'ressourceshtml', 'fonts', 'img', 'extras', 'virgin', '_virgin', 'mail', 'cache.manifest', 'mysql.class.php', 'GoogleMapAPI.class.php', 'assets', 'plugins');
    $MyDirectory = opendir($Directory);
    while($Entry = @readdir($MyDirectory))
    {
        if (!in_array($Entry, $tab_exclu))
        {
            if ($dossier == './')
                $fichier = './' . $Entry;
            else
                $fichier = $dossier . '/' . $Entry;

            if (is_dir($fichier))
            {
                echo '
    <li>'.$Entry.'
        <ul>';
                $texte += scan($fichier);
                echo '
        </ul>
    </li>';

            } else {
                $f = fopen($fichier, 'r');

                if (filesize($fichier)) {
                    $cf = fread($f, filesize($fichier));
                    $texte += substr_count($cf, "\n");
                    $texte++;

                }
            }
        }
    }
    
    return $texte;
}

echo '
<ul>
    '.scan('./') .' lignes au total
</ul>';

?>