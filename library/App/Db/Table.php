<?php
/**
 * @see Zend_Db_Table
 */
require_once 'Zend/Db/Table.php';

/**
 * Class for SQL table interface.
 * 
 * @category   App
 * @package    App_Db
 */
class App_Db_Table extends Zend_Db_Table 
{
    
    /**
     * Overrides parent function to catch any notices thrown and do nothing with them
     * Would like to figure out where they are coming from
     * @return boolean 
     */
    protected function _setupMetadata()
    {
        try {
            return parent::_setupMetadata();
        } catch (Exception $e) {
            // Unable to save metadata
            return false;
        }
    }

    /**
     * Returns the count based on where statement
     * @param string $where
     * @return int
     */
    public function count($where = null)
    {
        $select = $this->getDefaultAdapter()->select();
        $select->from($this->_name, array('num' => 'COUNT(*)'));
        if (null !== $where) {
            $select->where($where);
        }
        $row = $this->getDefaultAdapter()->fetchRow($select);
        return (int)$row['num'];
    }
}