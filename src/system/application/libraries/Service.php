<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Service {
    
    private $CI	= null;
    private $_output_types=array('json','xml');
    
    function Service() {
        $this->CI=&get_instance();
    }
    //---------------------
    function handle($type, $data) {
        $this->CI->load->model('user_admin_model');
        $data	= trim($data);
        
        // check for empty request...
        if (strlen(trim($data))<0) {
            return array('output'=>'msg','data'=>array('msg'=>'Invalid request [empty]!'));
        }
        
        $hdrs	= array_change_key_case(getallheaders(), CASE_UPPER);
        
        // Split it out if the header includes the character set
        // Ex: "text/xml; charset=UTF-8"
        if (!empty($hdrs['CONTENT-TYPE'])) {
            $ct_p=explode(';', $hdrs['CONTENT-TYPE']);
            $hdrs['CONTENT-TYPE']=$ct_p[0];
        }
        
        // If it's not set, assume it's XML
        if (!isset($hdrs['CONTENT-TYPE']) || $hdrs['CONTENT-TYPE']=='text/xml') {
            $xml=$this->parseReqXML($data);
            if (!$xml) { return array('output'=>'msg','data'=>array('msg'=>'Invalid request!')); }
            $rtype	= (string)$xml->action['type'];
        } elseif (in_array($hdrs['CONTENT-TYPE'], array('text/x-json','text/json','application/json'))) {
            // We're working with json now...
            $xml	= $this->parseReqJson($data);
            if (!$xml) { return array('output'=>'msg','data'=>array('msg'=>'Invalid request!')); }
            $rtype	= (string)$xml->action['type'];
        }

        /**
         * So, we want each of the actions to handle their own authentication
         * information. We need to move the functionality from above (public_actions)
         * down into the actions themselves
         *
         */
        
        ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.BASEPATH.'application/libraries/wsactions');
        $ws_root=$_SERVER['DOCUMENT_ROOT'].'/system/application/libraries/wsactions';

        // Be sure we have at least an action and a type
        if (empty($rtype)) { return array('output'=>'msg','data'=>array('msg'=>'Invalid request type!')); }

        // Get the permissions type of the requested action
        $ret=array();
        $action=$ws_root.'/'.$type.'/'.ucwords($rtype).'.php';
        if (is_file($action)) {
            // Get our base web service library
            $this->CI->load->library('wsactions/BaseWsRequest');
            include_once($action);
            $obj=new $rtype($xml);
            
            // Be sure we have a "checkSecurity" method in the object
            if (!method_exists($obj,'checkSecurity')) { 
                return array('output'=>'msg','data'=>array('msg'=>'Internal security error!'));
            }
            
            if ($obj && $obj->checkSecurity($xml)) {
                // Execute our action...
                $out=$obj->run();
                if (!empty($out)) { $ret=$out; }
                
                //if an output format is specified in the message, use that
                if (isset($xml->action['output'])) { 
                    $outf=$xml->action['output'];
                    // Be sure it's one of our allowed types
                    if (in_array($outf, $this->_output_types)) {
                        $ret['output']=strtolower($outf); 
                    } else { 
                        return array('output'=>'msg','data'=>array('msg'=>'Invalid output type ('.$outf.')!'));
                    }
                }
            } else { 
                return array('output'=>'msg','data'=>array('msg'=>'Invalid permissions!'));
            }
        } else {
            // Invalid request type - error!
            return array('output'=>'msg','data'=>array('msg'=>'Invalid request type ('.$type.'/'.$rtype.')!'));
        }
        return $ret;
        ############################

    }
    function parseReqXML($xml) {
        $ret_xml=null;
        try {
            $ret_xml=simplexml_load_string($xml);
        }catch(Exception $e) { /* exceptions */ }
        return $ret_xml;
    }
    // Transform the json into XML and make a SimpleXML object out of it
    function parseReqJson($json) {
        $js=json_decode($json);
        if (!isset($js->request)) { return false; }
        
        $xml='<request>';
        if (isset($js->request->auth)) {
            $xml.='<auth><user>'.htmlspecialchars($js->request->auth->user).'</user>';
            $xml.='<pass>'.htmlspecialchars($js->request->auth->pass).'</pass></auth>';
        }
        //see if we have an alternate output format specified
        if (isset($js->request->action->output)) {
            $alt_out='output="'.htmlspecialchars($js->request->action->output).'"';
        } else { $alt_out=''; }
        
        $xml.='<action type="'.htmlspecialchars($js->request->action->type).'" '.$alt_out.'>';
        foreach ($js->request->action->data as $k=>$v) {
            $xml.='<'.htmlspecialchars($k).'>'.htmlspecialchars($v).'</'.htmlspecialchars($k).'>';
        }
        $xml.='</action></request>';
        
        //return $js->request;
        return simplexml_load_string($xml);
    }

    //------------------------
    function checkPublicRules($rtype, $raction) {
        $find=$rtype.'/'.$raction;
        if (array_key_exists($find, $this->public_actions)) {
            $pass	= true;
            $rules	= $this->public_actions[$find]; //print_r($rules);
            foreach ($rules as $k=>$v) {
                $ret=$this->{'rule_'.$v}();
                if (!$ret) { $pass=false; }
            }
            return $pass;
        } else { return false; }
    }
    function rule_logged() {
        //check to see if they are logged in or not
        return ($this->CI->user_model->isAuth()) ? true : false;
    }
    function rule_isadmin() {
        return ($this->CI->user_model->isSiteAdmin()) ? true : false;
    }	
}
?>
