<?php
namespace ElasticSearch\Model;

use ElasticSearch\Model\DBMethods;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\I18n\Translator\Translator;
use Zend\Http\Client;
use Zend\Http\Client\Adapter\Curl;
class BulkActions {

	protected $db;

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
        foreach( $comments as $comment ) {
            $json .= '{"create":{"_index":"zend","_type":"comment","_id":"' . $comment['id'] . '"}}
            {"email":"' . $comment['email'] . '","comment":"' . $comment['comment'] . '" }
            ';
        }

        $client = new Client( 'http://zend:9200/_bulk');
        $client->setMethod('POST');
        //url http://zend:9200/zend/comment/1
        $client->setRawBody($json);
        $client->setHeaders(
            array(
                'Content-Type: application/json',
            )
        );
        $client->setAdapter(new Curl());
        $client->send();
        var_dump($client->getResponse());
	}

} 