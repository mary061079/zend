<?php
/**
 * Created by PhpStorm.
 * User: marina
 * Date: 5/13/14
 * Time: 4:49 PM
 */

namespace test\Model;
use Zend\Db\TableGateway\TableGateway;

class CommentsData {
	protected $tableGateway;
	public function __construct( TableGateway $tableGateway ) {
		$this->tableGateway = TableGateway;
	}

	public function fetchAll() {
		return $this->tableGateway->select();
	}

	public function getComment( $id ) {
		$comment_data = $this->tableGateway->select( array( 'id' => (int)$id ) );
		$comment = $comment_data->current();
		if ( !$comment ) {
			throw new \Exception( "Could not find the comment" );
		}
		return $comment;
	}

	public function saveComment( test $comment ) {
		$data = array(
			'email' => $comment->email,
			'comment' => $comment->comment,
		    'created' => 'NOW()'
		);
		/** @var if we want to update, we give the function an object with $id*/
		$comment_id = (int)$comment->id;
		if ( $comment_id == 0 ) {
			$this->tableGateway->insert( $data );
		} else {
			$this->tableGateway->update( $data );
		}
	}
}