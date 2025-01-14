<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Clients;
use App\Http\Resources\Clients as ClientsResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\User;

class ClientsController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
        if($request->moderator) {
            $clients = Clients::with('cities', 'users', 'comments', 'moderator', 'manager')->where('moderator_id', $request->moderator)->get();   
        }
        else if($request->manager) {
            $clients = Clients::with('cities', 'users', 'comments', 'moderator', 'manager')->where('manager_id', $request->manager)->get();   
        }
        else if(json_decode($request->city)) {
            $arr = [];
            foreach (json_decode($request->city) as $key => $value) {
                $arr[] = $value->city_id;
            }
            $clients = Clients::with('cities', 'users', 'comments','moderator')->whereIn('city_id', $arr)->get();   
        } else  {
            $clients = Clients::with('cities', 'users' , 'comments', 'moderator', 'manager')->get();  
        }
        return ClientsResource::collection($clients);
    }

    public function managersAddress($id)
    {
        $clients = Clients::with('cities', 'users' , 'comments')->where('city_id', $id)->get();
        return ClientsResource::collection($clients);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $users = $request->isMethod('put') ? User::findOrFail($request->users_id) : new User;

        if($request->isMethod('post')) {
            $users->id = $request->input('id');
        }

        // if(!empty($request->input('email'))) {
        //     $this->validate($request, [
        //         'email' => 'unique:users',
        //         'email' => Rule::unique('users')->ignore($request->users_id),
        //     ]);
        // }

        $users->name = $request->input('name');
        $users->email = $request->input('email');
        $users->phone = $request->input('phone');
        $users->login = $request->input('login');
        $users->role = 'client';
        
        if(!empty($request->input('password'))) {
            $users->password = bcrypt($request->input('password'));
            $token = $users->createToken('Laravel Password Grant Client')->accessToken;
        }
    
        if($users->save()) { 
            $clients = $request->isMethod('put') ? Clients::with('comments')->findOrFail($request->id) : new Clients;
            if($request->isMethod('post')) {
                $clients->id = $request->input('id');
            }
            $clients->users_id = $users->id;
            $clients->city_id = $request->input('city_id');
            $clients->legal_name = $request->input('legal_name');
            $clients->actual_title = $request->input('actual_title');
            $clients->legal_address = $request->input('legal_address');
            $clients->actual_address = $request->input('actual_address');
            $clients->bik = $request->input('bik');
            $clients->cor_score = $request->input('cor_score');
            $clients->settlement_account = $request->input('settlement_account');
            $clients->bank_name = $request->input('bank_name');
            $clients->moderator_id = $request->input('moderator_id');
            $clients->manager_id = $request->input('manager_id');
           
            if($clients->save()) {
                return new ClientsResource($clients);
            }
        }

    }

    public function loadFiles(Request $request)
    {
        if($request->isMethod('delete') && $request->fileClient) {
            Clients::removeTableFiles($request->fileClient);
        } else {
            if ($request->isMethod('post') && is_array($request->fileClient) || $request->isMethod('put') && is_array($request->fileClient)) {
                foreach ($request->fileClient as $key => $item) {
                    Clients::saveTableFiles(json_decode($request->fileClient[$key])->id, $request->idClient, $request->comment);    
                }
            }
        }

        $clients = Clients::with('comments')->findOrFail($request->idClient);
        
        return new ClientsResource($clients);
    }

    public function addComment(Request $request)
    {
        if($request->isMethod('delete')) {
            Clients::removeComment($request->id);
        } 

        if  ($request->isMethod('post')) {
            Clients::saveComment($request->fileId, $request->idClient, $request->comment);
        }

        if ($request->isMethod('put')) {
            Clients::editComment($request->id, $request->comment);
        }

        $clients = Clients::with('comments')->findOrFail($request->idClient);
        return new ClientsResource($clients);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $clients = Clients::findOrFail($id);
        return new ClientsResource($clients);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $clients = Clients::findOrFail($id);

        if($clients->delete()) {
            return new ClientsResource($clients);
        }
    }
}
