<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of App_Paginator_Adapter_DbSelect
 *
 * @author Rod
 */
class App_Paginator_Adapter_DbSelect extends Zend_Paginator_Adapter_DbSelect 
{
    /**
     * @var App_Model_DataMapper
     */
    protected $_mapper;
    
    public function __construct(Zend_Db_Select $select, App_Model_DataMapper $mapper) 
    {
        parent::__construct($select);
        $this->_mapper = $mapper;
    }
    
    /**
     * Returns an array of items for a page.
     *
     * @param  integer $offset Page offset
     * @param  integer $itemCountPerPage Number of items per page
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $result = parent::getItems($offset, $itemCountPerPage);
        $entityCollectionClass = App_Model::getClassSibling($this->_mapper, App_Model::MODEL_TYPE_COLLECTION);
        return new $entityCollectionClass($result);
    }
    
}
