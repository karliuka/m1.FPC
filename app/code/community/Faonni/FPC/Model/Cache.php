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
class Faonni_FPC_Model_Cache 
	extends Mage_Core_Model_Cache
{
    /**
     * Cache types constant
	 *
     * @var array
     */
	const XML_TYPE = 'faonni_fpc';
	
    /**
     * Xml dynamic blocks path
	 *
     * @var array
     */	
	const XML_PATH_BLOCKS = 'global/faonni_fpc/frontend_options/dynamic_blocks';
	
    /**
     * Xml cacheable actions path
	 *
     * @var array
     */	
	const XML_PATH_ACTIONS = 'global/faonni_fpc/frontend_options/cache_actions';	
	
    /**
     * FPC cache instance
	 *
     * @var Mage_Core_Model_Cache
     */
    static protected $_cache;
	
    /**
     * Cache instance static getter
	 *
     * @return Mage_Core_Model_Cache
     */
    static public function getCacheInstance()
    {
        if(null === self::$_cache){
            $options = Mage::app()->getConfig()->getNode('global/faonni_fpc');
            if(!$options){
                self::$_cache = Mage::app()->getCacheInstance();
                return self::$_cache;
            }
            $options = $options->asArray();
            foreach(array('backend_options', 'slow_backend_options') as $tag){
                if(!empty($options[$tag]['cache_dir'])){
                    $options[$tag]['cache_dir'] = Mage::getBaseDir('var') . DS . $options[$tag]['cache_dir'];
                    Mage::app()->getConfig()->getOptions()->createDirIfNotExists($options[$tag]['cache_dir']);
                }
            }
            self::$_cache = Mage::getModel('core/cache', $options);
        }
        return self::$_cache;
    }
	
	/**
     * Retrieve url key by request
	 *
     * @param Mage_Core_Controller_Request_Http $request
     * @return string
     */
    static public function getUrlKey(Mage_Core_Controller_Request_Http $request)
    {
        return md5($request->getCookie('store', 'default') . $request->getRequestUri());
    }
	
	/**
     * Retrieve block key by name In Layout
	 *
     * @param string $nameInLayout
     * @return string
     */
    static public function getBlockKey($nameInLayout)
    {
        return md5($nameInLayout);
    }
	
    /**
     * Retrieve block placeholder by name In Layout	
	 *
     * @param string $nameInLayout
     * @return string
     */
    static public function getPlaceholder($nameInLayout)
    {
        $key = self::getBlockKey($nameInLayout);
		return self::getPlaceholderByKey($key);
    }
	
    /**
     * Retrieve block placeholder by name In Layout	
	 *
     * @param string $key
     * @return string
     */
    static public function getPlaceholderByKey($key)
    {
        return '<!--' . $key . '-->';
    }		
}
