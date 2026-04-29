const http = require('http');
const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '../dist/fingather/browser');
const port = parseInt(process.env.E2E_PORT, 10) || 4200;

const mime = {
    '.html': 'text/html; charset=utf-8',
    '.js': 'application/javascript; charset=utf-8',
    '.mjs': 'application/javascript; charset=utf-8',
    '.css': 'text/css; charset=utf-8',
    '.json': 'application/json; charset=utf-8',
    '.svg': 'image/svg+xml',
    '.png': 'image/png',
    '.jpg': 'image/jpeg',
    '.jpeg': 'image/jpeg',
    '.gif': 'image/gif',
    '.ico': 'image/x-icon',
    '.webp': 'image/webp',
    '.woff': 'font/woff',
    '.woff2': 'font/woff2',
    '.ttf': 'font/ttf',
    '.otf': 'font/otf',
    '.eot': 'application/vnd.ms-fontobject',
    '.map': 'application/json; charset=utf-8',
    '.txt': 'text/plain; charset=utf-8',
};

const server = http.createServer((req, res) => {
    const urlPath = decodeURIComponent(req.url.split('?')[0]);
    const safePath = path.normalize(urlPath).replace(/^(\.\.[\\/])+/, '');
    let file = path.join(root, safePath);

    fs.stat(file, (err, stat) => {
        if (err || stat.isDirectory()) {
            file = path.join(root, 'index.html');
        }
        fs.readFile(file, (err2, data) => {
            if (err2) {
                res.writeHead(404, { 'Content-Type': 'text/plain' });
                res.end('Not found');
                return;
            }
            const ext = path.extname(file).toLowerCase();
            res.writeHead(200, { 'Content-Type': mime[ext] || 'application/octet-stream' });
            res.end(data);
        });
    });
});

server.listen(port, () => {
    console.log(`Local:   http://localhost:${port}/`);
});
