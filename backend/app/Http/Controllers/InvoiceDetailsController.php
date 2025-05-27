<?php

namespace App\Http\Controllers;


use App\Services\InvoiceDetailsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;



class InvoiceDetailsController extends Controller
{
    public $invoiceDetailsService;

    public function __construct(InvoiceDetailsService $invoiceDetailsService){
        $this->invoiceDetailsService=$invoiceDetailsService;
    }
   public function getInvoiceDetails($invoiceNumber,Request $request){
         $response=$this->invoiceDetailsService->getInvoiceDetails($invoiceNumber,$request);
         return response()->json(['gridData'=>$response[0],'invoice'=>$response[1]]);
   }

   public function addLineItem(Request $request){
            $this->invoiceDetailsService->addLineItem($request);

            return response()->json('Line Item Added Successfully',200);

            
   }
   public function deleteLineItem($invoiceId,$invoiceDetailId,Request $request){
                    $this->invoiceDetailsService->deleteLineItem($request,$invoiceId,$invoiceDetailId);

                    return response()->json('Deleted successfully',200);
   }
}
