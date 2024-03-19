<?php

namespace App\Http\Controllers;

use App\Http\Controllers\PubSubController;
use Illuminate\Http\Request;
use Http;

if (!defined('AUTH')) {
    define('AUTH', env('AUTH_HOST'));
}

if (!defined('PUBSUB')) {
    define('PUBSUB', env('PUB_SUB_HOST'));
}

if (!defined('METADATA')) {
    define('METADATA', env('METADATA_HOST'));
}

class StorageController extends Controller
{
    public function push(Request $request, $tokenuser, $catalog, $keyobject)
    {

        $url = "http://" . AUTH . '/auth/v1/user?tokenuser=' . $tokenuser;
        
        $response = Http::get($url);

        if ($response->status() == 404) {
            return response()->json([
                "message" => "Unauthorized"
            ], 401);
        }

        #create catalog. If exists, it will return 302
        $pubsub = new PubSubController();
        $result = $pubsub->createCatalog($request, $tokenuser, $catalog)->getData();
        $tokencatalog = $result->data->tokencatalog;


        $url = 'http://' . METADATA  . '/api/' . $tokenuser . '/' . $tokencatalog . '/objects/' . $keyobject;

        $response = Http::put($url, [
            'name' => $request->name,
            'size' => $request->size,
            'hash' => $request->hash,
            'is_encrypted' => $request->is_encrypted,
            'chunks' => $request->chunks,
            'required_chunks' => $request->required_chunks,
            'disperse' => $request->disperse
        ]);

        #print_r($response->body());
        #print_r($response->status());

        #ToDO Insert file in pubsub and upload object in storage 
        
        if ($response->status() == 201) {
            return response()->json([
                'status' => 'success',
                'message' => 'Object pushed successfully',
                'data' => json_decode($response->body())
            ], 201);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Object push failed',
                'data' => json_decode($response->body())
            ], 500);
        }
    }
}