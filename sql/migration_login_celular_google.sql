-- =====================================================
-- AgroAmigo — Login por celular + Login com Google
-- Execute no SQL Editor do Supabase
-- Seguro de rodar mais de uma vez (tudo IF NOT EXISTS)
-- =====================================================

-- ── 1. Celular como forma de login ───────────────────
-- O telefone digitado continua em `telefone` (formatado, como o produtor escreveu).
-- `telefone_norm` guarda só os dígitos com DDI 55, para busca e unicidade.
-- Ex.: "(99) 98765-4321" -> "5599987654321"
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS telefone_norm VARCHAR(20);

-- Preenche o normalizado para quem já está cadastrado.
-- Regra: tira tudo que não é dígito; se ficar com 10 ou 11 dígitos, prefixa 55.
UPDATE usuarios
   SET telefone_norm = CASE
         WHEN length(regexp_replace(telefone, '\D', '', 'g')) IN (10, 11)
              THEN '55' || regexp_replace(telefone, '\D', '', 'g')
         WHEN length(regexp_replace(telefone, '\D', '', 'g')) IN (12, 13)
              THEN regexp_replace(telefone, '\D', '', 'g')
         ELSE NULL
       END
 WHERE telefone IS NOT NULL
   AND telefone_norm IS NULL;

-- Unicidade só entre os não-nulos: dois cadastros não podem ter o mesmo celular,
-- mas quem não informou telefone continua podendo se cadastrar.
CREATE UNIQUE INDEX IF NOT EXISTS uniq_usuarios_telefone_norm
    ON usuarios (telefone_norm)
    WHERE telefone_norm IS NOT NULL;

-- ── 2. Login com Google ──────────────────────────────
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS google_id        VARCHAR(64);
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS avatar_url       TEXT;
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS email_verificado BOOLEAN NOT NULL DEFAULT FALSE;

CREATE UNIQUE INDEX IF NOT EXISTS uniq_usuarios_google_id
    ON usuarios (google_id)
    WHERE google_id IS NOT NULL;

-- Quem entrou pelo Google não tem senha local. A coluna senha_hash é NOT NULL,
-- então esses usuários recebem um hash impossível de casar (nenhuma senha bate).
-- Assim ninguém consegue logar por senha numa conta criada via Google.
COMMENT ON COLUMN usuarios.google_id IS
    'sub do Google OAuth. Se preenchido e senha_hash começa com "!", a conta é só-Google.';

-- ── 3. Estado do OAuth (proteção contra CSRF no callback) ──
-- Guarda o `state` de cada início de login com Google. Sem isso, um atacante
-- pode forjar o callback e logar a vítima na conta dele (login CSRF).
CREATE TABLE IF NOT EXISTS oauth_state (
    state       VARCHAR(64)  PRIMARY KEY,
    nonce       VARCHAR(64)  NOT NULL,
    redirect_to VARCHAR(255),
    ip          VARCHAR(45),
    criado_em   TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    usado_em    TIMESTAMPTZ
);

CREATE INDEX IF NOT EXISTS idx_oauth_state_criado ON oauth_state (criado_em);

-- ── 4. Verificação ───────────────────────────────────
-- Esperado: telefone_norm, google_id, avatar_url, email_verificado presentes
SELECT column_name, data_type, is_nullable
  FROM information_schema.columns
 WHERE table_name = 'usuarios'
   AND column_name IN ('telefone', 'telefone_norm', 'google_id', 'avatar_url', 'email_verificado')
 ORDER BY column_name;

-- Confere se algum telefone ficou fora do padrão (revisar manualmente se aparecer)
SELECT id, nome, telefone, telefone_norm
  FROM usuarios
 WHERE telefone IS NOT NULL AND telefone_norm IS NULL;
