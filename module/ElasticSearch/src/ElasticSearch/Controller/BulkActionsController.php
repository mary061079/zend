<?php
/**
 * Console application. Calling it from root:
 * cd /var/www/zend/public
 * php index.php update
 */

namespace ElasticSearch\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use ElasticSearch\Model\BulkActions;
use ElasticSearch\Model\DBMethods;
use Zend\Db\TableGateway\TableGateway;
use Zend\Console\Request as ConsoleRequest;


class BulkActionsController extends AbstractActionController {
    protected $esActions;

    public function __construct() {
        $this->log_file = '/path/to/file';
    }

    public function indexAction() {
        $request = $this->getRequest();
        $mode = $request->getParam( 'mode' );

        switch( $mode ) {
            case 'update':
                $this->updateRecords();
	            break;

            case 'delete':
                $this->deleteRecords();
	            break;

            default:
                $this->bulkInsert();
                break;
        }
    }

    public function updateRecords() {
        try {
            $this->esActions()->bulkUpdate();
        } catch( \Exception $e ) {
	        $this->esActions()->log_info( $e->getMessage() );
        };
    }

    public function deleteRecords() {
	    try {
		    $this->esActions()->bulkDelete();
	    } catch( \Exception $e ) {
		    $this->esActions()->log_info( $e->getMessage() );
	    };
    }

    public function bulkInsert() {
        try {
	        $this->esActions()->bulkInsert();
        } catch( \Exception $e ) {
	        $this->esActions()->log_info( $e->getMessage() );
        };
    }

    public function esActions() {
        if ( !$this->esActions ) {
            $sm = $this->getServiceLocator();
            $this->esActions = $sm->get( 'ElasticSearch\Model\BulkActions' );
	    }
        return $this->esActions;
    }
}