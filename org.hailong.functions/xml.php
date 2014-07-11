<?php

function xml_is_object($data){
	if(is_object($data)){
		return true;
	}
	else if(is_array($data)){
		$isobject = false;
		foreach ($data as $key=>$value){
			if(! is_int($key)){
				$isobject = true;
				break;
			}
		}
		return $isobject;
	}
	return false;
}

function xml_object_ouptut($data,$output = 'php://output',$root='root'){
	
	$xml = null;
	
	if($output instanceof XMLWriter){
		$xml = $output;
	}
	else {
		$xml = new XMLWriter();
		$xml->openUri($output);
	}
	
	if($root){
		$xml->startDocument();
		$xml->startElement($root);
	}
	
	if(xml_is_object($data)){
		foreach($data as $key=>$value){
			$xml->startElement($key);
			xml_object_ouptut($value,$xml,false);
			$xml->endElement();
		}
	}
	else if(is_array($data)){
		foreach($data as $value){
			$xml->startElement("item");
			xml_object_ouptut($data,$xml,false);
			$xml->endElement("item");
		}
	}
	else {
		$xml->text('a'.$data);
		echo 'a'.$data;
	}
	
	if($root){
		$xml->endElement();
		$xml->endDocument();
	}
	
	return $xml;
}

function xml_object_encode($data,$tag){
	
	$xml = new XMLWriter();
	
	$xml->openMemory();

	xml_object_ouptut($data,$xml);
	
	return $xml->flush();
}

?>