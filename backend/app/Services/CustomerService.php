<?php
namespace App\Services;
use App\Models\Customers;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerService  
{
public function store( $request)
{
    try
        {
            DB::beginTransaction();
            $exists = Customers::where('email', $request['email'])
                            ->where('is_deleted',0)
                            ->exists();

            if ($exists) {
                return [
                    "message" => "Email Already Exists",
                    "status" => 409
                ];
            }
            $user = Auth::user();
            $organization_id = $user->organization_id;
            $customer = customers::insert([
                "customer_name" => $request['customer_name'],
                "organization_id"=>$organization_id,
                "email" => $request["email"],
                "tax_no"=> $request["tax_no"],
                "address"=> $request["address"],
                "state"=> $request["state"],
                "pincode"=> $request["pincode"],
                "company_id"=> $request["company_id"],
            ]);
            DB::commit();
            return["customers" => $customer, "status"=> 200];
        }

    catch(Exception $e)
        {
            DB::rollBack();
            return redirect()->back()->with("error", $e->getMessage());
        }
}

public function getCustomers($request)
{
    try{
        $perPage = $request->input('per_page');
        $page = $request->input('page');
        
        $query = Customers::where(['is_deleted' => '0'])
                        ->select('*');

        $display = $query->orderBy('customer_id','desc')
                        ->paginate($perPage, ['*'], 'page', $page );
    
        return[ 
            'data'=> $display->items(),
            'total'=>$display->total()];
    }
catch(Exception $e){   
    return redirect()->back()->with('error', $e->getMessage());
}
    
}

public function updateData($request, $id)
{
try
    {
        DB::beginTransaction();
        $user = Auth::user();
        $organization_id = $user->organization_id;

            $customer = customers::where('customer_id',$id)
                ->update([
                    'customer_name' => $request['customer_name'],
                    'organization_id'=> $organization_id,
                    'email' => $request->email,
                    'tax_no' => $request->tax_no,
                    'address'=>$request->address,
                    'state' => $request->state,
                    'pincode' => $request->pincode,
                    "company_id"=> $request["company_id"],
                ]);

        DB::commit();
        return $customer;
    }catch(Exception $e){
        DB::rollBack();
        return redirect()->back()->with("error", $e->getMessage());
    }
}

public function deleteData($id)
{
    try
    {
        DB::beginTransaction();
        
            $customerLists = customers::where('customer_id',$id)
                        ->update(['is_deleted'=>'1']);
                        DB::commit();
        return $customerLists;
    }

    catch(Exception $e)
    {
        DB::rollBack();
        return redirect()->back()->with('error', $e->getMessage());
    }
}

public function getCustomersByCompanyId($request)
{
    $id = $request->input('id');
    $perPage = $request->input('per_page');
    $page = $request->input('page');
    $filter = $request->input('filter');

    try
    {

        $query = Customers :: where('company_id',$id)
                                    ->where('is_deleted',0)
                                    ->select('*');
        
        if (!empty($filter)) {
            foreach ($filter as $filterField => $filterValue) {
                if ($filterValue !== null && $filterValue !== '') {
                    $query->where($filterField, 'like', "%$filterValue%");
                }
            }
        }   

        $customerLists = $query->orderBy('customer_id','desc')
                            ->paginate($perPage, ['*'], 'page', $page);

        return $customerLists;
    }                
    catch(Exception $e)
    {
        DB::rollBack();
        return redirect()->back()->with('error', $e->getMessage());
    }
}
}