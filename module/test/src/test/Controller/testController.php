<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace test\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use test\Model\fetchData;
use test\Form\testForm;

class testController extends AbstractActionController
{
	protected $commentsData;
    public function indexAction()
    {
        return new ViewModel(
	        array(
		        'comments' => $this->getCommentsData()->fetchAll()
	        )
        );
    }

	public function addAction() {
		// Here we use the class we wrote from module/test/src/test/Form/testForm.php

		$form = new testForm();

		// Setting value on Submit button
		$form->get('submit')->setAttribute( 'value', 'Add' );
		// getting request, method of AbstractActionController class
		$request = $this->getRequest();
		if ( $request->isPost() ) {
			$comment = new fetchData();
			$form->setInputFilter( $comment->getInputFilter() );
			$form->setData( $request->getPost() );

			if ( $form->isValid() ) {
				$comment->exchangeArray( $form->getData() );
				$this->getCommentsData()->saveComment( $comment );

				//redirecting back to the list of albums
				$this->redirect()->toRoute( 'test' );
			}
		}
		//setting the output array
		return array( 'form' => $form );
	}

	public function editAction() {
		//the url is kind of /test/edit/4
		//getting the params; 0 - number of parameter from the end
		$id = (int) $this->params()->fromRoute('id', 0);
		//if id doesn't exist, redirect it to /add page
		if (!$id) {
			return $this->redirect()->toRoute('test', array(
				'action' => 'add'
			));
		}
		try {
			$comment = $this->getCommentsData()->getComment( $id );

		} catch ( \Exception $e ) {
			// how to set query parameters for url string
			return $this->redirect()->toRoute(
	                'test',
	                array(
						'action' => 'index'
					),
					array(
						'query' => array( 'edited' => 'false' )
				)
			);
		}
		$form = new testForm();

		// Setting value on Submit button
		$form->get('submit')->setAttribute( 'value', 'Edit' );
		//this function fills all fields with the comment data
		$form->bind( $comment );

		$request = $this->getRequest();
		if ( $request->isPost() ) {
			$form->setInputFilter( $comment->getInputFilter() );
			$form->setData( $request->getPost() );

			if ( $form->isValid() ) {
				/**
				 * !!!! ATTENTION !!!
				 * we are using here data from getCommenta and not new instance of fetchData
				 */
				$this->getCommentsData()->saveComment( $comment );

				//redirecting back to the list of albums
				$this->redirect()->toRoute( 'test' );
			}
		}
		//setting the output array
		//just in case also return our $id
		return array(
			'form' => $form,
			'id' => $id
		);
	}

	public function deleteAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        $this->getCommentsData()->deleteComment( $id );
       // $this->redirect()->toRoute( 'test' );
	}

	public function getCommentsData() {
		if ( !$this->commentsData ) {
			$sm = $this->getServiceLocator();
			$this->commentsData = $sm->get( 'test\Model\CommentsData' );
		}
		return $this->commentsData;
	}
}
