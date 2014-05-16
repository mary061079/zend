<?php
// module/test/conﬁg/module.config.php:
return array(
	'router' => array(
		'routes' => array(

			'test' => array(
				'type'    => 'segment',
				'options' => array(
					'route'    => '/test[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id'     => '[0-9]+',
					),
					'defaults' => array(
						'controller'    => 'test\Controller\test',
						'action'        => 'index',
					),
				),
				'may_terminate' => true,

			),
		),
	),
    'controllers' => array(
      'invokables' => array(
        'test\Controller\test' => 'test\Controller\testController'
      ),

    ),
    'view_manager' => array(

//	    'template_map' => array(
//		    'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
//		    'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
//		    'error/404'               => __DIR__ . '/../view/error/404.phtml',
//		    'error/index'             => __DIR__ . '/../view/index.phtml',
//	    ),
        'template_path_stack' => array(
	        'test' => __DIR__ . '/../view',
        ),
    ),
    //'models' =>
);
$callbackCache = new Zend\Cache\Pattern\CallbackCache();
$callbackCache->setOptions(new Zend\Cache\Pattern\PatternOptions(array(
	'storage' => 'apc',
)));