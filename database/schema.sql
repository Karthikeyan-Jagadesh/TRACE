-- TRACE — Lost & Found Intelligence Network
-- Normalized Relational Schema for MySQL / MariaDB

DROP TABLE IF EXISTS user_preferences;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS report_flags;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS claim_status_history;
DROP TABLE IF EXISTS claims;
DROP TABLE IF EXISTS match_suggestions;
DROP TABLE IF EXISTS report_private_details;
DROP TABLE IF EXISTS report_images;
DROP TABLE IF EXISTS reports;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS organizations;

-- 1. Organizations
CREATE TABLE organizations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    organization_id INT NULL,
    status ENUM('active', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL,
    INDEX idx_user_email (email),
    INDEX idx_user_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Reports
CREATE TABLE reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(30) NOT NULL UNIQUE,
    creator_id INT NOT NULL,
    report_type ENUM('lost', 'found') NOT NULL,
    item_title VARCHAR(200) NOT NULL,
    category VARCHAR(50) NOT NULL,
    brand VARCHAR(100) NULL,
    color VARCHAR(50) NULL,
    description TEXT NOT NULL,
    general_location VARCHAR(255) NOT NULL,
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    event_date DATE NOT NULL,
    event_time TIME NULL,
    contact_preference VARCHAR(50) DEFAULT 'in_app',
    reward_amount DECIMAL(10, 2) NULL,
    status ENUM('active', 'under_review', 'claimed', 'recovered', 'expired', 'closed') DEFAULT 'active',
    expiration_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_report_type (report_type),
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_event_date (event_date),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Report Images
CREATE TABLE report_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
    INDEX idx_report_images (report_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Private Identifying & Custody Details (Finder Secret Verification)
CREATE TABLE report_private_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL UNIQUE,
    custody_status VARCHAR(150) NULL,
    safe_collection_instructions TEXT NULL,
    secret_question VARCHAR(255) NULL,
    secret_answer TEXT NULL,
    secret_notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Smart Match Suggestions
CREATE TABLE match_suggestions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lost_report_id INT NOT NULL,
    found_report_id INT NOT NULL,
    score INT NOT NULL,
    confidence_band ENUM('strong', 'possible', 'weak') NOT NULL,
    explanation TEXT NOT NULL,
    status ENUM('unreviewed', 'relevant', 'dismissed', 'claimed') DEFAULT 'unreviewed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_lost_found (lost_report_id, found_report_id),
    FOREIGN KEY (lost_report_id) REFERENCES reports(id) ON DELETE CASCADE,
    FOREIGN KEY (found_report_id) REFERENCES reports(id) ON DELETE CASCADE,
    INDEX idx_match_score (score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Claims
CREATE TABLE claims (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    claimant_id INT NOT NULL,
    status ENUM('submitted', 'under_review', 'info_requested', 'approved', 'rejected', 'cancelled', 'completed') DEFAULT 'submitted',
    claim_description TEXT NOT NULL,
    ownership_evidence TEXT NOT NULL,
    verification_notes TEXT NULL,
    return_instructions TEXT NULL,
    reviewer_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
    FOREIGN KEY (claimant_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_claim_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Claim Status Transition History
CREATE TABLE claim_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    claim_id INT NOT NULL,
    old_status VARCHAR(50) NULL,
    new_status VARCHAR(50) NOT NULL,
    note TEXT NULL,
    changed_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (claim_id) REFERENCES claims(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_type VARCHAR(50) NOT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    link_url VARCHAR(255) NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notif_user_read (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Report Flags (Abuse / Fraud Moderation)
CREATE TABLE report_flags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    reporter_id INT NOT NULL,
    reason VARCHAR(100) NOT NULL,
    details TEXT NULL,
    status ENUM('pending', 'reviewed', 'dismissed', 'action_taken') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Audit Logs
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT NULL,
    details TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_audit_entity (entity_type, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. User Preferences
CREATE TABLE user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    theme ENUM('dark', 'light') DEFAULT 'dark',
    email_notifications TINYINT(1) DEFAULT 1,
    in_app_notifications TINYINT(1) DEFAULT 1,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
