<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content">
        <!-- BEGIN PAGE BAR -->
        <!-- BEGIN THEME PANEL -->

        <!-- END THEME PANEL -->
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li>
                    <a href="dashboard">Accueil</a>
                    <?php echo new Font('circle');?>
                </li>
                <li>
                    <?php echo $page_title;?>
                </li>
            </ul>

            <?php
            if((right('inter', 10) AND (AccesActivableModul('intervention_holtzmann'))) OR (AccesActivableModul('intervention_informatique') AND (right('inter', 20)  OR right('inter', 22))))
            {
                $alertes = 0;
                if (right('inter', 10) AND (AccesActivableModul('intervention_holtzmann')))
                    $alertes += $nombre_inter_a_solder;
                if (AccesActivableModul('intervention_informatique'))
                    $alertes += $nb_restitutions;
                if (AccesActivableModul('intervention_informatique') AND right('inter', 20))
                    $alertes += $nb_facturations;

                if ($alertes)
                    $badge = new Badge($alertes);
                else
                    $badge = '';
                ?>
                <div class="page-toolbar">
                    <div class="btn-group pull-right">
                        <button type="button" class="btn green btn-sm btn-outline dropdown-toggle"
                                data-toggle="dropdown"> Actions
                            <?php
                            echo new Font('angle-down');
                            echo $badge; ?>
                        </button>
                        <ul class="dropdown-menu pull-right" role="menu">
                            <?php

                            if (right('inter', 10) AND (AccesActivableModul('intervention_holtzmann'))) {
                                ?>
                                <li>
                                    <a href="intervention-a_solder">
                                        <?php echo new Font('file-text-o').$nombre_inter_a_solder; ?>
                                        interventions à solder</a>
                                </li>
                                <?php
                            }
                            if (AccesActivableModul('intervention_informatique')  AND right('inter', 22)) {
                                ?>
                                <li>
                                    <a href="intervention-restitution">
                                        <?php echo new Font('share').$nb_restitutions; ?> Restitutions
                                        client</a>
                                </li>
                                <?php
                            }
                            if (AccesActivableModul('intervention_informatique') AND right('inter', 20)) {
                                ?>
                                <li>
                                    <a href="intervention-facturation">
                                        <?php echo new Font('eur').$nb_facturations; ?> Facturations
                                        interventions</a>
                                </li>
                                <?php
                            }
                            if (right('stats', 1) AND (AccesActivableModul('stats') OR AccesActivableModul('stats_informatique'))) {
                                ?>
                                <li class="divider"></li>
                                <li>
                                    <a href="stats">
                                        <?php echo new Font('signal');?> Statistiques</a>
                                </li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div>
                </div>
                <?php
            }
            ?>


        </div>
        <!-- END PAGE BAR -->