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
class Faonni_FPC_Model_Observer
{
    /**
	 * Dynamic blocks list
	 *
     * @var array
     */
    protected $_blocks = array();
	
    /**
	 * Faonni_FPC Model Session
	 *
     * @var object
     */
	protected $_session;

    /**
     * Clean full page cache
	 *
     * @return Faonni_FPC_Model_Observer
     */
    public function cleanCache(Varien_Event_Observer $observer)
    {
        $type = $observer->getEvent()->getType();
		if(empty($type) || $type == Faonni_FPC_Model_Cache::XML_TYPE){
			Faonni_FPC_Model_Cache::getCacheInstance()
				->clean();
		}	
        return $this;
    }
	
    /**
     * Save page body to cache storage
	 *
     * @param Varien_Event_Observer $observer
     * @return Faonni_FPC_Model_Observer
     */	
    public function cacheResponse(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('faonni_fpc');

        if($helper->isCacheUsed()){
			$response = $observer->getEvent()->getResponse();
			if($response->getHttpResponseCode() != 200) return $this;
			
			$request = Mage::app()->getRequest();
			
            if(in_array($helper->getFullActionName($request), $helper->getCacheAbleActions())){
				$urlKey = Faonni_FPC_Model_Cache::getUrlKey($request);
				$cache = Faonni_FPC_Model_Cache::getCacheInstance();
				
                $content = $response->getBody();
				
				$content = str_replace(
					$this->getSession()->getFormKey(), 
					Faonni_FPC_Model_Cache::getPlaceholder('_form_key_marker_'), 
					$content
				);				
				
                $cache->save($content, $urlKey);
				
                foreach($this->_blocks as $key => $html){
					$placeholder = Faonni_FPC_Model_Cache::getPlaceholderByKey($key);
					$content = str_replace($placeholder, $html, $content);
				}
				
				$content = str_replace(
					Faonni_FPC_Model_Cache::getPlaceholder('_form_key_marker_'), 
					$this->getSession()->getFormKey(), 
					$content
				);
                $response->setBody($content);
            }
        }
		return $this;
    }
	
    /**
     * Save block html to cache storage
	 *
     * @param Varien_Event_Observer $observer
     * @return Faonni_FPC_Model_Observer
     */
    public function cacheBlock(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('faonni_fpc');
		
        if ($helper->isCacheUsed()){
            $block = $observer->getEvent()->getBlock();
            $nameInLayout = $block->getNameInLayout();
			$request = Mage::app()->getRequest();
			
            if (in_array($nameInLayout, $helper->getDynamicBlocks())){
				$key = Faonni_FPC_Model_Cache::getBlockKey($nameInLayout);
				$html = $observer->getTransport()->getHtml();
				
				$this->_blocks[$key] = $html;
				$this->getSession()->setBlockHtml($key, $html);

				if(in_array($helper->getFullActionName($request), $helper->getCacheAbleActions())){
					$observer->getTransport()->setHtml(
						Faonni_FPC_Model_Cache::getPlaceholder($nameInLayout)
					);
				}
            }
        }
    }
	
    /**
     * Retrieve fpc session model
	 *
     * @return Faonni_FPC_Model_Session
    */	
    public function getSession()
    {
		if(null === $this->_session){
			$this->_session = new Faonni_FPC_Model_Session;
		}
		return $this->_session;
    }	
}