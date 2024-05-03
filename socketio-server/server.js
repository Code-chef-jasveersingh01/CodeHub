const http = require('http');
const express = require('express');
const { Server } = require('socket.io');
const cors = require('cors');

const app = express();
const server = http.createServer(app);
const io = new Server(server, { cors: { origin: '*' } });


// const corsOptions = {
//     origin: 'http://127.0.0.1:8000', 
//     methods: ['GET', 'POST'] 
// };

// app.use(cors(corsOptions));

app.use(cors()); 

app.get('/', function (req, res) {
    res.send('Hello World')
})

io.on('connection', (socket) => {
    console.log('a user connected');

    socket.on('dataFromClient', (data) => {
        console.log('Data received from client:', data);
        socket.emit('dataFromServer', {
            message: 'Hello from the server!',
            timestamp: new Date().toISOString()
        });
    });

    socket.on('disconnect', () => {
        console.log('user disconnected');
    });
});

server.listen(3000, () => {
    console.log('listening on *:3000');
});
