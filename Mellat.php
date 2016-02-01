<?php
 
 /*
	Programming : Mohammad Ghanbari
	Mobile : 09336505170
 */
 
class Mellat {

    private $terminal = NULL;
    private $username = NULL;
    private $password = NULL;
    private $amount = NULL;
    private $callback = NULL;
    private $order = NULL;

    public function set_options($terminal = '', $username = '', $password = '', $amount = 0, $order = 0, $callback = ''){
        if(!empty($terminal))
            $this->terminal = $terminal;

        if(!empty($username))
            $this->username = $username;

        if(!empty($password))
            $this->password = $password;

        if(!empty($amount))
            $this->amount = $amount;

        if(!empty($order))
            $this->order = $order;

        if(!empty($callback))
            $this->callback = $callback;
    }

    public function send(){
        $client             = new nusoap_client('https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl','wsdl');
        $terminalId         = $this->terminal;
        $userName           = $this->username;
        $userPassword       = $this->password;
        $orderId            = $this->order;
        $amount             = $this->amount * 10;
        $callBackUrl        = $this->callback;
        $localDate          = date('Ymd');
        $localTime          = date('His');
        $additionalData     = '';
        $payerId = 0;
        $err = $client->getError();
        if ($err) {
            return false;
        }
        $parameters = array(
            'terminalId'        => $terminalId,
            'userName'          => $userName,
            'userPassword'      => $userPassword,
            'orderId'           => $orderId,
            'amount'            => $amount,
            'localDate'         => $localDate,
            'localTime'         => $localTime,
            'additionalData'    => $additionalData,
            'callBackUrl'       => $callBackUrl,
            'payerId'           => $payerId
        );
        $result = $client->call('bpPayRequest', $parameters, 'http://interfaces.core.sw.bps.com/');

        if ($client->fault) {
            return false;
        }
        else {
            $resultStr  = $result;
            $err = $client->getError();
            if ($err) {
                return false;
            }
            else {
                foreach ($resultStr as $value) {
                    $resultStr = $value;
                }
                $res = explode (',',$resultStr);
                $ResCode = $res[0];	
                if ($ResCode == "0") {
                    $this->postRefId($res[1]);
                }
                else {
                    return false;
                }
            }
        }
    }

    protected function verify_payment($params){
        $client                     = new nusoap_client( 'https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl','wsdl') ;
        $orderId                    = $params["SaleOrderId"];
        $verifySaleOrderId          = $params["SaleOrderId"];
        $verifySaleReferenceId      = $params['SaleReferenceId'];
        $err = $client->getError();
        if ($err) {
            return false;
        }
        $parameters = array(
            'terminalId'            => $this->terminal,
            'userName'              => $this->username,
            'userPassword'          => $this->password,
            'orderId'               => $orderId,
            'saleOrderId'           => $verifySaleOrderId,
            'saleReferenceId'       => $verifySaleReferenceId
        );
        $result = $client->call('bpVerifyRequest', $parameters, 'http://interfaces.core.sw.bps.com/');
        if ($client->fault) {
            return false;
        } 
        else {
            $resultStr = $result;
            $err = $client->getError();
            if ($err) {
                return false;
            }
            else {
                return true;
            }
        }
        return false;
    }

    protected function settle_payment($params){
        $client                     = new nusoap_client('https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl') ;
        $orderId                    = $params["SaleOrderId"];
        $settleSaleOrderId          = $params["SaleOrderId"];
        $settleSaleReferenceId      = $params['SaleReferenceId'];
        $err = $client->getError();
        if ($err) {
            return FALSE;
        }
        $parameters = array(
            'terminalId'            => $this->terminal, 
            'userName'              => $this->username, 
            'userPassword'          => $this->password, 
            'orderId'               => $orderId,
            'saleOrderId'           => $settleSaleOrderId,
            'saleReferenceId'       => $settleSaleReferenceId);
        $result = $client->call('bpSettleRequest', $parameters, 'http://interfaces.core.sw.bps.com/');
        if ($client->fault) {
            return false;
        }
        else {
            $resultStr = $result;
            $err = $client->getError();
            if ($err) {
                return false;
            }
            else {
                if($resultStr == '0') {
                    return true;
                }
                return $resultStr ;
            }
        }
        return false;
    }

    protected function postRefId($refIdValue){
        echo '<script language="javascript" type="text/javascript"> 
                function postRefId (refIdValue) {
                var form = document.createElement("form");
                form.setAttribute("method", "POST");
                form.setAttribute("action", "https://bpm.shaparak.ir/pgwchannel/startpay.mellat");         
                form.setAttribute("target", "_self");
                var hiddenField = document.createElement("input");              
                hiddenField.setAttribute("name", "RefId");
                hiddenField.setAttribute("value", refIdValue);
                form.appendChild(hiddenField);
                document.body.appendChild(form);         
                form.submit();
                document.body.removeChild(form);
            }
            postRefId("' . $refIdValue . '");
            </script>';
    }

    protected function error($number){
        return false;
    }

    public function get($params){
        if( $params["ResCode"] == 0 ){
            if( $this->verify_payment($params) == true ) {
                if( $this->settle_payment($params) == true ) {
                    return array(
                        "status"            =>  "success", 
                        "ResCode"           =>  $params["ResCode"], 
                        "RefId"             =>  $params["RefId"], 
                        "SaleOrderId"       =>  $params["SaleOrderId"], 
                        "SaleReferenceId"   =>  $params["SaleReferenceId"]
                    );
                }
            }
        }
        return false;
    }

}
