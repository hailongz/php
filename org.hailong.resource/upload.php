<?php

require_once "config.php";

$rs = array();

$uploadDir = "res/";

if($_FILES){
	foreach($_FILES as $name=>$file){
		if($file["size"] >0 && $file["tmp_name"]){
			
			$extName = upload_extname($file);
			$isImage = false;
			
			if($extName == ".jpeg" || $extName==".jpg"
				|| $extName==".png" || $extName==".gif"){
				$uploadDir = "images/";
				$isImage = true;
			}
			else{
				$uploadDir = "res/";
			}
		
			$dir = $uploadDir.date("Y-m-d")."/";
			
			$value = upload_name($file);
			
			if(!file_exists($dir)){
				mkdirs($dir);
			}
			
			move_uploaded_file($file["tmp_name"], $dir.$value);
			
			$rs[$name] = $dir.$value;
			
			if($isImage){
				buildThumbs($dir.$value);
			}
		}
	}
}

output($rs);

?>