<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServerRequest;
use App\Http\Requests\UpdateServerRequest;
use App\Models\Server;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http as Http;
use App\Support\csv_log;

class ServerController extends Controller
{
    /** -------- timing helpers (ns → ms) -------- */
    private static function t0(): int
    {
        return hrtime(true);
    }

    private static function ms_since(int $t0): string
    {
        return number_format((hrtime(true) - $t0) / 1e6, 3, '.', '');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($tokenuser)
    {
        $t_total = self::t0();
        csv_log('SERVERS','INDEX',$tokenuser,'START','RUN','', 'info');

        // Auth
        $t_auth = self::t0();
        $url = "http://" . env('AUTH_HOST') . '/auth/v1/user?tokenuser=' . $tokenuser;
        $response = Http::get($url);
        $auth_ms = self::ms_since($t_auth);

        if ($response->status() == 404) {
            csv_log('SERVERS','INDEX',$tokenuser,'END','UNAUTHORIZED',"status=401;auth_time_ms={$auth_ms}", 'warning');
            return response()->json(["message" => "Unauthorized"], 401);
        }

        // Fetch servers
        $t_fetch = self::t0();
        $servers = Server::all()->toArray();
        $fetch_ms = self::ms_since($t_fetch);

        foreach ($servers as $key => $server) {
            $t_server = self::t0();

            $t_f = self::t0();
            $files_utilization = DB::table('files_in_servers')
                ->join('files', 'files.keyfile', '=', 'files_in_servers.keyfile')
                ->where('server_id', $server["id"])
                ->sum('size');
            $files_ms = self::ms_since($t_f);

            $t_c = self::t0();
            $chunks_utilization = DB::table('chunks')
                ->where('server_id', $server["id"])
                ->sum('size');
            $chunks_ms = self::ms_since($t_c);

            $utilization = $files_utilization + $chunks_utilization;

            $t_cnt = self::t0();
            $n_files = DB::table('files_in_servers')
                ->join('files', 'files.keyfile', '=', 'files_in_servers.keyfile')
                ->where('server_id', $server["id"])
                ->count();
            $count_ms = self::ms_since($t_cnt);

            $servers[$key]["utilization"] = $utilization;
            $servers[$key]["n_files"] = $n_files;

            $server_ms = self::ms_since($t_server);

            csv_log(
                'SERVERS','INDEX',$tokenuser,'END','SERVER_STATS',
                "server_id={$server['id']};url={$server['url']};utilization={$utilization};n_files={$n_files};files_util_time_ms={$files_ms};chunks_util_time_ms={$chunks_ms};count_time_ms={$count_ms};server_total_time_ms={$server_ms}",
                'debug'
            );
        }

        $total_ms = self::ms_since($t_total);
        csv_log('SERVERS','INDEX',$tokenuser,'END','SUCCESS',"count=".count($servers).";auth_time_ms={$auth_ms};fetch_time_ms={$fetch_ms};total_time_ms={$total_ms}", 'info');
        return response($servers, 200);
    }

    public function statistics($tokenuser)
    {
        $t_total = self::t0();
        csv_log('SERVERS','STATISTICS',$tokenuser,'START','RUN','', 'info');

        // Note: original code used AUTH_HOST_HOST; keeping as-is.
        $t_auth = self::t0();
        $url = "http://" . env('AUTH_HOST_HOST') . '/auth/v1/user?tokenuser=' . $tokenuser;
        $response = Http::get($url);
        $auth_ms = self::ms_since($t_auth);

        if ($response->status() == 404) {
            csv_log('SERVERS','STATISTICS',$tokenuser,'END','UNAUTHORIZED',"status=401;auth_time_ms={$auth_ms}", 'warning');
            return response()->json(["message" => "Unauthorized"], 401);
        }

        $t_fetch = self::t0();
        $servers = Server::all()->toArray();
        $fetch_ms = self::ms_since($t_fetch);

        foreach ($servers as $key => $server) {
            $t_server = self::t0();

            $t_f = self::t0();
            $files_utilization = DB::table('files_in_servers')
                ->join('files', 'files.keyfile', '=', 'files_in_servers.keyfile')
                ->where('server_id', $server["id"])
                ->sum('size');
            $files_ms = self::ms_since($t_f);

            $t_cnt = self::t0();
            $n_files = DB::table('files_in_servers')
                ->join('files', 'files.keyfile', '=', 'files_in_servers.keyfile')
                ->where('server_id', $server["id"])
                ->count();
            $count_ms = self::ms_since($t_cnt);

            $t_c = self::t0();
            $chunks_utilization = DB::table('chunks')
                ->where('server_id', $server["id"])
                ->sum('size');
            $chunks_ms = self::ms_since($t_c);

            $utilization = $files_utilization + $chunks_utilization;

            $servers[$key]["n_files"] = $n_files;
            $servers[$key]["utilization"] = $utilization;

            $server_ms = self::ms_since($t_server);

            csv_log(
                'SERVERS','STATISTICS',$tokenuser,'END','SERVER_STATS',
                "server_id={$server['id']};url={$server['url']};utilization={$utilization};n_files={$n_files};files_util_time_ms={$files_ms};chunks_util_time_ms={$chunks_ms};count_time_ms={$count_ms};server_total_time_ms={$server_ms}",
                'debug'
            );
        }

        $total_ms = self::ms_since($t_total);
        csv_log('SERVERS','STATISTICS',$tokenuser,'END','SUCCESS',"count=".count($servers).";auth_time_ms={$auth_ms};fetch_time_ms={$fetch_ms};total_time_ms={$total_ms}", 'info');
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
        $t_total = self::t0();
        csv_log('SERVERS','STORE',$tokenuser,'START','RUN','', 'info');

        $t_auth = self::t0();
        $url = "http://" . env('AUTH_HOST') . '/auth/v1/user?tokenuser=' . $tokenuser;
        $response = Http::get($url);
        $auth_ms = self::ms_since($t_auth);

        if ($response->status() == 404) {
            csv_log('SERVERS','STORE',$tokenuser,'END','UNAUTHORIZED',"status=401;auth_time_ms={$auth_ms}", 'warning');
            return response()->json(["message" => "Unauthorized"], 401);
        }

        $server = new Server;
        $server->url = $request->input('url');
        $server->memory = $request->input('memory');
        $server->storage = $request->input('storage');
        $server->up = $request->input('up'); // TODO: health-check url

        $t_save = self::t0();
        $server->save();
        $save_ms = self::ms_since($t_save);

        $total_ms = self::ms_since($t_total);
        csv_log(
            'SERVERS','STORE',$tokenuser,'END','SUCCESS',
            "id={$server->id};url={$server->url};up={$server->up};storage={$server->storage};memory={$server->memory};auth_time_ms={$auth_ms};save_time_ms={$save_ms};total_time_ms={$total_ms}",
            'info'
        );

        return response()->json(["message" => "Server record created"], 201);
    }

    public function clean()
    {
        $t_total = self::t0();
        csv_log('SERVERS','CLEAN','-','START','RUN','', 'info');

        $t_fetch = self::t0();
        $servers = Server::all()->toArray();
        $fetch_ms = self::ms_since($t_fetch);

        foreach ($servers as $key => $server) {
            $url = $server["url"] . '/clean';
            $t_http = self::t0();
            $response = Http::get($url);
            $http_ms = self::ms_since($t_http);

            $ok = $response->status() == 200;
            $servers[$key]["clean"] = $ok ? "success" : "failed";

            csv_log(
                'SERVERS','CLEAN',$server['id'],'END',$ok ? 'SUCCESS' : 'ERROR',
                "route={$url};status={$response->status()};http_time_ms={$http_ms}",
                $ok ? 'info' : 'warning'
            );
        }

        // wipe metadata tables (measure)
        $t_del_fis = self::t0();
        $deleted_files_in_servers = DB::table('files_in_servers')->delete();
        $del_fis_ms = self::ms_since($t_del_fis);

        foreach ($servers as $key => $server) {
            $t_del_chunks = self::t0();
            $deleted_chunks = DB::table('chunks')
                ->where('server_id', $server["id"])
                ->delete();
            $del_chunks_ms = self::ms_since($t_del_chunks);

            csv_log(
                'SERVERS','CLEAN_META',$server['id'],'END','SUCCESS',
                "files_in_servers_deleted={$deleted_files_in_servers};chunks_deleted={$deleted_chunks};fis_del_time_ms={$del_fis_ms};chunks_del_time_ms={$del_chunks_ms}",
                'debug'
            );
        }

        $total_ms = self::ms_since($t_total);
        csv_log('SERVERS','CLEAN','-','END','SUCCESS',"servers=".count($servers).";fetch_time_ms={$fetch_ms};total_time_ms={$total_ms}", 'info');
        return $servers;
    }

    public function deleteAll()
    {
        $t_total = self::t0();
        csv_log('SERVERS','DELETE_ALL','-','START','RUN','', 'warning');

        $t_fis = self::t0();
        $del_fis = DB::table('files_in_servers')->delete();
        $fis_ms = self::ms_since($t_fis);

        $t_chunks = self::t0();
        $del_chunks = DB::table('chunks')->delete();
        $chunks_ms = self::ms_since($t_chunks);

        $t_servers = self::t0();
        $del_servers = DB::table("servers")->delete();
        $servers_ms = self::ms_since($t_servers);

        $total_ms = self::ms_since($t_total);
        csv_log(
            'SERVERS','DELETE_ALL','-','END','SUCCESS',
            "files_in_servers_deleted={$del_fis};chunks_deleted={$del_chunks};servers_deleted={$del_servers};fis_time_ms={$fis_ms};chunks_time_ms={$chunks_ms};servers_time_ms={$servers_ms};total_time_ms={$total_ms}",
            'warning'
        );

        return "Servers deleted";
    }
}
