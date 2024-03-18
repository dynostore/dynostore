<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Http;

class AuthController extends Controller
{
    //
    public function createOrganization(Request $request)
    {
        // ...
        # Make request to auth service
        $url_auth = 'http://' . env('AUTH_HOST') . '/auth/v1/hierarchy';


        $response = Http::post($url_auth, [
            'option' => "NEW",
            'acronym' => $request->acronym,
            'fullname' => $request->fullname,
            'fathers_token' => $request->fathers_token
        ]);

        if($response->status() == 200){
            return response()->json([
                'status' => 'success',
                'message' => 'Organization created successfully',
                'data' => json_decode($response->body())
            ], 200);
        }else{
            return response()->json([
                'status' => 'error',
                'message' => 'Organization creation failed',
                'data' => json_decode($response->body())
            ], 500);
        }
        
    }

    public function getOrganization(Request $request, $acronym, $name)
    {
        // ...
        # Make request to auth service
        $url_auth = 'http://' . env('AUTH_HOST') . '/auth/v1/hierarchy';


        $response = Http::post($url_auth, [
            'option' => "CHECK",
            'acronym' => $acronym,
            'fullname' => $name
        ]);
        if($response->status() == 200){
            return response()->json([
                'status' => 'success',
                'message' => 'Organization does not exists',
                'data' => json_decode($response->body())
            ], 400);
        }else{
            return response()->json([
                'status' => 'error',
                'message' => 'Organization exists',
                'data' => json_decode($response->body())
            ], 200);
        }
        
    }

    public function createUser(Request $request)
    {
        // ...
        # Make request to auth service
        $client = new \GuzzleHttp\Client();
        $url_auth = 'http://' . env('AUTH_HOST') . '/auth/v1/users/create';
        

        $response = Http::post($url_auth, [
            'option' => 'NEW',
            'email' => $request->email,
            'password' => $request->password,
            'username' => $request->username,
            'tokenorg' => $request->tokenorg
        ]);

        if($response->status() == 200){
            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully',
                'data' => json_decode($response->body())
            ], 200);
        }else{
            return response()->json([
                'status' => 'error',
                'message' => 'User creation failed',
                'data' => json_decode($response->body())
            ], 500);
        }
    }
}
