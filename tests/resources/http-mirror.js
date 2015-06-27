var port = 1337,
    server,
    clientCallback = function (socket) {
        var headerSent = false,
            timer,
            chunkId = 0,
            showChunks = false;

        socket.on('data', function (data) {
            if ((''+data).indexOf('show_chunks') != -1) {
                showChunks = true;
            }

            if (!headerSent) {
                socket.write('HTTP/1.1 200 OK\r\nContent-Type: text/plain\r\n\r\n');
                headerSent = true;
            }
            else {
                ++chunkId;
                if (showChunks) {
                    socket.write('::chunk_' + chunkId + '::');
                }
            }

            if (timer) {
                clearTimeout(timer);
            }
            socket.write(data);
            timer = setTimeout(function(){socket.end();}, 500);
        });
    };

process.argv.forEach(function (val, index, array) {
    val = val.split('=');

    if (val[0] == 'tls') {
        var options = {
            pfx: require('fs').readFileSync('server.pfx')
        };
        server = require('tls').createServer(options, clientCallback);
    }

    else if (val[0] == 'port') {
        port = val[1];
    }

});

if (!server) {
    server = require('net').createServer(clientCallback);
}

server.listen(port);
