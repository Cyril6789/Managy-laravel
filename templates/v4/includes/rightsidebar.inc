<!-- BEGIN QUICK SIDEBAR -->
<a href="javascript:;" class="page-quick-sidebar-toggler">
    <i class="icon-login"></i>
</a>
<div class="page-quick-sidebar-wrapper" data-close-on-body-click="false">
    <div class="page-quick-sidebar">
        <ul class="nav nav-tabs">
            <li class="active">
                <a href="javascript:;" data-target="#quick_sidebar_tab_1" data-toggle="tab"> Tchat
                    <span class="badge badge-danger unread_chat"></span>
                </a>
            </li>
            <?php
            if(right('logs', 1)) {
                ?>
                <li>
                    <a href="javascript:;" data-target="#quick_sidebar_tab_2" data-toggle="tab"> Logs
                        <span class="badge badge-success"></span>
                    </a>
                </li>
                <?php
            }
            ?>

        </ul>
        <div class="tab-content">
            <div class="tab-pane active page-quick-sidebar-chat" id="quick_sidebar_tab_1">
                <div class="page-quick-sidebar-chat-users panel-collabos" data-rail-color="#ddd" data-wrapper-class="page-quick-sidebar-list">
                    <h3 class="list-heading">Staff</h3>
                    <?php



                    $staff = new GetInfosStaff($_SESSION['id']);
                    $prenom = new Hidden('prenom', 'prenom_chat');
                    $prenom->setValue($staff->GetPrenom());
                    $nom = new Hidden('nom', 'nom_chat');
                    $nom->setValue($staff->GetNom());
                    echo $nom.$prenom;

                    ?>
                    <ul class="media-list list-items" id="list_collabos">
                    </ul>
                    <h3 class="list-heading">Clients</h3>
                </div>
                <div class="page-quick-sidebar-item">
                    <div class="page-quick-sidebar-chat-user">
                        <div class="page-quick-sidebar-nav">
                            <a href="javascript:;" class="page-quick-sidebar-back-to-list">
                                <i class="icon-arrow-left"></i>Retour</a>
                        </div>
                        <div class="page-quick-sidebar-chat-user-messages " id="chat_content" style="overflow-y: scroll;">

                        </div>
                        <div class="page-quick-sidebar-chat-user-form">
                            <div class="input-group">
                                <input type="text" class="form-control"  id="input_chat" placeholder="Ecrivez votre message ici...">
                                <div class="input-group-btn">
                                    <button type="button" class="btn green">
                                        <i class="icon-paper-clip"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            if(right('logs', 1)) {
                ?>
                <div class="tab-pane page-quick-sidebar-alerts" id="quick_sidebar_tab_2">
                    <div class="page-quick-sidebar-alerts-list">
                        <ul class="feeds list-items">
                            <?php
                            foreach ($tab_logs AS $log)
                            {
                                if($log['login'])
                                    $icon = 'sign-in';
                                else{
                                    if($log['id_inter'])
                                        $icon = 'wrench';
                                    else
                                    {
                                        if($log['id_client'])
                                            $icon = 'user';
                                        else
                                            $icon = 'bullhorn';
                                    }
                                }

                                $staff = new GetInfosStaff($log['id_staff']);


                                ?>
                                <li>
                                    <div class="col1">
                                        <div class="cont">
                                            <div class="cont-col1">
                                                <div class="label label-sm label-info">
                                                    <?php echo new Font($icon);?>
                                                </div>
                                            </div>
                                            <div class="cont-col2">
                                                <div class="desc"> <?php echo $staff->GetPrenom().' '.$log['texte'];?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col2">
                                        <div class="date"> <?php echo parse_date($log['time']);?> </div>
                                    </div>
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
    </div>
</div>
<!-- END QUICK SIDEBAR -->
</div>
<!-- END CONTAINER -->