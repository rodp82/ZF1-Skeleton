<?php
/** 
 * @see App_Model 
 */
require_once 'App/Model.php';

/**
 * Provides base functionality for data mappers
 * 
 * @category   App
 * @package    App_Model
 */
abstract class App_Model_DataMapper extends App_Model 
{	
    
    /**
     * Class name for generated entities
     * @var string
     */
    protected $_entityClass;

    /**
     * Class name for generated collections
     * @var string
     */
    protected $_collectionClass;

    abstract public function fetchAll();
    
    abstract public function fetchById($id);

    abstract public function save(App_Model_Entity $entity);

    abstract public function insert(App_Model_Entity $entity);
    
    abstract public function update(App_Model_Entity $entity);

    abstract public function delete(App_Model_Entity $entity);

    abstract public function getProperties();

    /**
     * Prepare data for insert/update
     *
     * This method is meant to be subclassed by concrete data mappers.
     * It's useful when you need to prepare data before insert/update.
     *
     * @todo This should be _prepData($entity) but that might break BC
     * @param array $data
     * @return array
     */
    protected function _prepData(array $data)
    {
        return $data;
    }

    /**
     * Returns the class name for generated Entities
     * @return string
     */
    protected function _getEntityClass()
    {
        if (null == $this->_entityClass) {
            $this->_entityClass = App_Model::getClassSibling($this, App_Model::MODEL_TYPE_ENTITY);
        }
        return $this->_entityClass;
    }

    /**
     * Sets the name of the entity clase
     * @param mixed[string|object] $className
     * @return Iinet_Model_DataMapper 
     */
    public function setEntityClass($className)
    {
        $this->_entityClass = $className;
        return $this;
    }

    /**
     * Get the collection class name for the collection of entities
     * @return string
     */
    protected function _getCollectionClass()
    {
        if (null == $this->_collectionClass) {
            $this->setCollectionClass(App_Model::getClassSibling($this, App_Model::MODEL_TYPE_COLLECTION));
        }
        return $this->_collectionClass;
    }

    /**
     * Manually set the Collection class to use
     * @param string $className
     * @return Iinet_Model_DataMapper
     */
    public function setCollectionClass($className)
    {
        $this->_collectionClass = $className;
        return $this;
    }
    

}
