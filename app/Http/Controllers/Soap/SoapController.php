<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;

use App\Http\Controllers\Controller;
use AdminItem;

class SoapController extends Controller {

  public function __construct() {
    $protocol = 'https://';
    $this->url = $protocol.'qualitynet.alianza.com.bo/AlianzaRS';
    $this->user = config('services.test_alianza_user');
    $this->password = config('services.test_alianza_password');
      // $this->url = $protocol.'www.alianza.com.bo/AlianzaRS';
      // $this->user = config('services.prod_alianza_user');
      // $this->password = config('services.prod_alianza_password');
  }

  public function processResults($method, $response, $action) {
    $response = json_decode($response, true);
    if(!isset($response['Data'])){
      return 'Error';
    }
    $data = $response['Data'];
    \Log::info('Data: '.json_encode($data));
    if($data['errorData']['ncode']===0){
      $data = $data['requestData'];
      if($action=='FindClient'){
        $data = $data[0];
      } else if($action=='updExternalInvoiceCycle'){
        $data = ['sType'=>'PROCESADO'];
      }
      \Log::info('Success: '.json_encode($data));
      return $data;
    } else {
      if($action=='ClientRemindersLife'){
        return [];
      }
      \Log::info('Error: '.$data['errorData']['sdesc']);
      return 'Error';
    }
  }

  public function processNew($method, $action, $parameters, $form_data = false) {
    $headers = array("Content-Type:application/json, Accept: */*, Host: alianzaseguros.solunes.site");
    $url = $this->url.'/'.$action;
    $final_parameters = ['lstrUser'=>$this->user, 'lstrPassword'=>$this->password];
    $get_parameters = 'lstrUser='.$this->user.'&lstrPassword='.$this->password;
    $count = 0;
    foreach($parameters as $parameter_key => $parameter_value){
      $count++;
      if($form_data){
        $final_parameters[$parameter_key] = $parameter_value;
      } else {
        $final_parameters[$parameter_key] = urlencode($parameter_value);
      }
      if($parameter_key=='lstrXmlElement'){
        $final_parameters[$parameter_key] = str_replace('+','%20',$final_parameters[$parameter_key]);
        //$final_parameters[$parameter_key] = $final_parameters[$parameter_key];
      }
      if(!$form_data){
        $get_parameters .= '&'.$parameter_key.'='.$final_parameters[$parameter_key];
      }
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    if($form_data){
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_ENCODING, '');
      curl_setopt($ch, CURLOPT_POST, true);
      if($method=='post'){
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
      } else if($method=='put'){
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
      } else if($method=='delete'){
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
      }
      curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($final_parameters));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
      \Log::info('REST Post v2 Parameters: '.$action.' - '.json_encode($final_parameters));
    } else {
      if($method=='post'){
        curl_setopt($ch, CURLOPT_URL, $url.'?'.$get_parameters);
      } else {
        curl_setopt($ch, CURLOPT_URL, $url.'?'.$get_parameters);
      }
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      if($method=='get'){
        \Log::info('REST Get Parameters: '.$action.' - '.$url.'?'.$get_parameters);
      } else {
        curl_setopt($ch, CURLOPT_POST, true);
        if($method=='post'){
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        } else if($method=='put'){
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        } else if($method=='delete'){
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, array());
        \Log::info('REST Post Parameters: '.$action.' - '.$url.'?'.$get_parameters);
        //\Log::info('REST Post Body: '.json_encode($final_parameters));
      }
    }

