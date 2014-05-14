<?php
// module/test/conﬁg/module.config.php:
return array(
	'router' => array(
		'routes' => array(
			'home' => array(
				'type' => 'Zend\Mvc\Router\Http\Literal',
				'options' => array(
					'route'    => '/test',
					'defaults' => array(
						'controller' => 'test\Controller\test',
						'action'     => 'test',
					),
				),
			),
			// The following is a route to simplify getting started creating
			// new controllers and actions without needing to create a new
			// module. Simply drop new controllers in, and you can access them
			// using the path /application/:controller/:action
			'test' => array(
				'type'    => 'Literal',
				'options' => array(
					'route'    => '/test',
					'defaults' => array(
						'__NAMESPACE__' => 'test\Controller',
						'controller'    => 'test',
						'action'        => 'test',
					),
				),
				'may_terminate' => true,
				'child_routes' => array(
					'default' => array(
						'type'    => 'Segment',
						'options' => array(
							'route'    => '/[:controller[/:action]]',
							'constraints' => array(
								'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
								'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
							),
							'defaults' => array(
							),
						),
					),
				),
			),
		),
	),
    'controllers' => array(
      'invokables' => array(
        'test\Controller\test' => 'test\Controller\testController'
      ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
	        __DIR__ . '/../view',
        ),
    ),
    //'models' =>
);
$callbackCache = new Zend\Cache\Pattern\CallbackCache();
$callbackCache->setOptions(new Zend\Cache\Pattern\PatternOptions(array(
	'storage' => 'apc',
)));