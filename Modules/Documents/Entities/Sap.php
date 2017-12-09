<?php

namespace Modules\Documents\Entities;

use Illuminate\Database\Eloquent\Model;
use SoapClient;
use SoapFault;

class Sap extends Model
{
    protected $fillable = [];
    
    public static function get_po($po){
      try {
        $client = new SoapClient(config('app.soap_sap_po'), array('login' => config('app.sap.user'),'password' => config('app.sap.passwd')));
        $get_po = $client->ZMmPrpo([
                  'Ebeln' => $po,
                  'Banfn' => false,
                  'Poheader' => false,
                  'Poitem' => false,
                  'Pritem'=>false,
                  'UsrInf'=>false,
                  'VendorInf'=>false
                ]);
          return ['status'=>true,'data'=>$get_po,'length'=>count($get_po->Poitem->item)];
      } catch(SoapFault $ex) {
          //$msg = 'Exception: ' . $ex->getMessage() . PHP_EOL;
          return ['status'=>true,'data'=>$ex->faultstring,'other'=>$ex,'length'=>0];
      }
    }
}
