-- Tokens de reset de senha
-- Execute no Supabase SQL Editor

CREATE TABLE IF NOT EXISTS reset_senha (
    id            UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    usuario_id    UUID NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    token_hash    VARCHAR(64) NOT NULL UNIQUE,  -- SHA-256 do token enviado por e-mail
    expira_em     TIMESTAMPTZ NOT NULL,
    usado_em      TIMESTAMPTZ,
    ip_solicitou  VARCHAR(45),
    criado_em     TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_reset_token     ON reset_senha(token_hash);
CREATE INDEX IF NOT EXISTS idx_reset_usuario   ON reset_senha(usuario_id);
