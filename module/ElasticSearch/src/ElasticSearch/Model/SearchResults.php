<?php
namespace ElasticSearch\Model;

class SearchResults {
    public $post;

    public function __construct( $post ) {
        $this->post = $post;
    }
}