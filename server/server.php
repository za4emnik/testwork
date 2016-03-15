<?php
	ini_set("soap.wsdl_cache_enabled", "0");
	
	class Server{
		
		public $width; //custom width
		public $height; //custom height
		public $types = array('image/gif', 'image/png', 'image/jpeg', 'image/pjpeg'); //allow types
	
	
		/**
		* resize image client
		*/
		public function resizeImg($img, $type, $width, $height){
			switch ($type){
				case "image/jpeg": $ex = ".jpg";break;
				case "image/gif": $ex = ".gif";break;
				case "image/png": $ex = ".png";break;
				default: throw new SoapFault("Server", "Unknown data type");
			}
		$img = base64_decode($img);
		$name = "tmp/".md5(time()).mt_rand(0, 100).$ex; //temp file
		file_put_contents($name, $img);
		
		//check client data
		if (!$this->check($name, $type, $width, $height)){
			throw new SoapFault("Server", "Your data is incorrect. Please, try again!");
		}
		
		list($orign_width, $orign_height) = getimagesize($name);
		
		$new_img = imagecreatetruecolor($this->width, $this->height); //create image width custom size
		
			switch ($type){
				case "image/jpeg": $img = imagecreatefromjpeg($name);break;
				case "image/gif": $img = imagecreatefromgif($name);break;
				case "image/png": $img = imagecreatefrompng($name);break;
				default: throw new SoapFault("Server", "Unknown data type");
			}
		imagecopyresampled($new_img, $img, 0,0,0,0, $this->width, $this->height, $orign_width, $orign_height); //resize image
		
			switch ($type){
				case "image/jpeg": $img = imagejpeg($new_img, $name);break;
				case "image/gif": $img = imagegif($new_img, $name);break;
				case "image/png": $img = imagepng($new_img, $name);break;
				default: throw new SoapFault("Server", "Unknown data type");
			}
		$file = base64_encode(file_get_contents($name));
		unlink($name); //remove temp file
		return $file;
		}
		
		
		/**
		* check client data
		*/
		private function check($img, $t, $w, $h){
			$imageinfo = getimagesize($img);
			if (in_array($t, $this->types)){ //check loaded file
				if(in_array($imageinfo['mime'], $this->types)){
					$this->width = (int)trim(strip_tags($w)); //clear client options
					$this->height = (int)trim(strip_tags($h));
					return true;
				}
			}
		return false;
		}
	}
	
	$server = new SoapServer("http://localhost/testwork/server/resize.wsdl");
	
	$server->setClass("Server");
	$server->handle();
?>