<?php
class AbstractController extends Fgsl_Crud_Controller_Abstract
{
    public function init()
    {
        parent::init();
        $this->translate = Zend_Registry::get( 'translate' );

        $this->_useModules = true;
        $this->_uniqueTemplatesForApp = false;
        $this->_searchButtonLabel = 'Pesquisar';
        $this->_searchOptions = array('nome'=>'Nome');
        $this->_config();

        $this->_baseUrl = $this->getFrontController()->getBaseUrl() . "/" . $this->getRequest()->getControllerName();
        Fgsl_Session_Namespace::set('rest_url_path',$this->_rest_url);

        //Verifica se existe um javascript específico para o controller acessado
        $js_controller = PUBLIC_PATH . "/js/controller/" . $this->getRequest()->getControllerName() . ".js";
        if(file_exists($js_controller))
            Fgsl_Session_Namespace::set('js-controller',file_get_contents($js_controller));
    }

    public function indexAction()
    {
        $response = '';
        try
        {
            $response = Zend_Json::decode($this->exeCurl($this->_rest_url));
        }
        catch(Exception $e ){}

        $this->view->assign('tabela', $this->getTable($response));
        $this->view->assign('translate', $this->translate);
        $this->_response->setBody($this->view->render($this->_controllerAction.'/list.phtml'));
    }

    public function insertAction()
    {
        $id = $this->getRequest()->getParam('id');
        if(empty($id))
        {
            $this->method = "POST";
            $url = $this->_rest_url . 'form';
        }
        else
        {
            $this->method = "PUT";
            $url = $this->_rest_url . "form/{$id}";
        }

        $response = Zend_Json::decode($this->exeCurl($url));
        $this->view->assign('response', $response);
        $this->view->assign('title',$this->view->escape($this->_title));
        $this->view->assign('method',$this->method);
        $this->view->assign('action',$this->_rest_url);
        $this->view->assign('translate', $callbackthis->translate);
        $this->_response->setBody($this->view->render($this->_controllerAction.'/insert.phtml'));
    }

    public function editAction()
    {
        $this->_forward('insert');
    }

    public function getTable($response)
    {
        $arrHeaders = array();
        $strTable = "";
        if(!empty($response))
        {
            foreach($response as $line => $rows)
            {
                $create_headers = empty($arrHeaders);
                foreach($rows as $id => $value)
                {
                    if($create_headers)
                        $arrHeaders[] = "<th>".$this->translate->translate($id)."</th>";
                    $arrRowsTable[$line][] = "<td>{$value}</td>";
                }
                //Opções
                $delete = "<a href='{$this->_rest_url}".$rows["id"]."' class='action_delete'><img src='".BASE_URL."/public/image/package/delete.png' title='".$this->translate->translate("Remover")."' /></a>";
                $edit = "<a href='{$this->_rest_url}edit/id/".$rows["id"]."' class='action_edit'><img src='".BASE_URL."/public/image/package/pencil.png' title='".$this->translate->translate("Editar")."' /></a>";
                $view = "<a href='{$this->_rest_url}form/".$rows["id"]."' class='action_view'><img src='".BASE_URL."/public/image/package/magnifier.png' title='".$this->translate->translate("Editar")."' /></a>";
                $arrRowsTable[$line][] = "<td align='center'>{$view}{$edit}{$delete}</td>";
            }
        }
        else
            $arrRowsTable[][] = "<td>".$this->translate->translate("Não existe registro")."</td>";

        $strTable.= "<table border='0' class='ui-widget ui-widget-content' style='margin: 1em 0; border-collapse: collapse; width: 100%;'>";
        if(!empty($arrHeaders))
        {
            $strTable.= "<thead>";
            $strTable.= "  <tr class='ui-widget-header'>";
            foreach($arrHeaders as $header)
                $strTable.= "{$header}";
            $strTable.= "    <th>".$this->translate->translate("Opções")."</th>";
            $strTable.= "  </tr>";
            $strTable.= "</thead>";
        }

        if(!empty($arrRowsTable))
        {
            $strTable.= "<tbody>";
            foreach($arrRowsTable as $cels)
            {
                $strTable.= "<tr>";
                foreach($cels as $cel)
                    $strTable.= $cel;
                $strTable.= "</tr>";
            }
            $strTable.= "</tbody>";
        }
        $strTable.= "</table>";

        return $strTable;
    }
    /**
    * Executa o camando cURL e retorna o conteúdo de resposta
    * @param String $url
    * @param Array $headers
    * @return String
    */
    public function exeCurl($url, $headers = array())
    {
        $arrHeader["accept"] = "application/json";
        $arrHeader["cache-control"] = "max-age=0";
        $arrHeader["connection"] = "keep-alive";
        $arrHeader["keep-alive"] = "300";
        $arrHeader["accept-charset"] = "ISO-8859-1,utf-8;q=0.7,*;q=0.7";
        $arrHeader["accept-language"] = "en-us,en;q=0.5";
        $arrHeader["pragma"] = ""; // browsers keep this blank.
        //Se for passado algum cabeçalho deve incorpora-lo no cabeçalho default
        if(!empty($header))
          foreach($header as $key => $header)
              $arrHeader[$key] = $header;
        foreach($arrHeader as $key => $header)
            $arrHeader_[] = "{$key}: {$header}";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Googlebot/2.1 (+http://www.google.com/bot.html)');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $arrHeader_);
        curl_setopt($curl, CURLOPT_REFERER, 'http://www.google.com');
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);

        $output = curl_exec($curl); // execute the curl command
        curl_close($curl); // close the connection

        return $output;
    }
}