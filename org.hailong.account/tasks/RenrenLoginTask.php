<?php

/**
 * QQ登陆任务
 * @author zhanghailong
 *
 */
class RenrenLoginTask implements ITask{
	
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
	/**
	 * 用户信息
	 * @var array
	 */
	public $user;
	
	public function prefix(){
		return "auth";
	}
	
}

?>