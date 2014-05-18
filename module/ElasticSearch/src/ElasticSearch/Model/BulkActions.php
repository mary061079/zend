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
        $last_updated = $this->tableGateway->select( array( 'option_name' => 'es_last_added_index' ) );
        $last_updated_id = $last_updated->current();
        $comments_data = $this->tableGateway->select( 'where id > ' . $last_updated_id );
        if ( !$comments_data ) {
            throw new \Exception( 'No comments found' );
        }
        return $comments_data;

    }

}