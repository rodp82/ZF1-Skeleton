<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    /**
     * Stores all the config data from application.ini in the
     * Zend_Registry object
     */
    protected function _initConfig()
    {
        $config = new Zend_Config($this->getOptions(), true);
    	Zend_Registry::set('config', $config);
        return $config;
    }

    /**
     * defines all the constants set in the config file
     * @see /application/configs/constants.ini
     */
    protected function _initConstants()
    {
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/constants.ini', APPLICATION_ENV);

        foreach($config as $name => $value) {
            if (!defined($name)) {
                define($name, $value);
            }
        }
    }

    /**
     * Sets up the caching options
     */
    protected function _initCache()
    {
        if ($this->hasPluginResource('cachemanager')) {
            $cache = $this->getPluginResource('cachemanager')
                          ->getCacheManager()
                          ->getCache('app');
            // Save to registry
            Zend_Registry::set('cache', $cache);
        }
    }

    /**
     * Initialises the logger and saves it to the Zend_Registry
     */
    protected function _initLog()
    {
        // use Zend_Registry::get("log")->info('Hello World!'); in code to log messages
        if ($this->hasPluginResource('Log')) {
            $log = $this->getPluginResource('Log')
                        ->getLog();
            Zend_Registry::set('log', $log);
        }
    }

}

