<?php
/** 
 * @see App_Model 
 */
require_once 'App/Model.php';

/** 
 * @see Zend_Acl_Resource_Interface 
 */
require_once 'Zend/Acl/Resource/Interface.php';

/** 
 * @see Zend_Filter_Word_UnderscoreToCamelCase 
 */
require_once 'Zend/Filter/Word/UnderscoreToCamelCase.php';

/** 
 * @see Zend_Filter_Word_SeparatorToSeparator 
 */
require_once 'Zend/Filter/Word/SeparatorToSeparator.php';

/**
 * Provides common model functionality
 * 
 * @category   App
 * @package    App_Model
 */
abstract class App_Model_Entity extends App_Model {

    /**
     * Stores entity's property data
     * @var array
     */
    protected $_data = array();
    
    /**
     * constructor
     * @param array $data 
     */
    public function __construct(array $data = null)
    {
        if (null !== $data) {
            $this->populate($data);
        }
    }
    
    public function __unset($name)
    {
        if (!empty($this->_data) && isset($this->_data[$name])) {
            unset($this->_data[$name]);
        } 
        return $this;
    }

    /**
     * Sets a specified property with a value
     * @param mixed $property
     * @param mixed  $value
     * @return App_Model_Entity 
     */
    public function set($property, $value) 
    {
        $filter = new Zend_Filter_Word_UnderscoreToCamelCase();
        $property = $filter->filter($property);
        $method = "set{$property}";

        // TODO: Refactor?
        if (!method_exists($this, $method)) {

            if ('Id' != $property && 'Id' == substr($method, -2)) {
                $method = substr($method, 0, -2);
                if (!method_exists($this, $method)) {
                    throw new BadMethodCallException("No property '$property' exists for entity '" . get_class($this) . "'");
                }
            } else {
                throw new BadMethodCallException("No property '$property' exists for entity '" . get_class($this) . "'");
            }
        }

        $this->$method($value);
        
        return $this;
    }
    
    /**
     *
     * @param array     $data   data to populate with
     * @param boolean   $reset  set to reset any current data
     * @return Iinet_Model_Entity
     */
    public function populate(array $data, $reset = false)
    {
        if (true === $reset) {
    		$this->_data = array();
    	}

        $filter = new Zend_Filter_Word_UnderscoreToCamelCase();
        foreach ($data as $property => $value) {
            $this->set($property, $value);
            $property = $filter->filter($property);
            $method = "set{$property}";
            
            // TODO: Refactor?
            if (!method_exists($this, $method)) {
                
            	if ('Id' != $property && 'Id' == substr($method, -2)) {
            		$method = substr($method, 0, -2);
            		if (!method_exists($this, $method)) {
            			throw new BadMethodCallException("No property '$property' exists for entity '" . get_class($this) . "'");
            		}
            	} else {
            		throw new BadMethodCallException("No property '$property' exists for entity '" . get_class($this) . "'");
            	}
            }
            
            $this->$method($value);
        }
        
        return $this;
    }

    /**
     * Returns all entities properties as an array
     * @return array
     */
    public function toArray()
    {
        $data = array();
    	$filter = new Zend_Filter_Word_UnderscoreToCamelCase();
    	foreach ($this->_data as $key => $value) {
            $method = 'get' . $filter->filter($key);
            $value = $this->$method();
            $data[$key] = $value; 
    	}
    	
    	return $data;
    }
    
    /**
     * Returns all entities properties as an array to be used when inserting into
     * the database. 
     * @return array
     */
    public function toDbArray()
    {
        return  $this->toArray();
    }
    
    /**
     * Returns this entity's data as is, unformatted.
     * Usually to be used when saving to the database.
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }
    
    /**
     * Saves entity to data source. Ideally should be overwritten in child entities
     * @return boolean|int
     */
    public function save()
    {
        $this->getDataMapper()->save($this);
        return $this;
    }

    /**
     * Returns the resource ID for the model in the form model:name
     *
     * @return string
     */
    public function getResourceId()
    {
        $filter = new Zend_Filter_Word_SeparatorToSeparator('_', '.');
        $className = strtolower($this->getClassType($this));
        $resourceId = 'model:' . $filter->filter(str_replace('model_', '', $className)); 
        return $resourceId;
    }

