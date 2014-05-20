<?php
namespace ElasticSearch\Model;

use ElasticSearch\Model\DBMethods;
use Zend\Mvc\Controller\AbstractActionController;

class BulkActions extends AbstractActionController {

	protected $db;

	public function __construct( DBMethods $DBMethods ) {
		$this->db = $DBMethods;
	}

	public function bulkInsert() {
		
	}

} 