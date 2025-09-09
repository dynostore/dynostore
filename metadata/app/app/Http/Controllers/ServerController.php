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
        csv_log('SERVERS','INDEX',$tokenuser,'START','RUN','', 'info');

        $url = "http://" . env('AUTH_HOST') . '/auth/v1/user?tokenuser=' . $tokenuser;
        $response = Http::get($url);

        if ($response->status() == 404) {
            csv_log('SERVERS','INDEX',$tokenuser,'END','UNAUTHORIZED','status=401', 'warning');
            return response()->json(["message" => "Unauthorized"], 401);
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

            csv_log(
                'SERVERS','INDEX',$tokenuser,'END','SERVER_STATS',
                "server_id={$server['id']};url={$server['url']};utilization={$utilization};n_files={$servers[$key]['n_files']}",
                'debug'
            );
        }

        csv_log('SERVERS','INDEX',$tokenuser,'END','SUCCESS','count='.count($servers), 'info');
        return response($servers, 200);
    }

    public function statistics($tokenuser)
    {
        csv_log('SERVERS','STATISTICS',$tokenuser,'START','RUN','', 'info');

        $url = "http://" . env('AUTH_HOST_HOST') . '/auth/v1/user?tokenuser=' . $tokenuser;
        $response = Http::get($url);

        if ($response->status() == 404) {
            csv_log('SERVERS','STATISTICS',$tokenuser,'END','UNAUTHORIZED','status=401', 'warning');
            return response()->json(["message" => "Unauthorized"], 401);
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

            csv_log(
                'SERVERS','STATISTICS',$tokenuser,'END','SERVER_STATS',
                "server_id={$server['id']};url={$server['url']};utilization={$utilization};n_files={$servers[$key]['n_files']}",
                'debug'
            );
        }

        csv_log('SERVERS','STATISTICS',$tokenuser,'END','SUCCESS','count='.count($servers), 'info');
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
        csv_log('SERVERS','STORE',$tokenuser,'START','RUN','', 'info');

        $url = "http://" . env('AUTH_HOST') . '/auth/v1/user?tokenuser=' . $tokenuser;
        $response = Http::get($url);

        if ($response->status() == 404) {
            csv_log('SERVERS','STORE',$tokenuser,'END','UNAUTHORIZED','status=401', 'warning');
            return response()->json(["message" => "Unauthorized"], 401);
        }

        $server = new Server;
        $server->url = $request->input('url');
        $server->memory = $request->input('memory');
        $server->storage = $request->input('storage');
        $server->up = $request->input('up'); // TODO: health-check url
        $server->save();

        csv_log(
            'SERVERS','STORE',$tokenuser,'END','SUCCESS',
            "id={$server->id};url={$server->url};up={$server->up};storage={$server->storage};memory={$server->memory}",
            'info'
        );

        return response()->json(["message" => "Server record created"], 201);
    }

    public function clean()
    {
        csv_log('SERVERS','CLEAN','-','START','RUN','', 'info');

        $servers = Server::all()->toArray();

        foreach ($servers as $key => $server) {
            $url = $server["url"] . '/clean';
            $response = Http::get($url);
            $ok = $response->status() == 200;

            $servers[$key]["clean"] = $ok ? "success" : "failed";

            csv_log(
                'SERVERS','CLEAN',$server['id'],'END',$ok ? 'SUCCESS' : 'ERROR',
                "route={$url};status={$response->status()}",
                $ok ? 'info' : 'warning'
            );
        }

        // wipe metadata tables
        $deleted_files_in_servers = DB::table('files_in_servers')->delete();
        foreach ($servers as $key => $server) {
            $deleted_chunks = DB::table('chunks')
                ->where('server_id', $server["id"])
                ->delete();

            csv_log(
                'SERVERS','CLEAN_META',$server['id'],'END','SUCCESS',
                "files_in_servers_deleted={$deleted_files_in_servers};chunks_deleted={$deleted_chunks}",
                'debug'
            );
        }

        csv_log('SERVERS','CLEAN','-','END','SUCCESS','servers='.count($servers), 'info');
        return $servers;
    }

    public function deleteAll()
    {
        csv_log('SERVERS','DELETE_ALL','-','START','RUN','', 'warning');

        $del_fis = DB::table('files_in_servers')->delete();
        $del_chunks = DB::table('chunks')->delete();
        $del_servers = DB::table("servers")->delete();

        csv_log(
            'SERVERS','DELETE_ALL','-','END','SUCCESS',
            "files_in_servers_deleted={$del_fis};chunks_deleted={$del_chunks};servers_deleted={$del_servers}",
            'warning'
        );

        return "Servers deleted";
    }
}
