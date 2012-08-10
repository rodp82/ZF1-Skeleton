<?php
/** 
 * @see App_Model 
 */
require_once 'App/Model.php';

/**
 * Provides a basic wrapper around an array of Entities
 * 
 * @category   App
 * @package    App_Model
 */
class App_Model_Collection extends App_Model implements Iterator, Countable, ArrayAccess
{
    /**
     * Collection storage
     * @var array
     */
    protected $_entities = array();

    /**
     * Classname for entities
     * @var string
     */
    protected $_entityClass = null;

    /**
     * Current count of entities
     * @var integer
     */
    protected $_count = 0;

    /**
     * Constructor
     * 
     * @param array|object $entities Either an array or an object that implements Iterator & Countable
     * @param array $options
     */
    public function __construct($entities = null) 
    {
        if (null !== $entities) {
            $this->setEntities($entities);
        }
    }

    /**
     * Sets the collection's entities
     * 
     * @param array|object $entities Either an array or an object that implemetns Iterator & Countable
     * @return App_Model_Collection
     */
    public function setEntities($entities) 
    {
        if (!is_array($entities) && (!$entities instanceof Iterator || !$entities instanceof Countable)) {
            // TODO: Use a better Exception class
            throw new Exception('Collection entities must be an array or implement Iterator and Countable');
        }

        $this->_entities = $entities;
        $this->_count = count($entities);

        return $this;
    }

    public function append($entity) 
    {
        if (!is_array($entity) && !($entity instanceof App_Model_Entity)) {
            throw new Exception('Collection entities must be an array or entity model type');
        }
        $this->_entities[] = $entity;
        $this->_count = count($this->_entities);
        return $this;
    }
    
    /**
     * Clear the collection
     */
    public function clear()
    {
        $this->_entities = array();
    }

    /** 
     * Implementation of Iterator
     */

    public function current() 
    {
        return $this->_ensureEntity(current($this->_entities));
    }

    public function key() 
    {
        return key($this->_entities);
    }

    public function next() 
    {
        next($this->_entities);
    }

    public function rewind() 
    {
        reset($this->_entities);
    }

    public function valid() 
    {
        return (null !== $this->key());
    }

    /**
     * Implementation of ArrayAccess
     */

    public function offsetExists($offset) 
    {
        return isset($this->_entities[$offset]);
    }

    public function offsetGet($offset) 
    {
        return ($this->offsetExists($offset) ? $this->_ensureEntity($this->_entities[$offset]) : null);
    }

    public function offsetSet($offset, $value) 
    {
        // TODO: Should you even be able to update a collection?
        $value = $this->_ensureEntity($value);

        $this->_entities[$offset] = $value;
        $this->_count = count($this->_entities);
    }

    public function offsetUnset($offset) 
    {
        unset($this->_entities[$offset]);
        $this->_entities = array_values($this->_entities);
        $this->_count = count($this->_entities);
    }

    /**
     * Implementation of Countable
     */
    public function count() 
    {
        return count($this->_entities);
    }

    /**
     * Manually set the Entity class to use
     * @param string $className
     */
    public function setEntityClass($className) 
    {
        $this->_entityClass = $className;
    }

    /**
     * Get the class name for generated Entities
     * @return string
     */
    protected function _getEntityClass() 
    {
        if (null == $this->_entityClass) {
            $namespace = self::getClassNamespace($this);
            $modelName = self::getClassType($this);
            $this->_entityClass = "{$namespace}_Model_{$modelName}";
        }

        return $this->_entityClass;
    }

    protected function _ensureEntity($entity) 
    {
        $className = $this->_getEntityClass();

        if (is_array($entity)) {
            $entity = new $className($entity);
        }

        if (!$entity instanceof $className) {
            throw new InvalidArgumentException(get_class($this) . " expects all entities to be of type '{$className}', instead received : " . get_class($entity));
        }

        return $entity;
    }

    /**
     * Returns this collections data as an array
     * @return <type>
     */
    public function toArray() 
    {
        $data = array();
        foreach ($this->_entities as $entity) {
            if (is_array($entity)) {
                $className = $this->_getEntityClass();
                $entity = new $className($entity);
            }

            $data[] = $this->_ensureEntity($entity) ? $entity->toArray() : null;
        }
        return $data;
    }

    /**
     * Returns all the ID values of all the entities in the collection
     * @return array
     */
    public function getAllEntityIds() 
    {
        $ids = array();
        foreach ($this as $entity) {
            $ids[] = $entity->getId();
        }
        return $ids;
    }
    
    /**
     * Returns the collection of entities with the entity ids as the array keys
     * @return App_Model_Collection 
     */
    public function entityIdsAsKey() 
    {
        $entities = array();
        foreach ($this as $entity) {
            $entities[$entity->getId()] = $entity;
        }
        $this->setEntities($entities);
        return $this;
    }

    /**
     * Returns an associative array of array(id => name) to be used to populate
     * select lists
     * @param String $firstOption
     * @return array
     */
    public function toHtmlSelectArray($firstOption = '') 
    {
        $values = array();
        if ($firstOption != '') {
            $values[0] = $firstOption;
        }
        foreach ($this as $item) {
            $values[$item->getId()] = $item->getName();
        }
        return $values;
    }

}