<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 18/08/2016
 * Time: 21:02
 */

function crypt_pass($pass)
{
    return sha1($pass);
}