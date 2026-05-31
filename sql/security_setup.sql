-- =====================================================
-- AgroAmigo — Hardening de Segurança do Banco
-- Execute no SQL Editor do Supabase (em ordem)
-- =====================================================

-- ── 1. Criar usuário restrito para a aplicação ────────
-- A aplicação usará este usuário em vez do postgres (superuser)
CREATE USER agroamigo_app WITH PASSWORD 'TROQUE_POR_SENHA_FORTE_AQUI';

-- ── 2. Dar acesso ao schema public ───────────────────
GRANT USAGE ON SCHEMA public TO agroamigo_app;

-- ── 3. Permissões nas tabelas (apenas o necessário) ──
GRANT SELECT, INSERT, UPDATE, DELETE ON TABLE
    usuarios,
    propriedades,
    animais,
    pesagens,
    vacinacoes,
    ocorrencias_sanitarias,
    mortalidade,
    destinos_animais,
    fichas_salvas,
    logs_acesso,
    logs_atividade,
    logs_erros
TO agroamigo_app;

-- ── 4. Permissão nas sequences (BIGSERIAL) ────────────
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO agroamigo_app;

-- ── 5. Garantir permissões em tabelas futuras ─────────
ALTER DEFAULT PRIVILEGES IN SCHEMA public
    GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO agroamigo_app;

ALTER DEFAULT PRIVILEGES IN SCHEMA public
    GRANT USAGE, SELECT ON SEQUENCES TO agroamigo_app;

-- ── 6. Remover permissão de CREATE no schema público ──
-- (impede a aplicação de criar/dropar tabelas)
REVOKE CREATE ON SCHEMA public FROM agroamigo_app;

-- ── 7. Trocar senha do postgres (superuser) ───────────
-- Gera uma nova senha longa — guarde em local seguro
ALTER USER postgres PASSWORD 'TROQUE_POR_OUTRA_SENHA_FORTE_AQUI';

-- ── 8. Verificar o que foi criado ────────────────────
SELECT usename, usesuper, usecreatedb, usecreaterole
FROM pg_user
WHERE usename IN ('postgres', 'agroamigo_app');
