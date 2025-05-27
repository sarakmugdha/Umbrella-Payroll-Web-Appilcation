<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CustomerService;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    protected $customerServices;
    public function __construct(CustomerService $customerServices)
    {
        $this->customerServices = $customerServices;
    } 
        public function store(Request $request)
        {
        $request->validate([
            'customer_name' => 'required|string|max:25',
            'email' => 'required',
             'tax_no' => 'required',
            'address' => 'required',
            'state' => 'required',
            'pincode' => 'required', 
        ]);
        $customer = $this->customerServices->store($request);
        $status = $customer['status'];

        if($status === 409){
            return response()->json([
                "status"=> $status,
            ]);  
        }
        
        return response()->json([$customer]);
    }
  
    public function display(Request $request){
        $customerDetails =  $this->customerServices->getCustomers($request);
        $display = $customerDetails['data'];
        
        Log::info($display);
        return response()->json(["data"=>$display]);
    }
    public function updateData(Request $request, $id)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'email' => 'required',
            'tax_no' => 'required',
            'address' => 'required|string',
            'state' => 'required|string',
            'pincode' => 'required|max:10',
        ]);
        $this->customerServices->updateData($request, $id);
        return response()->json([
            "status"=> 0,
            "message" => "Updated Successfully"
        ]);
    }

            
    public function deleteData(Request $request)
    {
        $id = $request -> input('customer_id');
        $customerLists =  $this->customerServices->deleteData($id);
        return response()->json([$customerLists]);
    }

    public function getCustomersByCompanyId(Request $request)
    {
        $customerLists = $this->customerServices->getCustomersByCompanyId($request);
        return response()->json([
            'data' => $customerLists->items(),
            'total' => $customerLists->total()
        ]);
    }
} 

  

