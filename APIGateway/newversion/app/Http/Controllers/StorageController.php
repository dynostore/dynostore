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
    public function exists(Request $request, $tokenuser, $keyobject)
    {
        $url = 'http://' . METADATA . '/api/storage/' . $tokenuser . '/' . $keyobject . '/exists';

        $response = Http::get($url);

        #print($response->body());

        if ($response->status() != 200) {
            return response()->json([
                "message" => json_decode($response->body())->message
            ], 404);
        }else{
            return response()->json($response->json(), 200);
        }
    }

    public function delete(Request $request, $tokenuser, $keyobject)
    {
        $url = 'http://' . METADATA . '/api/storage/' . $tokenuser . '/' . $keyobject;

        $response = Http::delete($url);

        if ($response->status() != 200) {
            return response()->json([
                "message" => json_decode($response->body())->message
            ], 500);
        }else{
            return response()->json([
                "message" => "Object deleted successfully"
            ], 200);
        }
    }

    public function push(Request $request, $tokenuser, $catalog, $keyobject)
    {
        #create catalog. If exists, it will return 302
        $pubsub = new PubSubController();
        $result = $pubsub->createCatalog($request, $tokenuser, $catalog)->getData();
        $tokencatalog = $result->data->tokencatalog;


        $url = 'http://' . METADATA . '/api/storage/' . $tokenuser . '/' . $tokencatalog . '/' . $keyobject;

        $response = Http::put($url, [
            'name' => $request->name,
            'size' => $request->size,
            'hash' => $request->hash,
            'is_encrypted' => $request->is_encrypted,
            'chunks' => $request->chunks,
            'required_chunks' => $request->required_chunks,
            'disperse' => $request->disperse
        ]);

        if ($response->status() != 201) {
            return response()->json([
                "message" => json_decode($response->body())->message
            ], 500);
        }

        $data = json_decode($response->body());
        $nodes = $data->nodes;
        
        foreach ($nodes as $node) {
            $url = $node->route;

            $response = Http::withBody(
                $request->data, 'application/octet-stream'
            )->put($url);

            if ($response->status() != 201) {
                return response()->json([
                    "message" => "Error pushing object to storage"
                ], 500);
            }
        }

        #Insert file in pubsub and upload object in storage 
        if ($response->status() != 201) {
            return response()->json([
                "message" => "Error registering object metadata"
            ], 500);
        }


        $url = "http://" . PUBSUB . '/catalog/' . $tokencatalog . '/object/' . $keyobject;
        $response = Http::post($url);


        if ($response->status() != 201 && $response->status() != 302) {
            return response()->json([
                "message" => "Error adding file to catalog"
            ], 500);
        }


        return response()->json([
            'status' => 'success',
            'message' => 'Object pushed successfully',
            'data' => json_decode($response->body())
        ], 201);

    }

    public function pull(Request $request, $tokenuser, $keyobject)
    {
        $url = 'http://' . METADATA . '/api/storage/' . $tokenuser . '/' . $keyobject;

        $response = Http::get($url);
        print_r($response->body());
        if($response->status() >= 500){
            return response()->json([
                "message" => json_decode($response->body())->message
            ], 500);
        }

        if ($response->status() == 404) {
            return response()->json([
                "message" => json_decode($response->body())->message
            ], 404);
        }

        $data = json_decode($response->body());

        $routes = $data->data->routes;

        $result = array();

        foreach ($routes as $route) {
            $url = $route->route;
            $response = Http::get($url);

            if ($response->status() != 200) {
                return response()->json([
                    "message" => "Error pulling object from storage"
                ], 500);
            }

            $result[] = $response->body();
        }

        return response($result[0], 200)->header('Content-Type', 'text/plain');
    }
}