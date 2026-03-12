CREATE DATABASE IF NOT EXISTS event_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE event_management;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    venue VARCHAR(180) NOT NULL,
    event_date DATE NOT NULL,
    registration_open_at DATETIME NOT NULL,
    registration_close_at DATETIME NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_events_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_events_date (event_date),
    INDEX idx_events_open_at (registration_open_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS registrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(160) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    college VARCHAR(180) NOT NULL,
    submitted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_registrations_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    CONSTRAINT fk_registrations_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT uq_registrations_event_user UNIQUE (event_id, user_id),
    INDEX idx_registrations_event (event_id),
    INDEX idx_registrations_email (email)
) ENGINE=InnoDB;

INSERT INTO users (name, email, password_hash, role)
SELECT 'Default Admin', 'admin@example.com', '$2y$10$CucKZM0EgHZpw2HyWDDOvOd2LrVyO1kq9HXGZO1HzJw9oZaHmYrFC', 'admin'
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE email = 'admin@example.com'
);

INSERT INTO users (name, email, password_hash, role)
SELECT 'Student User', 'student@example.com', '$2y$10$5a/fSQB38cw9ZeAefFJHl.gxEj6GMUE3JfgfEpUTYn.kYZCkhvWN.', 'user'
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE email = 'student@example.com'
);

INSERT INTO events (
    title,
    description,
    venue,
    event_date,
    registration_open_at,
    registration_close_at,
    is_active,
    created_by
)
SELECT
    'Sarswoti puja',
    'Traditional celebration event for students.',
    'Main Courtyard',
    DATE_ADD(CURDATE(), INTERVAL 7 DAY),
    DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 DAY),
    DATE_ADD(UTC_TIMESTAMP(), INTERVAL 14 DAY),
    1,
    admin_user.id
FROM users admin_user
WHERE admin_user.email = 'admin@example.com'
  AND NOT EXISTS (
      SELECT 1 FROM events WHERE title = 'Sarswoti puja'
  );

INSERT INTO events (
    title,
    description,
    venue,
    event_date,
    registration_open_at,
    registration_close_at,
    is_active,
    created_by
)
SELECT
    'Annual Tech Fest',
    'Inter-college event for innovation and competitions.',
    'Auditorium Hall',
    DATE_ADD(CURDATE(), INTERVAL 20 DAY),
    DATE_ADD(UTC_TIMESTAMP(), INTERVAL 10 DAY),
    DATE_ADD(UTC_TIMESTAMP(), INTERVAL 25 DAY),
    1,
    admin_user.id
FROM users admin_user
WHERE admin_user.email = 'admin@example.com'
  AND NOT EXISTS (
      SELECT 1 FROM events WHERE title = 'Annual Tech Fest'
  );

INSERT INTO registrations (event_id, user_id, full_name, email, phone, college)
SELECT
    e.id,
    u.id,
    u.name,
    u.email,
    '+9779812345678',
    'Hetauda School of Management'
FROM events e
INNER JOIN users u ON u.email = 'student@example.com'
WHERE e.title = 'Sarswoti puja'
  AND NOT EXISTS (
      SELECT 1 FROM registrations r
      WHERE r.event_id = e.id AND r.user_id = u.id
  );
