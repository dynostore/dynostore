import httpx
from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session
from sqlalchemy import func
from sqlalchemy.exc import SQLAlchemyError

from database import get_db
from core.auth import verify_token
import schemas
from models import Server, FilesInServer, File, Chunk

router = APIRouter(
    prefix="/servers",
    tags=["servers"]
)

@router.post("/{tokenuser}", status_code=201, dependencies=[Depends(verify_token)])
async def store_server(tokenuser: str, payload: schemas.ServerCreate, db: Session = Depends(get_db)):
    server = Server(
        url=payload.url,
        memory=payload.memory,
        storage=payload.storage,
        up=payload.up
    )
    db.add(server)
    try:
        db.commit()
    except SQLAlchemyError as e:
        db.rollback()
        raise HTTPException(status_code=400, detail="Could not create server record.")
    
    return {"message": "Server record created"}

@router.get("/{tokenuser}", dependencies=[Depends(verify_token)])
async def index_servers(tokenuser: str, db: Session = Depends(get_db)):
    servers = db.query(Server).all()
    result = []
    
    for server in servers:
        used_fis = db.query(func.sum(File.size)).join(
            FilesInServer, FilesInServer.keyfile == File.keyfile
        ).filter(FilesInServer.server_id == server.id).scalar() or 0.0
        
        used_chunks = db.query(func.sum(Chunk.size)).filter(
            Chunk.server_id == server.id
        ).scalar() or 0.0
        
        utilization = float(used_fis) + float(used_chunks)
        
        n_files = db.query(func.count(FilesInServer.id)).filter(
            FilesInServer.server_id == server.id
        ).scalar() or 0
        
        result.append({
            "id": server.id,
            "url": server.url,
            "memory": server.memory,
            "storage": server.storage,
            "up": server.up,
            "utilization": utilization,
            "n_files": n_files
        })
        
    return result

@router.get("/{tokenuser}/statistics", dependencies=[Depends(verify_token)])
async def statistics_servers(tokenuser: str, db: Session = Depends(get_db)):
    # Very similar to index_servers, just returning array direct as per PHP controller
    return await index_servers(tokenuser, db)

@router.get("/delete/{tokenuser}", dependencies=[Depends(verify_token)])
async def delete_all_servers(tokenuser: str, db: Session = Depends(get_db)):
    # Warning: delete everything!
    try:
        db.query(FilesInServer).delete()
        db.query(Chunk).delete()
        db.query(Server).delete()
        db.commit()
    except SQLAlchemyError:
        db.rollback()
        raise HTTPException(status_code=500, detail="Database delete error")
        
    return "Servers deleted"

# Route independent of auth prefix (mapped from web/api routes logic)
@router.get("/clean/all", tags=["servers"])
async def clean_servers(db: Session = Depends(get_db)):
    # The route in laravel was GET 'clean'
    servers = db.query(Server).all()
    result = []
    
    async with httpx.AsyncClient() as client:
        for server in servers:
            url = f"{server.url}/clean"
            try:
                response = await client.get(url, timeout=5.0)
                ok = (response.status_code == 200)
            except httpx.RequestError:
                ok = False
                
            result.append({
                "id": server.id,
                "url": server.url,
                "memory": server.memory,
                "storage": server.storage,
                "up": server.up,
                "clean": "success" if ok else "failed"
            })
            
            try:
                db.query(Chunk).filter(Chunk.server_id == server.id).delete()
            except SQLAlchemyError:
                pass

    try:
        db.query(FilesInServer).delete()
        db.commit()
    except SQLAlchemyError:
        db.rollback()

    return result
