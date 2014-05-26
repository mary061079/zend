<?php
namespace ElasticSearch\Model;

use ElasticSearch\Model\DBMethods;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\I18n\Translator\Translator;
use Zend\Http\Client;
use Zend\Http\Client\Adapter\Curl;
use Zend\Db\Sql\Sql;

class BulkActions {

	protected $db, $tr;

	public function __construct( DBMethods $DBMethods ) {
		$this->db = $DBMethods;
        $this->tr = new Translator();
	}

	public function bulkInsert() {
        try {
            $comments = $this->db->getLatestComments();
        } catch( \Exception $e ) {
            echo $e->getMessage();
        }

        $json = '';
        if ( empty( $comments ) ) {
            throw new \Exception( __METHOD__ . ":\n" . 'No comments found' );
        }
        foreach( $comments as $comment ) {
            $json .= '{"create":{"_index":"zend","_type":"comment","_id":"' . $comment['id'] . '"}}
            {"email":"' . $comment['email'] . '","comment":"' . $comment['comment'] . '" }
            ';
            $last_id = $comment['id'];
        }

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
            throw new \Exception( __METHOD__ . ":\n" . $errors );
        }
        //set the last id in the list inserted to start next cron run
        $this->db->updateCronInfo( $last_id );
	}

    /**
     * Update all coupons if available for update
     */
    public function bulkUpdate() {
        $cron = $this->db->getCronInfo();
        if ( !empty( $cron ) ) {
            $json = '{ "update" : {"_id" : "%d", "_type" : "comment", "_index" : "zend"} }
            { "doc" : {"email" : "%s","comment":"%s"} }
            ';
            $this->processRequest( $json, $cron->cron_value, 'update' );
        }
    }

    private function processRequest( $json_request, $last_index_id, $request ) {
        try {
            if ( $request == 'update' ) {
                $comments = $this->db->getUpdatedComments();
            } else {
                $comments = $this->db->getLatestComments();
            }
        } catch( \Exception $e ) {
            throw new \Exception( __METHOD__ . ":\n" . $e->getMessage() );
        }
        if ( empty( $comments ) ) {
            throw new \Exception( __METHOD__ . ":\n" . 'No comments found' );
        }
        $json = '';
        foreach( $comments as $comment ) {
            $json .= sprintf($json_request, $comment['id']. $comment['email'],$comment['comment']);
        }

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
            throw new \Exception( __METHOD__ . ":\n" . $errors );
        }
        $this->db->updateCronInfo( $last_index_id );
    }
}