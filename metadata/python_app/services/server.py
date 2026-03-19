import uuid
import random
import httpx
from sqlalchemy.orm import Session
from models import Chunk, FilesInServer, Server
from fastapi import HTTPException

# Constants
NODES_REQUIRED_PUSH = 5

def sort_nodes_by_uf(nodes: list[dict], file_size: float):
    # nodes is a list of dicts (from SQLAlchemy models converted to dict, plus `used` key)
    for node in nodes:
        used = node.get("used", 0)
        storage = float(node.get("storage", 1.0)) or 1.0  # Prevent division by zero
        node["uf"] = 1.0 - float((storage - (used + file_size)) / storage)

    nodes.sort(key=lambda x: x["uf"])

def allocate_single(db: Session, file_model, nodes: list[dict], token_user: str, userImpactFactor=0.1):
    sort_nodes_by_uf(nodes, file_model.size)
    node = nodes[0]
    
    url = f'{node["url"]}/objects/{file_model.keyfile}/{token_user}'
    
    file_in_server = FilesInServer(
        server_id=node["id"],
        keyfile=file_model.keyfile
    )
    db.add(file_in_server)
    db.commit()
    
    return url

def allocate_ida(db: Session, file_model, nodes: list[dict], token_user: str, userImpactFactor: float = 0.1):
    print("IDAAA", flush=True)
    required_nodes = file_model.chunks
    chunk_size = file_model.size / max(1, file_model.chunks)
    total_nodes = len(nodes)

    if required_nodes > total_nodes:
        raise ValueError("Not enough nodes")

    sort_nodes_by_uf(nodes, file_model.size)

    result = []
    # From 1 to required_nodes
    for i in range(1, required_nodes + 1):
        chunk_name = f"c{i}_{file_model.name}"
        keychunk = str(uuid.uuid4())
        node = nodes[i - 1]
        
        chunk = Chunk(
            name=chunk_name,
            size=chunk_size,
            keyfile=file_model.keyfile,
            keychunk=keychunk,
            server_id=node["id"]
        )
        db.add(chunk)
        
        route = f'{node["url"]}/objects/{file_model.keyfile}{keychunk}/{token_user}'
        result.append({"route": route})
    
    db.commit()
    return result

def allocate(db: Session, file_model, nodes: list[dict], token_user: str):
    if file_model.disperse in ["IDA", "SIDA"]:
        data = allocate_ida(db, file_model, nodes, token_user)
        return data # returns list of routes
    elif file_model.disperse == "SINGLE":
        url = allocate_single(db, file_model, nodes, token_user)
        return [{"route": url}]
    return []

def locate_single(db: Session, token_user: str, file_model):
    nodes = db.query(FilesInServer, Server).join(
        Server, Server.id == FilesInServer.server_id
    ).filter(
        FilesInServer.keyfile == file_model.keyfile
    ).all()
    
    result = []
    if nodes:
        fis, srv = nodes[0]
        result.append({"route": f"{srv.url}/objects/{file_model.keyfile}/{token_user}"})
    return result

async def locate_ida(db: Session, token_user: str, file_model):
    number_chunks = file_model.chunks
    required_chunks = file_model.required_chunks
    
    chunks_query = db.query(Chunk, Server).join(
        Server, Server.id == Chunk.server_id
    ).filter(
        Chunk.keyfile == file_model.keyfile
    ).all()
    
    result = []
    i: int = 0

    print(chunks_query, flush=True)
    
    async with httpx.AsyncClient() as client:
        # We need to iterate over chunks and find `required_chunks` healthy servers
        for chunk, srv in chunks_query:
            print(chunk, srv, flush=True)
            if i >= required_chunks:
                break
            
            url = f"http://{srv.url}"
            
            try:
                print(f"URL: {url}", flush=True)
                response = await client.get(f"{url}/health", timeout=3.0)
                print(f"Response: {response.status_code}", flush=True)
                if response.status_code == 200:
                    result.append({
                        "chunk": {
                            "id": chunk.id,
                            "keyfile": chunk.keyfile,
                            "keychunk": chunk.keychunk,
                            "name": chunk.name,
                            "size": chunk.size,
                            "server_id": chunk.server_id
                        },
                        "route": f"{url}/objects/{chunk.keyfile}{chunk.keychunk}/{token_user}",
                        "server": url
                    })
                    i += 1  # type: ignore
            except httpx.RequestError as e:
                print(str(e), flush=True)
                print("Error", flush=True)
                continue

    return result

async def locate(db: Session, token_user: str, file_model):
    if file_model.disperse in ["IDA", "SIDA"]:
        servers = await locate_ida(db, token_user, file_model)
        return {"routes": servers}
    elif file_model.disperse == "SINGLE":
        servers = locate_single(db, token_user, file_model)
        if not servers:
            raise ValueError("File not found")
        return {"routes": servers}
    return {}

def emplazador(esferas: int, contenedores: list[dict], total_contenedores: int):
    # Random node selection logic as in original PHP
    if not contenedores or total_contenedores <= 0:
        return []

    indice_contenedores = random.randint(0, total_contenedores - 1)
    resultado_contenedores = []
    
    for i in range(esferas):
        resultado_contenedores.append(contenedores[indice_contenedores])
        indice_contenedores += 1
        if indice_contenedores >= total_contenedores:
            indice_contenedores = 0
            
    return resultado_contenedores
