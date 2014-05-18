<?php
namespace ElasticSearch\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ElasticSearch\Model\SearchResults;
use ElasticSearch\Form\SearchForm;

class ElasticSearchController extends AbstractActionController
{
    protected $searchResults;

    public function indexAction()
    {
        return new ViewModel(
            array(
               'form' => new SearchForm()
            )
        );
    }

    public function searchAction() {
        $request = $this->getRequest();
        if ( $request->isPost() ) {
            $form = new SearchForm();
            $form->setInputFilter( $form->getInputFilter() );
            $form->setData( $request->getPost() );
            if ( $form->isValid() ) {
                $search = new SearchResults( $request->getPost( 'search' ) );
            }
//                $comment->exchangeArray( $form->getData() );
//                $this->getCommentsData()->saveComment( $comment );
//
//                //redirecting back to the l   ist of albums
//                $this->redirect()->toRoute( 'test' );
//            }
//        }
//        //setting the output array
        }
        return array( 'response' => $search );
    }
}
