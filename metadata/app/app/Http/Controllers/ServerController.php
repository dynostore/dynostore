<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServerRequest;
use App\Http\Requests\UpdateServerRequest;
use App\Models\Server;
use Illuminate\Http\Request;
use Http;
use Illuminate\Support\Facades\DB;

class ServerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($tokenuser)
    {
        //
        $url = "http://" . env('AUTH_HOST') . '/auth/v1/user?tokenuser=' . $tokenuser;
        $response = Http::get($url);


        if ($response->status() == 404) {
            return response()->json([
                "message" => "Unauthorized"
            ], 401);
        }

        $servers = Server::all()->toArray();

        foreach ($servers as $key => $server) {
            $files_utilization = DB::table('files_in_servers')
                ->join('files', 'files.keyfile', '=', 'files_in_servers.keyfile')
                ->where('server_id', $server["id"])
                ->sum('size');
            $chunks_utilization = DB::table('chunks')
                ->where('server_id', $server["id"])
                ->sum('size');
            $utilization = $files_utilization + $chunks_utilization;
            $servers[$key]["utilization"] = $utilization;
            $servers[$key]["n_files"] = DB::table('files_in_servers')
                ->join('files', 'files.keyfile', '=', 'files_in_servers.keyfile')
                ->where('server_id', $server["id"])
                ->count();
        }

        return response($servers, 200);
    }

    public function statistics($tokenuser)
    {
        $url = "http://" . env('AUTH_HOST_HOST') . '/auth/v1/user?tokenuser=' . $tokenuser;
        $response = Http::get($url);


        if ($response->status() == 404) {
            return response()->json([
                "message" => "Unauthorized"
            ], 401);
        }

        $servers = Server::all()->toArray();

        foreach ($servers as $key => $server) {
            $files_utilization = DB::table('files_in_servers')
                ->join('files', 'files.keyfile', '=', 'files_in_servers.keyfile')
                ->where('server_id', $server["id"])
                ->sum('size');
            $servers[$key]["n_files"] = DB::table('files_in_servers')
                ->join('files', 'files.keyfile', '=', 'files_in_servers.keyfile')
                ->where('server_id', $server["id"])
                ->count();
            $chunks_utilization = DB::table('chunks')
                ->where('server_id', $server["id"])
                ->sum('size');
            $utilization = $files_utilization + $chunks_utilization;
            $servers[$key]["utilization"] = $utilization;
        }

        #$servers = Server::all();
        return $servers;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreServerRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $tokenuser)
    {
        //
        $url = "http://" . env('AUTH_HOST') . '/auth/v1/user?tokenuser=' . $tokenuser;
        $response = Http::get($url);


        if ($response->status() == 404) {
            return response()->json([
                "message" => "Unauthorized"
            ], 401);
        }

        $server = new Server;
        $server->url = $request->input('url');
        $server->memory = $request->input('memory');
        $server->storage = $request->input('storage');
        $server->up = $request->input('up'); #ToDo: check if url is up
        $server->save();
        return response()->json([
            "message" => "Server record created"
        ], 201);
    }

    public function clean()
    {
        $servers = Server::all()->toArray();

        #print_r($servers);

        foreach ($servers as $key => $server) {
            $url = $server["url"] . '/clean';
            $response = Http::get($url);

            if ($response->status() == 200) {
                $servers[$key]["clean"] = "success";
            } else {
                $servers[$key]["clean"] = "failed";
            }
        }

        foreach ($servers as $key => $server) {
            $files_utilization = DB::table('files_in_servers')
                ->delete();
            $chunks_utilization = DB::table('chunks')
                ->where('server_id', $server["id"])
                ->delete();
        }

        return $servers;
    }

    public function deleteAll(){
        DB::table('files_in_servers')
                ->delete();
                DB::table('chunks')
                ->delete();
        DB::table("servers")->delete();
        return "Servers deleted";
    }
}
