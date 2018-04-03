<?php session_start();


?>
<a href="javascript:;" class="nav-link nav-toggle">
    <i class="fa fa-<?php echo $icone;?>"></i>
    <span class="title"><?php echo $type;?></span>
    <span class="selected"></span>
</a>
<ul class="sub-menu">
<?php



foreach($result AS $r)
    {
        ?>
        <li class="nav-item start open active">
            <a href="<?php echo $r['href'];?>" class="nav-link">
                   <?php echo $r['title'];?>
            </a>
            <ul class="sub-menu">
                <li class="nav-item">
                        <span class="nav-link">
                            <?php echo $r['sub'];?>
                        </span>
                </li>
            </ul>
            <?php //echo $r['sub'];?>
        </li>
        <?php
    }

?>
</ul>