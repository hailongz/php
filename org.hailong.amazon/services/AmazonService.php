<?php

use ApaiIO\Request\RequestFactory;
use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\Search;
use ApaiIO\ResponseTransformer\ObjectToArray;
use ApaiIO\Operations\Lookup;
use ApaiIO\Operations\SimilarityLookup;
use ApaiIO\ApaiIO;
use ApaiIO\Operations\BrowseNodeLookup;

class AmazonService extends Service{

	private $apaiIO;
	
	public function apaiIO(){
		
		if(!$this->apaiIO ){
			
			global $library;
			
			$cfg = require "$library/org.hailong.configs/amazon.php";
			
			$conf = new GenericConfiguration();
		
			$conf
			->setCountry($cfg["country"])
			->setAccessKey($cfg["api-key"])
			->setSecretKey($cfg["secret-key"])
			->setAssociateTag($cfg["associate-tag"])
			->setResponseTransformer('\ApaiIO\ResponseTransformer\XmlToSimpleXmlObject');
		
			$this->apaiIO = new ApaiIO($conf);
		}
		
		return $this->apaiIO ;
	}
	
	public function handle($taskType, $task){
		
		if($task instanceof AmazonSearchTask){
			
			$apaiIO = $this->apaiIO();
			
			$search = new Search();
			$search->setCategory($task->category);
			$search->setKeywords($task->keyword);
			$search->setPage($task->page);
			$search->setResponseGroup(array('Large', 'Small'));
			
			$task->results = $apaiIO->runOperation($search);
			
			return false;
		}
		
		if($task instanceof AmazonLookupTask){
				
			$apaiIO = $this->apaiIO();
				
			$lookup = new Lookup();
			
			$lookup->setItemId($task->itemId);
			$lookup->setResponseGroup(array('Large', 'Small'));
			
			if($task->itemIdType == 'isbn'){
				$lookup->setIdType(Lookup::TYPE_ISBN);
			}
			else {
				$lookup->setIdType(Lookup::TYPE_ASIN);
			}
			
			$task->results = $apaiIO->runOperation($lookup);
				
			return false;
		}
		
		return true;
	}
	
}
