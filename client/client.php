<?php
	session_start();
	ini_set("soap.wsdl_cache_enabled", "0"); //Disable WSDL caching
	
	class ClientException extends Exception{};
	
	class Client{
		
		public $image; //upload image
		public $client; //SOAP-client
		public $types = array('image/gif', 'image/png', 'image/jpeg', 'image/pjpeg'); //allow types
		public $width; //custom width
		public $height; //custom height
		const WSDL = "http://localhost/testwork/server/resize.wsdl"; //WSDL-file
		
		
		public function __construct(){
			$this->client = new SoapClient(client::WSDL); //Create SOAP-client
			$this->image = $_FILES['picture']; //get image
			$this->width = $_POST['width']; //get custom width
			$this->height = $_POST['height']; //get custom height
		}
		
		/**
		* run application
		*/
		public function run(){
			try{
				if (!$this->check()) throw new ClientException ('Invalid file type. Allowed types:  *.gif, *.png, *.jpg');
				if (($this->width == 0) or ($this->height == 0)) throw new ClientException ('Height and width of the image must be greater than 0');
				$this->resize($this->width, $this->height); //resize client image
			}catch(ClientException $e){
				echo "ERROR: " . $e->getMessage();
			}
		}
		
		/**
		* check image
		*/
		private function check(){
			$imageinfo = @getimagesize($this->image['tmp_name']);
			
			if (in_array($this->image['type'], $this->types)){ //check loaded file
				if(in_array($imageinfo['mime'], $this->types)){
					$this->width = (int)trim(strip_tags($this->width)); //clear client options
					$this->height = (int)trim(strip_tags($this->height));
					return true;
				}
			}
			return false;
		}
		
		/**
		* resize image
		*/
		private function resize($width, $height){
			$i = file_get_contents($this->image['tmp_name']);
			print_r($this->client->__getFunctions());
			try{
				$i = $this->client->resizeImg(base64_encode($i), $this->image['type'], $width, $height); //resize images
			}catch(SoapFault $e){
				echo $e->getMessage();
			}
			
			$img = base64_decode($i); //decode received data
			
			//save image
			$info = pathinfo($this->image['name']);
				if($_SESSION['authuser'] !== 1){ //check user session
					$_SESSION['authuser'] = 1;
					$folder = md5(time().mt_rand(0, 100));
					$_SESSION['folder'] = $folder;
					mkdir("upload/$folder", 0777); //create folder
				}
				$folder = $_SESSION['folder'];
				$path = "upload/$folder/".$info['filename'].md5(time()).".".$info['extension']; //path for file
				if(!file_exists("upload/$folder")){
					mkdir("upload/$folder", 0777); //create folder
				}
				file_put_contents($path, $img); //save
		}
	}
	
	if ($_SERVER['REQUEST_METHOD']=='POST'){
		$service = new Client();
		$service->run();
	}
?>

<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Resize image</title>
	</head>
	<body>
	<?php if (($_SESSION['authuser'] === 1) and (file_exists("upload/$_SESSION[folder]"))){
			$files = scandir("upload/$_SESSION[folder]");
			$i = 0;
			echo "<p>previous image:</p>";
			echo "<table border='1px solid'>
			<th>width</th>
			<th>height</th>
			<th>view</th>
			<th>download</th>";
			foreach ($files as $k){
				$i++;
				if ($i > 2){
					echo "<tr>";
						list($width, $height) = getimagesize("upload/$_SESSION[folder]/$k");
						echo "<td>$width</td>";
						echo "<td>$height</td>";
						echo "<td><img src='upload/$_SESSION[folder]/$k' width='200px' height='200px'></img></td>";
						echo "<td><a href='upload/$_SESSION[folder]/$k' download>$k</a></td>";
					echo "</tr>";
				}
			}
			echo "</table>";
		}
	?>
		<h1>Download image</h1>
			<form method="POST" enctype="multipart/form-data">
				<input type="file" name="picture" />
				<label>width</label>
				<input type="text" name="width" />
				<label>height</label>
				<input type="text" name="height" />
				<input type="submit" value="Go!" />
			</form>
	</body>
</html>