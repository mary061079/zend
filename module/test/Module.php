<?php
// module/test/Module.php
namespace test;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use test\Model\fetchData;
use test\Model\CommentsData;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

// -- Table Gateways

class Module {
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

	/**
	 *  For making direct DB queries we can use 2 methods:
	 *
	 * 1. class TableGateway which has adapter class included, available by calling magic __get method:
	 * $tableGateway = new TableGateway('comments', $dbAdapter, null, $resultSetPrototype);
	 * $adapter->$tableGateway->adapter;
	 *
	 * We can use this adapter class to make direct mysql queries in the model, i.e., we have
	 * /test/Model/CommentsData.php and use:
	 * $this->dbAdapter->query( "SELECT * FROM {$this->tableGateway->table} limit 0, 10",
	 *
	 * 2. Call adapter directly as it is done in my example below. Return instance of adapter class
	 */
    public function getServiceConfig(){
        return array(
            'factories' => array(

//				'test\Model\CommentsData' => function( $sm ) {
//						$tableGateway = $sm->get( 'testTableGateway' );
//						$table = new CommentsData( $tableGateway );
//						return $table;
//					},
				'test\Model\CommentsData' => function( $sm ) {
						$testDbAdapter = $sm->get( 'testDbAdapter' );
						$table = new CommentsData( $testDbAdapter );
						return $table;
					},
				'testTableGateway' => function( $sm ) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototype = new ResultSet();
						$resultSetPrototype->setArrayObjectPrototype( new fetchData() );
						/**
						 * 'comments' is a name of the table for my module
						 */
						return new TableGateway( 'comments', $dbAdapter, null, $resultSetPrototype);
					},
                'testDbAdapter' => function( $sm ) {
		                return $sm->get( 'Zend\Db\Adapter\Adapter' );
		            }
            )
        );
    }
}
