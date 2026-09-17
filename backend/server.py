"""
Reverse proxy (ASGI) that forwards every request to the Laravel application
running internally on 127.0.0.1:9000 (php-fpm + nginx).

The Kubernetes ingress routes /api/* to this process (port 8001). This proxy
therefore serves the REST + device API. HTML routes are served by the frontend
proxy on port 3000 (also forwarding to Laravel:9000).
"""
import httpx
from starlette.applications import Starlette
from starlette.responses import Response, StreamingResponse
from starlette.routing import Route

LARAVEL_TARGET = "http://127.0.0.1:9000"

client = httpx.AsyncClient(base_url=LARAVEL_TARGET, timeout=300.0, follow_redirects=False)

HOP_BY_HOP = {
    "connection", "keep-alive", "proxy-authenticate", "proxy-authorization",
    "te", "trailers", "transfer-encoding", "upgrade", "content-encoding",
    "content-length",
}


async def proxy(request):
    url = request.url.path
    if request.url.query:
        url += "?" + request.url.query

    headers = dict(request.headers)
    headers.pop("host", None)
    headers["X-Forwarded-Proto"] = "https"
    if "host" in request.headers:
        headers["X-Forwarded-Host"] = request.headers["host"]

    body = await request.body()

    rp_req = client.build_request(
        request.method, url, headers=headers, content=body,
    )
    rp = await client.send(rp_req, stream=True)

    resp_headers = [
        (k, v) for k, v in rp.headers.items() if k.lower() not in HOP_BY_HOP
    ]

    async def stream():
        async for chunk in rp.aiter_raw():
            yield chunk
        await rp.aclose()

    return StreamingResponse(
        stream(), status_code=rp.status_code, headers=dict(resp_headers)
    )


app = Starlette(routes=[
    Route("/{path:path}", proxy, methods=["GET", "POST", "PUT", "PATCH", "DELETE", "OPTIONS", "HEAD"]),
])
