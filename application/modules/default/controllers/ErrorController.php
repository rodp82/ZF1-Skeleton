<?php

class Default_ErrorController extends Zend_Controller_Action
{

    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        
        if (!$errors || !$errors instanceof ArrayObject) {
            $this->view->message = 'You have reached the error page';
            return;
        }
        
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $priority = Zend_Log::NOTICE;
                $this->view->message = 'Page not found';
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $priority = Zend_Log::CRIT;
                $this->view->message = 'Application error';
                break;
        }
        
        // Log exception, if logger available
        $log = $this->_getLog();
        if (is_a($log, 'Zend_Log')) {
            $log->log($this->view->message, $priority, $errors->exception);
            $log->log('Message : ' . $errors->exception->getMessage(), $priority);
            if(APPLICATION_ENV != 'production') {
                // log extra info\
                $log->log( 'Error Details : ' . PHP_EOL 
                         . 'Request Parameters : ' . var_export($errors->request->getParams(), true) . PHP_EOL
                         . 'Stack Trace : ' . PHP_EOL . $errors->exception->getTraceAsString()
                         , $priority);
                
            }
        }
        
        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }
        
        $this->view->request   = $errors->request;
    }

    /**
     * @return Zend_Log 
     */
    private function _getLog()
    {
        if (!Zend_Registry::isRegistered('log')) {
            return false;
        }
        $log = Zend_Registry::get('log');
        return $log;
    }
    


}

