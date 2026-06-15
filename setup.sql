-- CyberShield Portal — Database Setup
-- Import this file via phpMyAdmin into u336068262_cybershield
-- (Database is already created via hPanel — do NOT run CREATE DATABASE)

CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    department VARCHAR(100) DEFAULT 'General',
    role ENUM('student','admin') DEFAULT 'student',
    avatar_initials VARCHAR(3) DEFAULT 'U',
    total_xp INT DEFAULT 0,
    level INT DEFAULT 1,
    streak_days INT DEFAULT 0,
    last_active DATE,
    awareness_completed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS games (
    id INT PRIMARY KEY AUTO_INCREMENT,
    slug VARCHAR(50) NOT NULL UNIQUE,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    category VARCHAR(50),
    difficulty ENUM('beginner','easy','medium','hard','expert') DEFAULT 'medium',
    max_score INT DEFAULT 100,
    xp_reward INT DEFAULT 200,
    estimated_mins INT DEFAULT 10,
    requires_awareness BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS game_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    game_id INT NOT NULL,
    score INT DEFAULT 0,
    max_score INT DEFAULT 100,
    percentage DECIMAL(5,2) DEFAULT 0,
    xp_earned INT DEFAULT 0,
    time_taken INT DEFAULT 0,
    hints_used INT DEFAULT 0,
    answers JSON,
    completed BOOLEAN DEFAULT FALSE,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS escape_room_stages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT NOT NULL,
    user_id INT NOT NULL,
    stage_number INT NOT NULL,
    score INT DEFAULT 0,
    hints_used INT DEFAULT 0,
    time_taken INT DEFAULT 0,
    completed BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (session_id) REFERENCES game_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS achievements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    badge_slug VARCHAR(50) NOT NULL,
    badge_name VARCHAR(100),
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_achievement (user_id, badge_slug),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info','success','warning','danger') DEFAULT 'info',
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Seed games
INSERT INTO games (slug, title, description, category, difficulty, max_score, xp_reward, estimated_mins, requires_awareness, sort_order) VALUES
('awareness', 'Cyber Hygiene Basics', 'Mandatory intro: learn the top threats facing employees and the fundamental habits that keep you and your organisation safe.', 'Foundation', 'beginner', 100, 100, 15, FALSE, 1),
('phishing-email', 'Phishing Email Detective', 'A realistic inbox with genuine-looking phishing attempts. Identify every red flag before the attacker wins.', 'Email Security', 'easy', 250, 200, 12, TRUE, 2),
('phone-scam', 'The Suspicious Call', 'Receive and respond to a live-simulated phone call from an attacker impersonating IT Support. Audio plays automatically.', 'Social Engineering', 'medium', 200, 250, 10, TRUE, 3),
('escape-room', 'Cyber Escape Room: The Breach', 'It\'s Monday morning. The network was compromised over the weekend. Investigate, contain, and report the incident before time runs out.', 'Escape Room', 'hard', 1000, 500, 30, TRUE, 4),
('network-watchdog', 'Network Watchdog', 'Analyse a live stream of network packets and flag every suspicious or malicious connection before data is exfiltrated.', 'Network Security', 'medium', 300, 250, 12, TRUE, 5),
('ransomware-response', 'Ransomware Response', 'Files are being encrypted right now. Race against the clock to contain the spread and initiate recovery.', 'Incident Response', 'hard', 300, 400, 15, TRUE, 6);

-- Default admin user (password: Admin@123)
INSERT INTO users (username, email, password, full_name, department, role, avatar_initials)
VALUES ('admin', 'admin@cybershield.local', '$2b$12$y0QhfKqLFXuL3/omQ3H9Vuf3f6u9jGp7U.405uRn/JnqgSrL/BrLG', 'Administrator', 'IT Security', 'admin', 'AD');
