<?php
/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';

/**
 * @see Zend_Uri
 */
require_once 'Zend/Uri.php';

/**
 * @category  App
 * @package   App_Validate
 */
class App_Validate_Uri extends Zend_Validate_Abstract
{
	const INVALID = 'uriInvalid';
	const INVALID_FORMAT = 'uriInvalidFormat';
 
	/**
	 * @var array
	 */
    protected $_messageTemplates = array(
        self::INVALID => "Invalid type given, value should be a string",
        self::INVALID_FORMAT => "'%value%' is no valid URI in the basic format http://domain.tld",
    );
	
    /**
     * Validates a URI
     * 
     * Returns true if value passes Zend_Uri::check()
     * 
     * @see Zend_Uri::check()
     * @param string $value
     */
	public function isValid($value)
    {
        if (!is_string($value)) {
            $this->_error(self::INVALID);
            return false;
        }
        
        $this->_setValue($value);
        
        if (Zend_Uri::check($value)) {
        	return true;
        }
        
        $this->_error(self::INVALID_FORMAT);
        return false;
    }
}



