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
    protected $esActions, $log_file;

    public function __construct() {
        $this->log_file = '/path/to/file';
    }

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
        try {
	        $this->esActions()->bulkInsert();
        } catch( \Exception $e ) {
	        $this->log_info( $e->getMessage() );
        };
    }

    public function esActions() {
        if ( !$this->esActions ) {
            $sm = $this->getServiceLocator();
            $this->esActions = $sm->get( 'ElasticSearch\Model\BulkActions' );
	    }
        return $this->esActions;
    }

	public function log_info( $info ) {
		echo  date( 'Y-m-d H:i:s' ) . "\n".$info; die;
		if ( strlen( $info ) > 0 ) {
			$f = fopen( $this->log_file, 'a' );
			$date = date( 'Y-m-d H:i:s' ) . "\n";
			fwrite( $f, $date . $info . "\n" );
			fclose( $f );
		}
	}
}