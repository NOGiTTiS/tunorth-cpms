CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) UNIQUE NULL, -- รหัสนักเรียน (ถ้าเป็นครูจะว่างไว้)
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('ADMIN', 'TEACHER', 'STUDENT') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE project_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_name_th VARCHAR(255) NOT NULL,
    project_name_en VARCHAR(255) NOT NULL,
    advisor_id INT, -- FK ไปยัง users (role TEACHER)
    status ENUM('PENDING', 'APPROVED', 'COMPLETED') DEFAULT 'PENDING',
    FOREIGN KEY (advisor_id) REFERENCES users(id)
);

CREATE TABLE group_members (
    group_id INT,
    user_id INT,
    PRIMARY KEY (group_id, user_id),
    FOREIGN KEY (group_id) REFERENCES project_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE project_steps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    step_name VARCHAR(100) NOT NULL, -- เช่น บทที่ 1, บทที่ 2
    step_order INT NOT NULL -- ลำดับการส่ง
);

CREATE TABLE submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT,
    step_id INT,
    file_path VARCHAR(255),
    comment TEXT,
    status ENUM('PENDING', 'REJECTED', 'APPROVED') DEFAULT 'PENDING',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES project_groups(id),
    FOREIGN KEY (step_id) REFERENCES project_steps(id)
);