<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFileRequest;
use App\Http\Requests\UpdateFileRequest;
use App\Models\Abekey;
use App\Models\Chunk;
use App\Models\File;
use App\Models\Server;
use App\Models\FilesInServer;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

use Http;

define('NODES_REQUIRED_PUSH', 5);

class FileController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreFileRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function push(Request $request)
    {   
        $keys = array();
        $file = new File;
        $file->name = $request->input('name');
        $file->size = $request->input('size');
        $file->hash = $request->input('hash');
        $file->keyfile = $request->input('key');
        $file->is_encrypted = $request->input('is_encrypted');
        $file->chunks = $request->input('chunks');
        $file->required_chunks = $request->has('required_chunks') ? $request->input('required_chunks') : 1;
        $file->disperse = $request->input('disperse');

        $tokencatalog = $request->input('catalog');

        $tokenuser = $request->input('tokenuser');
        $url = "http://" . env('AUTH') . '/auth/v1/user?tokenuser=' . $tokenuser;
        $response = Http::get($url);

        if ($response->status() == 404) {
            return response()->json([
                "message" => "Unauthorized"
            ], 401);
        }

        $url = "http://" . env('PUBSUB') . '/subscription/v1/catalogs/' . $tokencatalog;
        $response = Http::get($url);


        if ($response->status() == 404) {
            return response()->json([
                "message" => "Catalog not found"
            ], 404);
        } else {
            $nodes = Server::where('up', True)->get()->toArray();
            foreach ($nodes as $key => $node) {
                $nodes[$key]["used"] = FilesInServer::where('server_id', $node["id"])
                                            ->join('files', 'files.keyfile', '=', 'files_in_servers.keyfile')
                                            ->sum('size');
            
                $nodes[$key]["used"] += Chunk::where('server_id', $node["id"])->sum('size');
                #File::where('server_id', $node["id"])->sum('size');
            }

            try {
                $file->save();
            } catch (QueryException $e) {
                return response()->json([
                    "message" => $e->getMessage()
                ], 409);
            }

            $data = Server::allocate($file, $nodes);

            if ($file->is_encrypted == 1) {
                $servers_abekeys = Server::emplazador(1, $nodes, NODES_REQUIRED_PUSH);
                $temp = $servers_abekeys[0]["url"] . "/upload.php?file=abekeys/" . $file->keyfile;
                $down_link = $servers_abekeys[0]["url"] . "abekeys/" . $file->keyfile;

                $abekey = new Abekey;
                $abekey->keyfile = $file->keyfile;
                $abekey->url = $down_link;
                $abekey->save();

                
                $keys[] = array("route" => $temp);
            }

            $url = "http://" . env('GATEWAY') . '/pub_sub/v1/catalogs/'. $tokencatalog .'/files/upload';
            $response = Http::post($url, ["keyfile" => $file->keyfile]);


            if($response->status() != 201){
                return response()->json([
                    "message" => "Error adding file to catalog"
                ], 500);
            }

            return response()->json([
                "message" => "File record created",
                "file" => $file,
                "nodes" => $data,
                "keys" => $keys,
            ], 201);
        }

    }

    public function pull(Request $request)
    {
        if (isset($request->key) && isset($request->tokenuser)) {
            $keyfile = $request->key;
            $tokenuser = $request->tokenuser;


            $url = "http://" . env('AUTH') . '/auth/v1/user?tokenuser=' . $tokenuser;
            $response = Http::get($url);

            if ($response->status() == 404) {
                return response()->json([
                    "message" => "Unauthorized"
                ], 401);
            }
            
            try {
                $file = File::where('keyfile', $keyfile)->first();
            } catch (QueryException $e) {
                return response()->json([
                    "message" => "File not found"
                ], 404);
            }
            
            $data = Server::locate($tokenuser, $file);

            if($file->is_encrypted == 1){
                $abekey = Abekey::where('keyfile', $keyfile)->first();
                $data["abekey"] = $abekey->url;
            }

            return response()->json([
                "message" => "File record found",
                "data" => $data
            ], 200);
            


        } else {
            return response()->json([
                "message" => "Bad request"
            ], 400);
        }

    }



    public function exists(Request $request, $tokenuser, $keyfile)
    {
        
        $url = "http://" . env('AUTH') . '/auth/v1/user?tokenuser=' . $tokenuser;
        $response = Http::get($url);

        if ($response->status() == 404) {
            return response()->json([
                "message" => "Unauthorized"
            ], 401);
        }


        $file = File::where('keyfile', $keyfile)->first();

        if ($file) {
            return response()->json([
                "message" => "File exists",
                "file" => $file,
                "exists" => true,
            ], 200);
        } else {
            return response()->json([
                "message" => "File not found"
            ], 404);
        }
    }


    public function delete(Request $request, $tokenuser, $keyfile)
    {
        
        $url = "http://" . env('AUTH') . '/auth/v1/user?tokenuser=' . $tokenuser;
        $response = Http::get($url);

        if ($response->status() == 404) {
            return response()->json([
                "message" => "Unauthorized"
            ], 401);
        }


        $file = File::where('keyfile', $keyfile)->first();

        if ($file) {
            $servers = Server::locate($tokenuser, $file)["routes"];
            $error = false;

            foreach($servers as $server){
                $url = $server["server"] . "/delete.php?file=" . $keyfile . "&tokenuser=" . $tokenuser;
                $response = Http::delete($url);

                $error = $response->status() != 200;
            }

            try {
                #$file->delete();
                $file->removed = 1;
                $file->save();
            } catch (QueryException $e) {
                return response()->json([
                    "message" => "Error removing metadata of the file."
                ], 404);
            }

            if($error){
                return response()->json([
                    "message" => "Error deleting file on server. Metadata removed correctly."
                ], 200);
            }
            
            return response()->json([
                "message" => "Metadata and file deleted",
            ], 200);
        } else {
            return response()->json([
                "message" => "File not found"
            ], 404);
        }
    }
}
