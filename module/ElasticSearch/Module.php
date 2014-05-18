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
                )
        );
    }
}
