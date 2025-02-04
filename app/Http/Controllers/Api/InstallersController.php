<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Installers;
use App\Http\Resources\Installers as InstallersResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\User;

class InstallersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if(json_decode($request->city)) {
            $arr = [];
            foreach (json_decode($request->city) as $key => $value) {
                $arr[] = $value->city_id;
            }
            $installers = Installers::with('users', 'cities', 'moderator.users')->whereIn('city_id', $arr)->get();   
        }

        if(!json_decode($request->city)) {
            $installers = Installers::with('users', 'cities', 'moderator.users')->get();   
        }

        if($request->user) {
            $installers = Installers::with('users', 'cities', 'moderator.users')->where('moderator_id', $request->user)->get();   
        }

        if(!$request->user) {
            $installers = Installers::with('users', 'cities', 'moderator.users')->get();   
        }
        
        return InstallersResource::collection($installers);
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
        
        $this->validate($request, [
            'email' => 'unique:users',
            'email' => Rule::unique('users')->ignore($request->users_id),
        ]);

        $users->name = $request->input('name');
        $users->email = $request->input('email');
        $users->phone = $request->input('phone');
        $users->login = $request->input('login');
        $users->role = 'installer';
        
        if(!empty($request->input('password'))) {
            $users->password = bcrypt($request->input('password'));
            $token = $users->createToken('Laravel Password Grant Client')->accessToken;
        }
    
        if($users->save()) { 
            $installers = $request->isMethod('put') ? Installers::findOrFail($request->id) : new Installers;
            if($request->isMethod('post')) {
                $installers->id = $request->input('id');
            }
            $installers->users_id = $users->id;
            $installers->city_id = $request->input('city_id');
            $installers->moderator_id = $request->input('moderator_id');

            if($installers->save()) {
                return new InstallersResource($installers);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $installers = Installers::findOrFail($id);
        return new InstallersResource($installers);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $installers = Installers::findOrFail($id);

        if($installers->delete()) {
            return new InstallersResource($installers);
        }
    }
}
