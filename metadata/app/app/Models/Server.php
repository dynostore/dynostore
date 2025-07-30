<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Server extends Model
{
    use HasFactory;

    protected $fillable = [
        "url",
        "memory",
        "storage",
        "up",
    ];

    public static function sortNodesByUF(&$nodes, $file_size)
    {
        foreach ($nodes as $key => $node) {
            $nodes[$key]["uf"] = 1.0 - (double) (($node["storage"] - ($node["used"] + $file_size)) / $node["storage"]);
        }
        usort($nodes, function ($a, $b) {
            if ($a["uf"] < $b["uf"])
                return -1;
            else if ($a["uf"] == $b["uf"])
                return 0;
            else
                return 1;
        });
    }

    public static function allocate_single($file, $nodes, $token_user, $userImpactFactor = 0.1)
    {
        #$total_nodes = count($nodes);
        //$replicationFactor = round($total_nodes * $userImpactFactor);
        Server::sortNodesByUF($nodes, $file->size);
        $url = $nodes[0]["url"] . '/objects/' . $file->keyfile . '/' . $token_user;
        $fileInServer = new FilesInServer;
        $fileInServer->server_id = $nodes[0]["id"];
        $fileInServer->keyfile = $file->keyfile;
        $fileInServer->save();
        return $url;
    }

    public static function allocate_ida($file, $nodes, $token_user, $userImpactFactor = 0.1){
        $required_nodes = $file->chunks;
        $chunk_size = $file->size / $file->chunks;
        $total_nodes = count($nodes);
        $result = false;

        if ($required_nodes > $total_nodes) {
            return response()->json([
                "message" => "Not enough nodes"
            ], 400);
        }else{

            Server::sortNodesByUF($nodes, $file->size);

            $result = array();
            for($i=1;$i <= $required_nodes; $i++){
                $chunk_name = "c" . $i . "_" . $file->name;
                $chunk = new Chunk;
                $chunk->name = $chunk_name;
                $chunk->size = $chunk_size;
                $chunk->keyfile = $file->keyfile;
                $chunk->keychunk = Str::uuid();
                $chunk->server_id = $nodes[$i-1]["id"];
                $chunk->save();
                $id = $chunk->id;
                $result[]["route"] = $nodes[$i-1]["url"] . '/objects/' . $file->keyfile . $chunk->keychunk . '/' . $token_user;
            }
        }
        return $result;
    }

    public static function allocate($file, $nodes, $token_user){
        $data = array();
        switch ($file->disperse) {
            case "IDA":
            case "SIDA":
                $data = Server::allocate_ida($file, $nodes, $token_user);
                break;
            case "SINGLE":
                $url = Server::allocate_single($file, $nodes, $token_user);
                $data[] = array("route" => $url);
                
                break;
        }

        return $data;
    }

    public static function locate_single($tokenuser, $file){
        $nodes = FilesInServer::where('keyfile', $file->keyfile)
                                ->join('servers', 'servers.id', '=', 'files_in_servers.server_id')
                                ->get()
                                ->toArray(); 
        $result = array();
        $result[] = array("route" => $nodes[0]["url"] . "/objects/$file->keyfile/$tokenuser");
        return $result;
    }

    public static function locate_ida($tokenuser, $file){
        $number_chunks = $file->chunks;
        $required_chunks = $file->required_chunks;
        $chunks = Chunk::where('keyfile', $file->keyfile)
                                ->join('servers', 'servers.id', '=', 'chunks.server_id')
                                ->get()
                                ->toArray(); 
        $result = array();
        $i = 0;

        while($i < $required_chunks){
            $url = $chunks[$i]["url"];
            $response = Http::get($url . "/health");
            if($response->status() == 200){
                $result[] = array("chunk" => $chunks[$i], "route" => $url . "/objects/" . $chunks[$i]["keyfile"] . $chunks[$i]["keychunk"] . "/$tokenuser", "server" => $url);
                $i++;
            }        
        }

        return $result;
    }


    public static function locate($tokenuser, $file){
        switch ($file->disperse) {
            case "IDA":
            case "SIDA":
                $servers = Server::locate_ida($tokenuser, $file);
                $data = array("routes" => $servers);
                break;
            case "SINGLE":
                $temp = Server::locate_single($tokenuser, $file);
                if(count($temp) == 0){
                    return response()->json([
                        "message" => "File not found"
                    ], 404);
                }
                $data = array("routes" => $temp);
                break;
        }
        return $data;
    }

    public static function emplazador($esferas, $contenedores, $total_contenedores){
        $indice_contenedores = rand(0, $total_contenedores - 1);
        $resultado_contenedores = array();

        for ($i = 0; $i < $esferas; $i++) {
            $resultado_contenedores[] = $contenedores[$indice_contenedores++];
            if ($indice_contenedores >= $total_contenedores) {
                $indice_contenedores = 0;
            }
        }
        return $resultado_contenedores;
    }
}
