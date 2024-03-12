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
        $url = "http://" . env('AUTH') . '/auth/v1/user?tokenuser=' . $tokenuser;
        $response = Http::get($url);


        if ($response->status() == 404) {
            return response()->json([
                "message" => "Unauthorized"
            ], 401);
        }

        $servers = Server::all();
        return response($servers, 200);
    }

    public function statistics($tokenuser)
    {
        $url = "http://" . env('AUTH') . '/auth/v1/user?tokenuser=' . $tokenuser;
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
        $url = "http://" . env('AUTH') . '/auth/v1/user?tokenuser=' . $tokenuser;
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Server  $server
     * @return \Illuminate\Http\Response
     */
    public function show(Server $server)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Server  $server
     * @return \Illuminate\Http\Response
     */
    public function edit(Server $server)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateServerRequest  $request
     * @param  \App\Models\Server  $server
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateServerRequest $request, Server $server)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Server  $server
     * @return \Illuminate\Http\Response
     */
    public function destroy(Server $server)
    {
        //
    }
}
