-- =====================================================
-- AgroAmigo — Envio de e-mail pelo painel
-- Execute no SQL Editor do Supabase. Seguro rodar de novo.
-- =====================================================

-- ── 1. E-mail de quem escreve pelo formulário de contato ──
-- Sem esta coluna não há para onde responder: hoje a mensagem guarda só
-- nome e telefone. Fica OPCIONAL de propósito — produtor que só tem
-- telefone não pode ser barrado por causa disso.
ALTER TABLE mensagens_contato ADD COLUMN IF NOT EXISTS email VARCHAR(200);

-- ── 2. Registro de tudo que o painel enviar ──────────
-- Serve para três coisas: auditoria (quem mandou o quê), limite diário
-- (o plano grátis do Brevo dá 300/dia) e para reenviar em caso de falha.
CREATE TABLE IF NOT EXISTS emails_enviados (
    id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    enviado_por UUID REFERENCES usuarios(id) ON DELETE SET NULL,
    destino     VARCHAR(200) NOT NULL,
    assunto     VARCHAR(255) NOT NULL,
    corpo       TEXT NOT NULL,
    tipo        VARCHAR(20) NOT NULL DEFAULT 'individual'
                    CHECK (tipo IN ('individual','resposta','massa','avulso')),
    sucesso     BOOLEAN NOT NULL DEFAULT FALSE,
    erro        VARCHAR(255),
    ip          VARCHAR(45),
    criado_em   TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_env_criado  ON emails_enviados (criado_em DESC);
CREATE INDEX IF NOT EXISTS idx_env_usuario ON emails_enviados (enviado_por, criado_em DESC);

-- ── 3. Marcar mensagem de contato como respondida ────
ALTER TABLE mensagens_contato ADD COLUMN IF NOT EXISTS respondida_em  TIMESTAMPTZ;
ALTER TABLE mensagens_contato ADD COLUMN IF NOT EXISTS respondida_por UUID
    REFERENCES usuarios(id) ON DELETE SET NULL;

-- ── 4. Segurança ─────────────────────────────────────
ALTER TABLE emails_enviados ENABLE ROW LEVEL SECURITY;
REVOKE ALL ON TABLE emails_enviados FROM anon, authenticated;

-- ── 5. Conferência ───────────────────────────────────
SELECT column_name FROM information_schema.columns
 WHERE table_name='mensagens_contato' AND column_name IN ('email','respondida_em','respondida_por')
 ORDER BY 1;
