<?php

/**
 * 豆瓣绑定任务
 * @author zhanghailong
 *
 */
class DoubanBindTask implements ITask{
	
	/**
	* 用户ID 为null时使用内部参数auth
	* @var int
	*/
	public $uid;
	/**
	 * 外部 APP KEY
	 * @var String
	 */
	public $appKey;
	/**
	*  外部APP SECRET
	*/
	public $appSecret;
	/**
	 * 验证token
	 * @var String
	 */
	public $etoken;
	/**
	* 过期时间
	* @var int
	*/
	public $expires_in;
	
	public function prefix(){
		return "auth";
	}
	
}

?>