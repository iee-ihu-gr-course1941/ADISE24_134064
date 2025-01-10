<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/> 
    <title>Blokus Game API Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        pre {
            background: #f2f2f2;
            padding: 10px;
            border-radius: 5px;
            overflow: auto;
        }
        button {
            margin: 5px 0;
        }
        .section {
            margin-bottom: 20px;
        }
        .action-block {
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .action-block input {
            margin: 5px 0;
            display: block;
        }
    </style>
    <script>
        async function makeRequest(url, method = 'GET', data = null, authToken = null) {
            const headers = {'Content-Type': 'application/json'};
            if (authToken) {
                headers['Authorization'] = authToken;
            }

            const options = {
                method: method,
                headers: headers
            };
            if (data) {
                options.body = JSON.stringify(data);
            }

            const response = await fetch(url, options);
            const result = await response.json();
            console.log(result);
            document.getElementById('response').innerText = JSON.stringify(result, null, 2);
        }

        function registerUser() {
            const username = document.getElementById('register_username').value.trim();
            const password = document.getElementById('register_password').value.trim();
            const data = { username: username, password: password };
            makeRequest('api/auth/register.php', 'POST', data);
        }

        function loginUser() {
            const username = document.getElementById('login_username').value.trim();
            const password = document.getElementById('login_password').value.trim();
            const data = { username: username, password: password };
            makeRequest('api/auth/login.php', 'POST', data);
        }

        function createGame() {
            const authToken = document.getElementById('auth_token').value.trim();
            const playersInput = document.getElementById('create_game_players').value.trim();
            const players = playersInput.split(',').map(p => p.trim()).filter(p => p.length > 0);
            const data = { players: players };
            makeRequest('api/create_game.php', 'POST', data, authToken);
        }

        function joinGame() {
            const authToken = document.getElementById('auth_token').value.trim();
            const gameId = document.getElementById('join_game_id').value.trim();
            const data = { game_id: parseInt(gameId, 10) };
            makeRequest('api/join_game.php', 'POST', data, authToken);
        }

        function makeMove() {
            const authToken = document.getElementById('auth_token').value.trim();
            const gameId = document.getElementById('move_game_id').value.trim();
            const piece = document.getElementById('move_piece').value.trim();
            const row = document.getElementById('move_row').value.trim();
            const col = document.getElementById('move_col').value.trim();

            const data = {
                game_id: parseInt(gameId, 10),
                piece: piece,
                row: parseInt(row, 10),
                col: parseInt(col, 10)
            };
            makeRequest('api/make_move.php', 'POST', data, authToken);
        }

        function getGameState() {
            const authToken = document.getElementById('auth_token').value.trim();
            const gameId = document.getElementById('state_game_id').value.trim();
            makeRequest('api/game_state.php?game_id=' + encodeURIComponent(gameId), 'GET', null, authToken);
        }
    </script>
</head>
<body>
    <h1>Blokus Game API Test</h1>

    <div class="section">
        <h2>Instructions for curl calls</h2>
        <p>local server http://localhost/blokus-game/public</p>

        <h3>Examples:</h3>
        <h4>1. Register a user:</h4>
        <h5>Player 1</h5>
        <pre>curl -X POST http://localhost/blokus-game/public/api/auth/register.php -H "Content-Type: application/json" -d "{\"username\":\"giorgos\",\"password\":\"giorgos\"}"</pre>
        <h5>Player 2</h5>
        <pre>curl -X POST http://localhost/blokus-game/public/api/auth/register.php -H "Content-Type: application/json" -d "{\"username\":\"kitsos\",\"password\":\"kitsos\"}"</pre>

        <h4>2. Login user:</h4>
        <h5>Player 1</h5>
        <pre>curl -X POST http://localhost/blokus-game/public/api/auth/login.php -H "Content-Type: application/json" -d "{\"username\":\"giorgos\",\"password\":\"giorgos\"}"</pre>
        <h5>Player 2</h5>
        <pre>curl -X POST http://localhost/blokus-game/public/api/auth/login.php -H "Content-Type: application/json" -d "{\"username\":\"kitsos\",\"password\":\"kitsos\"}"</pre>
        <p>Copy the <code>auth_token</code> from the response for subsequent requests.</p>

        <h4>3. Create a game:</h4>
        <pre>curl -X POST http://localhost/blokus-game/public/api/create_game.php -H "Content-Type: application/json" -H "Authorization: YOUR_AUTH_TOKEN_HERE" -d "{\"players\":[\"giorgos\",\"kitsos\"]}"</pre>
        <p>Creates the first player only (giorgos)</p>

        <h4>4. Join a game:</h4>
        <pre>curl -X POST http://localhost/blokus-game/public/api/join_game.php -H "Content-Type: application/json" -H "Authorization: YOUR_AUTH_TOKEN_HERE" -d "{\"game_id\":0}"</pre>

        <h4>5. Make a move:</h4>
        <pre>curl -X POST http://localhost/blokus-game/public/api/make_move.php -H "Content-Type: application/json" -H "Authorization: YOUR_AUTH_TOKEN_HERE" -d "{\"game_id\":0,\"piece\":\"square\",\"row\":0,\"col\":0}"</pre>

        <h4>6. Get game state:</h4>
        <pre>curl -X GET "http://localhost/blokus-game/public/api/game_state.php?game_id=0" -H "Authorization: YOUR_AUTH_TOKEN_HERE"</pre>
    </div>

    <div class="section">
        <h2>UI Testing</h2>
        <p>Register -> Login a user to get an authorization token, then paste the token into the box below for other actions.</p>
        <p>Token: <input type="text" id="auth_token" placeholder="Token" style="width:300px;" /> </p>

        <div class="action-block">
            <h3>Register User</h3>
            <input type="text" id="register_username" placeholder="Username" />
            <input type="text" id="register_password" placeholder="Password" />
            <button onclick="registerUser()">Register User</button>
        </div>

        <div class="action-block">
            <h3>Login User</h3>
            <input type="text" id="login_username" placeholder="Username" />
            <input type="text" id="login_password" placeholder="Password" />
            <button onclick="loginUser()">Login User</button>
        </div>

        <div class="action-block">
            <h3>Create Game</h3>
            <p>Enter players name</p>
            <input type="text" id="create_game_players" placeholder="Username" />
            <button onclick="createGame()">Create Game</button>
        </div>

        <div class="action-block">
            <h3>Join Game</h3>
            <input type="number" id="join_game_id" placeholder="Game ID" />
            <button onclick="joinGame()">Join Game</button>
        </div>

        <div class="action-block">
            <h3>Make Move</h3>
            <input type="number" id="move_game_id" placeholder="Game ID" />
            <input type="text" id="move_piece" placeholder="Piece Name (e.g. square)" />
            <input type="number" id="move_row" placeholder="Row" />
            <input type="number" id="move_col" placeholder="Column" />
            <button onclick="makeMove()">Make Move</button>
        </div>

        <div class="action-block">
            <h3>Get Game State</h3>
            <input type="number" id="state_game_id" placeholder="Game ID" />
            <button onclick="getGameState()">Get Game State</button>
        </div>

    </div>

    <div class="section">
        <h2>Response:</h2>
        <pre id="response" style="border:1px solid #ccc; padding:10px;"></pre>
    </div>
</body>
</html>
