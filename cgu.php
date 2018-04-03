<?php
include('./classes/mysql.class.php');
include('./landing/includes/head.php');
?>

<!-- ===== Start of Page Title ===== -->
<section class="page-title ptb50 overlay-black">
    <div class="container">
        <br />
        <h2 class="uppercase">Conditions Générales d'Utilisation</h2>
    </div>
</section>
<!-- ===== End of Page Title ===== -->


<!-- ===== Start of Blog Post Main Section ===== -->
<section class="blog-post pt80 pb60">
    <div class="container">
        <br />
        <?php

        $db = new MySQL();
        $sql = 'SELECT texte FROM cgu ORDER BY id DESC LIMIT 1';
        $db->Query($sql);

        $row = $db->Row();

        $texte = str_replace('<h2>', '<div class="col-md-12 post-title"><h2 class="uppercase">', $row->texte);
        $texte = str_replace('</h2>', '</h2></div>', $texte);

        echo $texte;


        ?>

    </div>
</section>
<!-- ===== End of Blog Post Main Section ===== -->





<?php
include('./landing/includes/foot.php');
?>
