<?php
/**
 * verify code service
 *
 * @author  octopus <zhangguipo@747.cn>
 * @final 2013-3-25
 */
class VerifyCodeService
{
	/**
     * kv pre key
     *
     * @var string
     */
	static private $_preKey = 'verifycode:';

	/**
     * code seed
     * 
     * @var string
     */
	static private $_seed = '0123456789';

	/**
     * seed max index
     *
     * @var int
	 */
	static private $_maxIndex = 9;

	/**
     * register code
     *
     * @param string $telephone
     * @return string
     */
	public function createRegisterCode($telephone)
	{
		$userInfo = TZ_Loader::model('Users', 'User')->getInfoByTelephone($telephone);			
		if (!empty($userInfo))
			throw new Exception('号码已注册，请尝试重置密码。');

		return $this->_sendCode($telephone);			
	}

	/**
     * reset code
     * 
     * @param string $telephone
     * @return string
     */
	public function createResetCode($telephone)
	{
		$userInfo = TZ_Loader::model('Users', 'User')->getInfoByTelephone($telephone);			
		if (empty($userInfo))
			throw new Exception('号码不存在，请确认输入号码是否正确。');

		return $this->_sendCode($telephone);
	}
	
	/**
     * check verify code
     * 
     * @param string $telephone 
     * @param string $validCode
	 * @return boolean
     */
	public function valid($telephone, $validCode)
	{
		//---------------ocotpus update 2014-01-03----------------------------
		//可以存在多个验证码，每个都可以验证
		$redis = TZ_Redis::connect('user');
		$val = $redis->get(self::$_preKey.$telephone.$validCode);
		//根据时间戳判断是否超时,如果存在，就判断时间，是否超时
		if(!empty($val)){
			if((time()-$val)<3600){
				return true;
			}
		}
		return false;
		//-------------------------end-----------------------------
	}

	/**
	 * unset verify code
     *
     * @param string $telephone
     * @return boolean
     */
	public function discard($telephone)
	{       
		$redis = TZ_Redis::connect('user');
                // var_dump($redis);
		$data=$redis->keys(self::$_preKey.$telephone.'*');
		foreach ($data as $key=>$val){
			$redis->delete($val);
		}
		return true;
	}

	/**
     * send verify code
	 *
     * @param string $telephone
	 * @return boolean
     */
	private function _sendCode($telephone)
	{
		$code = $this->_createCode($telephone);
		$saveStatus = $this->_saveCode($telephone, $code);
		if (!$saveStatus)
			throw new Exception('保存验证码失败。');

        $sender = new HM_SMS();
        $sendStatus = $sender->send_sms($telephone,$code);
		if (!$sendStatus)
			throw new Exception('验证码获取过于频繁，请稍后重试。');

		return "";
	}

	/**
     * 生成并获取验证码
     * 
     * @param string $telephone
     * @return string
     */
	private function _createCode($telephone, $length = 4)
	{
		$code = '';
		for ($i=0;$i<$length;$i++) {
			$code .= self::$_seed{rand(0, self::$_maxIndex)};	
		}
		return $code;
	}

	/**
     * 保存验证号
     *
     * @param string $code
	 * @param int $lifeTime
     * @return boolean
     */
	private function _saveCode($telephone, $code)
	{
		//---------------ocotpus update 2014-01-03----------------------------
		//可以存在多个验证码，每个都可以验证
		$redis = TZ_Redis::connect('user');
		return $redis->set(self::$_preKey.$telephone.$code, time());	
		//-------------------------end-----------------------------
	}

	/**
     * get sms message
     * 
     * @param string code
	 * @return string
     */
	private function _getSmsMessage($code)
	{
		$message = "验证码：{$code}，有效时间一小时。";
		$message .= '感谢您使用';
		return $message;
	}
    
    public function createTestCode($telephone)
    {
         $code = $this->_createCode($telephone);    

         return $code;
    }
    
}
