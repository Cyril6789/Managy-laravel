<?php

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 19/08/2016
 * Time: 08:28
 */
class reCaptcha
{
    private $site_key = "6Ley5ScTAAAAAD3Khq87gMp2w0I7e8AN3cWQrNi1";
    private $private_key = "6Ley5ScTAAAAALP4Pal7z1VlUrmnYb6jzihF21uq";

    public function __construct($cle_site='')
    {
        if($cle_site)
            $this->site_key = $cle_site;
    }

    public function checkHuman($code)
    {
        if(empty($code))
            return false;

        $url = 'https://www.google.com/recaptcha/api/siteverify?secret='.$this->private_key.'&response='.$code;
        //echo $url;

        if(function_exists('curl_version'))
        {
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 1);
            $response = curl_exec($curl);
        }
        else{
            $response = file_get_contents($url);
        }

        if(empty($response) OR is_null($response))
            return false;

        /*var_dump($response);

        die();*/

        $json = json_decode($response);

        return $json->success;
    }
}