<?php
// module/ElasticSearch/Module.php
namespace ElasticSearch;
use ElasticSearch\Model\ElasticSearchForm;
use ElasticSearch\Model\SearchResults;
use ElasticSearch\Model\BulkActions;


class Module{
    public function getAutoloaderConfig(){
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig(){
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig(){
        return array(
            'factories' => array(
                'ElasticSearch\Model\ElasticSearchForm' => function( $sm ) {
                        return $sm->get( 'ElasticSearchForm' );
                    },
                'ElasticSearch\Model\SearchResults' => function( $sm ) {
                        return $sm->get( 'SearchResults' );
                    },
                'ESTableGateway' => function( $sm ) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $resultSetPrototype = new ResultSet();
                        $resultSetPrototype->setArrayObjectPrototype( new fetchData() );
                        /**
                         * 'comments' is a name of the table for my module
                         */
                        return new TableGateway( 'comments', $dbAdapter, null, $resultSetPrototype);
                    },
                'ElasticSearch\Model\BulkActions' => function( $sm ) {
                        $ESTableGateway = $sm->get( 'ESTableGateway' );
                        return new BulkActions( $ESTableGateway );
                    },
                ),
        );
    }
}
