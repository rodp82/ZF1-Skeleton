<?php

/**
 * Load the dependencies
 */
require_once 'Zend/Uri.php';
require_once 'Zend/Http/Client.php';

class App_Mail_Transport_Sendgrid extends Zend_Mail_Transport_Smtp
{
    /**
     * Sendgrid username
     *
     * @var string|null
     */
    protected $_username;


    /**
     * Sendgrid password
     *
     * @var string|null
     */
    protected $_password;


    /**
     * Constructor.
     *
     * @param  string $endpoint (Default: smtp.sendgrid.net)
     * @param  array|null $config (Default: null)
     * @return void
     * @throws Zend_Mail_Transport_Exception if username is not present in the config
     * @throws Zend_Mail_Transport_Exception if password is not present in the config
     */
    public function __construct(Array $config = array(), $host = 'smtp.sendgrid.net')
    {
        if(array_key_exists('username', $config)){
            $this->_username = $config['username'];
        } else {
            $this->_username = SENDGRID_USERNAME;
        }

        if(array_key_exists('password', $config)){
            $this->_password = $config['password'];
        } else {
            $this->_password = SENDGRID_PASSWD;
        }

        if(empty($this->_username)){
            throw new Zend_Mail_Transport_Exception('This transport requires the Sendgrid username');
        }

        if(empty($this->_password)){
            throw new Zend_Mail_Transport_Exception('This transport requires the Sendgrid password');
        }
        $config['username'] = $this->_username;
        $config['password'] = $this->_password;
        $config['auth'] = 'plain';
        $config['port'] = '587';
        parent::__construct($host, $config);
    }
}