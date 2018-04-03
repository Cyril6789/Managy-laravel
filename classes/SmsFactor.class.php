<?php session_start();

class SmsFactor {
	//input parameters ---------------------
	private $username;                          //your username
	private $password;                          //your password
	private $sender;                            //sender text
	private $message;                           //message text
	private $flash;                             //Is flash message (1 or 0)
	private $inputgsmnumbers = array();         //destination gsm numbers
	private $type;                              //msg type ("bookmark" - for wap push, "longSMS" for text messages only)
	private $bookmark;                          //wap url (example: www.google.com)
	//--------------------------------------

	private $host;
	private $XMLgsmnumbers;
	private $xmldata;
	private $request_data;
	private $response;

        public function  __construct($username, $password, $sender, $flash=0, $type="longSMS", $bookmark='')
        {
                $this->username = $username;
		$this->password = $password;
		$this->sender = $sender;
                $this->flash = $flash;
                $this->type = $type;
		$this->bookmark = $bookmark;
        ;
    }

	public function sendSMS($message, $numero)
	{
		
		$this->message = stripslashes($message);
		$this->inputgsmnumbers[0] = $numero;
		

		$this->host = "www.smsfactor.com/apiV2.php";

		$this->convertGSMnumberstoXML();
		$this->prepareXMLdata();

		$this->response = $this->do_post_request($this->host,$this->request_data);
		return $this->response;
	}

	private function convertGSMnumberstoXML()
	{
		$gsmcount = count($this->inputgsmnumbers); #counts gsm numbers

		for ( $i = 0; $i < $gsmcount; $i++ )
		{
			$this->XMLgsmnumbers .= "<gsm>" . $this->inputgsmnumbers[$i] . "</gsm>";
                        
		}
	}

	private function prepareXMLdata()
	{
		$this->xmldata = "<sms><authentification><username>" . $this->username . "</username><password>" . $this->password . "</password></authentification><message><sender>" . $this->sender . "</sender><text>" . $this->message . "</text><isFlash>" . $this->flash . "</isFlash><type>" . $this->type . "</type><bookmark>" . $this->bookmark . "</bookmark></message><recipients>" . $this->XMLgsmnumbers . "</recipients></sms>";
		$this->request_data = 'XML=' . $this->xmldata;
	}


	private function do_post_request($url, $postdata, $optional_headers = null)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}

};

?>