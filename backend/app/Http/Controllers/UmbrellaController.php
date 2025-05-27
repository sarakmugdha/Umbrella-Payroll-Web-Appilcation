<?php

namespace App\Http\Controllers;


use App\Models\umbrella_company;
use Illuminate\Http\Request;

class UmbrellaController extends Controller
{
        public function storedCompany(Request $request)
        {
           

            $request->validate([
            'Companyname' => 'required|string|max:25',
            'Email'=> 'required',
            'Address'=> 'required',
            ]);

          

            umbrella_company::insert([
                'Companyname'=> $request['Companyname'],
                'Email'=> $request['Email'],
                'Address'=> $request['Address']]);
                
                   return response()->json([
                 "status"=> 1,
                 "message"=> $request->all()
             ]);
        }

}
