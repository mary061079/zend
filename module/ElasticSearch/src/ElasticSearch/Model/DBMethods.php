<?php
namespace ElasticSearch\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;
use Zend\I18n\Translator\Translator;

class DBMethods {
	protected $tableGateway, $translator, $cache, $sql;
    public function __construct( TableGateway $ESTableGateway ) {
	    $translator = new Translator();
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
	    $cron_info = $this->getCronInfo();
	    $last_id_added =  count( $cron_info ) ? $cron_info->cron_value : 0;
        $select = $this->sql->select();
        $select->from($this->tableGateway->table);
        $select->where->greaterThan( 'id', $last_id_added );
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $comments = $statement->execute();

        if ( !$comments ) {
            throw new \Exception( $this->translator->translate( 'No comments found' ) );
        }
        return $comments;
    }

	/**
	 * Getting all updated comments
	 */
	public function getUpdatedComments() {
		$cron_info = $this->getCronInfo();
		$cron_date =  $cron_info ? $cron_info->date : date( 'Y-m-d H:i:s' );
		$comments = $this->tableGateway->select( array( 'where updated > ' . $cron_date ) );
		if ( !$comments ) {
			throw new \Exception( $this->translator->translate( 'No new comments found' ) );
		}
		return $comments;
	}

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
	 * Getting info about last run of the cronjob
	 */
	private function getCronInfo() {
        $sql = new Sql($this->tableGateway->adapter);
        $select = $sql->select();
        $select->from( 'cron' );
        $select->columns( array( 'cron_value' ) );
        $select->where->equalTo( 'cron_name', 'es_cron' ) ;
        $statement = $sql->prepareStatementForSqlObject($select);
        try {
            $result = $statement->execute();
        } catch( \Exception $e ) {
            throw new \Exception( $e->getMessage() );
        }
        return $result;
	}
}