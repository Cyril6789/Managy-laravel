/**
 * Created by Cyril on 10/03/2016.
 */
"use strict";


function changeStatus(status)
{
    $('#img-status-chat').attr("src", "./templates/mango/img/icons/packs/fugue/16x16/"+status+".png");

    $.ajax({
        url : './ajax/change_status_chat.php',
        type : 'GET',
        data : 'status='+ status
    });

}

function select_conversation(id_staff)
{

    $('#chat_content').html('<img src="./img/loading_spinner.gif" width="45px"/>');
    $.ajax({
        url : './ajax/select_conversation_chat.php',
        type : 'GET',
        data : 'id_staff='+ id_staff
    });
}

function setConversationName(name)
{
    $('#panel_conversation-name').html(name);
}

function load_conversation()
{
    $.ajax({
        url : './ajax/load_conversation_chat.php',
        type : 'GET',
        dataType : 'html',
        complete : function(resultat, statut){
            $('#chat_content').html(resultat.responseText);
        }
    });

}

function getStatus()
{
    $.ajax({
        url : './ajax/get_status.php',
        type : 'GET',
        dataType : 'html',
        complete : function(resultat, statut){
            $('#list_collabos').html(resultat.responseText);
        }
    });
}

function countUnread()
{
    $.ajax({
        url : './ajax/count_unread_chat.php',
        type : 'GET',
        dataType : 'html',
        complete : function(resultat, statut)
        {
            var n = resultat.responseText;
            var title = document.title
            $('#unread_chat').html(n);

            title = title.replace(/\(\d\)/g, '')
            //title = title.replace('()', '')
            if(n > 0)
                document.title = '('+n+') '+title;
            else
                document.title = title;
        }
    });
}

function chat()
{
    countUnread();

    if($( "#body_chat_panel" ).is( ":visible" ))
    {
        getStatus();

        $.ajax({
            url : './ajax/get_last_message_id.php',
            type : 'GET',
            dataType : 'html',
            complete : function(resultat, statut){
                var new_last_id = resultat.responseText;
                var old_last_id = $('#chat_content').children('li').last().attr('id');

                if($.trim(new_last_id) != $.trim(old_last_id))
                {
                    load_conversation();
                    $('#panel-body').scrollTop($('#panel-body')[0].scrollHeight);
                    setTimeout(function(){
                        $('#panel-body').scrollTop($('#panel-body')[0].scrollHeight);
                    }, 1000);

                }
            }
        });

    }

    setTimeout(chat,1000);
}

$(document).ready(function(){


    $('#input_chat').keypress(function (e){

        if(e.which == 13)
        {
            var message = $('#input_chat').val();

            $.ajax({
                url: './ajax/send_message_chat.php',
                type: 'POST',
                data: 'message=' + message,
            });

            $('#chat_content').append('<li class="right clearfix" id="msg-0"><span class="chat-img pull-right"><img src="http://placehold.it/50/55C1E7/fff&text=' + $('#prenom_chat').val()[0] + '' + $('#nom_chat').val()[0] + '" alt="User Avatar" class="img-square" /></span><div class="chat-body clearfix"><div class="header"><strong class="primary-font">' + $('#prenom_chat').val() + ' ' + $('#nom_chat').val() + '</strong> <small class="pull-right text-muted"><span class="glyphicon glyphicon-time"></span>À l\'instant</small></div><p>' + message + '</p></div></li>');
            $('#panel-body').scrollTop($('#panel-body')[0].scrollHeight);

            $('#input_chat').val('');
            $('#input_char').focus();
        }

    });

    $('#collapse_chat_button').click(function(){
        if($( "#body_chat_panel" ).is( ":visible" ))
            var status = 'closed';
        else
            var status = 'open';

        $.ajax({
            url : './ajax/change_collapse_chat.php',
            type : 'GET',
            data : 'status='+ status
        });
    });

    chat('');

});