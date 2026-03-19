
CREATE TABLE IF NOT EXISTS users (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    email               VARCHAR(255) NOT NULL UNIQUE,
    password_hash       VARCHAR(255) NOT NULL,
    username            VARCHAR(100) NOT NULL UNIQUE,
    is_verified         TINYINT(1)   NOT NULL DEFAULT 0,
    verification_token  VARCHAR(64)  DEFAULT NULL,
    token_expires_at    DATETIME     DEFAULT NULL,  
    created_at          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
);
