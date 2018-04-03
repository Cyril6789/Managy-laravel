
<?php
session_start();
$app_key    = 'ly8s3l4ioffy08j';
$app_secret = 'ncw5tzes50bih9z';


if (!isset($_SESSION['myapp'])) {
    return;
}
$ch = curl_init();
$headers = array(
    'Authorization: OAuth oauth_version="1.0", oauth_signature_method="PLAINTEXT", oauth_consumer_key="' . $app_key . '", oauth_token="'  . $_SESSION['myapp']['oauth_access_token'] . '", oauth_signature="' . $app_secret . '&' . $_SESSION['myapp']['oauth_access_token_secret'] . '"'
);
curl_setopt( $ch, CURLOPT_HTTPHEADER	  , $headers);
curl_setopt( $ch, CURLOPT_URL						, 'https://api.dropbox.com/1/account/info');
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE);
$api_response  = curl_exec($ch);
$json_response = json_decode($api_response);
echo "<p>Détails du compte pour l'utilisateur <strong>" . $json_response->display_name . "</strong> - ";
echo '<a href="list.php">View Files</a></p>';
echo "<pre>" . print_r($json_response, true)  . "</pre>";
?>