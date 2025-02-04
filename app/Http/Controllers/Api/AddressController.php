<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Imports\AddressImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Address;
use App\Models\Areas;
use App\Models\Entrances;
use App\Models\CitiesToWorks;
use App\Models\AddressToOrders;
use App\Http\Resources\Address as AddressResource;
use App\Http\Resources\AddressRoleUser as AddressRoleUserResource;
use App\Http\Resources\CitiesToWorks as CitiesToWorksResource;
use App\Http\Resources\Areas as AreasResource;


class AddressController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->user) {
            if(json_decode($request->city)) {
                $arr = [];
                foreach (json_decode($request->city) as $key => $value) {
                    $arr[] = $value->city_id;
                }
                $toWorks = CitiesToWorks::whereIn('id', $arr)->get();
                $areas = Areas::with('cities')->whereIn('city_id', $arr)->get();
                $toAddress = Address::with('cities', 'areas', 'orderAddress', 'orderAddress.files', 'orderAddress.orders', 'entrances')->whereIn('city_id', $arr)->get();   
            }

            if(!json_decode($request->city)) {
                $toAddress = Address::with('cities', 'areas', 'orderAddress', 'orderAddress.files', 'orderAddress.orders', 'entrances')->get(); 
                $toWorks = CitiesToWorks::get();
                $areas = Areas::with('cities')->get();  
            }
            $address = AddressRoleUserResource::collection($toAddress);
        }
        else if(json_decode($request->surface)) {
            if(json_decode($request->city)) {
                $index = json_decode($request->surface)->index;
                $arr = [];
                foreach (json_decode($request->city) as $key => $value) {
                    $arr[] = $value->city_id;
                }
                
                $entrances = Entrances::where([
                    ['file_id', '!=', null],
                    ['shield', '=',  $index],
                ])->orWhere([
                    ['file_id', '!=', null],
                    ['information', '=',  $index]
                ])->orWhere([
                    ['file_id', '!=', null],
                    ['glass', '=',  $index]
                ])->orWhere([
                    ['file_id', '!=', null],
                    ['mood', '=',  $index]
                ])->pluck('address_id')->all();
              
                $toAddress = Address::with('cities', 'areas', 'orderAddress', 'orderAddress.files', 'orderAddress.orders', 'entrances')->whereIn('city_id', $arr)->whereIn('id', array_unique($entrances))->get();   
                $toWorks = CitiesToWorks::whereIn('id', $arr)->get();
                $areas = Areas::with('cities')->whereIn('city_id', $arr)->get();
            }

            if(!json_decode($request->city)) {
                $index = json_decode($request->surface)->index;
                
                $entrances = Entrances::where([
                    ['file_id', '!=', null],
                    ['shield', '=',  $index],
                ])->orWhere([
                    ['file_id', '!=', null],
                    ['information', '=',  $index]
                ])->orWhere([
                    ['file_id', '!=', null],
                    ['glass', '=',  $index]
                ])->orWhere([
                    ['file_id', '!=', null],
                    ['mood', '=',  $index]
                ])->pluck('address_id')->all();
              
                $toAddress = Address::with('cities', 'areas', 'orderAddress', 'orderAddress.files', 'orderAddress.orders', 'entrances')->whereIn('id', array_unique($entrances))->get();
                $toWorks = CitiesToWorks::get();
                $areas = Areas::with('cities')->get();  
            }
            $address = AddressResource::collection($toAddress);
        }
        else {
            if(json_decode($request->city)) {
                $arr = [];
                foreach (json_decode($request->city) as $key => $value) {
                    $arr[] = $value->city_id;
                }
                $toAddress = Address::with('cities', 'areas', 'orderAddress', 'orderAddress.files', 'orderAddress.orders', 'entrances')->whereIn('city_id', $arr)->get();   
                $toWorks = CitiesToWorks::whereIn('id', $arr)->get();
                $areas = Areas::with('cities')->whereIn('city_id', $arr)->get();
            }

            if(!json_decode($request->city)) {
                $toAddress = Address::with('cities', 'areas', 'orderAddress', 'orderAddress.files', 'orderAddress.orders', 'entrances')->get(); 
                $toWorks = CitiesToWorks::get();
                $areas = Areas::with('cities')->get();  
            }
            $address = AddressResource::collection($toAddress);
        }

        
        return response()->json(
            [
                'address' => $address, 
                'city' => CitiesToWorksResource::collection($toWorks), 
                'area' => AreasResource::collection($areas)
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $address = $request->isMethod('put') ? Address::findOrFail($request->id) : new Address;

        $address->id = $request->input('id');
        $address->city_id = $request->input('city_id');
        $address->area_id = $request->input('area_id');
        $address->street = $request->input('street');
        $address->house_number = $request->input('house_number');
        $address->number_entrances = $request->input('number_entrances');
        $address->management_company = $request->input('management_company');
        
        if($address->save()) {
            return new AddressResource($address);
        }
    }

    public function addExcelData(Request $request)
    {
        Excel::import(new AddressImport, $request->file('file'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $address = Address::findOrFail($id);
        return new AddressResource($address);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $address = Address::findOrFail($id);

        if($address->delete()) {
            return new AddressResource($address);
        }
    }
}
