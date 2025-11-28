-- Users table
CREATE TABLE IF NOT EXISTS users (
  id VARHCAR PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  phone VARCHAR(50),
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','organizer','attendee','pos','scanner') DEFAULT 'attendee',
  email_verified BOOLEAN DEFAULT FALSE,
  status ENUM('active','suspended') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_users_role ON users(role);

-- Organizers profile (optional separate table)
CREATE TABLE IF NOT EXISTS organizers (
  id VARCHAR PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  organization_name VARCHAR(255),
  bio TEXT,
  profile_image VARCHAR(255),
  social_facebook VARCHAR(255),
  social_instagram VARCHAR(255),
  social_twitter VARCHAR(255),
  verified BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Events
-- ------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS events (
  id VARCHAR PRIMARY KEY,
  organizer_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL UNIQUE,
  description LONGTEXT,
  category_id VARCHAR UNSIGNED,
  venue_name VARCHAR(255),
  address VARCHAR(255),
  map_url VARCHAR(255),
  banner_image VARCHAR(255),
  start_time DATETIME NOT NULL,
  end_time DATETIME NOT NULL,
  status ENUM('draft','pending','published','cancelled') DEFAULT 'draft',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Event images / gallery
CREATE TABLE IF NOT EXISTS event_images (
  id VARCHAR PRIMARY KEY,
  event_id VARCHAR NOT NULL,
  image_path VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tickets (ticket types)
CREATE TABLE IF NOT EXISTS tickets (
  id VARCHAR PRIMARY KEY,
  event_id VARCHAR NOT NULL,
  name VARCHAR(255) NOT NULL,
  price DECIMAL(10,2) DEFAULT 0.00,
  quantity INT NOT NULL DEFAULT 0,
  remaining INT NOT NULL DEFAULT 0,
  fixed_fee DECIMAL(10,2) DEFAULT 0.00,
  dynamic_fee DECIMAL(5,2) DEFAULT 0.00,
  sale_start DATETIME NULL,
  sale_end DATETIME NULL,
  max_per_user INT DEFAULT 10,
  ticket_image VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexes useful for searching available tickets
CREATE INDEX idx_tickets_event_id ON tickets(event_id);
CREATE INDEX idx_tickets_sale_dates ON tickets(sale_start, sale_end);

-- Orders (purchase)
CREATE TABLE IF NOT EXISTS orders (
  id VARCHAR PRIMARY KEY,
  attendee_id VARCHAR NOT NULL,
  event_id VARCHAR NOT NULL,
  payment_ref VARCHAR(255),
  payment_method VARCHAR(50),
  total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  is_   pos TINYINT(1) DEFAULT 0,
  status ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (attendee_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_orders_attendee ON orders(attendee_id);
CREATE INDEX idx_orders_status ON orders(status);

-- Order items (each purchased ticket instance grouped under orders)
CREATE TABLE IF NOT EXISTS order_items (
  id VARCHAR PRIMARY KEY,
  order_id VARCHAR NOT NULL,
  ticket_id VARCHAR NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  qr_code VARCHAR(255) UNIQUE,
  ticket_uid VARCHAR(255) UNIQUE,
  used TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_order_items_order ON order_items(order_id);
CREATE INDEX idx_order_items_ticket_uid ON order_items(ticket_uid);

-- POS accounts (point of sale)
CREATE TABLE IF NOT EXISTS pos_accounts (
  id VARCHAR PRIMARY KEY,
  organizer_id VARCHAR NOT NULL,
  username VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  event_ids JSON,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Scanner accounts
CREATE TABLE IF NOT EXISTS scanner_accounts (
  id VARCHAR PRIMARY KEY,
  organizer_id VARCHAR NOT NULL,
  username VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  event_ids JSON,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------
-- Attendances / Scan logs
-- ------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS attendances (
  id VARCHAR PRIMARY KEY,
  order_item_id VARCHAR NOT NULL,
  scanner_id VARCHAR NOT NULL,
  event_id VARCHAR NOT NULL,
  scanned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status ENUM('valid','used','invalid') DEFAULT 'valid',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE CASCADE,
  FOREIGN KEY (scanner_id) REFERENCES scanner_accounts(id) ON DELETE CASCADE,
  FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_attendances_event ON attendances(event_id);

-- ------------------------------------------------------------------
-- Payout requests
-- ------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payout_requests (
  id VARCHAR PRIMARY KEY,
  organizer_id VARCHAR NOT NULL,
  requested_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  status ENUM('pending','approved','rejected') DEFAULT 'pending',
  admin_note TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------
-- Settings table (key-value)
-- ------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
  id VARCHAR PRIMARY KEY,
  setting_key VARCHAR(255) NOT NULL UNIQUE,
  setting_value TEXT,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------
-- Blog & Help Center (optional)
-- ------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS blog_posts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255),
  slug VARCHAR(255) UNIQUE,
  content LONGTEXT,
  thumbnail VARCHAR(255),
  author_id BIGINT UNSIGNED,
  status ENUM('draft','published') DEFAULT 'draft',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS help_articles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255),
  content LONGTEXT,
  category VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------
-- Favorites & Followers
-- ------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS favorites (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  attendee_id BIGINT UNSIGNED NOT NULL,
  event_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_favorites_attendee_event (attendee_id, event_id),
  FOREIGN KEY (attendee_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS organizer_followers (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  attendee_id BIGINT UNSIGNED NOT NULL,
  organizer_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_followers_attendee_organizer (attendee_id, organizer_id),
  FOREIGN KEY (attendee_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------
-- Event reviews
-- ------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS event_reviews (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  attendee_id BIGINT UNSIGNED NOT NULL,
  event_id BIGINT UNSIGNED NOT NULL,
  rating INT NOT NULL,
  review TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_reviews_attendee_event (attendee_id, event_id),
  FOREIGN KEY (attendee_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
  CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------
-- Useful triggers / example: decrement ticket remaining on order_item insert
-- Note: for high-throughput systems it's better to manage availability in application logic
-- ------------------------------------------------------------------
DELIMITER $$
CREATE TRIGGER trg_order_items_after_insert
AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
  DECLARE current_remaining INT;
  SELECT remaining INTO current_remaining FROM tickets WHERE id = NEW.ticket_id FOR UPDATE;
  IF current_remaining IS NOT NULL THEN
    UPDATE tickets SET remaining = GREATEST(0, current_remaining - NEW.quantity) WHERE id = NEW.ticket_id;
  END IF;
END$$

CREATE TRIGGER trg_order_items_after_delete
AFTER DELETE ON order_items
FOR EACH ROW
BEGIN
  -- restore remaining when an order item is deleted (e.g., refund)
  UPDATE tickets SET remaining = remaining + OLD.quantity WHERE id = OLD.ticket_id;
END$$
DELIMITER ;

-- ------------------------------------------------------------------
-- Example seed for system settings
-- ------------------------------------------------------------------
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
('platform_name', 'Eventic'),
('primary_color', '#FF6A00'),
('secondary_color', '#FFFFFF'),
('default_currency', 'USD');

-- ------------------------------------------------------------------
-- End of schema
-- ------------------------------------------------------------------
