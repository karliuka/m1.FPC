<?php
/**
 * Faonni
 *  
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade module to newer
 * versions in the future.
 * 
 * @package     Faonni_FPC
 * @copyright   Copyright (c) 2015 Karliuka Vitalii(karliuka.vitalii@gmail.com) 
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Faonni_FPC_Model_Session 
	extends Varien_Object
{
    /**
     * Constructor
     *
     */
    public function __construct()
    {
		$this->start(Mage_Core_Controller_Front_Action::SESSION_NAMESPACE);
    }

    /**
     * Configure and start session
	 *
     * @param string $namespace
     * @return Faonni_FPC_Model_Session
     */
    public function start($namespace=null)
    {
        if (isset($_SESSION)){
			if(!isset($_SESSION['core'])){
				$_SESSION['core'] = array();
				$this->setIsValid(false);
			}

			$this->_data = &$_SESSION['core'];
			$this->setIsValid(true);           

			return $this;
        }
        /* getSessionSaveMethod has to return correct version of handler in any case */
		$moduleName = $this->getSessionSaveMethod();
		switch($moduleName){
            /* backward compatibility with db argument (option is @deprecated after 1.12.0.2) */
            case 'db':
                $moduleName = 'user';
                /* @var $sessionResource Mage_Core_Model_Resource_Session */
                $sessionResource = Mage::getResourceSingleton('core/session');
                $sessionResource->setSaveHandler();
                break;
            case 'user':
                /* getSessionSavePath represents static function for custom session handler setup */
                call_user_func($this->getSessionSavePath());
                break;
            case 'files':
                /* don't change path if it's not writable */
                if (!is_writable($this->getSessionSavePath())){
                    break;
                }
            default:
                session_save_path($this->getSessionSavePath());
                break;
        }
        session_module_name($moduleName);
		
		$sessionId = $this->getCookie()->get($namespace);
		if(!$sessionId){
			$this->setIsValid(false);
			return $this;
		}
		
		$this->setSessionId($sessionId)->run();
		
        if(!isset($_SESSION['core'])){
            $_SESSION['core'] = array();
			$this->setIsValid(false);
        }

        $this->_data = &$_SESSION['core'];
		$this->setIsValid(true);
		
        return $this;
    }	
	
    /**
     * Get sesssion save path
	 *
     * @return string
     */
    public function getSessionSavePath()
    {
        return Mage::getBaseDir('session');
    }	
	
    /**
     * Retrieve session save method
	 *
     * @return string
     */
    public function getSessionSaveMethod()
    {
        return (string)Mage::getConfig()->getNode('global/session_save');
    }	
	
    /**
     * Retrieve cookie object
	 *
     * @return Mage_Core_Model_Cookie
     */
    public function getCookie()
    {
        return Mage::getSingleton('core/cookie');
    }	

    /**
     * Retrieve session Id
	 *
     * @return string
     */
    public function getSessionId()
    {
        return session_id();
    }

    /**
     * Set custom session id
	 *
     * @param string $id
     * @return Faonni_FPC_Model_Session
     */
    public function setSessionId($id=null)
    {
        if (null !== $id && preg_match('#^[0-9a-zA-Z,-]+$#', $id)){
            session_id($id);
        }
        return $this;
    }
	
    /**
     * Session start
	 *
     * @return Faonni_FPC_Model_Session
     */
    public function run()
    {
        session_start();
		
        return $this;
    }
	
    /**
     * Retrieve Session Form Key
	 *
     * @return string A 16 bit unique key for forms
     */
    public function getFormKey()
    {
        if(!isset($this->_data['_form_key'])){
            $this->_data['_form_key'] = Mage::helper('core')->getRandomString(16);
        }
        return $this->_data['_form_key'];
    }

    /**
     * If $key is empty, checks whether there's any data in the object
	 *
     * @param string $key
     * @return boolean
     */
    public function hasBlockHtml($key)
    {
        return array_key_exists($key, $this->_data['_cache_block']);
    }
	
    /**
     * Retrieve block html
	 *
     * @param $key
     * @return mixed
    */
    public function getBlockHtml($key)
    {
		return isset($this->_data['_cache_block'][$key]) 
			? $this->_data['_cache_block'][$key] 
			: null;
    }

    /**
     * Set block html
	 *
     * @param $key
     * @param $html
     * @return $this
    */
    public function setBlockHtml($key, $html)
    {
		$this->_data['_cache_block'][$key] = $html;
		return $this;
    }
}