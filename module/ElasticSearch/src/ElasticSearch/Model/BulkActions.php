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
//

        $json = array(
            'took' => count($comments),
            'items' => array(
                array(
                'create' => array(
                    '_index' => 'zend',
                    '_type' => 'comment',
                    '_id' => 1,
                    '_version' => 1
                ),
                )
            )
        );
//        foreach( $comments as $comment ) {
//            $json['items'][] = array('create' => $comment);
//        }
        $json = json_encode($json) . "\n";
//        var_dump($json);die;
        $client = new Client( 'http://zend:9200/_bulk');
        $client->setMethod('POST');
        $json = '{"took":2,"items":[{"index":{"_index":"zend","_type":"comment","_id":"1","_version":1}}' . "\n" .
        '{"what":"100"}{"create":{"_index":"zend","_type":"comment","_id":"2","_version":1}}' . "\n" .
            '{"what":"100"}]}';
        $json = '{"index":{"_index":"test","_type":"type1","_id":"1"}}'."\n".
'{"field1":"value1"}';
        $client->setRawBody($json);
        $client->setHeaders(
            array(
                'Content-Type: application/json',
          //  'Authorization: Bearer 1jo41324knj23o',
            )
        );
        //curl -s -XPOST http://zend:9200/_bulk -d @file '{"index":{"_index":"zend","_type":"comment","_id":"1","_version":1}}'. "\n";

        $client->setAdapter(new Curl());
        $client->send();
        var_dump($client->getResponse());
	}

} 