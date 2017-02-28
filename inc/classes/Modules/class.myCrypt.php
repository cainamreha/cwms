<?php
namespace Concise;


/**
* Class for 2 way encryption of data
*
*
*/
class myCrypt 
{
    /**
    *
    * Constructor
    *
    * @access	public
    * @param	string    $key	myCrypt key, must be of valid size as from php 5.6 (key size use either 16, 24 or 32 byte keys for AES-128, 192 and 256 respectively)
    *
    */
    public function __construct($key = "cc-newmcrypt-key")
    {
	
		$this->key	= defined('CC_CRYPT_KEY') ? CC_CRYPT_KEY : $key;
		$this->ivs	= mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
		$this->iv	= mcrypt_create_iv($this->ivs, MCRYPT_DEV_URANDOM); // Note: MCRYPT_DEV_URANDOM is faster than MCRYPT_DEV_RANDOM

    }

    /**
    *
    * Encrypt a string
    *
    * @access	public
    * @param	string    $str
    * @return	string    The encrypted string
    *
    */
    public function encrypt($str)
    {
        // add end of str delimiter
        $str = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->key, $str, MCRYPT_MODE_ECB, $this->iv);
        return $this->urlbase64_encode($str);
    }
 
    /**
    *
    * Decrypt a string
    *
    * @access   public
    * @param	string    $str
    * @return   string    The decrypted string
    *
    */
    public function decrypt($str)
    {
        $str = $this->urlbase64_decode($str);
        return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->key, $str, MCRYPT_MODE_ECB, $this->iv);
    }

 
    /**
    *
    * Decrypt a string
    *
    * @access   public
    * @param	string    $str
    * @return   string    The decrypted string
    *
    */
	public function urlbase64_encode($str)
	{
		$data = base64_encode($str);
		$data = str_replace(array('+','/','='),array('-','_',''),$data);
		return $data;
	}
	
 
    /**
    *
    * Decrypt a string
    *
    * @access   public
    * @param	string    $str
    * @return   string    The decrypted string
    *
    */
	public function urlbase64_decode($str)
	{
		$data = str_replace(array('-','_'),array('+','/'),$str);
		$mod4 = strlen($data) % 4;
		
		if ($mod4) {
			$data .= substr('====', $mod4);
		}
		return base64_decode($data);
	}

} // end of class
