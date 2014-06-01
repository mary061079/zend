<?php
namespace ElasticSearch\Model;

use ElasticSearch\Model\DBMethods;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\I18n\Translator\Translator;
use Zend\Http\Client;
use Zend\Http\Client\Adapter\Curl;
use Zend\Db\Sql\Sql;

class BulkActions {

	protected $db, $tr, $log_file;

	public function __construct( DBMethods $DBMethods ) {
		$this->db = $DBMethods;
        $this->tr = new Translator();
		$this->log_file = '/path/to/log-file';
	}

	/**
	 * Import all data into ES database
	 */
	public function bulkInsert() {
		$json = '{"create":{"_index":"zend","_type":"comment","_id":"%d"}}
            {"email":"%s","comment":"%s","created":"%s","updated":"%s" }
            ';
		$this->processESRequest( $json );
	}

    /**
     * Update all coupons if available for update
     * php /var/www/zend/public/index.php esbulkactions update
     */
    public function bulkUpdate() {
        try{
		    $cron = $this->db->getCronInfo();
	    } catch( \Exception $e ) {
			throw new \Exception(  __METHOD__ . ":\n" . $e->getMessage() );
	    }
	    if ( !$cron ) {
		    //we don't need to do anything else if we have no data to update
		    return;
	    }
	    $json = '{ "update" : {"_id" : "%d", "_type" : "comment", "_index" : "zend"} }
            { "doc" : {"email" : "%s","comment":"%s","created":"%s","updated":"%s} }
            ';
        $this->processESRequest( $json, 'update', $cron->cron_value );
    }

	/**
	 * Bulk delete comments
     * php /var/www/zend/public/index.php esbulkactions delete
	 *
	 * @throws \Exception
	 */
	public function bulkDelete() {
		$queue = $this->db->getCommentsForDelete();

		if ( !$queue ) {
			return;
		}
        $url = 'http://zend:9200/_all/_query';
		$json = '{
                    "terms": {
                        "_id": [' . $queue->option_value . ']
                    }
                }
        ';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $if_deleted = json_decode( $result );
        if ( !$if_deleted ) {
	        throw new \Exception( __METHOD__ . ":\n" . 'Error in curl processing' );
        }
        foreach($if_deleted->_indices->zend as $shard ) {
            if ( $shard->failed != 0 ) {
                $this->log_info( __METHOD__ . ":\n delete failed" );
            }
        }

        //delete the queue
        $this->db->tableGateway->adapter->query(
            "DELETE from options where option_name = 'deleted_comments'",
            array()
        );
  	}

	/**
	 * Process any bulk request.
	 *
	 * @param $json_request
	 * @param $last_index_id
	 * @param $action Can be 'update' or 'insert'
	 *
	 * @throws \Exception
	 */
	private function processESRequest( $json_request, $action = 'insert', $last_added_id = 0 ) {
        try {
            if ( $action == 'update' ) {
                $comments = $this->db->getUpdatedComments();
            } else {
                $comments = $this->db->getLatestComments();
            }
        } catch( \Exception $e ) {
            throw new \Exception( __METHOD__ . ":\n" . $e->getMessage() . "\n" );
        }
        if ( empty( $comments ) ) {
            throw new \Exception( __METHOD__ . ":\n" . 'No comments found' );
        }
        $json = '';

        foreach( $comments as $comment ) {
            $json .= sprintf( $json_request, $comment['id'], $comment['email'],
                str_replace("\'", "'",mysql_real_escape_string( $comment['comment'] )), $comment['created'], $comment['updated'] );
	        $last_id = $comment['id'];
        }
        //var_dump($json, "\n\n" );die;
		// if we have $action = 'update' we should not update last id, only date, that's why we give $last_added_id an
		// old value.
		$last_id = $last_added_id != 0 ? $last_added_id : $last_id;
		$client = new Client( 'http://zend:9200/_bulk');
        $client->setMethod('POST');

        $client->setRawBody($json);
        $client->setHeaders(
            array(
                'Content-Type: application/json',
            )
        );
        $client->setAdapter(new Curl());
        $client->send();
        //url http://zend:9200/zend/comment/1
        //if we didn't receive a correct response

        $response = $client->getResponse();
        if ( $response->getStatusCode() != 200 ) {
            throw new \Exception( __METHOD__ . ":\n" . $response->getContent() );
        }
        $json = json_decode( $response->getContent() );

        // if somehow we received the wrong format of the response
        if ( !$json ) {
            throw new \Exception( __METHOD__ . ":\n" . $response->getContent() );
        }

        //if we have errors while adding indexes, let's add them into log
        $errors = '';
        foreach( $json->items as $item ) {
            if ( !empty( $item->create->error ) ) {
                $errors .= $item->create->error . "\n";
            }
        }

        if ( strlen( $errors ) > 0 ) {
            $this->log_info( __METHOD__ . ":\n" . $errors );
        }
        $this->db->updateCronInfo( $last_id );
    }

	public function log_info( $info ) {
		echo  date( 'Y-m-d H:i:s' ) . "\n" . $info;
//		if ( strlen( $info ) > 0 ) {
//			$f = fopen( $this->log_file, 'a' );
//			$date = date( 'Y-m-d H:i:s' ) . "\n";
//			fwrite( $f, $date . $info . "\n" );
//			fclose( $f );
//		}
	}
}