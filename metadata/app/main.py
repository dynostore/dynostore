import asyncio
import os
from contextlib import asynccontextmanager
from fastapi import FastAPI
from dotenv import load_dotenv
from database import engine, Base

load_dotenv()

from routers import storage, servers

@asynccontextmanager
async def lifespan(app: FastAPI):
    # Create the schema on start-up.  create_all only adds missing tables, so
    # it is safe against an existing database and needs no separate migration.
    # The database container may still be starting, so retry for a while.
    import models  # noqa: F401  (registers the tables on Base.metadata)
    from sqlalchemy.exc import OperationalError
    attempts = int(os.getenv("METADATA_DB_WAIT_ATTEMPTS", "60"))
    for attempt in range(1, attempts + 1):
        try:
            Base.metadata.create_all(bind=engine)
            break
        except OperationalError:
            if attempt == attempts:
                raise
            await asyncio.sleep(2)
    yield

app = FastAPI(
    title=os.getenv("APP_NAME", "Metadata API"),
    description="Python FastAPI Migration for Metadata Service",
    version="1.0.0",
    lifespan=lifespan
)

app.include_router(storage.router)
app.include_router(servers.router)

@app.get("/health")
def health_check():
    return {"status": "ok"}
