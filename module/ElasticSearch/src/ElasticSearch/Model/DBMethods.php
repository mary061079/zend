<?php
namespace ElasticSearch\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\I18n\Translator\Translator;

class DBMethods {
	public $tableGateway, $translator, $cache, $sql;
    public function __construct( TableGateway $ESTableGateway ) {
	    $this->translator = new Translator();
        $this->tableGateway = $ESTableGateway;
        $this->sql = new Sql($this->tableGateway->adapter);

	    $this->tableGateway->adapter->query(
            "CREATE TABLE IF NOT EXISTS `options` (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`option_name` VARCHAR(100) NOT NULL COLLATE 'utf8_bin',
			`option_value` VARCHAR(500) NOT NULL COLLATE 'utf8_bin',
			`date` DATETIME NOT NULL,
			PRIMARY KEY (`id`),
			INDEX `option_name` (`option_name`),
			)
			COLLATE='utf8_bin'
			ENGINE=InnoDB;"
	    );
	    $this->tableGateway->adapter->query(
                "CREATE TABLE IF NOT EXISTS `cron` (
				    `id` INT(11) NOT NULL AUTO_INCREMENT,
				`cron_name` VARCHAR(50) NOT NULL DEFAULT '0' COLLATE 'utf8_bin',
				`cron_value` VARCHAR(250) NOT NULL DEFAULT '0' COLLATE 'utf8_bin',
				`date` DATETIME NOT NULL,
				PRIMARY KEY (`id`)
			)
			COLLATE='utf8_bin'
			ENGINE=InnoDB;"
	    );
    }

    /**
     * getting new comments for bulk creating ES indexes
     * @throws \Exception
     */
    public function getLatestComments() {
	    try{
		    $cron_info = $this->getCronInfo();
	    } catch( \Exception $e ) {
		    throw new \Exception( $e->getMessage() );
	    }
	    $last_id_added =  $cron_info ? $cron_info->cron_value : 0;
        $select = $this->sql->select();
        $select->from($this->tableGateway->table);

        $select->where->greaterThan( 'id', $last_id_added );
        $statement = $this->sql->prepareStatementForSqlObject($select)->execute();

        $result = new ResultSet();
        $result->initialize( $statement );
        $comments = $result->toArray( $statement );
        if ( empty( $comments ) ) {
            throw new \Exception( $this->translator->translate( 'No comments found' ) );
        }
        return $comments;
    }

	/**
	 * Getting all updated comments
	 */
	public function getUpdatedComments() {
		try{
			$cron_info = $this->getCronInfo();
		} catch( \Exception $e ) {
			throw new \Exception( $e->getMessage() );
		}
		$cron_date =  $cron_info ? $cron_info->date : date( 'Y-m-d H:i:s' );
		$select = $this->sql->select()
        ->from($this->tableGateway->table);
        $select->where->greaterThan( 'updated', $cron_date );
        $statement = $this->sql->prepareStatementForSqlObject($select)->execute();

        $result = new ResultSet();
        $result->initialize( $statement );
        $comments = $result->toArray( $statement );
        if ( !empty( $comments ) ) {
			throw new \Exception( $this->translator->translate( 'No new comments found' ) );
		}
		return $comments;
	}

	/**
	 * Update cron date and last inserted id each time the cron runs
	 *
	 * @param $last_inserted_id
	 */
	public function updateCronInfo( $last_inserted_id ) {
		$date = date( 'Y-m-d H:i:s' );
		$this->tableGateway->adapter->query(
            'INSERT INTO cron (cron_name, cron_value, date)
 			VALUES(?, ?, ?)
 			ON DUPLICATE KEY UPDATE
 			cron_value = ?, date = ?',
			array( 'es_cron', $last_inserted_id, $date, $last_inserted_id, $date )
		);
	}

	/**
	 *
	 * @return array|\ArrayObject|null
	 */
	public function getCommentsForDelete() {
		$select = $this->sql->select()
        ->from('options');
		$select->columns( array( 'option_value' ) );
		$select->where->equalTo( 'option_name', 'deleted_comments' );
		$statement = $this->sql->prepareStatementForSqlObject($select);
		try {
            $queue = $statement->execute();
        } catch( \Exception $e ) {
            throw new \Exception( $e->getMessage() );
        }
        $rowset = new ResultSet();
        $rowset->initialize($queue);
        return $rowset->current();
	}

	/**
	 * Getting info about last run of the cronjob
	 */
	public function getCronInfo() {
        $sql = new Sql($this->tableGateway->adapter);
        $select = $sql->select();
        $select->from( 'cron' );
        $select->columns( array( 'cron_value', 'date' ) );
        $select->where->equalTo( 'cron_name', 'es_cron' ) ;
        $statement = $sql->prepareStatementForSqlObject($select);
        try {
            $result = $statement->execute();
        } catch( \Exception $e ) {
            throw new \Exception( $e->getMessage() );
        }
		$rowset = new ResultSet();
		$rowset->initialize($result);
		return $rowset->current();
    }
}