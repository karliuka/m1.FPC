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
class Faonni_FPC_Helper_Data 
	extends Mage_Core_Helper_Abstract
{
    /**
	 * Dynamic blocks list
	 *
     * @var array
     */
    protected $_blocks;
	
    /**
	 * Cached actions list
	 *
     * @var array
     */
	protected $_actions;
	
    /**
     * Retrieve cached actions list
	 *
     * @return array
     */
    public function getCacheAbleActions()
    {
        if(null === $this->_actions){
			$actions = Mage::app()->getConfig()
				->getNode(Faonni_FPC_Model_Cache::XML_PATH_ACTIONS);
			$this->_actions = array_keys($actions->asArray());	
		}
		return $this->_actions;
    }

    /**
     * Retrieve dynamic blocks list
	 *
     * @return array
     */
    public function getDynamicBlocks()
    {
        if(null === $this->_blocks){
			$blocks = Mage::app()->getConfig()
				->getNode(Faonni_FPC_Model_Cache::XML_PATH_BLOCKS);
			$this->_blocks = array_keys($blocks->asArray());	
		}
		return $this->_blocks;        
	}

    /**
     * Check cache used
	 *
     * @return bool
     */
    public function isCacheUsed()
    {
        return Mage::app()->useCache(Faonni_FPC_Model_Cache::XML_TYPE);
    }
	
    /**
     * Retrieve full action name
	 *
     * @return string
     */	
    public function getFullActionName(Mage_Core_Controller_Request_Http $request)
    {
        return $request->getModuleName() . '_' . 
					$request->getControllerName() . '_' . 
						$request->getActionName();
    }
	
	
}