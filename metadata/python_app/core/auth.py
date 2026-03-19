import os
import httpx
from fastapi import HTTPException, Path, Depends
import logging

logger = logging.getLogger(__name__)

AUTH_HOST = os.getenv("AUTH_HOST", "auth_host_placeholder")

async def verify_token(tokenuser: str = Path(...)):
    """
    FastAPI dependency that mimics the Laravel EnsureTokenIsValid middleware.
    It makes an HRTP request to the AUTH_HOST to validate the tokenuser.
    """
    url = f"http://{AUTH_HOST}/auth/v1/user?tokenuser={tokenuser}"
    
    try:
        async with httpx.AsyncClient() as client:
            response = await client.get(url, timeout=5.0)
            if response.status_code == 404:
                raise HTTPException(status_code=401, detail="Unauthorized")
    except httpx.RequestError as e:
        logger.warning(f"Failed to connect to AUTH_HOST: {e}")
        # Depending on requirements, we can fail open or closed. Let's fail closed.
        raise HTTPException(status_code=401, detail="Unauthorized")

    return tokenuser
