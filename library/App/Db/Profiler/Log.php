<?php
/**
 * This class is used when you want to log db queries to a text file.
 * Useful for when you want to see what queries are being called during 
 * ajax calls where Zend_Db_Profiler_Firebug is of no use.
 * 
 * @category   App
 * @package    App_Db
 */
class App_Db_Profiler_Log extends Zend_Db_Profiler {

    /**
     * Zend_Log instance
     * @var Zend_Log
     */
    protected $_log;

    /**
     * counter of the total elapsed time
     * @var double 
     */
    protected $_totalElapsedTime;

    public function __construct($enabled = false) 
    {
        parent::__construct($enabled);

        $stream = @fopen(BASE_PATH . '/data/logs/db-queries.log', 'w', false);
        if (!$stream) {
            throw new App_Exception('Failed to open stream');
        }
        
        $writer = new Zend_Log_Writer_Stream($stream);
        $this->_log = new Zend_Log($writer);
    }

    /**
     * Intercept the query end and log the profiling data.
     *
     * @param  integer $queryId
     * @throws Zend_Db_Profiler_Exception
     * @return void
     */
    public function queryEnd($queryId) 
    {
        $state = parent::queryEnd($queryId);

        if (!$this->getEnabled() || $state == self::IGNORED) {
            return;
        }

        // get profile of the current query
        $profile = $this->getQueryProfile($queryId, true);

        // update totalElapsedTime counter
        $this->_totalElapsedTime += $profile->getElapsedSecs();

        // create the message to be logged
        $message = "\r\nElapsed Secs: " . round($profile->getElapsedSecs(), 5) . "\r\n";
        $message .= "Query: " . $profile->getQuery() . "\r\n";

        // log the message as INFO message
        $this->_log->info($message);
    }

}