    /**
     * Saves data from a form to the database via entity. This should ideally be
     * overwritten in entity classes.
     * @param array $data
     * @return App_Model_Entity
     */
    public function saveForm(array $data)
    {
        $form = $this->getForm();

        // validate
        if (!$form->isValid($data)) {
            $this->populate($form->getValues());
            return false;
        }

        $this->populate($form->getValues())
             ->save();

        return $this;
    }

    /**
     * Deletes an entity
     * @return boolean
     */
    public function delete()
    {
        return $this->getDataMapper()->delete($this);
    }

    /**
     * Returns the data mapper for the entity
     * @return App_Model_DataMapper
     */
    public function getDataMapper($name = null)
    {
        $namespace = self::getClassNamespace($this);
        if (null == $name) {
            $name = self::getClassType($this);
        } else {
            $name = ucfirst($name);
        }

        $className = "{$namespace}_Model_DataMapper_{$name}";

        // TODO: Look into this
        if (!$dataMapper = self::getObjectFromCache($className)) {
            $dataMapper = new $className();
            self::addObjectToCache($dataMapper);
        }

        return $dataMapper;
    }

    /**
     * Gets a form object
     * Defaults to a form with the same name as the Entity
     *
     * @todo Might want to refactor the get[Object] methods
     * @return App_Form
     */
    public function getForm($name = null)
    {
        $namespace = self::getClassNamespace($this);
        if (null == $name) {
            $name = self::getClassType($this);
        } else {
            $name = ucfirst($name);
        }

        $className = "{$namespace}_Form_{$name}";

        /**
         * @todo 
         */
        if (!$form = self::getObjectFromCache($className)) {
            $form = new $className();
            self::addObjectToCache($form);
        }

        return $form;
    }

    /**
     * Is the same as getForm, but populates the form with $this objects values
     * @return App_Form
     */
    public function viewForm()
    {
        $form = $this->getForm();
        $form->populate($this->toArray());
        return $form;
    }
    
    /**
     * Validates data based on a Zend_From object with the Entity's name
     * @param array $data
     * @return boolean
     */
    public function isValid(array $data)
    {
        $form = $this->getForm();
        if ($form->isValid($data)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Checks to see if a specified variable is serialized
     * @param type $var
     * @return type 
     */
    public function isSerialized($var)
	{
		$check = @unserialize($var);
		return ($check === false && $var != serialize(false)) ? false : true;
	}
    
    /**
     * Checks to see if a specified variable is json string or not
     * @param type $var
     * @return type 
     */
    public function isJson($var)
	{
        try {
            Zend_Json::decode($var);
            return true;
        } catch (Zend_Exception $e) {
            return false;
        }
	}
    
    /**
     * Sets the value for a given property
     * 
     * @param string $property
     * @param mixed $value
     * @return App_Model_Entity
     */
    protected function _setPropertyData($property, $value)
    {
        $this->_data[$property] = $value;
        return $this;
    }
    
    /**
     * Gets the value for a given property
     * Optionally returns a default (null if not set)
     * 
     * @param string $property
     * @param mixed $default
     */
    protected function _getPropertyData($property, $default = null)
    {
    	// TODO: If property = xxxxx_id try to load model "xxxxx" with primary key of property value
    	// That is, if the value is not a model object (or perhaps if it's an integer)
    	
        if (!isset($this->_data[$property])) {
            return $default;
        }
        
        return $this->_data[$property];
    }
    
    /**
     * Gets the 'id' property
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->_getPropertyData('id');
    }

    /**
     * Sets the 'id' property
     *
     * @param mixed $id
     * @return App_Model_Entity
     */
    public function setId($id)
    {
        return $this->_setPropertyData('id', $id);
    }
    
    /**
     * Gets the 'created' property
     *
     * @return mixed
     */
    public function getCreated()
    {
        return $this->_getPropertyData('created');
    }

    /**
     * Sets the 'created' property
     *
     * @param mixed $created
     * @return App_Model_Entity
     */
    public function setCreated($created)
    {
        return $this->_setPropertyData('created', $created);
    }
    
    /**
     * Gets the 'updated' property
     *
     * @return mixed
     */
    public function getUpdated()
    {
        return $this->_getPropertyData('updated');
    }

    /**
     * Sets the 'updated' property
     *
     * @param mixed $updated
     * @return App_Model_Entity
     */
    public function setUpdated($updated)
    {
        return $this->_setPropertyData('updated', $updated);
    }
    
}