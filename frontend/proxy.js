// Reverse proxy: forwards ALL requests to the Laravel app running on 127.0.0.1:9000.
// Used by the "frontend" supervisor program (port 3000) to serve the Blade HTML UI.
const httpProxy = require('http-proxy');
const http = require('http');

const TARGET = 'http://127.0.0.1:9000';
const PORT = process.env.PORT || 3000;
const HOST = process.env.HOST || '0.0.0.0';

const proxy = httpProxy.createProxyServer({
  target: TARGET,
  changeOrigin: false,
  ws: true,
  xfwd: true,
  proxyTimeout: 300000,
  timeout: 300000,
});

proxy.on('error', (err, req, res) => {
  console.error('[proxy error]', err.message);
  if (res && !res.headersSent && res.writeHead) {
    res.writeHead(502, { 'Content-Type': 'text/plain' });
    res.end('Backend unavailable. Retrying...');
  }
});

// Preserve original Host + forwarded proto so Laravel builds correct absolute URLs.
proxy.on('proxyReq', (proxyReq, req) => {
  proxyReq.setHeader('X-Forwarded-Proto', 'https');
  if (req.headers.host) proxyReq.setHeader('X-Forwarded-Host', req.headers.host);
});

const server = http.createServer((req, res) => proxy.web(req, res));
server.on('upgrade', (req, socket, head) => proxy.ws(req, socket, head));
server.listen(PORT, HOST, () => {
  console.log(`AutoAds HTML proxy listening on ${HOST}:${PORT} -> ${TARGET}`);
});
