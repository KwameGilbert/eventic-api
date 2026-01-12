
-- USERS
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    phone TEXT,
    password TEXT NOT NULL,
    role TEXT NOT NULL,
    status TEXT DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO users (name, email, phone, password, role) VALUES
    ('Admin User', 'admin@eventic.com', '+233000000000', 'adminpassword', 'admin'),
    ('Organizer One', 'org1@eventic.com', '+233222222222', 'orgpassword', 'organizer'),
    ('Attendee One', 'attendee1@eventic.com', '+233333333333', 'attendeepassword', 'attendee');

-- ORGANIZERS
CREATE TABLE IF NOT EXISTS organizers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    organization_name TEXT,
    bio TEXT,
    profile_image TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO organizers (user_id, organization_name, bio) VALUES
    (2, 'Eventic Org', 'Leading event organizer in Ghana.');

-- EVENTS
CREATE TABLE IF NOT EXISTS events (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    organizer_id INTEGER,
    title TEXT NOT NULL,
    description TEXT,
    venue_name TEXT,
    address TEXT,
    start_time DATETIME,
    end_time DATETIME,
    status TEXT DEFAULT 'published',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO events (organizer_id, title, description, venue_name, address, start_time, end_time) VALUES
    (1, 'Tech Summit 2026', 'Annual tech summit.', 'Accra Conference Centre', 'Accra', '2026-03-15 09:00:00', '2026-03-15 17:00:00'),
    (1, 'Music Awards Night', 'Music awards ceremony.', 'National Theatre', 'Accra', '2026-05-20 19:00:00', '2026-05-20 23:00:00');

-- AWARDS
CREATE TABLE IF NOT EXISTS awards (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    organizer_id INTEGER,
    title TEXT NOT NULL,
    description TEXT,
    ceremony_date DATETIME,
    status TEXT DEFAULT 'published',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO awards (organizer_id, title, description, ceremony_date) VALUES
    (1, 'Best Innovator', 'Award for innovation.', '2026-03-15 16:00:00'),
    (1, 'Best Artist', 'Award for best music artist.', '2026-05-20 22:00:00');

-- AWARD CATEGORIES
CREATE TABLE IF NOT EXISTS award_categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    award_id INTEGER,
    name TEXT NOT NULL,
    description TEXT,
    cost_per_vote REAL DEFAULT 1.00,
    status TEXT DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO award_categories (award_id, name, description) VALUES
    (1, 'Tech Innovation', 'Most innovative tech project.'),
    (2, 'Best Song', 'Best song of the year.');

-- AWARD NOMINEES
CREATE TABLE IF NOT EXISTS award_nominees (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER,
    award_id INTEGER,
    name TEXT NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO award_nominees (category_id, award_id, name, description) VALUES
    (1, 1, 'Kwame Mensah', 'Innovative AI project.'),
    (2, 2, 'Ama Serwaa', 'Hit single.');

-- TICKET TYPES
CREATE TABLE IF NOT EXISTS ticket_types (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    event_id INTEGER,
    name TEXT NOT NULL,
    price REAL DEFAULT 0.00,
    quantity INTEGER DEFAULT 100,
    status TEXT DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO ticket_types (event_id, name, price, quantity) VALUES
    (1, 'VIP', 150.00, 50),
    (1, 'Regular', 50.00, 200),
    (2, 'Standard', 80.00, 100);

-- ORDERS
CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    total_amount REAL DEFAULT 0.00,
    status TEXT DEFAULT 'paid',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO orders (user_id, total_amount, status) VALUES
    (3, 150.00, 'paid'),
    (3, 50.00, 'paid');

-- ORDER ITEMS
CREATE TABLE IF NOT EXISTS order_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER,
    event_id INTEGER,
    ticket_type_id INTEGER,
    quantity INTEGER DEFAULT 1,
    unit_price REAL DEFAULT 0.00,
    total_price REAL DEFAULT 0.00,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO order_items (order_id, event_id, ticket_type_id, quantity, unit_price, total_price) VALUES
    (1, 1, 1, 1, 150.00, 150.00),
    (2, 1, 2, 1, 50.00, 50.00);

-- TICKETS
CREATE TABLE IF NOT EXISTS tickets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER,
    event_id INTEGER,
    ticket_type_id INTEGER,
    ticket_code TEXT NOT NULL,
    status TEXT DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO tickets (order_id, event_id, ticket_type_id, ticket_code, status) VALUES
    (1, 1, 1, 'VIP-001', 'active'),
    (2, 1, 2, 'REG-001', 'active');

-- TRANSACTIONS
CREATE TABLE IF NOT EXISTS transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    reference TEXT NOT NULL,
    transaction_type TEXT NOT NULL,
    order_id INTEGER,
    gross_amount REAL DEFAULT 0.00,
    status TEXT DEFAULT 'completed',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO transactions (reference, transaction_type, order_id, gross_amount, status) VALUES
    ('TXN001', 'ticket_sale', 1, 150.00, 'completed'),
    ('TXN002', 'ticket_sale', 2, 50.00, 'completed');

-- Create events table
CREATE TABLE IF NOT EXISTS events (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT,
    date DATETIME NOT NULL,
    location TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample events
INSERT INTO events (name, description, date, location) VALUES
    ('Tech Conference 2026', 'A conference for tech enthusiasts.', '2026-03-15 09:00:00', 'Accra International Conference Centre'),
    ('Music Awards Night', 'Annual music awards ceremony.', '2026-05-20 19:00:00', 'National Theatre'),
    ('Startup Pitch', 'Pitching event for startups.', '2026-04-10 14:00:00', 'Impact Hub Accra');

-- Create awards table
CREATE TABLE IF NOT EXISTS awards (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    event_id INTEGER,
    name TEXT NOT NULL,
    description TEXT,
    winner TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(event_id) REFERENCES events(id)
);

-- Insert sample awards
INSERT INTO awards (event_id, name, description, winner) VALUES
    (1, 'Best Innovator', 'Awarded to the most innovative participant.', 'Kwame Mensah'),
    (2, 'Best Artist', 'Awarded to the best music artist.', 'Ama Serwaa'),
    (3, 'Best Startup', 'Awarded to the top startup.', 'GreenTech Solutions');

-- Create tickets table
CREATE TABLE IF NOT EXISTS tickets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    event_id INTEGER,
    user_id INTEGER,
    type TEXT,
    price REAL,
    status TEXT DEFAULT 'valid',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(event_id) REFERENCES events(id),
    FOREIGN KEY(user_id) REFERENCES users(id)
);

-- Insert sample tickets
INSERT INTO tickets (event_id, user_id, type, price, status) VALUES
    (1, 2, 'VIP', 150.00, 'valid'),
    (2, 2, 'Regular', 50.00, 'valid'),
    (3, 2, 'Student', 20.00, 'valid');

-- Create orders table
CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    ticket_id INTEGER,
    amount REAL,
    status TEXT DEFAULT 'completed',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(user_id) REFERENCES users(id),
    FOREIGN KEY(ticket_id) REFERENCES tickets(id)
);

-- Insert sample orders
INSERT INTO orders (user_id, ticket_id, amount, status) VALUES
    (2, 1, 150.00, 'completed'),
    (2, 2, 50.00, 'completed'),
    (2, 3, 20.00, 'completed');
