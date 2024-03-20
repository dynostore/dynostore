<?php

namespace App\Http\Controllers;
use Http;
use Illuminate\Http\Request;

if (!defined('AUTH')) {
    define('AUTH', env('AUTH_HOST'));
}

if (!defined('PUBSUB')) {
    define('PUBSUB', env('PUB_SUB_HOST'));
}

if (!defined('METADATA')) {
    define('METADATA', env('METADATA_HOST'));
}

class PubSubController extends Controller
{
    //
    public function createCatalog(Request $request, $tokenuser, $catalogname)
    {
        $url = 'http://' . PUBSUB  . '/catalog/' . $catalogname . '/?tokenuser=' . $tokenuser;

        $response = Http::put($url, [
            'dispersemode' => isset($request->dispersemode) ? $request->dispersemode : "SINGLE",
            'encryption' => isset($request->encryption) ? $request->encryption : 0 ,
            'fathers_token' => isset($request->fathers_token) ? $request->fathers_token : '/',
            'processed' => isset($request->processed) ? $request->processed : 0 
        ]);
        
        if($response->status() == 201){
            return response()->json([
                'status' => 'success',
                'message' => 'Catalog created successfully',
                'data' => json_decode($response->body())->data
            ], 200);
        }else if($response->status() == 302){
            return response()->json([
                'status' => 'info',
                'message' => 'Catalog already exists',
                'data' => json_decode($response->body())->data
            ], 302);
        }else{
            return response()->json([
                'status' => 'error',
                'message' => 'Catalog creation failed',
                'data' => json_decode($response->body())
            ], 500);
        }
    }

    public function deleteCatalog(Request $request, $accesstoken, $tokencatalog)
    {
        $url = 'http://' . PUBSUB  . '/subscription/v1/catalogs?access_token=' . $accesstoken;

        $response = Http::delete($url, [
            'tokencatalog' => $tokencatalog,
            'option' => 'DELETE'
        ]);

        if($response->status() == 200){
            return response()->json([
                'status' => 'success',
                'message' => 'Catalog deleted successfully',
                'data' => json_decode($response->body())
            ], 200);
        }else{
            return response()->json([
                'status' => 'error',
                'message' => 'Catalog deletion failed',
                'data' => json_decode($response->body())
            ], 500);
        }
    }

    public function getCatalog(Request $request, $accesstoken, $tokencatalog)
    {
        $url = 'http://' . PUBSUB  . '/subscription/v1/view/catalog/' . $tokencatalog . '?access_token=' . $accesstoken;

        $response = Http::get($url);

        if($response->status() == 200){
            return response()->json([
                'status' => 'success',
                'message' => 'Catalog retrieved successfully',
                'data' => json_decode($response->body())
            ], 200);
        }else{
            return response()->json([
                'status' => 'error',
                'message' => 'Catalog retrieval failed',
                'data' => json_decode($response->body())
            ], 500);
        }
    }    
}
