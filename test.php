<?php session_start();
require_once './classes/PhpMailer/PHPMailerAutoload.php';

include('./classes/Maileur.class.php');

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





$i = 0;

function parseur($text)
{
    $text = str_replace('&#039;', '\'', $text);
    $text = str_replace('€', '&euro;', $text);
    $text = htmlentities($text);
    $text = html_entity_decode($text);
    $text = preg_replace("#<p>L’article <a rel=\"nofollow\"(.*)</a>.</p>#", '', $text);
    return $text;
}

$f = mktime(date('H'), 0, 0, date('m'), date('d'), date('Y'));
$d = $f - (60 * 60);
//echo ' '.$d.'<br />'.$f;

foreach ($xml->entry as $item) {
    if(strtotime($item->published) >= $d AND strtotime($item->published) < $f) {
        echo '<h2>' . $item->title . '</h2>';
        echo '<p>Created: ' . strtotime($item->published) . '</p>';
        echo '<p>' .parseur($item->content) . '</p>';
        $l = json_decode(json_encode($item->link), True);
        echo $l['@attributes']['href'];


        $mail = new Maileur('[News Managy.fr] '.str_replace('&#8217;', '\'', $item->title));
        $mail->addDest('contact@depaninfo67.com');
        $mail->AddTitle($item->title);
        $mail->body(parseur($item->content).'<p><a href="'.$l['@attributes']['href'].'">Voir la version en ligne</a></p>');
        $mail->send();

 
        $i = 1;
    }
}
?>
