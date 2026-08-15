-- =====================================================
-- AgroAmigo — Verificação de e-mail e avisos por e-mail
-- Execute no SQL Editor do Supabase. Seguro rodar de novo.
-- =====================================================

-- ── 1. Tokens de verificação de e-mail ───────────────
-- Mesma ideia do reset_senha: guarda só o HASH do token.
-- Se o banco vazar, ninguém consegue verificar conta alheia com o que roubou.
CREATE TABLE IF NOT EXISTS verificacao_email (
    id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    usuario_id  UUID NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    token_hash  VARCHAR(64) NOT NULL UNIQUE,
    expira_em   TIMESTAMPTZ NOT NULL,
    usado_em    TIMESTAMPTZ,
    criado_em   TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_verif_usuario ON verificacao_email (usuario_id);
CREATE INDEX IF NOT EXISTS idx_verif_expira  ON verificacao_email (expira_em);

-- ── 2. Dispositivos já conhecidos ────────────────────
-- Serve para o aviso de "login de um aparelho novo": se o par
-- navegador+rede nunca foi visto nesta conta, manda o alerta.
CREATE TABLE IF NOT EXISTS dispositivos_conhecidos (
    id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    usuario_id  UUID NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    marca       VARCHAR(64) NOT NULL,
    ip          VARCHAR(45),
    user_agent  VARCHAR(255),
    ultimo_uso  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    criado_em   TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    UNIQUE (usuario_id, marca)
);

-- ── 3. Fila de e-mails que falharam ──────────────────
-- Se o Gmail estiver fora do ar na hora, o e-mail não se perde:
-- fica registrado aqui para reenvio e para a equipe enxergar o problema.
CREATE TABLE IF NOT EXISTS emails_falhados (
    id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    destino     VARCHAR(200) NOT NULL,
    assunto     VARCHAR(255) NOT NULL,
    corpo       TEXT NOT NULL,
    erro        VARCHAR(255),
    tentativas  SMALLINT NOT NULL DEFAULT 1,
    criado_em   TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- ── 4. Segurança ─────────────────────────────────────
ALTER TABLE verificacao_email       ENABLE ROW LEVEL SECURITY;
ALTER TABLE dispositivos_conhecidos ENABLE ROW LEVEL SECURITY;
ALTER TABLE emails_falhados         ENABLE ROW LEVEL SECURITY;
REVOKE ALL ON TABLE verificacao_email, dispositivos_conhecidos, emails_falhados
    FROM anon, authenticated;

-- ── 5. Conferência ───────────────────────────────────
SELECT table_name FROM information_schema.tables
 WHERE table_schema='public'
   AND table_name IN ('verificacao_email','dispositivos_conhecidos','emails_falhados')
 ORDER BY 1;
