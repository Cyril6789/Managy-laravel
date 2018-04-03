<?php
/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 23/03/2016
 * Time: 18:08
 */

function managyCrypt($string)
{
    $key = CRYPT_KEY;  // Clé de 8 caractères max
    $data = serialize($string);
    $td = mcrypt_module_open(MCRYPT_DES,"",MCRYPT_MODE_ECB,"");
    $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
    mcrypt_generic_init($td,$key,$iv);
    $data = base64_encode(mcrypt_generic($td, '!'.$data));
    mcrypt_generic_deinit($td);
    return $data;

}

function managyDecrypt($string)
{
    $key = CRYPT_KEY;
    $td = mcrypt_module_open(MCRYPT_DES,"",MCRYPT_MODE_ECB,"");
    $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
    mcrypt_generic_init($td,$key,$iv);
    $data = mdecrypt_generic($td, base64_decode($string));
    mcrypt_generic_deinit($td);

    if (substr($data,0,1) != '!')
        return false;

    $data = substr($data,1,strlen($data)-1);
    return unserialize($data);
}



class Base64_Encryption{
    /*
    For URL encryption, change the key with this one:
    private static $clef="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_";

    nota: it is possible to build the key with characters of their choice (key length must be equal to 64), while maintaining base64 encoding. Practice is not it?
    In this case take care to adapt the regex accordingly (see bold line).
    */
    private static $clef="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
    public static function Crypter($a,$b){
        if($a==""||$b=="")return $a;
        $e=self::$clef;
        $u=$e{self::Seed(0,63,microtime(true).$b)};
        $f=md5($b.$u,true);
        $l=self::Unorder($e,$f,64);
        $t=$g="";
        $c=strlen($a);
        $s=$c-$c%3;
        $x=$l{self::Seed(0,63,$f.microtime(true))};
        $n=ord($x)+ord($u);
        $v=self::Unorder("0123",$f.$n,4);
        for($r=$i=0;$i<$s;$i+=3,$r++){
            $g=(ord(($a{$i}^$l{($n+$r+1)%64}))<<16)+
                (ord(($a{$i+1}^$l{($n+$r+2)%64}))<<8)+
                (ord(($a{$i+2}^$l{($n+$r+3)%64})));
            $g=array($v{0}=>$l{$g>>18},
                $v{1}=>$l{($g>>12)&63},
                $v{2}=>$l{($g>>6)&63},
                $v{3}=>$l{$g&63});
            ksort($g);
            $t.=join($g);}
        switch($c-$s){
            case 1:
                $g=ord(($a{$i}^$l{($n+$r+4)%64}))<<16;
                $v=self::Unorder("01",$f,2);
                $g=array($v{0}=>$l{$g>>18},
                    $v{1}=>$l{($g>>12)&63});
                ksort($g);
                $t.=join($g);
                break;
            case 2:
                $g=(ord(($a{$i}^$l{($n+$r+4)%64}))<<16)+
                    (ord(($a{$i+1}^$l{($n+$r+5)%64}))<<8);
                $v=self::Unorder("012",$f,3);
                $g=array($v{0}=>$l{$g>>18},
                    $v{1}=>$l{($g>>12)&63},
                    $v{2}=>$l{($g>>6)&63});
                ksort($g);
                $t.=join($g);
                break;}
        $c=strlen($t);
        $r=$c-self::Seed(0,$c-1,$b.$c);
        return substr_replace($t,$x.$u,-$r,-$r);
    }
    public static function Decrypter($a,$b)
    {
        /*
        For URL encryption, change the regex with this one:
        if(!preg_match("/^[a-zA-Z0-9_-]+$/",$a)||$b=="")return $a;
        */
        if(!preg_match("/^[a-zA-Z0-9\/+]+$/",$a)||$b=="")return $a;
        $c=strlen($a)-2;
        $mm=self::Seed(0,$c-1,$b.$c);
        $u=$a{$mm+1};
        $m=ord($a{$mm})+ord($u);
        $a=substr($a,0,$mm).substr($a,-($c-$mm));
        $e=self::$clef;
        $ff=md5($b.$u,true);
        $l=self::Unorder($e,$ff,64);
        $v=self::Unorder("0123",$ff.$m,4);
        $k=array(
            "0123","0132",
            "0213","0312",
            "0231","0321");
        switch($v{0}){
            case 0:
                $v=$v=="0231"?$k[3]:($v=="0312"?$k[4]:$v);
                break;
            case 1:
                $v=$v=="1203"?self::ReverseA($k[2]):
                    ($v=="1230"?self::ReverseA($k[3]):
                        ($v=="1302"?self::ReverseA($k[4]):
                            (($v=="1320"?self::ReverseA($k[5]):$v))));
                break;
            case 2:
                $v=$v=="2013"?self::ReverseB($k[0]):
                    ($v=="2031"?self::ReverseB($k[1]):(
                    $v=="2130"?self::ReverseB($k[3]):
                        ($v=="2310"?self::ReverseB($k[5]):$v)));
                break;
            case 3:
                $v=$v=="3012"?self::ReverseC($k[0]):
                    ($v=="3021"?self::ReverseC($k[1]):
                        ($v=="3102"?self::ReverseC($k[2]):
                            ($v=="3201"?self::ReverseC($k[4]):$v)));
                break;}
        $d=$g="";
        $f=0;
        while($c%4!==0){$a.="=";$c=strlen($a);$c=$c-4;$f++;};
        for($r=$i=0;$i<$c;$i+=4,$r++){
            $q=array($v{0}=>$e{strpos($l,$a{$i})},
                $v{1}=>$e{strpos($l,$a{$i+1})},
                $v{2}=>$e{strpos($l,$a{$i+2})},
                $v{3}=>$e{strpos($l,$a{$i+3})});
            ksort($q);
            $g=(strpos($e,$q[0])<<18)+
                (strpos($e,$q[1])<<12)+
                (strpos($e,$q[2])<<6)+
                (strpos($e,$q[3]));
            $d.=(chr($g>>16)^$l{($m+$r+1)%64}).
                (chr(($g>>8)&255)^$l{($m+$r+2)%64}).
                (chr($g&255)^$l{($m+$r+3)%64});}
        switch($f){
            case 1:
                $v=self::Unorder("012",$ff,3);
                $v=$v=="120"?"201":($v=="201"?"120":$v);
                $q=array($v{0}=>$e{strpos($l,$a{$i})},
                    $v{1}=>$e{strpos($l,$a{$i+1})},
                    $v{2}=>$e{strpos($l,$a{$i+2})});
                ksort($q);
                $g=(strpos($e,$q[0])<<18)+
                    (strpos($e,$q[1])<<12)+
                    (strpos($e,$q[2])<<6);
                $d.=(chr($g>>16)^$l{($m+$r+4)%64}).
                    (chr(($g>>8)&255)^$l{($m+$r+5)%64});
                break;
            case 2:
                $v=self::Unorder("01",$ff,2);
                $q=array($v{0}=>$e{strpos($l,$a{$i})},
                    $v{1}=>$e{strpos($l,$a{$i+1})});
                $g=(strpos($e,$q[0])<<18)+
                    (strpos($e,$q[1])<<12);
                $d.=(chr($g>>16)^$l{($m+$r+4)%64});
                break;}
        return $d;
    }

    private static function Unorder($x,$b,$c)
    {
        $w=0;$y=strlen($b);
        for($i=0;$i<$c;$i++){
            $w=($w+ord($x[$i])+
                    ord($b[$i%$y]))%$c;
            $j=$x[$i];$x[$i]=$x[$w];$x[$w]=$j;}
        return $x;}
    private static function ReverseA($a){
        return strrev(substr($a,0,2))
        .substr($a,-2);}
    private static function ReverseB($a){
        return substr(self::ReverseA($a),0,1)
        .strrev(substr(self::ReverseA($a),1,2))
        .substr(self::ReverseA($a),-1);}
    private static function ReverseC($a){
        return substr(self::ReverseB($a),0,2)
        .strrev(substr(self::ReverseB($a),2,3));}
    private static function Seed($a,$b,$c){
        $d=unpack("Na",hash("crc32",$c,true));
        return round((($d['a']&2147483647)/2147483647.0)*($b-$a))+$a;
    }
}

?>