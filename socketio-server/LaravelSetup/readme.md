Import socket cdn : <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.3.2/socket.io.js"></script>

npm install socket.io-client

add a line to resources/js/app.js : import io from 'socket.io-client';

Write this on js file : const socket = io('http://localhost:3000'); and use socket