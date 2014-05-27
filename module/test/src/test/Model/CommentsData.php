<?php
/**
 * Created by PhpStorm.
 * User: marina
 * Date: 5/13/14
 * Time: 4:49 PM
 */

namespace test\Model;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\ResultSet\ResultSet;

class CommentsData {
	protected $tableGateway, $dbAdapter;
//	public function __construct( TableGateway $tableGateway ) {
//		$this->tableGateway = $tableGateway;
//
//	}
	public function __construct( DbAdapter $DbAdapter ) {
		$this->dbAdapter = $DbAdapter;

		$resultSetPrototype = new ResultSet();
		$resultSetPrototype->setArrayObjectPrototype( new fetchData() );
		/**
		 * 'comments' is a name of the table for my module
		 */
		$this->tableGateway = new TableGateway('comments', $this->dbAdapter, null, $resultSetPrototype);

	}

	public function fetchAll() {
		/**
		 * if we use tableGateway class instead of adapter, we also can set a direct query with this:
		 * $this->tableGateway->adapter->query(
		 * "SELECT * FROM {$this->tableGateway->table} limit 0, 10",
		 * array()
		 * );
		 */
		return $this->dbAdapter->query( "SELECT * FROM {$this->tableGateway->table} limit 0, 10",
				array()
			);
	}

	public function getComment( $id ) {
		$comment_data = $this->tableGateway->select( array( 'id' => (int)$id ) );
		$comment = $comment_data->current();
		if ( !$comment ) {
			throw new \Exception( "Could not find the comment" );
		}
		return $comment;
	}

	public function saveComment( fetchData $comment ) {
		$data = array(
			'email' => $comment->email,
			'comment' => $comment->comment,
		    'created' => date( 'Y-m-d H:i:s' ),
		    'updated' => date( 'Y-m-d H:i:s' )
		);
		/** @var if we want to update, we give the function an object with $id*/
		$comment_id = (int)$comment->id;
		if ( $comment_id == 0 ) {
			$this->tableGateway->insert( $data );
		} else {
			try {
				$this->getComment( $comment_id );
			} catch ( \Exception $e ) {
				throw new \Exception( $e->getMessage() );
			}
			unset($data['created']);
			$this->tableGateway->update( $data, array(
				'id' => $comment_id
			) );
		}
	}

	/**
	 * Creating a list of ids for deleting from ES database. Didn't find a better way to note each id into the list
	 * and save into the cron table on delete event
	 * TODO: need to think how to implement some hooking system, dind of this:
	 * http://www.php.net/manual/en/functions.anonymous.php. May be use globals??
	 *
	 * @param $delete_id
	 */
	public function formDeleteQueue( $delete_id ) {
		$date = date( 'Y-m-d H:i:s' );
		$this->tableGateway->adapter->query(
            'INSERT INTO options (option_name, option_value, date)
			VALUES(?, ?, ?)
			ON DUPLICATE KEY UPDATE
			option_value = concat(option_value, ",?"), date = ?',
            array( 'deleted_comments', $delete_id, $date, $delete_id, $date )
		);
	}

	/**
	 * Delete a comment
	 *
	 * @param $id
	 */
	public function deleteComment( $id ) {
		$this->tableGateway->delete( array( 'id' => $id ) );
		$this->formDeleteQueue( $id );
	}
}