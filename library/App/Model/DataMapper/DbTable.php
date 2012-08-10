<?php
/** 
 * @see App_Model_DataMapper 
 */
require_once 'App/Model/DataMapper.php';

/**
 * Basic Zend_Db_Table implementation of a data mapper
 * 
 * @category   App
 * @package    App_Model
 */
abstract class App_Model_DataMapper_DbTable extends App_Model_DataMapper
{
    
    /**
     * @var string
     */
    protected $_dbTableClass = null;

    /**
     * @var App_Db_Table
     */
    protected $_dbTable = null;

    /**
     * Returns the dbTable object
     * @return App_Db_Table
     */
    public function getDbTable()
    {
        if (null == $this->_dbTable) {
            if (!$class = $this->_dbTableClass) {
                $class = App_Model::getClassSibling($this, App_Model::MODEL_TYPE_DBTABLE);
            }
            $this->_dbTable = new $class;
        }

        return $this->_dbTable;
    }
	
    /**
     * Fetch all Entities as a Collection
     *
     * @param string|array|Zend_Db_Table_Select $where
     * @param string|array $order
     * @param int $count
     * @param int $offset
     * @return App_Model_Collection
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset  = null)
    {
        $data = $this->_fetchAll($where, $order, $count, $offset);
        $collectionClass = $this->_getCollectionClass();
        return new $collectionClass($data);
    }

    /**
     * Fetch a single Entity by its ID/Primary Key
     * @param int $id
     * @return App_Model_Entity
     */
	public function fetchById($id)
	{
		$data = $this->_fetchById((int)$id);
        if (!$data) {
        	return false;
        }

        $entityClass = $this->_getEntityClass();
        return new $entityClass($data);
	}

    /**
     * Fetches a row from the database and returns it as a Model_Entity object
     * @param string|array|Zend_Db_Table_Select $where
     * @param string|array $order
     * @return App_Model_Entity
     */
    public function fetchRow($where = null, $order = null)
    {
        $data = $this->_fetchRow($where, $order);
        if (!$data) {
        	return false;
        }

        $entityClass = $this->_getEntityClass();
        return new $entityClass($data);
    }

    /**
     * Attempts to fetch a row from the database based on search criteria in $data
     * otherwise if none found will create new entity with that data
     * @param array $data
     * @return Zend_Db_Table_Row
     */
    public function fetchRowOrCreate($data)
    {
        $pk = $this->getDbTable()->info(Zend_Db_Table::PRIMARY);

        $pkData = array();
        foreach ($pk as $fieldName) {
            if (array_key_exists($fieldName, $data)) {
                $pkData[] = $data[$fieldName];
            } elseif (!empty($pkData)) {
                throw new Exception("Could not find Primary Key part '$fieldName'");
            }
        }
        
        if (empty($pkData)) {
            // no primary key data, just create row with other values
            $row = $this->getDbTable()->createRow($data, true);
        } else {
            // primary key data exists, attempt to find row in database
            $rowset = call_user_func_array(array($this->getDbTable() , 'find'), $pkData);
            if ($rowset->count() > 0) {
                // results found use current
                $row = $rowset->current();
            } else {
                // not found in database by the primary key values
                // unset the primary key data, but apply other data
                foreach ($pk as $fieldName) {
                    unset($data[$fieldName]);
                }
                $row = $this->getDbTable()->createRow($data, true);
            }
        }

        if (null === $row) {
            $row = $this->getDbTable()->createRow(array(), true);
        }
        return $row;
    }
    
    /**
     * Attempts to fetch an entity from datasource, otherwise create one if not 
     * found.
     * @param array $data
     * @return App_Model_Entity
     */
    public function fetchEntityOrCreate($data)
    {
        $row = $this->fetchRowOrCreate($data);
        $entityClass = $this->_getEntityClass();
        $model = new $entityClass($row->toArray());
        return $model;
    }

    /**
     * Saves an entity to the database.
     * @param App_Model_Entity $entity
     * @return boolean|int
     */
    public function save(App_Model_Entity $entity)
    {
        $id = $entity->getId();
        if ($id > 0) {
            $entity->setUpdated(Zend_Date::now()->toString('yyyy-MM-dd HH:mm:ss'));
            $r = $this->update($entity);
        } else {
            $entity->setCreated(Zend_Date::now()->toString('yyyy-MM-dd HH:mm:ss'))
                   ->setUpdated(Zend_Date::now()->toString('yyyy-MM-dd HH:mm:ss'));
            $r = $this->insert($entity);
        }
        return $r;
    }

