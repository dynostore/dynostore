<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFileRequest;
use App\Http\Requests\UpdateFileRequest;
use Illuminate\Support\Facades\DB;
use App\Models\Abekey;
use App\Models\Chunk;
use App\Models\File;
use App\Models\Server;
use App\Models\FilesInServer;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Http;

define('NODES_REQUIRED_PUSH', 5);
define('AUTH', env('AUTH_HOST'));

class FileController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreFileRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function push(Request $request, $tokenuser, $tokencatalog, $keyfile)
    {
        $url = "http://" . AUTH . '/auth/v1/user?tokenuser=' . $tokenuser;

        $response = Http::get($url);

        if ($response->status() == 404) {
            return response()->json([
                "message" => "Unauthorized"
            ], 401);
        }

        $keys = array();
        $file = new File;
        $file->name = $request->input('name');
        $file->keyfile = $keyfile;
        $file->size = $request->input('size');
        $file->hash = $request->input('hash');
        $file->is_encrypted = $request->input('is_encrypted');
        $file->chunks = $request->input('chunks');
        $file->required_chunks = $request->has('required_chunks') ? $request->input('required_chunks') : 1;
        $file->disperse = $file->chunks == 1 ? "SINGLE" : "IDA";

        $file->owner = $tokenuser;

        # Get servers and their utilization
        try {
            $nodes = Server::where('up', True)->get()->toArray();
            foreach ($nodes as $key => $node) {
                $nodes[$key]["used"] = FilesInServer::where('server_id', $node["id"])
                    ->join('files', 'files.keyfile', '=', 'files_in_servers.keyfile')
                    ->sum('size');

                $nodes[$key]["used"] += Chunk::where('server_id', $node["id"])->sum('size');
                #File::where('server_id', $node["id"])->sum('size');
            }

            if (empty($nodes)) {
                return response()->json([
                    "message" => "Not enough servers to store the file"
                ], 409);
            }

        } catch (QueryException $e) {
            return response()->json([
                "message" => $e->getMessage()
            ], 409);
        }

        # Regist object metadata
        try {
            $file = File::updateOrCreate(
                ["keyfile" => $keyfile],
                [
                    "name" => $file->name,
                    "size" => $file->size,
                    "hash" => $file->hash,
                    "is_encrypted" => $file->is_encrypted,
                    "chunks" => $file->chunks,
                    "required_chunks" => $file->required_chunks,
                    "owner" => $file->owner,
                    "disperse" => $file->disperse
                ]
            );
        } catch (QueryException $e) {
            return response()->json([
                "message" => $e->getMessage()
            ], 400);
        }

        # Allocate object
        $data = Server::allocate($file, $nodes, $tokenuser);

        if ($file->is_encrypted == 1) {
            $servers_abekeys = Server::emplazador(1, $nodes, NODES_REQUIRED_PUSH);
            $temp = $servers_abekeys[0]["url"] . '/abekeys/' . $file->keyfile . '/' . $tokenuser;
            $down_link = $servers_abekeys[0]["url"] . '/abekeys/' . $file->keyfile . '/' . $tokenuser;

            $abekey = new Abekey;
            $abekey->keyfile = $file->keyfile;
            $abekey->url = $down_link;
            $abekey->save();


            $keys[] = array("route" => $temp);
        }

        return response()->json([
            "message" => "Object record created or updated",
            "file" => $file,
            "nodes" => $data,
            "keys" => $keys,
        ], 201);


    }

    public function pushDREX(Request $request, $tokenuser, $tokencatalog, $keyfile)
    {
        $url = "http://" . AUTH . '/auth/v1/user?tokenuser=' . $tokenuser;

        $response = Http::get($url);

        if ($response->status() == 404) {
            return response()->json([
                "message" => "Unauthorized"
            ], 401);
        }

        $keys = array();
        $file = new File;
        $file->name = $request->input('name');
        $file->keyfile = $keyfile;
        $file->size = $request->input('size');
        $file->hash = $request->input('hash');
        $file->is_encrypted = $request->input('is_encrypted');
        $file->chunks = $request->input('chunks');
        $file->required_chunks = $request->has('required_chunks') ? $request->input('required_chunks') : 1;
        $file->disperse = $file->chunks == 1 ? "SINGLE" : "IDA";
        $file->owner = $tokenuser;

        $desired_nodes = $request->input("nodes");
        

        # Regist object metadata
        try {
            $file = File::updateOrCreate(
                ["keyfile" => $keyfile],
                [
                    "name" => $file->name,
                    "size" => $file->size,
                    "hash" => $file->hash,
                    "is_encrypted" => $file->is_encrypted,
                    "chunks" => $file->chunks,
                    "required_chunks" => $file->required_chunks,
                    "owner" => $file->owner,
                    "disperse" => $file->disperse
                ]
            );
        } catch (QueryException $e) {
            return response()->json([
                "message" => $e->getMessage()
            ], 400);
        }

        try {
            $nodes = Server::where('up', True)->get()->toArray();

            if (empty($nodes)) {
                return response()->json([
                    "message" => "Not enough servers to store the file"
                ], 409);
            }

        } catch (QueryException $e) {
            return response()->json([
                "message" => $e->getMessage()
            ], 409);
        }
        

        $result = array();
        $chunk_size = $file->size / $file->chunks;
        for ($i = 1; $i <= $file->chunks; $i++) {
            $chunk_name = "c" . $i . "_" . $file->name;
            $chunk = new Chunk;
            $chunk->name = $chunk_name;
            $chunk->size = $chunk_size;
            $chunk->keyfile = $file->keyfile;
            $chunk->keychunk = Str::uuid();
            $chunk->server_id = $nodes[$desired_nodes[$i - 1]]["id"];
            $chunk->save();
            $id = $chunk->id;
            $result[]["route"] = $nodes[$desired_nodes[$i - 1]]["url"] . '/objects/' . $file->keyfile . $chunk->keychunk . '/' . $tokenuser;
        }

        if ($file->is_encrypted == 1) {
            $servers_abekeys = Server::emplazador(1, $nodes, NODES_REQUIRED_PUSH);
            $temp = $servers_abekeys[0]["url"] . '/abekeys/' . $file->keyfile . '/' . $tokenuser;
            $down_link = $servers_abekeys[0]["url"] . '/abekeys/' . $file->keyfile . '/' . $tokenuser;

            $abekey = new Abekey;
            $abekey->keyfile = $file->keyfile;
            $abekey->url = $down_link;
            $abekey->save();


            $keys[] = array("route" => $temp);
        }

        return response()->json([
            "message" => "Object record created or updated",
            "file" => $file,
            "nodes" => $result,
            "keys" => $keys,
        ], 201);
    }

    public function pull(Request $request, $tokenuser, $keyfile)
    {

        try {
            $file = File::where('keyfile', $keyfile)
                ->where('removed', 0)
                ->where('owner', "=", $tokenuser)
                ->first();
        } catch (QueryException $e) {
            return response()->json([
                "message" => "Object " . $keyfile . " not found or not authorized"
            ], 404);
        }

        if (!$file) {
            return response()->json([
                "message" => "Object " . $keyfile . "  not found or not authorized for " . $tokenuser
            ], 404);
        }

        $data = Server::locate($tokenuser, $file);

        $data["file"] = array(
            "name" => $file->name,
            "keyfile" => $file->keyfile,
            "size" => $file->size,
            "hash" => $file->hash,
            "is_encrypted" => $file->is_encrypted,
            "chunks" => $file->chunks,
            "required_chunks" => $file->required_chunks,
            "disperse" => $file->disperse
        );

        if ($file->is_encrypted == 1) {
            $abekey = Abekey::where('keyfile', $keyfile)->first();
            $data["abekey"] = $abekey->url;
            
        }

        return response()->json([
            "message" => "File record found",
            "data" => $data
        ], 200);

    }



    public function exists(Request $request, $tokenuser, $keyfile)
    {
        try {
            $file = File::where('keyfile', $keyfile)
                ->where('removed', 0)
                ->where('owner', "=", $tokenuser)
                ->first();
        } catch (QueryException $e) {
            return response()->json([
                "message" => "Object not found or not authorized"
            ], 404);
        }


        $file = File::where('keyfile', $keyfile)
            ->where('removed', 0)
            ->where('owner', "=", $tokenuser)
            ->first();

        if ($file) {
            return response()->json([
                "message" => "File exists",
                "exists" => true,
                "metadata" => [
                    "name" => $file->name,
                    "keyfile" => $file->keyfile,
                    "size" => $file->size,
                    "hash" => $file->hash,
                    "is_encrypted" => $file->is_encrypted,
                    "chunks" => $file->chunks,
                    "required_chunks" => $file->required_chunks,
                    "disperse" => $file->disperse
                ]
            ], 200);
        } else {
            return response()->json([
                "message" => "File not found",
                "exists" => false
            ], 200);
        }
    }


    public function delete(Request $request, $tokenuser, $keyfile)
    {
        $file = File::where('keyfile', $keyfile)
            ->where('removed', 0)
            ->where('owner', "=", $tokenuser)
            ->first();

        if ($file) {
            $servers = Server::locate($tokenuser, $file)["routes"];
            $error = false;

            foreach ($servers as $server) {
                $response = Http::delete($server["route"]);
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

            if ($error) {
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
