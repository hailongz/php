<?php

/**
 * LBS服务
 * @author zhanghailong
 *
 */
class LBSService extends Service{
	
	public function handle($taskType,$task){
		
		if($task instanceof LBSDistanceTask){
			
			$radLat1 = $task->latitude1 * pi() / 180.0;
			$radLat2 = $task->latitude2 * pi() / 180.0;
			$a = $task->latitude1 - $task->latitude2;
			$b = ($task->longitude1 * pi() / 180.0) - ($task->longitude2 * pi() / 180.0);
			$s = 2.0 * asin(sqrt(pow(sin($a/2.0),2.0) +
				cos($radLat1) * cos($radLat2) * pow(sin($b/2.0),2.0)));
			
			$s = $s * 6378.137;
			
			$task->distance = $s * 1000;
			
			return false;
		}
		
		if($task instanceof LBSSourceUpdateTask){
			
			$context = $this->getContext();
			$dbContext = $context->dbContext(DB_LBS);
			
			$sid = intval($task->sid);
			$latitude = doubleval($task->latitude);
			$longitude = doubleval($task->longitude);
			$item = $dbContext->querySingleEntity("DBLBSSource","sid=$sid");
			
			if($item){
				if(doubleval($item->latitude) != $latitude
					|| doubleval($item->longitude) != $longitude){
					$item->latitude = $task->latitude;
					$item->longitude = $task->longitude;
					$item->updateTime = time();
				}
				$dbContext->update($item);
			}
			else{
				$item = new DBLBSSource();
				$item->sid = intval($task->sid);
				$item->latitude = doubleval($task->latitude);
				$item->longitude = doubleval($task->longitude);
				$item->createTime = time();
				$item->updateTime = time();
				$dbContext->insert($item);
			}
		
			$dbContext->commit();
			
		}
		
		if($task instanceof LBSSourceRemoveTask){
			
			$context = $this->getContext();
			$dbContext = $context->dbContext(DB_LBS);
				
			$sid = intval($task->sid);
			
			$dbContext->delete("DBLBSSource","sid=$sid");
			$dbContext->delete("DBLBSSearch","sid=$sid");
			$dbContext->commit();
			
		}
		
		if($task instanceof LBSSearchTask){
			
			$context = $this->getContext();
			$dbContext = $context->dbContext(DB_LBS);
			
			$sid = intval($task->sid);
			$latitude = doubleval($task->latitude);
			$longitude = doubleval($task->longitude);
			$distance = intval($task->distance);
			
			if(!$distance){
				$distance = 1000;
			}
			
			$item = $dbContext->querySingleEntity("DBLBSSource","sid=$sid");
			
			if($item){
				
				if($item->updateTime != $item->searchTime){
				
					$dbContext->delete("DBLBSSearch","sid=$sid");
					$dbContext->commit();
					
					$dr = $distance / 111100.0;
					
					$sql = "SELECT * FROM ".DBLBSSource::tableName()." WHERE sid!={$sid}"
							." AND latitude>=".($latitude - $dr)
							." AND latitude<=".($latitude + $dr)
							." AND longitude>=".($longitude - $dr)
							." AND longitude<=".($longitude + $dr);
					
					$rs = $dbContext->query($sql);
					
					if($rs){
						
						$t = new LBSDistanceTask();
						
						while($row = $dbContext->nextObject($rs,"DBLBSSource")){
							
							$r = new DBLBSSearch();
							$r->sid = $sid;
							$r->updateTime = $row->updateTime;
							$r->createTime = time();
							$r->near_sid = $row->sid;
							$r->near_latitude = $row->latitude;
							$r->near_longitude = $row->longitude;
							
							
							$t->latitude1 = $latitude;
							$t->longitude1 = $longitude;
							$t->latitude2 = $row->latitude;
							$t->longitude2 = $row->longitude;
							
							$context->handle("LBSDistanceTask",$t);
							
							$r->distance = $t->distance;
							
							$dbContext->insert($r);
						}
						
						$dbContext->free($rs);
					}
					
					$item->searchTime = $item->updateTime;
					
					$dbContext->update($item);
				}
				
				$pageIndex = intval($task->pageIndex);
				$pageSize = intval($task->pageSize);
				
				if($pageIndex < 1){
					$pageIndex = 1;
				}
				
				if($pageSize <= 0){
					$pageSize = 20;
				}
				
				$offset = ($pageIndex - 1) * $pageSize;
				
				$sql = "SELECT * FROM ".DBLBSSearch::tableName()." WHERE sid=$sid ORDER BY distance DESC LIMIT $offset,$pageSize";
				
				$rs = $dbContext->query($sql);
				
				$task->results = array();
				
				if($rs){
				
					while($row = $dbContext->nextObject($rs,"DBLBSSearch")){
						$task->results[] = $row;
					}
				
					$dbContext->free($rs);
				}
				
			}
			
		}
		
		
		return true;
	}
}

?>