    /**
     * Inserts a new row into the database table
     * @param App_Model_Entity $entity
     * @return int
     */
    public function insert(App_Model_Entity $entity)
    {
        $entity->setId(null);
        $data = $this->_prepData($entity->getData());
        unset($data['id']);
        $r = $this->getDbTable()->insert($data);

        if (is_array($r)) {
            $entity->setId($r['id']);
        } else {
            $entity->setId($r);
        }

        //$this->_postSave($entity);
        return $r;
    }

    /**
     * Updates a row in the table
     * @param App_Model_Entity $entity
     * @return boolean
     */
    public function update(App_Model_Entity $entity)
	{
		$id = $entity->getId();
		$where = $this->_buildPrimaryKeyWhere($id);
		$data = $this->_prepData($entity->getData());
		$r = (1 == $this->getDbTable()->update($data, $where));
		//$this->_postSave($entity);
		return $r;
	}

    /**
     * Returns an entity as a database row object
     * @param App_Model_Entity $entity
     * @return Zend_Db_Table_Row_Abstract 
     */
    public function toDbRow(App_Model_Entity $entity)
    {
        return $this->getDbTable()->createRow($entity->getData());
    }

    /**
     * Deletes an entity from the database. A Zend_Db_Table_Row object is first
     * created from the entity as this ensures any cascading delete operations on
     * dependant tables is also performed
     * @param App_Model_Entity $entity
     * @return boolean
     */
    public function delete(App_Model_Entity $entity)
    {
        return $this->getDbTable()->createRow($entity->getData())->delete();
    }

    /**
     * Deletes a row from the database
     * @param int $id
     * @return boolean
     */
    public function deleteById($id)
    {
        $where = $this->_buildPrimaryKeyWhere($id);
		return (1 == $this->getDbTable()->delete($where));
    }

    /**
     * Deletes a number of records in the database using the id field
     * @param array $arrayIds
     * @return int  The number of rows deleted
     */
	public function deleteByIds($arrayIds)
	{
		return $this->getDbTable()->delete('id IN (' . implode(',', $arrayIds) . ')');
	}

    /**
     * Returns the column names of a table
     * @return array
     */
    public function getProperties()
    {
        return $this->getDbTable()->info(App_Model_DbTable::COLS);
    }
    
    /**
     * Fetches a result set from the database and returns as an array
     * @param string|array|Zend_Db_Table_Select $where
     * @param string|array $order
     * @param int $count
     * @param int $offset
     * @return array
     */
    protected function _fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $result = $this->getDbTable()->fetchAll($where, $order, $count, $offset);
		return $result->toArray();
    }

    /**
     * Fetches a row from the database and returns it as an array
     * @param string|array|Zend_Db_Table_Select $where
     * @param string|array $order
     * @return array
     */
    protected function _fetchRow($where = null, $order = null)
    {
        $row = $this->getDbTable()->fetchRow($where, $order);
        if (null === $row) {
            return false;
        }
        return $row->toArray();
    }

    /**
     * Fetches a row from the database and returns as an array
     * @param int $id
     * @return array
     */
    protected function _fetchById($id)
    {
        $results = $this->getDbTable()->find($id);
        if (1 !== count($results)) {
        	return false;
        }
        return $results->current()->toArray();
    }

    /**
     * Preps the entity's data before saving it to the database. In this case,
     * property names are reverted back to lowercase and underscores, and any
     * properties that are not in the table are removed from the array
     * @param array $data
     * @return array 
     */
    protected function _prepData(array $data)
    {
        $filter = new Zend_Filter_Word_CamelCaseToUnderscore();
        $preppedData = array();
        // get the columns from the tables
        $tableColumns = $this->getDbTable()->info(Zend_Db_Table::COLS);
        
        foreach ($data as $key => $val) {
            $col = strtolower($filter->filter($key));
            // this makes sure that only existing table properties get inserted
            // as well as any values that are null are unset
            if (in_array($col, $tableColumns) /* && null !== $val */) {
                $preppedData[$col] = $val;
            }
        }

        return $preppedData;
    }

    /**
	 * Builds a where statement for the primary key
	 *
	 * @param mixed $id
	 * @return array
	 */
	protected function _buildPrimaryKeyWhere($id)
	{
		$id = $this->_normalizeId($id);
		$where = array();
		foreach ($id as $column => $value) {
			$where[] = $this->getDbTable()->getAdapter()->quoteInto("{$column} = ?", $value);
		}

		return $where;
	}

	/**
	 * Ensures that ID is in col => value format
	 *
	 * @param mixed $id
	 * @return array
	 */
	protected function _normalizeId($id)
	{
		$primary = $this->getDbTable()->info(Zend_Db_Table::PRIMARY);
		if (!is_array($id)) {
			$id = array(current($primary) => $id);
		}
		if (count($id) != count($primary)) {
			throw new Zend_Exception('Primary key is an invalid length');
		}

		return $id;
	}
    
}