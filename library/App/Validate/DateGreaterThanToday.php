<?php
/**
 * @category  App
 * @package   App_Validate
 */
class App_Validate_DateGreaterThanToday extends Zend_Validate_Abstract
{
    const GREATER_THAN = 'greaterThan';
    const GREATER_THAN_STRICT = 'greaterThanStrict';

    protected $_messageTemplates = array(
        self::GREATER_THAN          => "'%value%' is not greater than today",
        self::GREATER_THAN_STRICT   => "'%value%' is not greater than or equal today"
    );
    
    /**
     * Whether to do inclusive comparisons, allowing equivalence to min and/or max
     *
     * If false, then strict comparisons are done, and the value may equal neither
     * the min nor max options
     *
     * @var boolean
     */
    protected $_inclusive;
    
    /**
     * Date format
     * @var string
     */
    protected $_format;

    /**
     * Sets validator options
     * Accepts the following option keys:
     *   'inclusive' => boolean, inclusive border values
     *
     * @param  array|Zend_Config $options
     * @return void
     */
    public function __construct($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (!is_array($options)) {
            $options = func_get_args();
            
            if (!empty($options)) {
                $temp['inclusive'] = array_shift($options);
            }
            if (!empty($options)) {
                $temp['format'] = array_shift($options);
            }

            $options = $temp;
        }

        if (!array_key_exists('inclusive', $options)) {
            $options['inclusive'] = true;
        }
        if (!array_key_exists('format', $options)) {
            $options['format'] = 'Y-m-d';
        }

        $this->setInclusive($options['inclusive'])
             ->setFormat($options['format']);
    }
    
    /**
     * Returns the inclusive option
     *
     * @return boolean
     */
    public function getInclusive()
    {
        return $this->_inclusive;
    }

    /**
     * Sets the inclusive option
     *
     * @param  boolean $inclusive
     * @return Zend_Validate_Between Provides a fluent interface
     */
    public function setInclusive($inclusive)
    {
        $this->_inclusive = $inclusive;
        return $this;
    }
    
    /**
     * Returns the format option
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->_format;
    }

    /**
     * Sets the inclusive option
     *
     * @param  string $format
     * @return Zend_Validate_Between Provides a fluent interface
     */
    public function setFormat($format)
    {
        $this->_format = $format;
        return $this;
    }

    public function isValid($value)
    {
        $this->_setValue($value);

        $today = date($this->getFormat());

        if ($this->getInclusive()) {
            if ($value < $today) {
                $this->_error(self::GREATER_THAN);
                return false;
            }
        } else {
            if ($value <= $today) {
                $this->_error(self::GREATER_THAN_STRICT);
                return false;
            }
        }

        return true;
    }
}
