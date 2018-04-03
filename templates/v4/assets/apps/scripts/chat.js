/**
 * Created by Cyril on 10/03/2016.
 */
"use strict";
var QuickSidebar = function () {

    var wrapper = $('.page-quick-sidebar-wrapper');
    var wrapperChat = wrapper.find('.page-quick-sidebar-chat');
    // Handles quick sidebar toggler
    var handleQuickSidebarToggler = function () {
        // quick sidebar toggler
        $('.dropdown-quick-sidebar-toggler a, .page-quick-sidebar-toggler, .quick-sidebar-toggler').click(function (e) {
            $('body').toggleClass('page-quick-sidebar-open');
        });
    };

    // Handles quick sidebar chats
    var handleQuickSidebarChat = function () {


        wrapper.find('.page-quick-sidebar-chat-users .media-list > .media').click(function () {
            wrapperChat.addClass("page-quick-sidebar-content-item-shown");
            select_conversation($(this).attr('id'));
        });

        wrapper.find('.page-quick-sidebar-chat-user .page-quick-sidebar-back-to-list').click(function () {
            wrapperChat.removeClass("page-quick-sidebar-content-item-shown");
            select_conversation('0');
        });

        var handleChatMessagePost = function (e) {
            e.preventDefault();

            var chatContainer = wrapperChat.find(".page-quick-sidebar-chat-user-messages");
            var input = wrapperChat.find('.page-quick-sidebar-chat-user-form .form-control');

            var text = input.val();
            if (text.length === 0) {
                return;
            }

            var preparePost = function(dir, time, name, avatar, message) {
                var tpl = '';
                tpl += '<div class="post '+ dir +'">';
                tpl += '<img class="avatar" alt="" src="' + Layout.getLayoutImgPath() + avatar +'.jpg"/>';
                tpl += '<div class="message">';
                tpl += '<span class="arrow"></span>';
                tpl += '<a href="#" class="name">Bob Nilson</a>&nbsp;';
                tpl += '<span class="datetime">' + time + '</span>';
                tpl += '<span class="body">';
                tpl += message;
                tpl += '</span>';
                tpl += '</div>';
                tpl += '</div>';

                return tpl;
            };

            // handle post
            var time = new Date();
            var message = preparePost('out', (time.getHours() + ':' + time.getMinutes()), "Bob Nilson", 'avatar3', text);
            message = $(message);
            chatContainer.append(message);

            chatContainer.slimScroll({
                scrollTo: '1000000px'
            });

            input.val("");

            // simulate reply
            setTimeout(function(){
                var time = new Date();
                var message = preparePost('in', (time.getHours() + ':' + time.getMinutes()), "Ella Wong", 'avatar2', 'Lorem ipsum doloriam nibh...');
                message = $(message);
                chatContainer.append(message);

                chatContainer.slimScroll({
                    scrollTo: '1000000px'
                });
            }, 3000);
        };

        wrapperChat.find('.page-quick-sidebar-chat-user-form .btn').click(handleChatMessagePost);
        /*wrapperChat.find('.page-quick-sidebar-chat-user-form .form-control').keypress(function (e) {
            if (e.which == 13) {
                var message = $('#input_chat').val();
                $.ajax({
                    url: './ajax/send_message_chat.php',
                    type: 'POST',
                    data: 'message=' + message,
                });
                //

                return false;
            }
        });*/
    };

    // Handles quick sidebar tasks
    var handleQuickSidebarAlerts = function () {
        var wrapper = $('.page-quick-sidebar-wrapper');

        var initAlertsSlimScroll = function () {
            var alertList = wrapper.find('.page-quick-sidebar-alerts-list');
            var alertListHeight;

            alertListHeight = wrapper.height() - 80 - wrapper.find('.nav-justified > .nav-tabs').outerHeight();

            // alerts list
            App.destroySlimScroll(alertList);
            alertList.attr("data-height", alertListHeight);
            App.initSlimScroll(alertList);
        };

        initAlertsSlimScroll();
        App.addResizeHandler(initAlertsSlimScroll); // reinitialize on window resize
    };

    // Handles quick sidebar settings
    var handleQuickSidebarSettings = function () {
        var wrapper = $('.page-quick-sidebar-wrapper');

        var initSettingsSlimScroll = function () {
            var settingsList = wrapper.find('.page-quick-sidebar-settings-list');
            var settingsListHeight;

            settingsListHeight = wrapper.height() - 80 - wrapper.find('.nav-justified > .nav-tabs').outerHeight();

            // alerts list
            App.destroySlimScroll(settingsList);
            settingsList.attr("data-height", settingsListHeight);
            App.initSlimScroll(settingsList);
        };

        initSettingsSlimScroll();
        App.addResizeHandler(initSettingsSlimScroll); // reinitialize on window resize
    };

    return {

        init: function () {
            //layout handlers
            handleQuickSidebarToggler(); // handles quick sidebar's toggler
            handleQuickSidebarChat(); // handles quick sidebar's chats
            handleQuickSidebarAlerts(); // handles quick sidebar's alerts
            handleQuickSidebarSettings(); // handles quick sidebar's setting
        }
    };

}();

function changeStatus(status)
{
    //$('#img-status-chat').attr("src", "./templates/mango/img/icons/packs/fugue/16x16/"+status+".png");

    $.ajax({
        url : './ajax/change_status_chat.php',
        type : 'GET',
        data : 'status='+ status
    });

}

function select_conversation(id_staff)
{

    //$('#chat_content').html('<img src="./img/loading_spinner.gif" width="45px"/>');
    $.ajax({
        url : './ajax/select_conversation_chat.php',
        type : 'GET',
        data : 'id_staff='+ id_staff
    });
}

