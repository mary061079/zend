<?php
namespace ElasticSearch\Model;

/**
 * need this to use form classes
 */
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class ElasticSearchForm implements InputFilterAwareInterface {
	public $id, $email, $comment, $created;
	protected $inputFilter;

	/**
	 * the function name MUST be only exchangeArray
	 */
	public function exchangeArray( $data ) {
		$this->id = ( !empty( $data['id'] ) ) ? $data['id'] : null;
		$this->email = ( !empty( $data['email'] ) ) ? $data['email'] : null;
		$this->comment = ( !empty( $data['comment'] ) ) ? $data['comment'] : null;
		$this->date = ( !empty( $data['created'] ) ) ? $data['created'] : null;
	}

	/**
	 * Using this in order to implement bind function in the testController.
	 */
	public function getArrayCopy() {
		return get_object_vars($this);
	}

	public function setInputFilter( InputFilterInterface $inputFilter ) {
		throw new \Exception( "Not used" );
	}

	/**
	 * start form validation
	 * all validators can be found in the folder /www/zend/vendor/zendframework/zendframework/library/Zend/Validator/
	 * each array element is the class instance, i.e.
	 * 'validators' => array(
	 *      'name' => new EmailAddress()
	 * )
	 * is a name of the class in the above folder
	 *
	 * Same with filters array. Folder is /www/zend/vendor/zendframework/zendframework/library/Zend/Filter
	 */
	public function getInputFilter() {
		if ( !$this->inputFilter ) {
			$inputFilter = new InputFilter();

			$inputFilter->add(
				array(
					'name' => 'search',
				    'required' => true,
				)
			);
			$this->inputFilter = $inputFilter;
		}
		return $this->inputFilter;
	}
}