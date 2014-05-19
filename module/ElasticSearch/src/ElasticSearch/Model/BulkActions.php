<?php
namespace ElasticSearch\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\ResultSet\ResultSet;

class BulkActions {

    public function __construct( ESTableGateway $ESTableGateway ) {
        $this->tableGateway = $ESTableGateway;

    }

    /**
     * getting new comments for bulk creating ES indexes
     * @throws \Exception
     */
    public function getLatestComments() {
	    $this->tableGateway->adapter->query(
		    "CREATE TABLE IF NOT EXISTS `options` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`option_name` VARCHAR(100) NOT NULL COLLATE 'utf8_bin',
				`option_value` VARCHAR(500) NOT NULL COLLATE 'utf8_bin',
				`date` DATETIME NOT NULL,
				PRIMARY KEY (`id`),
				INDEX `option_name` (`option_name`),
				FULLTEXT INDEX `option_value` (`option_value`)
			)
			COLLATE='utf8_bin'
			ENGINE=InnoDB;"
	    );
	    $this->tableGateway->table = 'options';
        $last_updated = $this->tableGateway->select( array( 'option_name' => 'es_last_added_index' ) );
	    $last_id_added = $last_updated->current();

	    // if cron hasn't run yet, we start from 0
	    if ( !$last_id_added ) {
		    $last_id_added = 0;
	    }
	    $this->tableGateway->table = 'comments';
        $last_updated_id = $last_updated->current();
        $comments_data = $this->tableGateway->select( 'where id > ' . $last_id_added );
        if ( !$comments_data ) {
            throw new \Exception( 'No comments found' );
        }
        return $comments_data;
    }

	public function getUpdatedQueue() {

	}

}