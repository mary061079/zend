<?php
namespace ElasticSearch\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ElasticSearch\Model\SearchResults;
use ElasticSearch\Form\SearchForm;
use Zend\Http\Client;
use Zend\Http\Client\Adapter\Curl;

class ElasticSearchController extends AbstractActionController
{
    protected $searchResults, $postsPerPage;

    public function indexAction()
    {
        return new ViewModel(
            array(
               'form' => new SearchForm()
            )
        );
    }

    /**
     * http://localhost:9200/zend/comment/_search?q=comment:whatsearch
     *
     * @return array
     */
    public function searchAction() {
        $request = $this->getRequest();
        if ( $request->isGet() ) {
            $form = new SearchForm();
            $form->setInputFilter( $form->getInputFilter() );
            $form->setData( $request->getPost() );

            if ( $form->isValid() ) {
                $search = new SearchResults( $request->getQuery( 'search' ) );
            }
            $config = $this->getServiceLocator()->get('Config');
            $this->postsPerPage = $config['router']['routes']['search']['options']['posts_per_page'];

            $client = new Client( 'http://zend:9200/zend/comment/_search?q=comment:' . $search->post .
                '&from=' . $this->params()->fromRoute( 'id', 0 ) .
                '&size=' . $this->postsPerPage );
            $client->setAdapter(new Curl());
            $client->send();
            $response = $client->getResponse();
            if ( $response->getStatusCode() != 200 ) {
                $json = json_decode( $response->getContent() );
                return array( 'response' => $json->error );
            }
            $json = json_decode( $response->getContent() );

            // if somehow we received the wrong format of the response
            if ( !$json ) {
                return array( 'response' => $response->getContent() );
            }
            $view = array(
                'response' => $json,
                'query' => $search->post,
                'pagination' => array()
            );
            if ( $json->hits->total > $this->postsPerPage ) {
                $view['pagination'] = array(
                        'total' => $json->hits->total,
                        'current' => $this->params()->fromRoute( 'id', 1 ),
                        'posts_per_page' => $this->postsPerPage
                );
            }
            return $view;
        } else {
            $this->redirect()->toRoute( '/' );
        }
    }
}
