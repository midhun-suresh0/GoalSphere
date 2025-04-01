-- Create quiz_categories table
CREATE TABLE IF NOT EXISTS quiz_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create quiz_questions table
CREATE TABLE IF NOT EXISTS quiz_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    question_text TEXT NOT NULL,
    difficulty ENUM('easy', 'medium', 'hard') NOT NULL DEFAULT 'medium',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES quiz_categories(id)
);

-- Create quiz_answers table
CREATE TABLE IF NOT EXISTS quiz_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    answer_text TEXT NOT NULL,
    is_correct TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE
);

-- Insert default categories
INSERT INTO quiz_categories (name, description) VALUES
('General Football', 'General knowledge about football'),
('Champions League', 'Questions about UEFA Champions League'),
('World Cup', 'Questions about FIFA World Cup'),
('Football History', 'Historical football facts and figures'),
('Player Stats', 'Questions about player statistics and records'),
('Football Rules', 'Questions about the rules of the game'); 