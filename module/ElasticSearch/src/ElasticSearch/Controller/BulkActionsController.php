<?php
/**
 * Created by PhpStorm.
 * User: marina
 * Date: 5/18/14
 * Time: 2:08 PM
 */

namespace ElasticSearch\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use ElasticSearch\Model\BulkActions;
use Zend\Console\Request as ConsoleRequest;


class BulkActionsController extends AbstractActionController {

    public function updateAction() {
        $request = $this->getRequest();
        var_dump($request);
    }

    public function addAction() {
        $request = $this->getRequest();
    }

    public function deleteAction() {
        $request = $this->getRequest();
    }

} 