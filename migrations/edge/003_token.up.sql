CREATE TABLE user_activation_tokens (
    token TEXT PRIMARY KEY,
    UID INTEGER NOT NULL,
    purpose TEXT NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    additional_data TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UID) REFERENCES user_main(UID) ON DELETE CASCADE
);

CREATE INDEX idx_token_user_purpose ON user_activation_tokens (UID, purpose);
CREATE INDEX idx_token_expires ON user_activation_tokens (expires_at);