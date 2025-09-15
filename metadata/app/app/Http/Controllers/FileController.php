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
use Illuminate\Support\Facades\Log;
use App\Support\csv_log;
use Http;

define('NODES_REQUIRED_PUSH', 5);
define('AUTH', env('AUTH_HOST'));

class FileController extends Controller
{
    /** High-resolution timer helpers */
    private static function t0(): int
    {
        return hrtime(true); // ns
    }

    private static function ms_since(int $t0): string
    {
        return number_format((hrtime(true) - $t0) / 1e6, 3, '.', '');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function push(Request $request, $tokenuser, $tokencatalog, $keyfile)
    {
        $t_total = self::t0();
        csv_log('METADATA','PUSH',$keyfile,'START','RUN',"user={$tokenuser};catalog={$tokencatalog}", 'info');

        // Auth
        $t_auth = self::t0();
        $url = "http://" . AUTH . '/auth/v1/user?tokenuser=' . $tokenuser;
        $response = Http::get($url);
        $auth_ms = self::ms_since($t_auth);

        if ($response->status() == 404) {
            csv_log('METADATA','PUSH',$keyfile,'END','UNAUTHORIZED',"status=401;auth_time_ms={$auth_ms}", 'warning');
            return response()->json(["message" => "Unauthorized"], 401);
        }

        $keys = [];
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

        // Get servers and utilization
        try {
            $t_nodes = self::t0();
            $nodes = Server::where('up', true)->get()->toArray();
            foreach ($nodes as $key => $node) {
                $t_util = self::t0();
                $nodes[$key]["used"] = FilesInServer::where('server_id', $node["id"])
                    ->join('files', 'files.keyfile', '=', 'files_in_servers.keyfile')
                    ->sum('size');
                $nodes[$key]["used"] += Chunk::where('server_id', $node["id"])->sum('size');
                $util_ms = self::ms_since($t_util);
                csv_log('METADATA','PUSH',$keyfile,'END','UTILIZATION_OK',"server_id={$node['id']};util_time_ms={$util_ms}", 'debug');
            }
            $nodes_ms = self::ms_since($t_nodes);

            if (empty($nodes)) {
                csv_log('METADATA','PUSH',$keyfile,'END','ALLOCATE_ERROR',"no_servers;status=409;nodes_time_ms={$nodes_ms}", 'error');
                return response()->json(["message" => "Not enough servers to store the file"], 409);
            }
        } catch (QueryException $e) {
            csv_log('METADATA','PUSH',$keyfile,'END','DB_ERROR',"msg={$e->getMessage()};status=409", 'error');
            return response()->json(["message" => $e->getMessage()], 409);
        }

        // Register object metadata
        try {
            $t_meta = self::t0();
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
            $meta_ms = self::ms_since($t_meta);
            csv_log('METADATA','PUSH',$keyfile,'END','METADATA_OK',
                "name={$file->name};size={$file->size};chunks={$file->chunks};required={$file->required_chunks};metadata_time_ms={$meta_ms}", 'info');
        } catch (QueryException $e) {
            csv_log('METADATA','PUSH',$keyfile,'END','METADATA_ERROR',"msg={$e->getMessage()};status=400", 'error');
            return response()->json(["message" => $e->getMessage()], 400);
        }

        // Allocate object
        $t_alloc = self::t0();
        $data = Server::allocate($file, $nodes, $tokenuser);
        $alloc_ms = self::ms_since($t_alloc);
        csv_log('METADATA','PUSH',$keyfile,'END','ALLOCATE_OK',"routes=".count($data['routes'] ?? []).";allocate_time_ms={$alloc_ms}", 'info');

        // Optional ABE key
        if ($file->is_encrypted == 1) {
            $t_abe = self::t0();
            $servers_abekeys = Server::emplazador(1, $nodes, NODES_REQUIRED_PUSH);
            $temp = $servers_abekeys[0]["url"] . '/abekeys/' . $file->keyfile . '/' . $tokenuser;
            $down_link = $servers_abekeys[0]["url"] . '/abekeys/' . $file->keyfile . '/' . $tokenuser;

            $abekey = new Abekey;
            $abekey->keyfile = $file->keyfile;
            $abekey->url = $down_link;
            $abekey->save();
            $keys[] = ["route" => $temp];

            $abe_ms = self::ms_since($t_abe);
            csv_log('METADATA','PUSH',$keyfile,'END','ABEKEY_OK',"route={$down_link};abekey_time_ms={$abe_ms}", 'info');
        }

        $total_ms = self::ms_since($t_total);
        csv_log('METADATA','PUSH',$keyfile,'END','SUCCESS',"status=201;total_time_ms={$total_ms}", 'info');
        return response()->json([
            "message" => "Object record created or updated",
            "file" => $file,
            "nodes" => $data,
            "keys" => $keys,
        ], 201);
    }

    public function pushDREX(Request $request, $tokenuser, $tokencatalog, $keyfile)
    {
        $t_total = self::t0();
        csv_log('METADATA','PUSH_DREX',$keyfile,'START','RUN',"user={$tokenuser};catalog={$tokencatalog}", 'info');

        // Auth
        $t_auth = self::t0();
        $url = "http://" . AUTH . '/auth/v1/user?tokenuser=' . $tokenuser;
        $response = Http::get($url);
        $auth_ms = self::ms_since($t_auth);

        if ($response->status() == 404) {
            csv_log('METADATA','PUSH_DREX',$keyfile,'END','UNAUTHORIZED',"status=401;auth_time_ms={$auth_ms}", 'warning');
            return response()->json(["message" => "Unauthorized"], 401);
        }

        $keys = [];
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
        csv_log('METADATA','PUSH_DREX',$keyfile,'START','PARAMS',
            "desired_nodes_count=".count((array)$desired_nodes).";chunks={$file->chunks}", 'debug');

        // Register metadata
        try {
            $t_meta = self::t0();
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
            $meta_ms = self::ms_since($t_meta);
            csv_log('METADATA','PUSH_DREX',$keyfile,'END','METADATA_OK',
                "name={$file->name};size={$file->size};chunks={$file->chunks};required={$file->required_chunks};metadata_time_ms={$meta_ms}", 'info');
        } catch (QueryException $e) {
            csv_log('METADATA','PUSH_DREX',$keyfile,'END','METADATA_ERROR',"msg={$e->getMessage()};status=400", 'error');
            return response()->json(["message" => $e->getMessage()], 400);
        }

        // Nodes
        try {
            $t_nodes = self::t0();
            $nodes = Server::where('up', true)->get()->toArray();
            $nodes_ms = self::ms_since($t_nodes);
            if (empty($nodes)) {
                csv_log('METADATA','PUSH_DREX',$keyfile,'END','ALLOCATE_ERROR',"no_servers;status=409;nodes_time_ms={$nodes_ms}", 'error');
                return response()->json(["message" => "Not enough servers to store the file"], 409);
            }
            csv_log('METADATA','PUSH_DREX',$keyfile,'END','NODES_OK',"count=".count($nodes).";nodes_time_ms={$nodes_ms}", 'debug');
        } catch (QueryException $e) {
            csv_log('METADATA','PUSH_DREX',$keyfile,'END','DB_ERROR',"msg={$e->getMessage()};status=409", 'error');
            return response()->json(["message" => $e->getMessage()], 409);
        }

        // Build chunk routes
        $t_chunks_total = self::t0();
        $result = [];
        $chunk_size = $file->size / max(1, (int)$file->chunks);
        for ($i = 1; $i <= $file->chunks; $i++) {
            $t_one = self::t0();
            $chunk_name = "c" . $i . "_" . $file->name;
            $chunk = new Chunk;
            $chunk->name = $chunk_name;
            $chunk->size = $chunk_size;
            $chunk->keyfile = $file->keyfile;
            $chunk->keychunk = Str::uuid();
            $chunk->server_id = $nodes[$desired_nodes[$i - 1]]["id"];
            $chunk->save();

            $route = $nodes[$desired_nodes[$i - 1]]["url"] . '/objects/' . $file->keyfile . $chunk->keychunk . '/' . $tokenuser;
            $result[]["route"] = $route;

            $one_ms = self::ms_since($t_one);
            csv_log('METADATA','PUSH_DREX_CHUNK',$keyfile,'END','ROUTE_OK',
                "index={$i};chunk={$chunk->keychunk};server_id={$chunk->server_id};route={$route};chunk_time_ms={$one_ms}", 'debug');
        }
        $chunks_total_ms = self::ms_since($t_chunks_total);

        // ABE key (optional)
        if ($file->is_encrypted == 1) {
            $t_abe = self::t0();
            $servers_abekeys = Server::emplazador(1, $nodes, NODES_REQUIRED_PUSH);
            $temp = $servers_abekeys[0]["url"] . '/abekeys/' . $file->keyfile . '/' . $tokenuser;
            $down_link = $servers_abekeys[0]["url"] . '/abekeys/' . $file->keyfile . '/' . $tokenuser;

            $abekey = new Abekey;
            $abekey->keyfile = $file->keyfile;
            $abekey->url = $down_link;
            $abekey->save();
            $keys[] = ["route" => $temp];

            $abe_ms = self::ms_since($t_abe);
            csv_log('METADATA','PUSH_DREX',$keyfile,'END','ABEKEY_OK',"route={$down_link};abekey_time_ms={$abe_ms}", 'info');
        }

        $total_ms = self::ms_since($t_total);
        csv_log('METADATA','PUSH_DREX',$keyfile,'END','SUCCESS',
            "status=201;routes=".count($result).";chunks_total_time_ms={$chunks_total_ms};total_time_ms={$total_ms}", 'info');

        return response()->json([
            "message" => "Object record created or updated",
            "file" => $file,
            "nodes" => $result,
            "keys" => $keys,
        ], 201);
    }

    public function pull(Request $request, $tokenuser, $keyfile)
    {
        $t_total = self::t0();
        csv_log('METADATA','PULL',$keyfile,'START','RUN',"user={$tokenuser}", 'info');

        // DB fetch
        try {
            $t_db = self::t0();
            $file = File::where('keyfile', $keyfile)
                ->where('removed', 0)
                ->where('owner', "=", $tokenuser)
                ->first();
            $db_ms = self::ms_since($t_db);
            csv_log('METADATA','PULL',$keyfile,'END','DB_OK',"db_time_ms={$db_ms}", 'debug');
        } catch (QueryException $e) {
            csv_log('METADATA','PULL',$keyfile,'END','DB_ERROR',"msg={$e->getMessage()};status=404", 'error');
            return response()->json(["message" => "Object " . $keyfile . " not found or not authorized"], 404);
        }

        if (!$file) {
            csv_log('METADATA','PULL',$keyfile,'END','NOT_FOUND',"status=404;total_time_ms=".self::ms_since($t_total), 'warning');
            return response()->json([
                "message" => "Object " . $keyfile . "  not found or not authorized for " . $tokenuser
            ], 404);
        }

        // Locate routes
        $t_loc = self::t0();
        $data = Server::locate($tokenuser, $file);
        $loc_ms = self::ms_since($t_loc);
        csv_log('METADATA','PULL',$keyfile,'END','LOCATE_OK',"routes=".count($data['routes'] ?? []).";locate_time_ms={$loc_ms}", 'info');

        $data["file"] = [
            "name" => $file->name,
            "keyfile" => $file->keyfile,
            "size" => $file->size,
            "hash" => $file->hash,
            "is_encrypted" => $file->is_encrypted,
            "chunks" => $file->chunks,
            "required_chunks" => $file->required_chunks,
            "disperse" => $file->disperse
        ];

        if ($file->is_encrypted == 1) {
            $t_abe = self::t0();
            $abekey = Abekey::where('keyfile', $keyfile)->first();
            $data["abekey"] = $abekey->url ?? null;
            $abe_ms = self::ms_since($t_abe);
            csv_log('METADATA','PULL',$keyfile,'END','ABEKEY_REF',"url=".($abekey->url ?? 'null').";abekey_time_ms={$abe_ms}", 'debug');
        }

        $total_ms = self::ms_since($t_total);
        csv_log('METADATA','PULL',$keyfile,'END','SUCCESS',"status=200;total_time_ms={$total_ms}", 'info');
        return response()->json([
            "message" => "File record found",
            "data" => $data
        ], 200);
    }

    public function exists(Request $request, $tokenuser, $keyfile)
    {
        $t_total = self::t0();
        csv_log('METADATA','EXISTS',$keyfile,'START','RUN',"user={$tokenuser}", 'info');

        try {
            $t_db = self::t0();
            $file = File::where('keyfile', $keyfile)
                ->where('removed', 0)
                ->where('owner', "=", $tokenuser)
                ->first();
            $db_ms = self::ms_since($t_db);
            csv_log('METADATA','EXISTS',$keyfile,'END','DB_OK',"db_time_ms={$db_ms}", 'debug');
        } catch (QueryException $e) {
            csv_log('METADATA','EXISTS',$keyfile,'END','DB_ERROR',"msg={$e->getMessage()};status=404", 'error');
            return response()->json(["message" => "Object not found or not authorized"], 404);
        }

        $t_db2 = self::t0();
        $file = File::where('keyfile', $keyfile)
            ->where('removed', 0)
            ->where('owner', "=", $tokenuser)
            ->first();
        $db2_ms = self::ms_since($t_db2);

        if ($file) {
            $total_ms = self::ms_since($t_total);
            csv_log('METADATA','EXISTS',$keyfile,'END','FOUND',"status=200;db2_time_ms={$db2_ms};total_time_ms={$total_ms}", 'info');
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
            $total_ms = self::ms_since($t_total);
            csv_log('METADATA','EXISTS',$keyfile,'END','NOT_FOUND',"status=200;db2_time_ms={$db2_ms};total_time_ms={$total_ms}", 'info');
            return response()->json([
                "message" => "File not found",
                "exists" => false
            ], 200);
        }
    }

    public function delete(Request $request, $tokenuser, $keyfile)
    {
        $t_total = self::t0();
        csv_log('METADATA','DELETE',$keyfile,'START','RUN',"user={$tokenuser}", 'info');

        $t_db = self::t0();
        $file = File::where('keyfile', $keyfile)
            ->where('removed', 0)
            ->where('owner', "=", $tokenuser)
            ->first();
        $db_ms = self::ms_since($t_db);
        csv_log('METADATA','DELETE',$keyfile,'END','DB_OK',"db_time_ms={$db_ms}", 'debug');

        if ($file) {
            $t_loc = self::t0();
            $servers = Server::locate($tokenuser, $file)["routes"];
            $loc_ms = self::ms_since($t_loc);
            csv_log('METADATA','DELETE',$keyfile,'END','LOCATE_OK',"routes=".count($servers).";locate_time_ms={$loc_ms}", 'info');

            $error = false;

            foreach ($servers as $server) {
                $t_http = self::t0();
                $response = Http::delete($server["route"]);
                $http_ms = self::ms_since($t_http);
                $ok = ($response->status() == 200);
                $error = $error || !$ok;
                csv_log(
                    'METADATA','DELETE_REMOTE',$keyfile,'END',
                    $ok ? 'SUCCESS' : 'ERROR',
                    "route={$server['route']};status={$response->status()};http_time_ms={$http_ms}",
                    $ok ? 'info' : 'warning'
                );
            }

            try {
                $t_upd = self::t0();
                $file->removed = 1;
                $file->save();
                $upd_ms = self::ms_since($t_upd);
                csv_log('METADATA','DELETE',$keyfile,'END','METADATA_REMOVED',"status=200;update_time_ms={$upd_ms}", 'info');
            } catch (QueryException $e) {
                csv_log('METADATA','DELETE',$keyfile,'END','DB_ERROR',"msg={$e->getMessage()};status=404", 'error');
                return response()->json(["message" => "Error removing metadata of the file."], 404);
            }

            $total_ms = self::ms_since($t_total);
            if ($error) {
                csv_log('METADATA','DELETE',$keyfile,'END','PARTIAL',"remote_error=1;status=200;total_time_ms={$total_ms}", 'warning');
                return response()->json([
                    "message" => "Error deleting file on server. Metadata removed correctly."
                ], 200);
            }

            csv_log('METADATA','DELETE',$keyfile,'END','SUCCESS',"status=200;total_time_ms={$total_ms}", 'info');
            return response()->json(["message" => "Metadata and file deleted"], 200);
        } else {
            csv_log('METADATA','DELETE',$keyfile,'END','NOT_FOUND',"status=404;total_time_ms=".self::ms_since($t_total), 'warning');
            return response()->json(["message" => "File not found"], 404);
        }
    }
}