function setConversationName(name)
{
    //$('#panel_conversation-name').html(name);
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

            var res = resultat.responseText;
            res = res.replace(/\n|\r|(\n\r)/g, "" );
            var res2 = res.replace(/ /gi, "" );
            res2 = res2.replace(/\"\/>/gi, "\">" );



            var old = $('#list_collabos').html();
            old = old.replace(/\n|\r|(\n\r)/g, "" );
            old = old.replace(/ /gi, "" );
            old = old.replace(/&amp;/gi, "&" );

            if(res2 != old)
            {
                $('#list_collabos').html(res);
                QuickSidebar.init();
            }
        }
    });

}

function countUnread()
{
    $.ajax({
        url : './ajax/count_unread_chat.php',
        type : 'GET',
        dataType : 'html',
        complete : function(resultat, statut){


            var n = resultat.responseText;
            var title = document.title

            var old = $('.unread_chat').html();


                if(n != old) {
                    $('.unread_chat').html(n);


                    title = title.replace(/\(\d\)/g, '')
                    //title = title.replace('()', '')
                    if (n > 0)
                        document.title = '(' + n + ') ' + title;
                    else
                        document.title = title;
                }

        }
    });
}


function chat()
{
    countUnread();


    getStatus();



    $.ajax({
        url : './ajax/get_last_message_id.php',
        type : 'GET',
        dataType : 'html',
        complete : function(resultat, statut){
            var new_last_id = resultat.responseText;
            var old_last_id = $('#chat_content').children('.post').last().attr('id');
            var wrapper = $('.page-quick-sidebar-wrapper');
            var wrapperChat = wrapper.find('.page-quick-sidebar-chat');
            var chatContainer = wrapperChat.find(".page-quick-sidebar-chat-user-messages");

            if($.trim(new_last_id) != $.trim(old_last_id))
            {
                chatContainer.slimScroll({
                    scrollTo: '1000000px'
                });
            }
            load_conversation();
        }
    });



   setTimeout(chat,1000);
}

function preparePost(dir, time, name, avatar, message) {
    var tpl = '';
    tpl += '<div class="post '+ dir +'">';
    tpl += '<img class="avatar" alt="" src="https://placehold.it/50/55C1E7/fff&text='+ avatar +'"/>';
    tpl += '<div class="message">';
    tpl += '<span class="arrow"></span>';
    tpl += '<a href="#" class="name">'+name+'</a>&nbsp;';
    tpl += '<span class="datetime">' + time + '</span>';
    tpl += '<span class="body">';
    tpl += message;
    tpl += '</span>';
    tpl += '</div>';
    tpl += '</div>';

    return tpl;
};

$(document).ready(function(){

    select_conversation(0);

    var wrapper = $('.page-quick-sidebar-wrapper');
    var wrapperChat = wrapper.find('.page-quick-sidebar-chat');

    var initChatSlimScroll = function () {
        var chatUsers = wrapper.find('.page-quick-sidebar-chat-users');
        var chatUsersHeight;

        chatUsersHeight = wrapper.height() - wrapper.find('.nav-tabs').outerHeight(true);

        // chat user list
        App.destroySlimScroll(chatUsers);
        chatUsers.attr("data-height", chatUsersHeight);
        App.initSlimScroll(chatUsers);

        var chatMessages = wrapperChat.find('.page-quick-sidebar-chat-user-messages');
        var chatMessagesHeight = chatUsersHeight - wrapperChat.find('.page-quick-sidebar-chat-user-form').outerHeight(true);
        chatMessagesHeight = chatMessagesHeight - wrapperChat.find('.page-quick-sidebar-nav').outerHeight(true);

        // user chat messages
        App.destroySlimScroll(chatMessages);
        chatMessages.attr("data-height", chatMessagesHeight);
        App.initSlimScroll(chatMessages);
    };

    initChatSlimScroll();
    App.addResizeHandler(initChatSlimScroll); // reinitialize on window resize


    $('#input_chat').keypress(function (e){

        if(e.which == 13)
        {
            e.preventDefault();
            var wrapper = $('.page-quick-sidebar-wrapper');
            var wrapperChat = wrapper.find('.page-quick-sidebar-chat');
            var chatContainer = wrapperChat.find(".page-quick-sidebar-chat-user-messages");
            var input = wrapperChat.find('.page-quick-sidebar-chat-user-form .form-control');

            var text = input.val();
            if (text.length === 0) {
                return;
            }


            // handle post
            var time = new Date();
            var message = preparePost('out', (time.getHours() + ':' + time.getMinutes()), "Utilisateur", 'XX', text);
            message = $(message);
            chatContainer.append(message);

            chatContainer.slimScroll({
                scrollTo: '1000000px'
            });

            input.val("");

            $.ajax({
                url: './ajax/send_message_chat.php',
                type: 'POST',
                data: 'message=' + text,
            });



           /* $('#chat_content').append('<li class="right clearfix" id="msg-0"><span class="chat-img pull-right"><img src="http://placehold.it/50/55C1E7/fff&text=' + $('#prenom_chat').val()[0] + '' + $('#nom_chat').val()[0] + '" alt="User Avatar" class="img-square" /></span><div class="chat-body clearfix"><div class="header"><strong class="primary-font">' + $('#prenom_chat').val() + ' ' + $('#nom_chat').val() + '</strong> <small class="pull-right text-muted"><span class="glyphicon glyphicon-time"></span>À l\'instant</small></div><p>' + message + '</p></div></li>');
            $('#panel-body').scrollTop($('#panel-body')[0].scrollHeight);

            $('#input_chat').val('');
            $('#input_char').focus();*/
        }
    });


    chat('');

});