CREATE DATABASE IF NOT EXISTS gigabyte
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE gigabyte;

CREATE TABLE IF NOT EXISTS leads (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    created_at      DATETIME     NOT NULL,
    name            VARCHAR(60)  NOT NULL,
    phone           VARCHAR(20)  NOT NULL,
    city            ENUM('yakutsk','vladivostok') NOT NULL DEFAULT 'yakutsk',
    device          VARCHAR(32)  NOT NULL DEFAULT 'other',
    visit           ENUM('office','onsite')        NOT NULL DEFAULT 'office',
    address         VARCHAR(200) NOT NULL DEFAULT '',
    fault           TEXT         NULL,
    consent         TINYINT(1)   NOT NULL DEFAULT 1,
    consent_version VARCHAR(64)  NOT NULL DEFAULT 'privacy-v1 / terms-v1',
    ip              VARCHAR(45)  NOT NULL DEFAULT '',
    ua              VARCHAR(300) NOT NULL DEFAULT '',
    page            VARCHAR(300) NOT NULL DEFAULT '',
    utm             VARCHAR(300) NOT NULL DEFAULT '',
    status          ENUM('new','in_progress','done','spam') NOT NULL DEFAULT 'new',
    INDEX idx_created (created_at),
    INDEX idx_status  (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admins (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    login         VARCHAR(40)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at    DATETIME     NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO admins (login, password_hash, created_at) VALUES (
    'admin',
    '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfgqw2u9VQ5q5f3cH9z2u2e7pWq5r3Ie',
    NOW()
);