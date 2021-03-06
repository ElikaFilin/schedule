<?php
/**
 * Created by IntelliJ IDEA.
 * User: DenysBarsuk
 * Date: 16.10.2018
 * Time: 14:53
 */

namespace Schedule\Modules\Authority\Controllers;


use Schedule\Core\PageParser;
use Schedule\Modules\Authority\Models\PageSystem;

class PageController extends ControllerBase
{

    public function indexAction()
    {
        $page_sys=new PageSystem();
        $uni_pages=$page_sys->getAllPages();
        $this->view->pages=$uni_pages;
    }
    public function createAction()
    {   $pp=new PageParser();
        $page_sys=new PageSystem();
        $form=$page_sys->getForm($pp);

        if($form->isValid($_POST,$pp)){
        $pp->savePage();
            $this->flashSession->success("The page module ".$pp->module_name." was successfully saved ");
        }




//        $this->dispatcher->forward([
//              "module"     => "Authority",
//              "controller" => "page",
//              "action"     => "index",
//        ]);
    }

    public function formAction()
    {
        $pp = new PageParser();
        $page_sys=new PageSystem();
        if($this->request->getQuery('edit')){
          if(($params=$this->dispatcher->getParams())!==false&&(!empty($params[0]))){
              $uri='';
              if ((($lang_id = $this->request->getQuery('lang')) !== false)) {
                  $lang = $pp->getLanguageById($lang_id);
              } else {
                  $this->response->redirect($this->router->getRewriteUri());
                  exit();
              }

              $module=$params[0];
              $form = $page_sys->getForm($pp->getPage($lang, '', $module));
              $this->view->setVar('title','Editing of existing page');
              }
        }else{
            $form=$page_sys->getForm($pp);
            $this->view->setVar('title','Creation of new page');
        }

        $this->view->form=$form;

    }
}