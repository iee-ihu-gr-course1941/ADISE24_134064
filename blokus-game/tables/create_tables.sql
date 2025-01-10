
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) DEFAULT NULL, -- NULL for passwordless accounts
    auth_token VARCHAR(255) DEFAULT NULL, -- Token for session management
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    status ENUM('pending', 'active', 'finished') DEFAULT 'pending',
    turn INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    game_id INT NOT NULL,
    color ENUM('blue', 'yellow', 'red', 'green'),
    score INT DEFAULT 0,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
);


CREATE TABLE board (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    row INT NOT NULL,
    col INT NOT NULL,
    color ENUM('blue', 'yellow', 'red', 'green') DEFAULT NULL,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
);


CREATE TABLE moves (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    player_id INT NOT NULL,
    piece VARCHAR(50) NOT NULL,
    position VARCHAR(100) NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE
);

-- update players table to reference users
ALTER TABLE players ADD COLUMN user_id INT;
ALTER TABLE players ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- update games table to also store turn_color
ALTER TABLE games ADD COLUMN turn_color VARCHAR(50) NOT NULL;