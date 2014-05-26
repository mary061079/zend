<?php
namespace ElasticSearch\Model;

use ElasticSearch\Model\DBMethods;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\I18n\Translator\Translator;
use Zend\Http\Client;
use Zend\Http\Client\Adapter\Curl;
class BulkActions {

	protected $db, $tr;

	public function __construct( DBMethods $DBMethods ) {
		$this->db = $DBMethods;
        $this->tr = new Translator();
	}

	public function __call( $name, $arguments ) {
		echo "Calling object method '$name' " . "\n";
	}

	public function bulkInsert() {
        try {
            $comments = $this->db->getLatestComments();
        } catch( \Exception $e ) {
            echo $e->getMessage();
        }
        $json = '';
        foreach( $comments as $comment ) {
            $json .= '{"create":{"_index":"zend","_type":"comment","_id":"' . $comment['id'] . '"}}
            {"email":"' . $comment['email'] . '","comment":"' . $comment['comment'] . '" }
            ';
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
			throw new \Exception( $response->getContent() );
		}
		$json = json_decode( $response->getContent() );

		// if somehow we received the wrong format of the response
		if ( !$json ) {
			throw new \Exception( $response->getContent() );
		}

		//if we have errors while adding indexes, let's add them into log
		if( $json->errors === true ) {
			$errors = '';
			foreach( $json->items as $item ) {
				if ( !empty( $item->create->error ) ) {
					$errors .= $item->create->error . "\n";
				}
			}
			throw new \Exception( $errors );
		}
	}
}