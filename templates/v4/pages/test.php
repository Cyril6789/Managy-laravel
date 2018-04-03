<?php session_start();

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 14/09/2017
 * Time: 18:44
 */


$modal = new modalAjax('sms', 'new');
$settings = Array(
    'id_client' => 32,
    'id_inter'  => 2099
);
$modal->setDebug();
$modal->settings($settings);
echo $modal;


$submit_js = '
                    <script>
                        function sms_submit()
                        {
                           $(\'#error_msg_sms\').html(\''.preg_replace("#\n|\t|\r#", "", addslashes(new Danger('test', false))).'\');
                           $(\'#error_msg_sms\').show(\'slow\');
                        }
                    </script>
                    ';

echo $submit_js;