    $response = curl_exec($ch); 
    if ($response === false) {
      \Log::info('Curl Error: '.curl_error($ch));
    }
    curl_close($ch);
    \Log::info('REST Response: '.$action.' - '.json_encode($response));
    return $this->processResults($method, $response, $action);
  }

  // 1, alianza generales, 2. alianza vida
  public function reaFindClient($lstrClient) {
    return $this->processNew('get', 'FindClient', ['lstrCliename'=>'','lstrClient'=>$lstrClient,'llngCuit'=>'0']);
  }

  public function reaClientReminders($lstrParamClient, $lshtCompany) {
    return $this->processNew('get', 'ClientReminders', ['lstrParamClient'=>$lstrParamClient,'lshtCompany'=>$lshtCompany]);
  }

  public function insGenReceiptMonthly($lshtOffice, $lshtParamCompany, $lstrClient, $lshtBranch, $lintPolicy, $lshtParamCountPend) {
    return $this->processNew('post','GenReceiptMonthly', ['lshtOffice'=>$lshtOffice,'lshtParamCompany'=>$lshtParamCompany,'lstrClient'=>$lstrClient,'lshtBranch'=>$lshtBranch,'lintPolicy'=>$lintPolicy,'lshtParamCountPend'=>$lshtParamCountPend]);
  }

  public function insCollectionMovil($lstrXmlElement,$lshtType) {
    return $this->processNew('post','CollectionMovil', ['lstrXmlElement'=>$lstrXmlElement, 'lshtType'=>$lshtType]);
  }

  public function updInvoiceExternal($lstrCodAuthoriz, $lintMov, $lintInvoice, $ldecAmountInvoice, $lintBordereaux, $lstrClienameInvoice, $lstrNitInvoice, $lstrControlCode) {
    return $this->processNew('post','updInvoiceExternal', ['lstrCodAuthoriz'=>$lstrCodAuthoriz,'lintMov'=>$lintMov,'lintInvoice'=>$lintInvoice,'ldecAmountInvoice'=>$ldecAmountInvoice,'lintBordereaux'=>$lintBordereaux,'lstrClienameInvoice'=>$lstrClienameInvoice,'lstrNitInvoice'=>$lstrNitInvoice,'lstrControlCode'=>$lstrControlCode]);
  }

  public function updExternalInvoiceCycle($lstrCodAuthoriz, $ldecAuthorization, $lstrsDosage, $ldtmEffedateCycle, $ldtmExpirdatCycle, $lstrEticket, $lstrLegend, $lintPointInv, $lintInvoice, $ldecAmountInvoice, $lintBordereaux, $lstrClientInvoice, $llngNitInvoice, $lstrControlCode, $lstrComment, $lshtCompanyPay, $lstrCuf, $lstrCufd, $lstrCodPayMeth, $lshtPlatform, $lshtTypeDocNIT, $lstrTypeInv, $lstrIdentifier = '12345', $lstrTransaction = '12345', $ldecAmountOrig = '43', $ldtmCollectInv = NULL) {
    if($ldtmCollectInv==NULL){
      $ldtmCollectInv = date('Y/m/d');
    }
    return $this->processNew('post','updExternalInvoiceCycle', ['lstrCodAuthoriz'=>$lstrCodAuthoriz,'ldecAuthorization'=>$ldecAuthorization,'lstrsDosage'=>$lstrsDosage,'ldtmEffedateCycle'=>$ldtmEffedateCycle,'ldtmExpirdatCycle'=>$ldtmExpirdatCycle,'lstrEticket'=>$lstrEticket,'lstrLegend'=>$lstrLegend,'lintPointInv'=>$lintPointInv,'lintInvoice'=>$lintInvoice,'ldecAmountInvoice'=>$ldecAmountInvoice,'lintBordereaux'=>$lintBordereaux,'lstrClientInvoice'=>$lstrClientInvoice,'lstrNitInvoice'=>$llngNitInvoice,'lstrControlCode'=>$lstrControlCode,'lstrComment'=>$lstrComment,'lshtCompanyPay'=>$lshtCompanyPay,'lstrCuf'=>$lstrCuf,'lstrCufd'=>$lstrCufd,'lstrCodPayMeth'=>$lstrCodPayMeth,'lshtPlatform'=>$lshtPlatform,'lshtTypeDocNIT'=>$lshtTypeDocNIT,'lstrTypeInv'=>$lstrTypeInv,'lstrIdentifier'=>$lstrIdentifier,'lstrTransaction'=>$lstrTransaction,'ldecAmountOrig'=>$ldecAmountOrig,'ldtmCollectInv'=>$ldtmCollectInv]);
  }

  public function insReverseCollectionExternal($lstrCodAuthoriz, $lintBordereaux) {
    return $this->processNew('post','ReverseCollectionExternal', ['lstrCodAuthoriz'=>$lstrCodAuthoriz,'lintBordereaux'=>$lintBordereaux]);
  }

  public function reaCollectionConciliation($ldtmDate_Ingress, $lshtCompany) {
    $year = date('Y');
    $month = date('m');
    $ldtmDate_Ingress = $ldtmDate_Ingress.'/'.$month.'/'.$year;
    \Log::info('ldtmDate_Ingress: '.$ldtmDate_Ingress);
    \Log::info('lshtCompany: '.$lshtCompany);
    return $this->processNew('get','CollectionConciliation', ['ldtmDate_Ingress'=>$ldtmDate_Ingress,'lshtCompany'=>$lshtCompany]);
  }

  // Servicios Life Largo Plazo //
// Alianza vida largo plazo
  public function reaFindClientLife($lstrClient) {
    return $this->processNew('get','FindClientLife', ['lstrCliename'=>NULL,'lstrClient'=>$lstrClient,'lstrCuit'=>NULL]);
  }

  public function reaClientRemindersLife($lstrParamClient) {
    return $this->processNew('get','ClientRemindersLife', ['lstrParamClient'=>$lstrParamClient]);
  }

  public function reaCollectionMovilLife($Data, $nType) {
    return $this->processNew('post','CollectionMovilLife', ['Data'=>$Data, 'lstrType'=>$nType], true);
  }

  public function updReceipExternalLife($sCodAuthoriz, $nMov, $nReceipt, $nAmountReceipt, $nBordereaux, $sClienameReceipt, $sNitReceipt, $sControlCode) {
    return $this->processNew('put','ReceiptExternalLife', ['Data'=>['sCodAuthoriz'=>$sCodAuthoriz,'nMov'=>$nMov,'nReceipt'=>$nReceipt,'nAmountReceipt'=>$nAmountReceipt,'nBordereaux'=>$nBordereaux,'sClienameReceipt'=>$sClienameReceipt,'sNitReceipt'=>$sNitReceipt,'sControlCode'=>$sControlCode]], true);
  }

  public function insReverseCollectionExternalLife($lstrCod_Authoriz, $lintBordereaux) {
    return $this->processNew('delete','ReverseCollectionExternalLife', ['lstrCod_Authoriz'=>$lstrCod_Authoriz,'lintBordereaux'=>$lintBordereaux]);
  }
}