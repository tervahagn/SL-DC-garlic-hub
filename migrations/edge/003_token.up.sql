CREATE TABLE user_tokens (
    token BLOB(32) PRIMARY KEY,
    UID INTEGER NOT NULL,
    purpose TEXT NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used_at TIMESTAMP,
    additional_data TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UID) REFERENCES user_main(UID) ON DELETE CASCADE
);

CREATE INDEX idx_token_user_purpose ON user_tokens (UID, purpose);
CREATE INDEX idx_token_expires ON user_tokens (expires_at);