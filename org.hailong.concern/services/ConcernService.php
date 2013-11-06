<?php

/**
 * 单向关注服务
 * @Tasks ConcernCreateTask,ConcernCancelTask,ConcernBlockTask,ConcernBlockCancelTask,ConcernFetchUserTask,ConcernCheckTask
 * @author zhanghailong
 *
 */
class ConcernService extends Service{
	
	public function handle($taskType,$task){
	
		
		if($task instanceof ConcernCreateTask){
			
			$context = $this->getContext();
			$dbContext = $context->dbContext();
				
			$dbContextTask = new DBContextTask(DB_CONCERN);
				
			$context->handle("DBContextTask",$dbContextTask);
				
			if($dbContextTask->dbContext){
				$dbContext = $dbContextTask->dbContext;
			}
			
			$uid = $task->uid;
			
			if($uid === null){
				$uid = $context->getInternalDataValue("auth");
			}
			
			$item = $dbContext->querySingleEntity("DBConcern","uid={$uid} and tuid={$task->tuid}");
			$changed = false;
			
			if($item){
				if(intval($item->deleted)){
					$item->deleted = 0;
					$item->updateTime = time();
					$dbContext->update($item);
					$changed = true;
				}
			}
			else{
				$item = new DBConcern();
				$item->uid = $uid;
				$item->tuid = $task->tuid;
				$item->deleted = 0;
				$item->tblock = 0;
				$item->source = $task->source;
				$item->updateTime = time();
				$item->createTime = time();
				
				$dbContext->insert($item);
				$changed = true;
			}
			
			$task->cid = $item->cid;
		
			if($changed){
				$t = new CachePutTask();
				$t->path = CACHE_CONCERN_TIMESTAMP;
				$t->value = time();
				
				$context->handle("CachePutTask",$t);
				
				$t = new CachePutTask();
				$t->path = str_replace("{uid}", $uid, CACHE_CONCERN_USER_TIMESTAMP);
				$t->value = time();
				
				$context->handle("CachePutTask",$t);
				
				$dbContext->commit();
			}
			
			return false;
		}
		
		if($task instanceof ConcernCancelTask){
			
			$context = $this->getContext();
			$dbContext = $context->dbContext();
			
			$dbContextTask = new DBContextTask(DB_CONCERN);
			
			$context->handle("DBContextTask",$dbContextTask);
			
			if($dbContextTask->dbContext){
				$dbContext = $dbContextTask->dbContext;
			}

			$uid = $task->uid;
			
			if($uid === null){
				$uid = $context->getInternalDataValue("auth");
			}
			
			$item = $dbContext->querySingleEntity("DBConcern","uid={$uid} and tuid={$task->tuid}");
			
			if($item){
				if(! intval($item->deleted)){
					$item->deleted = 1;
					$item->updateTime = time();
					$dbContext->update($item);
					
					$t = new CachePutTask();
					$t->path = CACHE_CONCERN_TIMESTAMP;
					$t->value = time();
					
					$context->handle("CachePutTask",$t);
					
					$t = new CachePutTask();
					$t->path = str_replace("{uid}", $uid, CACHE_CONCERN_USER_TIMESTAMP);
					$t->value = time();
					
					$context->handle("CachePutTask",$t);
					
					$dbContext->commit();
				}
			}
			
			return false;
		}
		
		if($task instanceof ConcernBlockTask){
				
			$context = $this->getContext();
			$dbContext = $context->dbContext();
				
			$dbContextTask = new DBContextTask(DB_CONCERN);
				
			$context->handle("DBContextTask",$dbContextTask);
				
			if($dbContextTask->dbContext){
				$dbContext = $dbContextTask->dbContext;
			}
		
			$uid = $task->uid;
				
			if($uid === null){
				$uid = $context->getInternalDataValue("auth");
			}
				
			$item = $dbContext->querySingleEntity("DBConcern","tuid={$uid} and uid={$task->tuid}");
				
			if($item){
				if(! intval($item->tblock)){
					$item->tblock = 1;
					$item->updateTime = time();
					$dbContext->update($item);
					
					$t = new CachePutTask();
					$t->path = CACHE_CONCERN_TIMESTAMP;
					$t->value = time();
					
					$context->handle("CachePutTask",$t);
					
					$t = new CachePutTask();
					$t->path = str_replace("{uid}", $task->tuid, CACHE_CONCERN_USER_TIMESTAMP);
					$t->value = time();
					
					$context->handle("CachePutTask",$t);
				}
			}
				
			return false;
		}
		
		if($task instanceof ConcernBlockCancelTask){
		
			$context = $this->getContext();
			$dbContext = $context->dbContext();
		
			$dbContextTask = new DBContextTask(DB_CONCERN);
		
			$context->handle("DBContextTask",$dbContextTask);
		
			if($dbContextTask->dbContext){
				$dbContext = $dbContextTask->dbContext;
			}
		
			$uid = $task->uid;
		
			if($uid === null){
				$uid = $context->getInternalDataValue("auth");
			}
		
			$item = $dbContext->querySingleEntity("DBConcern","tuid={$uid} and uid={$task->tuid}");
		
			if($item){
				if(intval($item->tblock)){
					$item->tblock = 0;
					$item->updateTime = time();
					$dbContext->update($item);
					
					$t = new CachePutTask();
					$t->path = CACHE_CONCERN_TIMESTAMP;
					$t->value = time();
						
					$context->handle("CachePutTask",$t);
						
					$t = new CachePutTask();
					$t->path = str_replace("{uid}", $task->tuid, CACHE_CONCERN_USER_TIMESTAMP);
					$t->value = time();
						
					$context->handle("CachePutTask",$t);
				}
			}
		
			return false;
		}
		
		if($task instanceof ConcernFetchUserTask){
		
			$context = $this->getContext();
			$dbContext = $context->dbContext();
		
			$dbContextTask = new DBContextTask(DB_CONCERN);
		
			$context->handle("DBContextTask",$dbContextTask);
		
			if($dbContextTask->dbContext){
				$dbContext = $dbContextTask->dbContext;
			}
		
			$uid = $task->uid;
		
			if($uid === null){
				$uid = $context->getInternalDataValue("auth");
			}
		
			$sql = "uid={$uid} and deleted<>1";
			if(!$task->allowBlock){
				$sql .=" AND tblock=0";
			}
			
			$sql .= " ORDER BY cid DESC";
			
			if($task->pageIndex !== null && $task->pageSize !== null){
				$offset = ($task->pageIndex - 1) * $task->pageSize;
				$limit = $task->pageSize;
				$sql .= " LIMIT {$offset},{$limit}";
			}
			
			$rs = $dbContext->queryEntitys("DBConcern",$sql);
			
			if($rs){
				while($item = $dbContext->nextObject($rs,"DBConcern")){
					if(!$task->fetchItem($item)){
						break;
					}
				}
				$dbContext->free($rs);
			}
		
			return false;
		}
		
		if($task instanceof ConcernCheckTask){
			
			$context = $this->getContext();
			$dbContext = $context->dbContext();
			
			$dbContextTask = new DBContextTask(DB_CONCERN);
			
			$context->handle("DBContextTask",$dbContextTask);
			
			if($dbContextTask->dbContext){
				$dbContext = $dbContextTask->dbContext;
			}
			
			$uid = $task->uid;
			
			if($uid === null){
				$uid = $context->getInternalDataValue("auth");
			}
			
			if($dbContext->countForEntity("DBConcern","uid={$uid} and tuid={$task->tuid} and tblack<>1 and deleted<>1") <=0){
				throw new ConcernException("concern check invalid", ERROR_CONCERN_CHECK_INVALID);
			}
			
			return false;
		}
		
		if($task instanceof ConcernFansCountTask){
			
			$context = $this->getContext();
			$dbContext = $context->dbContext();
				
			$dbContextTask = new DBContextTask(DB_CONCERN);
				
			$context->handle("DBContextTask",$dbContextTask);
				
			if($dbContextTask->dbContext){
				$dbContext = $dbContextTask->dbContext;
			}
				
			$uid = $task->uid;
				
			if($uid === null){
				$uid = $context->getInternalDataValue("auth");
			}
			
			$task->results = $dbContext->countForEntity("DBConcern","tuid={$uid} and deleted<>1");
			
			return false;
		}
		
		if($task instanceof ConcernFollowCountTask){
				
			$context = $this->getContext();
			$dbContext = $context->dbContext();
		
			$dbContextTask = new DBContextTask(DB_CONCERN);
		
			$context->handle("DBContextTask",$dbContextTask);
		
			if($dbContextTask->dbContext){
				$dbContext = $dbContextTask->dbContext;
			}
		
			$uid = $task->uid;
		
			if($uid === null){
				$uid = $context->getInternalDataValue("auth");
			}
				
			$task->results = $dbContext->countForEntity("DBConcern","uid={$uid} and deleted<>1");
				
			return false;
		}
		
		if($task instanceof ConcernIsFollowTask){
			
			$context = $this->getContext();
			$dbContext = $context->dbContext();
				
			$dbContextTask = new DBContextTask(DB_CONCERN);
				
			$context->handle("DBContextTask",$dbContextTask);
				
			if($dbContextTask->dbContext){
				$dbContext = $dbContextTask->dbContext;
			}
				
			$uid = $task->uid;
				
			if($uid === null){
				$uid = $context->getInternalDataValue("auth");
			}
				
			$task->results = $dbContext->countForEntity("DBConcern","uid={$uid} and tuid={$task->tuid} and deleted<>1") >0;
				
			return false;
		}
		
		if($task instanceof ConcernFetchFansTask){
		
			$context = $this->getContext();
			$dbContext = $context->dbContext();
		
			$dbContextTask = new DBContextTask(DB_CONCERN);
		
			$context->handle("DBContextTask",$dbContextTask);
		
			if($dbContextTask->dbContext){
				$dbContext = $dbContextTask->dbContext;
			}
		
			$uid = $task->uid;
		
			if($uid === null){
				$uid = $context->getInternalDataValue("auth");
			}
		
			$sql = "tuid={$uid} and deleted<>1";
				
			$sql .= " ORDER BY cid DESC";
				
			if($task->pageIndex !== null && $task->pageSize !== null){
				$offset = ($task->pageIndex - 1) * $task->pageSize;
				$limit = $task->pageSize;
				$sql .= " LIMIT {$offset},{$limit}";
			}
				
			$rs = $dbContext->queryEntitys("DBConcern",$sql);
				
			if($rs){
				while($item = $dbContext->nextObject($rs,"DBConcern")){
					if(!$task->fetchItem($item)){
						break;
					}
				}
				$dbContext->free($rs);
			}
		
			return false;
		}
		
		return true;
	}
}

?>