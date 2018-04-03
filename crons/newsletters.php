<?php session_start();

require_once './../classes/PhpMailer/PHPMailerAutoload.php';
include('./../classes/Maileur.class.php');
include('./../classes/mysql.class.php');
$fichier = 'http://faq.managy.fr/feed/atom/';

$curl = curl_init();
curl_setopt_array($curl, Array(
    CURLOPT_URL            => $fichier,
    CURLOPT_USERAGENT      => 'spider',
    CURLOPT_TIMEOUT        => 120,
    CURLOPT_CONNECTTIMEOUT => 30,
    CURLOPT_RETURNTRANSFER => TRUE,
    CURLOPT_ENCODING       => 'UTF-8'
));
$data = curl_exec($curl);
curl_close($curl);
$xml = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);


$db = new MySQL();
$sql = 'SELECT mail_contact FROM comptes_principaux WHERE fin_abo > "'.time().'" AND id != "8" AND id != "2" AND id != "1"  ';


function parseur($text)
{
    $text = str_replace('&#039;', '\'', $text);
    $text = str_replace('€', '&euro;', $text);
    $text = htmlentities($text);
    $text = html_entity_decode($text);
    $text = preg_replace("#<p>L’article <a rel=\"nofollow\"(.*)</a>.</p>#", '', $text);
    $text = preg_replace("#width=\"([0-9]*)\"#", 'width="100%"', $text);
    $text = preg_replace("#height=\"([0-9]*)\"#", '', $text);
    return $text;
}

$f = mktime(date('H'), 0, 0, date('m'), date('d'), date('Y'));
$d = $f - (60 * 60);
//echo ' '.$d.'<br />'.$f;

foreach ($xml->entry as $item) {
    //echo parseur($item->content);
    if(strtotime($item->published) >= $d AND strtotime($item->published) < $f) {
      //  echo '<h2>' . $item->title . '</h2>';
       // echo '<p>Created: ' . strtotime($item->published) . '</p>';
       // echo '<p>' .parseur($item->content) . '</p>';
        $l = json_decode(json_encode($item->link), True);
       // echo $l['@attributes']['href'];

        $title = str_replace('&#8217;', '\'', $item->title);
        $title = str_replace('&#8211;', '-', $title);

        $mail = new Maileur('[News Managy.fr] '.$title);

        $db->Query($sql);
        while($row = $db->Row())
            $mail->addDest($row->mail_contact);

        //$mail->addDest('contact@depaninfo67.com');

        $mail->addExpediteur('contact@managy.fr', 'Managy.fr');
        $mail->AddTitle($item->title);
        $mail->body(parseur($item->content).'<p><a href="'.$l['@attributes']['href'].'">Voir la version en ligne</a></p>');
        $mail->send();


        $i = 1;
    }
}

$db->Close();
?>
