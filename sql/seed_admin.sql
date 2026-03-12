USE event_management;

INSERT INTO users (name, email, password_hash, role)
SELECT 'Default Admin', 'admin@example.com', '$2y$10$CucKZM0EgHZpw2HyWDDOvOd2LrVyO1kq9HXGZO1HzJw9oZaHmYrFC', 'admin'
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE email = 'admin@example.com'
);
