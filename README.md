This service is designed to change the image size.
To get started you need to create a soap-connection with the service and specify the path to the WSDL file. WSDL file is located in server/resize.wsdl

<code>$server = new SoapClient('http://localhost/testwork/server/resize.wsdl');</code>

Next, you need to call <b>resizeImg</b> method with a set of parameters:

<code>string resizeImg(string $img, string $type, integer $width, integer $height)</code>
where<br>
$img - image<br>
$type - type of image (allowed types are: *.gif, *.png, *.jpg)<br>
$width and $height - width and height for new image<br>


You get the picture as a string. To save an image, you can use function <code>file_put_content()</code>

<h2>Example</h2>
<code>$wsdl = 'http://localhost/testwork/server/resize.wsdl';</code><br>
<code>$img = 1.png;</code><br>
<code>$type = "image/png";</code><br>
  <code>$client = new SoapClient($wsdl);</code><br>
 <code>$i = file_get_contents($img);</code><br>
  <code>$i = base64_encode($i);</code><br>
  
  <code>$new_img = $client->resizeImg($i, $type, 100, 100);</code>
  <code>$new_img = base64_decode($new_img);</code>
  
  <code>$file_put_contents('2.png', $new_img);</code>


