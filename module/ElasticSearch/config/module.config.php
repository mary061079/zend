<?php
// module/ElasticSearch/conﬁg/module.config.php:
//http://localhost:9200/zend/comment/_search?q=comment:something

return array(
    'router' => array(
        'routes' => array(

            'search' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/search[/][:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller'    => 'ElasticSearch\Controller\ElasticSearch',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,

            ),
        ),
    ),
    /**
     * console application to manage bulk add/delete/update indexes in Elastic Search
     *
     * php /var/www/zend/public/index.php esbulkactions update
     * php /var/www/zend/public/index.php esbulkactions delete
     */
    'console' => array(
        'router' => array(
            'routes' => array(
                'esbulkactions' => array(
                    'options' => array(
                        'route' => 'esbulkactions [update|delete]:mode',
                        'defaults' => array(
                            'controller' => 'ElasticSearch\Controller\BulkActions',
                            'action' => 'index'
                        )
                    )
                )
            )
        )
    ),
    'controllers' => array(
      'invokables' => array(
        'ElasticSearch\Controller\ElasticSearch' => 'ElasticSearch\Controller\ElasticSearchController',
        'ElasticSearch\Controller\BulkActions' => 'ElasticSearch\Controller\BulkActionsController'
      ),
    ),

    /**
     * !!! ATTENTION !!!!
     * when creating the module with 2 caps in the name, i.e. ElasticSearch, it thinks that all views are in elastic-search
     * folder, i.e. we need to rename the folder /view/elastic-search/elastic-search
     */
    'view_manager' => array(
        'display_not_found_reason' => true,
        'template_path_stack' => array(
            'ElasticSearch' => __DIR__ . '/../view',
        ),
    ),
);
