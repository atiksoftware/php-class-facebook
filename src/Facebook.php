<?php 

	namespace Atiksoftware\Facebook;

	use \Curl\Curl; 
	use PHPHtmlParser\Dom;

	class Facebook
	{
		private $curl ;

		public $logged = false;


		function __construct(){
			$this->curl = new Curl();	
			$this->curl->setUserAgent('Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; WOW64; Trident/4.0; SLCC1)'); 
			$this->curl->setHeader('accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8');
			// $this->curl->setHeader('accept-encoding', 'gzip, deflate, br');
			$this->curl->setHeader('accept-language', 'tr-TR,tr;q=0.9,en-US;q=0.8,en;q=0.7,de;q=0.6');
			$this->curl->setHeader('cache-control', 'no-cache');
			$this->curl->setHeader('pragma', 'no-cache');
			
			$this->curl->setOpt(CURLOPT_FOLLOWLOCATION, true); 
		}

		function getCookieFileForUser($username){
			$cookieFolder = __DIR__."/cookies";
			if(!file_exists($cookieFolder)){
				mkdir($cookieFolder);
			}
			$cookieUser = "{$cookieFolder}/{$username}";
			if(!file_exists($cookieUser)){
				mkdir($cookieUser);
			}
			return "{$cookieUser}/cookies.txt"; 
		}

		function parseForms($data = "",$filterAction = false){
			$dom = new Dom;
			$dom->load($data);
			$formList = [];
			foreach($dom->find('form') as $formItem){
				$form = [];
				$form["action"] = htmlspecialchars_decode($formItem->getAttribute('action'));
				foreach($dom->find('input') as $inputItem){
					$name = $inputItem->getAttribute('name');
					$value = $inputItem->getAttribute('value');
					$type = $inputItem->getAttribute('type');
					if($type == "text" || $type == "password" || $type == "hidden" || $type == "checkbox" || $type == "radio" || $type == "file"){
						$form["inputs"][$name] = $value;
					}
					if($type == "submit"){
						$form["submits"][$name] = $value;
					}
				}
				foreach($dom->find('textarea') as $inputItem){
					$name = $inputItem->getAttribute('name');
					$value = $inputItem->nodeValue;
					$form["inputs"][$name] = $value;
				}
				if(!$filterAction || ($filterAction && strstr($form["action"],$filterAction))){
					$formList[] = $form;
				}
			}
			return $formList; 
		}


		function login($username = "", $password = ""){
			// unlink($this->getCookieFileForUser($username));
			$this->curl->setCookieFile($this->getCookieFileForUser($username));
			$this->curl->setCookieJar($this->getCookieFileForUser($username));
				// $this->logged = true;
				// return $this;
			if($this->loginCheck()){
				$this->logged = true;
				return $this;
			} 
			$data = $this->curl->get('https://m.facebook.com/login');
			$forms = $this->parseForms($data );
			if(!count($forms)){
				throw new \Exception('Facebook a giriş yapmak için login formu tespit edilemedi');
				return $this;
			}
			$form = $forms[0];
			$form["inputs"]["email"] = $username;
			$form["inputs"]["pass"] = $password;
			$this->curl->post($form["action"], $form["inputs"]);

			if($this->loginCheck()){
				$this->logged = true;
			} 
			else{ 
				throw new \Exception('Facebook a giriş yapılamadı. Kullanıcı adı veya şifre hatalı');
				return $this;
			}
			return $this; 
		}
		function loginCheck(){
			$data = $this->curl->get("https://m.facebook.com/profile.php"); 
			if(strstr($data,"login.php?")){
				return false;
			}
			return true; 
		}



		function post($pageId,$caption = "", $media = false){

			$data = $this->curl->get("https://m.facebook.com/profile.php?id={$pageId}");
 
			$forms = $this->parseForms($data,"composer/mbasic");
			if(!count($forms)){
				throw new \Exception('Facebook ta paylaşım yapmak için ana ekrandaki paylaşım formu tespit edilemedi');
				return $this;
			}
			$form = $forms[0];
			$form["inputs"]["view_photo"] = $form["submits"]["view_photo"];
			$this->curl->setReferer($this->curl->getUrl());
			$data = $this->curl->get($form["action"], $form["inputs"]);

 
			$forms = $this->parseForms($data,"composer/mbasic");
			if(!count($forms)){
				throw new \Exception('Facebook ta paylaşım yapmak için media formu tespit edilemedi');
				return $this;
			} 

			$form = $forms[0];
 
			$form["inputs"]["add_photo_done"] = $form["submits"]["add_photo_done"];
			if($media){
				$form["inputs"]["file1"] = new \CURLFile($media);
			}
			
			$this->curl->setReferer($this->curl->getUrl());
			$data = $this->curl->post($form["action"], $form["inputs"]);
			
			$forms = $this->parseForms($data,"composer/mbasic");
			if(!count($forms)){
				throw new \Exception('Facebook ta paylaşım yapmak için onay formu tespit edilemedi');
				return $this;
			} 
 
			$form = $forms[0];
			$form["inputs"]["view_post"] = $form["submits"]["view_post"];
			$form["inputs"]["xc_message"] = $caption;
			$return_uri       = $form["inputs"]["return_uri"];
			$return_uri_error = $form["inputs"]["return_uri_error"];
			$data = $this->curl->post($form["action"], $form["inputs"]);

			return $this;
			 
		}

	}