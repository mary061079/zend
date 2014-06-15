<?php
namespace ElasticSearch\Model;

use Zend\View\Model\ViewModel;
use ElasticSearch\Form\SearchForm;

class SearchResults{
    public $post;

    public function __construct( $post ) {
        $this->post = $post;
    }

}