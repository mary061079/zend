<?php
/**
 * Created by PhpStorm.
 * User: marina
 * Date: 5/18/14
 * Time: 2:08 PM
 */

namespace ElasticSearch\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Console\Request as ConsoleRequest;


class BulkActionsController extends AbstractActionController {

    public function updateAction() {
        //$request = $this->getRequest();
        var_dump('sdfdsfs');
        return new ViewModel();
    }

    public function addAction() {
        $request = $this->getRequest();
    }

    public function deleteAction() {
        $request = $this->getRequest();
    }

} 