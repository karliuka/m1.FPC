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
class Faonni_FPC_Model_Processor
{
    /**
     * Check if processor is allowed for current HTTP request.
     * Disable processing AJAX requests and requests with "NO_CACHE" cookie
	 *
     * @param object $request Mage_Core_Controller_Request_Http
     * @return bool
     */
    public function isAllowed(Mage_Core_Controller_Request_Http $request)
    {
        if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'){
            return false;
        }	
        if(isset($_COOKIE['NO_CACHE']) || isset($_GET['no_cache'])){
            return false;
        }
        if(isset($_GET[Mage_Core_Model_Session_Abstract::SESSION_ID_QUERY_PARAM])){
            return false;
        }
        if(!Mage::app()->useCache(Faonni_FPC_Model_Cache::XML_TYPE)){
            return false;
        }
		if($request->isAjax()){
			return false;
		}
		if($request->isPost() || $request->isPut() || $request->isDelete() || 
			$request->isHead() || $request->isOptions()){
			return false;
		}		
        return true;
    }

    /**
     * Get page content from cache storage
	 *
     * @param string $content
     * @return string|false
     */
    public function extractContent($content)
    {	
		$request = Mage::app()->getRequest();
		if(!$this->isAllowed($request)) return false;
		
		$urlKey = Faonni_FPC_Model_Cache::getUrlKey($request);
		$cache = Faonni_FPC_Model_Cache::getCacheInstance();
			
		$content = $cache->load($urlKey);
		if($content){
			$session = new Faonni_FPC_Model_Session;
			if($session->getIsValid()){
				return $this->_prepareHtml($content, $session);
			}	
		}
		return false;
    }
	
    /**
     * Determine and process all defined containers.
	 *
     * @param string $content
     * @param object $session Faonni_FPC_Model_Session
     * @return string|false
     */
	protected function _prepareHtml($content, Faonni_FPC_Model_Session $session)
	{
		preg_match_all("#<\!\-\-([a-z0-9]{32})\-\->#isD", $content, $match, PREG_SET_ORDER);
		
		if(is_array($match)){
			foreach($match as $data){
				$key = $data[1];         /* key */
				$placeholder = $data[0]; /* <!--key--> */
				if($key == Faonni_FPC_Model_Cache::getBlockKey('_form_key_marker_')) continue;
				if(!$session->hasBlockHtml($key)) return false;
				$content = str_replace($placeholder, $session->getBlockHtml($key), $content);
			}
		}
		$content = str_replace(
			Faonni_FPC_Model_Cache::getPlaceholder('_form_key_marker_'), 
			$session->getFormKey(), 
			$content
		);
		return $content;			
	}
}