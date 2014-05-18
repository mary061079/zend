<?php
namespace ElasticSearch\Form;
use Zend\Form\Form;
class SearchForm extends Form {
	public function __construct( $name = null ) {
		parent::__construct('ElasticSearch');

		$this->add(
			array(
				'name' => 'search',
			    'type' => 'text',
                'options' => array(
                    'label' => 'Search'
                ),
                'validators' => array()
			)
		);
		$this->add(
		     array(
			     'name' => 'submit',
			     'type' => 'submit',
		         'attributes' => array(
			         'value' => 'Seach',
		             'id' => 'submit'
		         )
		     )
		);
	}
}

