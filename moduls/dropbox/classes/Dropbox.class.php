<?php session_start();

/**
 * Created by PhpStorm.
 * User: Cyril
 * Date: 14/07/2016
 * Time: 11:24
 */
require_once("./moduls/dropbox/ressources/DropboxClient.php");

class Dropbox
{
    private $app_key = 'ly8s3l4ioffy08j';
    private $app_secret = 'ncw5tzes50bih9z';
    private $full_access = true;
    private $objet_dropbox;
    private $access_token_name;
    private $folder_tokens = './moduls/dropbox/tokens/';
    private $url_to_connect = '';
    private $racine_folder = 'Applications/Managy/';

    public function __construct($acces_name_token = '')
    {
        $this->objet_dropbox = new DropboxClient(array(
            'app_key' => $this->app_key,
            'app_secret' => $this->app_secret,
            'app_full_access' => $this->full_access,
        ),'fr');

        if(empty($acces_name_token))
            $this->access_token_name = $_SESSION['compte_principal'];
        else
            $this->access_token_name = $acces_name_token;
        $this->init();
    }

    public function getInfoAccount()
    {
        return $this->objet_dropbox->GetAccountInfo();
    }

    public function upload_file($source, $dossier='', $new_name='', $overwrite=true)
    {

        try
        {
            $this->objet_dropbox->CreateFolder($this->racine_folder.$dossier);
        }
        catch (Exception $e)
        {
            // just ignore
        }

        if($this->isAccessible())
            $this->objet_dropbox->UploadFile($source, $this->racine_folder.$dossier.'/'.$new_name, $overwrite);

    }

    public function deconnexion()
    {
        @unlink($this->folder_tokens.$this->access_token_name.'.token');
    }

    private function init()
    {
        $access_token = $this->load_token($this->access_token_name);

        if(!empty($access_token))  //Si token exisant
        {
            $this->objet_dropbox->SetAccessToken($access_token);
        }
        elseif(!empty($_GET['auth_callback'])) // sinon, si on revient de la page d'authentification
        {
            $request_token = $this->load_token($_GET['oauth_token']);
            if (!empty($request_token))
            {
                $access_token = $this->objet_dropbox->GetAccessToken($request_token);
                $this->store_token($access_token, $this->access_token_name);
                $this->delete_token($_GET['oauth_token']);
            }
        }
    }

    private function setUrlToDropboxConnect()
    {
        $return_url = "http://www.managy.fr/dropbox?auth_callback=1";
        $auth_url = $this->objet_dropbox->BuildAuthorizeUrl($return_url);
        $request_token = $this->objet_dropbox->GetRequestToken();
        $this->store_token($request_token, $request_token['t']);
        $this->url_to_connect = $auth_url;

    }

    public function isAccessible()
    {
        if($this->objet_dropbox->IsAuthorized())
            return true;
        else
        {
            $this->setUrlToDropboxConnect();
            return false;
        }
    }

    public function GetUrlToDropboxConnect()
    {
        return $this->url_to_connect;
    }

    private function store_token($token, $name)
    {
        if(file_put_contents($this->folder_tokens.$name.'.token', serialize($token)))
            return true;
        else
            return false;
    }

    private function load_token($name)
    {
        if(!file_exists($this->folder_tokens.$name.'.token'))
            return null;
        return @unserialize(@file_get_contents($this->folder_tokens.$name.'.token'));
    }

    private function delete_token($name)
    {
        @unlink($this->folder_tokens.$name.'.token');
    }

}