<?php
/**
 * Provides common model functionality
 * 
 * @category   App
 * @package    App_Model
 */
abstract class App_Model
{
	const MODEL_TYPE_ENTITY = 'entity';
	const MODEL_TYPE_COLLECTION = 'collection';
	const MODEL_TYPE_DBTABLE = 'dbtable';
	const MODEL_TYPE_DATAMAPPER = 'datamapper';
	
	/**
     * Stores common objects like Forms and DAOs
     * @var array
     */
    protected static $_objectCache = array();
    
    /**
     * Gets namespace of class (Application_ or Admin_ for example)
     * 
     * @param string $className
     * @return string
     */
	public static function getClassNamespace($className)
    {
        if (is_object($className)) {
            $className = get_class($className);
        }
        return substr($className, 0, strpos($className, '_'));
    }
    
    /**
     * Gets the type of model class (User or Entry for example)
     * 
     * @param string|object $className
     * @return string
     */
    public static function getClassType($className)
    {
        if (is_object($className)) {
            $className = get_class($className);
        }
        return substr($className, strrpos($className, '_') + 1);
    }
    
	/**
     * Gets the name of a sibling class
     * 
     * For example, if supplied with Admin_Model_Collection_User and asked
     * for the Data Mapper, would return Admin_Model_DataMapper_User
     * 
     * @param string $className
     * @param string siblingType
     * @return string
     */
    public static function getClassSibling($className, $siblingType)
    {
        $namespace = self::getClassNamespace($className);
        $type = self::getClassType($className);
        
        switch ($siblingType) {
        	case self::MODEL_TYPE_COLLECTION:
        		return "{$namespace}_Model_Collection_{$type}";
        	case self::MODEL_TYPE_DATAMAPPER:
        		return "{$namespace}_Model_DataMapper_{$type}";
        	case self::MODEL_TYPE_DBTABLE:
        		return "{$namespace}_Model_DbTable_{$type}";
        	case self::MODEL_TYPE_ENTITY:
        		return "{$namespace}_Model_{$type}";
        }
        
        throw new App_Model_Exception("No such model type: '{$siblingType}'");
    }
    
    /**
     * Retreives cached object
     * 
     * @param string $objectName
     * @return object|boolean Returns the object if it exists, else false
     */
    protected static function getObjectFromCache($objectName)
    {
        if (isset(self::$_objectCache[$objectName])) {
            return self::$_objectCache[$objectName];
        }
        
        return false;
    }
    
    /**
     * Adds an object to cache
     * 
     * @param object $object
     */
    protected static function addObjectToCache($object)
    {
        self::$_objectCache[get_class($object)] = $object;
    }
}