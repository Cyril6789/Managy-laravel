<script>
        $$.ready(function() {
    // Dialog with Form Elements
 
    $("#<?php echo $id_dialog;?>").dialog({
        autoOpen: false,
        modal: true,
        width: 600,
        open: function() {
            $(this).parent().css('overflow', 'visible');
            $$.utils.forms.resize();
            $(this).find('textarea').cleditor({
                width: '100%',
                height: '250px',
                bodyStyle: 'margin: 10px; font: 12px Arial,Verdana; cursor:text',
                useCSS: true
            });
        }
    }).find('button.submit').click(function() {
        var $el = $(this).parents('.ui-dialog-content');
        if ($el.validate().form()) {
            $el.find('form')[0].submit();
            $el.dialog('close');
        }
    }).end().find('button.cancel').click(function() {
        var $el = $(this).parents('.ui-dialog-content');
        $el.find('form')[0].reset();
        $el.dialog('close');;
    });
 
    $("#<?php echo $id_button;?>").click(function() {
        $("#<?php echo $id_dialog;?>").dialog("open");
        return false;
    });
});


</script>