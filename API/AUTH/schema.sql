CREATE TABLE IF NOT EXISTS users (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    email               VARCHAR(255)    NOT NULL UNIQUE,
    password_hash       VARCHAR(255)    NOT NULL,
    username            VARCHAR(100)    NOT NULL UNIQUE,
    is_verified         TINYINT(1)      NOT NULL DEFAULT 0 COMMENT '0:Non-vérifié, 1:Vérifié',
    verification_token  VARCHAR(255)    DEFAULT NULL,
    token_expires_at    DATETIME        DEFAULT NULL,
    created_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    role_id             TINYINT         NOT NULL DEFAULT 1 COMMENT '1:Etudiant , 2:Lauréat , 3:Mentor , 4:Admin',
    oauth_provider      VARCHAR(50)     DEFAULT NULL,
    oauth_id            VARCHAR(255)    DEFAULT NULL
);