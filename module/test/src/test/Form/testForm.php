<?php
namespace test\Form;
use Zend\Form\Form;
class TestForm extends Form {
	public function __construct( $name = null ) {
		parent::__construct('test');

		$this->add(
			array(
				'name' => 'id',
			    'type' => 'hidden'
			)
		);
		$this->add(
		     array(
			     'name' => 'email',
			     'type' => 'email',
		         'options' => array(
			         'label' => 'Email'
		         )
		     )
		);
		$this->add(
		     array(
			     'name' => 'comment',
			     'type' => 'textarea',
			     'options' => array(
				     'label' => 'Comment'
			     )
		     )
		);
		$this->add(
		     array(
			     'name' => 'submit',
			     'type' => 'submit',
		         'attributes' => array(
			         'value' => 'Submit',
		             'id' => 'submit'
		         )
		     )
		);
	}
}

