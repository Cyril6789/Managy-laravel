/**********************

custom.js
=============

Author:  Gino Aliaj
Template: Cloudify - Web Hosting HTML Landing Page
Created: 22.09.2016
Updated: ---
Version: 1.0

Author URI: gnodesign.com
***************************/


(function ($) {

    "use strict";



    /*----------------------------------------------------
      LOADING PAGE
    ----------------------------------------------------*/
    $(window).on('load', function () {
        $(".loader").delay(2000).fadeOut(1000);
    });





    /*----------------------------------------------------
      STICKY HEADER
    ----------------------------------------------------*/
    $(window).on('scroll', function () {


        if ($('header').length > 0) {

            var window_height = $(this).scrollTop();

            //  Get Top Menu Height
            var topheaderheight = $('.top-header').height();

            if (window_height > topheaderheight) {

                // Check if Header is fixed or not
                if ($("header").hasClass('fixed')) {
                    $("header").addClass('navbar-fixed-top');
                }

            } else {
                $("header").removeClass("navbar-fixed-top");
            }
        }
    });





    /*----------------------------------------------------
       PUSH MENU
     ----------------------------------------------------*/

    (function ($) {

        $.fn.jPushMenu = function (customOptions) {
            var o = $.extend({}, $.fn.jPushMenu.defaultOptions, customOptions);

            /* add class to the body.*/

            $('body').addClass(o.bodyClass);
            $(this).addClass('jPushMenuBtn');
            $(this).on('click', function () {
                var target = '',
                    push_direction = '';


                if ($(this).is('.' + o.showLeftClass)) {
                    target = '.cbp-spmenu-left';
                    push_direction = 'toright';
                } else if ($(this).is('.' + o.showRightClass)) {
                    target = '.cbp-spmenu-right';
                    push_direction = 'toleft';
                } else if ($(this).is('.' + o.showTopClass)) {
                    target = '.cbp-spmenu-top';
                } else if ($(this).is('.' + o.showBottomClass)) {
                    target = '.cbp-spmenu-bottom';
                }


                $(this).toggleClass(o.activeClass);
                $(target).toggleClass(o.menuOpenClass);

                if ($(this).is('.' + o.pushBodyClass)) {
                    $('body').toggleClass('cbp-spmenu-push-' + push_direction);
                }

                /* disable all other button*/
                $('.jPushMenuBtn').not($(this)).toggleClass('disabled');

                return false;
            });
            var jPushMenu = {
                close: function (o) {
                    $('.jPushMenuBtn,body,.cbp-spmenu').removeClass('disabled active cbp-spmenu-open cbp-spmenu-push-toleft cbp-spmenu-push-toright');
                }
            }

            if (o.closeOnClickOutside) {
                $(document).on('click', function () {
                    jPushMenu.close();
                });

                $(document).on('click touchstart', function () {
                    jPushMenu.close();
                });

                $('.cbp-spmenu,.toggle-menu').on('click', function (e) {
                    e.stopPropagation();
                });

                $('.cbp-spmenu,.toggle-menu').on('click touchstart', function (e) {
                    e.stopPropagation();
                });
            }

            // On Click Link
            if (o.closeOnClickLink) {
                $('.cbp-spmenu a').on('click', function () {
                    jPushMenu.close();
                });
            }
        };

        /* in case you want to customize class name,
         *  do not directly edit here, use function parameter when call jPushMenu.
         */
        $.fn.jPushMenu.defaultOptions = {
            bodyClass: 'cbp-spmenu-push',
            activeClass: 'menu-active',
            showLeftClass: 'menu-left',
            showRightClass: 'menu-right',
            showTopClass: 'menu-top',
            showBottomClass: 'menu-bottom',
            menuOpenClass: 'cbp-spmenu-open',
            pushBodyClass: 'push-body',
            closeOnClickOutside: true,
            closeOnClickInside: true,
            closeOnClickLink: true
        };
    })(jQuery);

    //initilizer
    $(document).ready(function () {
        $('.toggle-menu').jPushMenu({
            closeOnClickLink: false
        });
    });





    /*----------------------------------------------------
      NAVIGATION SCROLL
    ----------------------------------------------------*/

    $(document).ready(function () {
        $('#main-nav .navbar-nav').onePageNav({
            currentClass: 'active',
            scrollThreshold: 0.5, // Adjust if Navigation highlights too early or too late
            scrollSpeed: 1500,
            changeHash: true,
            easing: 'easeInOutExpo'
        });
    });





    /*----------------------------------------------------
      LIVE CHAT FUNCTION
    ----------------------------------------------------*/
    $(document).ready(function () {
        //when button clicked open live chat
        $('.top-header .top-button a.customer-support').on('click', function () {
            $('.chat').slideToggle(300, 'swing');
            $('.live-chat').css('bottom', '0');
        });

        //when header clicked slide in/out the whole chat
        $('.live-chat .header').on('click', function () {
            $('.chat').slideToggle(300, 'swing');
            $('.chat-message-counter').fadeToggle(300, 'swing');
        });

        //when "chat close" button clicked fade out the whole chat section
        $('.chat-close').on('click', function () {
            $('.live-chat').css("bottom", "-100px");
        });
    });





    /*----------------------------------------------------
      LOGIN POP UP FUNCTION
    ----------------------------------------------------*/
    $(document).ready(function ($) {
        var $form_modal = $('.cd-user-modal'),
            $form_login = $form_modal.find('#cd-login'),
            $form_signup = $form_modal.find('#cd-signup'),
            $form_forgot_password = $form_modal.find('#cd-reset-password'),
            $form_modal_tab = $('.cd-switcher'),
            $tab_login = $form_modal_tab.children('li').eq(0).children('a'),
            $tab_signup = $form_modal_tab.children('li').eq(1).children('a'),
            $forgot_password_link = $form_login.find('p.cd-form-bottom-message a'),
            $back_to_login_link = $form_forgot_password.find('.cd-form-bottom-message a'),
            $main_nav = $('.top-button .login');

        //open modal

        $main_nav.on('click', function (event) {

            if ($(event.target).is($main_nav)) {
                // on mobile open the submenu
                $(this).children('').addClass('is-visible');
            } else {
                // on mobile close submenu
                $main_nav.children('').removeClass('is-visible');
                //show modal layer
                $form_modal.addClass('is-visible');
                //show the selected form
                ($(event.target).is('#modal_trigger')) ? login_selected(): login_page();

            }

        });

        //close modal
        $('.cd-user-modal').on('click', function (event) {
            if ($(event.target).is($form_modal) || $(event.target).is('.cd-close-form')) {
                $form_modal.removeClass('is-visible');
            }
        });
        //close modal when clicking the esc keyboard button
        $(document).on('keyup', function (event) {
            if (event.which == '27') {
                $form_modal.removeClass('is-visible');
            }
        });

        //switch from a tab to another
        $form_modal_tab.on('click', function (event) {
            event.preventDefault();
            ($(event.target).is($tab_login)) ? login_selected(): signup_selected();
        });

        //show forgot-password form 
        $forgot_password_link.on('click', function (event) {
            event.preventDefault();
            forgot_password_selected();
        });

        //back to login from the forgot-password form
        $back_to_login_link.on('click', function (event) {
            event.preventDefault();
            login_selected();
        });

        function login_selected() {
            $form_login.addClass('is-selected');
            $form_signup.removeClass('is-selected');
            $form_forgot_password.removeClass('is-selected');
            $tab_login.addClass('selected');
            $tab_signup.removeClass('selected');
        }

        function signup_selected() {
            $form_login.removeClass('is-selected');
            $form_signup.addClass('is-selected');
            $form_forgot_password.removeClass('is-selected');
            $tab_login.removeClass('selected');
            $tab_signup.addClass('selected');
        }

        function forgot_password_selected() {
            $form_login.removeClass('is-selected');
            $form_signup.removeClass('is-selected');
            $form_forgot_password.addClass('is-selected');
        }


        //IE9 placeholder fallback
        if (!Modernizr.input.placeholder) {
            $('[placeholder]').on('focus', function () {
                var input = $(this);
                if (input.val() == input.attr('placeholder')) {
                    input.val('');
                }
            }).blur(function () {
                var input = $(this);
                if (input.val() == '' || input.val() == input.attr('placeholder')) {
                    input.val(input.attr('placeholder'));
                }
            }).blur();
            $('[placeholder]').parents('form').on('submit', function () {
                $(this).find('[placeholder]').each(function () {
                    var input = $(this);
                    if (input.val() == input.attr('placeholder')) {
                        input.val('');
                    }
                })
            });
        }

    });
    
    
    
    
    
    /*----------------------------------------------------
      INITIALIZE WOW
    ----------------------------------------------------*/
    new WOW().init();



    /*----------------------------------------------------
      INITIALIZE SWIPER
    ----------------------------------------------------*/
    var swiper = new Swiper('.swiper-container', {
        pagination: '.swiper-pagination',
        paginationClickable: true,
        prevButton: '.swiper-button-prev',
        nextButton: '.swiper-button-next',
        autoplay: 7000,
        loop: true,
        simulateTouch: false
    });





    /*----------------------------------------------------
      INITIALIZE COUNT UP
    ----------------------------------------------------*/
    $('section#countup').on('inview', function (event, visible, visiblePartX, visiblePartY) {
        if (visible) {
            $(this).find('.counter').each(function () {
                var $this = $(this);
                $('.counter').countTo({
                    speed: 3000,
                    refreshInterval: 50
                });
            });
            $(this).unbind('inview');
        }
    });





    /*----------------------------------------------------
      INITIALIZE OWL SLIDER
    ----------------------------------------------------*/
    $(document).ready(function () {
        $('.testimonial').owlCarousel({
            navigation: true,
            items: 3,

            //Basic Speeds
            slideSpeed: 2000,

            //Autoplay
            autoPlay: false,
            stopOnHover: false,

            // Navigation
            navigation: true,
            navigationText: ['<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>'],
            rewindNav: true,
            scrollPerPage: false,

            //Pagination
            pagination: false,
            paginationSpeed: 800,

            // Responsive 
            responsive: true,
            responsiveRefreshRate: 200,
            itemsDesktop: [1199, 3],
            itemsDesktopSmall: [992, 1],
            itemsTablet: [768, 1],
            itemsMobile: [479, 1],

            // After action for adding class active & removing class inactive
            afterAction: function (el) {
                if (screen.width < 992) {
                    //remove class active
                    this
                        .$owlItems
                        .removeClass('active')

                    //add class active
                    this
                        .$owlItems //owl internal $ object containing items
                        .eq(this.currentItem)
                        .addClass('active')
                } else {

                    //remove class active
                    this
                        .$owlItems
                        .removeClass('active')

                    //add class active
                    this
                        .$owlItems //owl internal $ object containing items
                        .eq(this.currentItem + 1)
                        .addClass('active')
                }
            }

        });


        $('.partners-slider').owlCarousel({
            // Most important features
            items: 6,
            itemsDesktop: [1199, 6],
            itemsDesktopSmall: [992, 4],
            itemsTablet: [768, 3],
            itemsTabletSmall: false,
            itemsMobile: [479, 2],

            //Basic Speeds
            slideSpeed: 200,
            paginationSpeed: 800,

            //Autoplay
            autoPlay: 8000,
            stopOnHover: false,

            // Navigation
            navigation: false,
            navigationText: ["prev", "next"],
            rewindNav: true,
            scrollPerPage: false,

            //Pagination
            pagination: true,

            // Responsive 
            responsive: true,
            responsiveRefreshRate: 200
        });
    });




    /*----------------------------------------------------
      MAILCHIMP
    ----------------------------------------------------*/
    $('.mailchimp').ajaxChimp({
        callback: mailchimpFunct,
        url: "your-mailchimp-url-here" //Replace this with your own mailchimp post URL. Paste the url inside "".  
    });

    function mailchimpFunct(response) {
        if (response.result === 'success') {
            $('#subscribe-result').html('<div class="alert alert-success">' + resp.msg + '</div>').fadeIn(500);
            $("#subscribe-result").delay(5000).fadeOut(1000);

        } else if (response.result === 'error') {
            $('#subscribe-result').html('<div class="alert alert-danger">' + resp.msg + '</div>').fadeIn(500);
            $("#subscribe-result").delay(5000).fadeOut(1000);
        }
    }




    /*----------------------------------------------------
      SUBSCRIBE
    ----------------------------------------------------*/
    $("#subscribtion-form").on('submit', function (e) {
        e.preventDefault();

        //Get input field values from HTML form
        var subscriber_email = $("input[name=email]").val();


        //Input validation
        var proceed = true; //Set proceed as true

        //If empty set border colors red

        if (subscriber_email == "") {
            $("input[name=email]").css('border', '1px solid red');
            proceed = false;
        }


        //Everything it's ok...
        if (proceed) {

            //Data to be sent to server
            var post_data;
            var output;
            post_data = {
                'subscriber_email': subscriber_email
            };

            //Ajax post data to server
            $.post('php/subscribe/subscribe.php', post_data, function (response) {

                //Response server message
                if (response.type == 'error') {
                    output = '<div class="alert alert-danger">' + response.text + '</div>';

                } else {
                    output = '<div class="alert alert-success">' + response.text + '</div>';
                    //If success clear inputs
                    $("input").val('');
                    $("textarea").val('');
                }

                $("#subscribe-result").fadeIn(500).html(output).fadeIn(500);
                $("#subscribe-result").delay(5000).fadeOut(1000);
            }, 'json');

        }
    });

    //Reset border colors
    $("input, textarea").on("change keyup", function (event) {
        $("input, textarea").css('border-color', '');
        $("#subscribe-result").fadeOut(500);
    });




    /*----------------------------------------------------
      CONTACT FORM
    ----------------------------------------------------*/
    $("#contact-form").on('submit', function (e) {
        e.preventDefault();

        //Get input field values from HTML form
        var user_name = $("input[name=name]").val();
        var user_email = $("input[name=email]").val();
        var user_subject = $("input[name=subject]").val();
        var user_message = $("textarea[name=message]").val();


        //Input validation
        var proceed = true; //Set proceed as true

        //If empty set border colors red
        if (user_name == "") {
            $("input[name=name]").css('border-color', 'red');
            proceed = false;
        }

        if (user_email == "") {
            $("input[name=email]").css('border-color', 'red');
            proceed = false;
        }

        if (user_message == "") {
            $("textarea[name=message]").css('border-color', 'red');
            proceed = false;
        }

        if (user_subject == "") {
            $("input[name=subject]").css('border-color', 'red');
            proceed = false;
        }


        //Everything it's ok...
        if (proceed) {

            //Data to be sent to server
            var post_data;
            var output;
            post_data = {
                'user_name': user_name,
                'user_email': user_email,
                'user_message': user_message,
                'user_subject': user_subject
            };

            //Ajax post data to server
            $.post('php/email.php', post_data, function (response) {

                //Response server message
                if (response.type == 'error') {
                    output = '<div class="alert alert-danger">' + response.text + '</div>';

                } else {
                    output = '<div class="alert alert-success">' + response.text + '</div>';
                    //If success clear inputs
                    $("input").val('');
                    $("textarea").val('');
                }

                $("#contact-result").fadeIn(500).html(output).fadeIn(500);
                $("#contact-result").delay(5000).fadeOut(1000);
            }, 'json');

        }
    });

    //Reset border colors
    $("input, textarea").on("change keyup", function (event) {
        $("input, textarea").css('border-color', '');
        $("#contact-result").fadeOut(500);
    });





    /*----------------------------------------------------
      INITIALIZE EXPAND-COLLAPSE
    ----------------------------------------------------*/
    $('.expand-form').simpleexpand({
        'defaultTarget': '.contact-us .contact-form'
    });





})(jQuery);