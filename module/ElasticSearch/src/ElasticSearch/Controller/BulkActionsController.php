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

//    public function __construct( DBMethods $dbMethods ) {
//        $this->esActions = $this->esActions();
//    }

    public function indexAction() {
        $request = $this->getRequest();
        $mode = $request->getParam( 'mode' );
        switch( $mode ) {
            case 'add':
                $this->updateRecords();

            case 'delete':
                $this->deleteRecords();

            default:
                $this->bulkInsert();
        }
    }

    public function updateRecords() {
        $request = $this->getRequest();
    }

    public function deleteRecords() {
        echo 'delete';
    }

    public function bulkInsert() {
        $this->esActions()->bulkInsert();
    }

    public function esActions() {
        if ( !$this->esActions ) {
            $sm = $this->getServiceLocator();
            $this->esActions = $sm->get( 'ElasticSearch\Model\BulkActions' );
        }
        return $this->esActions;
    }
}