<?php
class PessoasController extends Akna_Rest_Controller
    {
    
    /**
     * (non-PHPdoc)
     * @see Zend_Rest_Controller::indexAction()
     */
    public function indexAction()
    {
        if($this->auth->isValid())
            {
            $pessoas = $this->entityManager->getRepository('Default_Model_Pessoa')->findAll();
            $this->view->items = array();
            if($pessoas)
                foreach($pessoas as $pessoa)
                    $this->view->items[] = array(
                        'id'=>$pessoa->getId(),
                        'nome'=>$pessoa->getNome(),
                        'sobrenome'=>$pessoa->getSobrenome()
                        );
            }
        }
    
    /**
     * (non-PHPdoc)
     * @see Zend_Rest_Controller::getAction()
     */
    public function getAction()
        {
        if($this->auth->isValid())
            {
            $pessoa = $this->entityManager->find('Default_Model_Pessoa', (int)$this->getRequest()->getParam('id'));
            if($pessoa)
                {
                $this->view->id = $pessoa->getId();
                $this->view->nome = $pessoa->getNome();
                $this->view->sobrenome = $pessoa->getSobrenome();
                }
            else
                {
                $this->view->code = 404;
                $this->view->message = 'Not found';
                $this->getResponse()->setHttpResponseCode($this->view->code);
                $this->_helper->viewRenderer('status', null, true);
                }
            }
        }
    
    /**
     * (non-PHPdoc)
     * @see Zend_Rest_Controller::postAction()
     */
    public function postAction()
        {
        if($this->auth->isValid())
            {
            $request = $this->getRequest();
            $pessoa = new Default_Model_Pessoa;
            $pessoa->setNome($request->getPost('nome'));
            $pessoa->setSobrenome($request->getPost('sobrenome'));
            $this->entityManager->persist($pessoa);
            $this->entityManager->flush();
            $id = $pessoa->getId();
            
            $this->_helper->viewRenderer->setNoRender(true);
            $this->getResponse()->clearAllHeaders()->setRedirect(
            	'http://'.$request->getServer('HTTP_HOST').$request->getBaseUrl().'/pessoas/'.$id,
                201
                );
            }
        else
            {
            $this->view->code = 401;
            $this->view->message = 'Unauthorized';
            $this->getResponse()->setHttpResponseCode($this->view->code);
            $this->_helper->viewRenderer('status', null, true);
            }
        }
    
    /**
     * (non-PHPdoc)
     * @see Zend_Rest_Controller::putAction()
     */
    public function putAction()
        {
        if($this->auth->isValid())
            {
            $request = $this->getRequest();
            $pessoa = $this->entityManager->find('Default_Model_Pessoa', (int)$this->getRequest()->getParam('id'));
            if($pessoa)
                {
                $pessoa->setNome($request->getParam('nome'));
                $pessoa->setSobrenome($request->getParam('sobrenome'));
                $this->entityManager->persist($pessoa);
                $this->entityManager->flush();
                }
            $this->view->code = 204;
            $this->view->message = 'No Content';
            $this->getResponse()->setHttpResponseCode($this->view->code);
            $this->_helper->viewRenderer('status', null, true);
            }
        else
            {
            $this->view->code = 401;
            $this->view->message = 'Unauthorized';
            $this->getResponse()->setHttpResponseCode($this->view->code);
            $this->_helper->viewRenderer('status', null, true);
            }
        }
    
    /**
     * (non-PHPdoc)
     * @see Zend_Rest_Controller::deleteAction()
     */
    public function deleteAction()
        {
        if($this->auth->isValid())
            {
            $request = $this->getRequest();
            $pessoa = $this->entityManager->find('Default_Model_Pessoa', (int)$this->getRequest()->getParam('id'));
            if($pessoa)
                {
                $this->entityManager->remove($pessoa);
                $this->entityManager->flush();
                }
            $this->view->code = 204;
            $this->view->message = 'No Content';
            $this->getResponse()->setHttpResponseCode($this->view->code);
            $this->_helper->viewRenderer('status', null, true);
            }
        else
            {
                $this->view->code = 401;
                $this->view->message = 'Unauthorized';
                $this->getResponse()->setHttpResponseCode($this->view->code);
                $this->_helper->viewRenderer('status', null, true);
            }
        }
    }