CREATE DATABASE goalsphere;
USE goalsphere;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    email VARCHAR(100),
    password VARCHAR(255),
    terms_accepted TINYINT(1),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
); 