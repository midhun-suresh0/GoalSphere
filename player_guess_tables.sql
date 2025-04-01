-- Create players table
CREATE TABLE IF NOT EXISTS guess_players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    team VARCHAR(100) NOT NULL,
    position VARCHAR(50) NOT NULL,
    nationality VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    difficulty ENUM('easy', 'medium', 'hard') NOT NULL DEFAULT 'medium',
    image_url VARCHAR(255) NOT NULL,
    hint1 TEXT,
    hint2 TEXT,
    hint3 TEXT,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create guess_results table to track user guesses
CREATE TABLE IF NOT EXISTS guess_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    player_id INT NOT NULL,
    attempts INT NOT NULL DEFAULT 0,
    correct TINYINT(1) NOT NULL DEFAULT 0,
    time_taken INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (player_id) REFERENCES guess_players(id)
); 