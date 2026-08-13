-- =====================================================
-- AgroAmigo — Hardening de Segurança do Banco
-- Execute no SQL Editor do Supabase (em ordem)
-- =====================================================
--
-- ⚠️ LEIA ANTES DE RODAR
--
-- A versão anterior deste arquivo criava o usuário `agroamigo_app` só com
-- GRANTs. Isso DERRUBA O SITE, porque as 13 tabelas estão com RLS (Row Level
-- Security) LIGADO e ZERO policies. Com RLS ligado e nenhuma policy, quem não
-- é dono da tabela enxerga zero linhas e não consegue inserir nada — o login
-- passaria a falhar sempre e nada mais salvaria.
--
-- E NÃO adianta desligar o RLS: no Supabase os papéis `anon` e `authenticated`
-- têm SELECT/INSERT/UPDATE/DELETE/TRUNCATE em TODAS as tabelas. Hoje o RLS é a
-- única coisa impedindo que qualquer pessoa com a chave pública anon leia a
-- tabela `usuarios` (com os hashes de senha) ou apague tudo pela API REST.
--
-- Resumo: RLS FICA LIGADO. O que este script faz é dar ao `agroamigo_app`
-- policies próprias, para que só ele passe pelo RLS. `anon` continua barrado.
-- =====================================================


-- ── 1. Criar usuário restrito para a aplicação ────────
-- Troque a senha antes de rodar. Guarde-a: é o DB_PASS do Render.
CREATE USER agroamigo_app WITH PASSWORD 'TROQUE_POR_SENHA_FORTE_AQUI';

-- ── 2. Dar acesso ao schema public ───────────────────
GRANT USAGE ON SCHEMA public TO agroamigo_app;

-- ── 3. Permissões nas tabelas ────────────────────────
-- (inclui reset_senha, que faltava na versão anterior — sem ela a
--  recuperação de senha quebra com "permission denied")
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
    reset_senha,
    logs_acesso,
    logs_atividade,
    logs_erros
TO agroamigo_app;

-- ── 4. Permissão nas sequences ───────────────────────
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO agroamigo_app;

-- ── 5. Garantir permissões em tabelas futuras ─────────
ALTER DEFAULT PRIVILEGES IN SCHEMA public
    GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO agroamigo_app;

ALTER DEFAULT PRIVILEGES IN SCHEMA public
    GRANT USAGE, SELECT ON SEQUENCES TO agroamigo_app;

-- ── 6. Remover permissão de CREATE no schema público ──
REVOKE CREATE ON SCHEMA public FROM agroamigo_app;


-- ── 7. ⭐ POLICIES DE RLS PARA A APLICAÇÃO ⭐ ──────────
-- Sem este bloco o site NÃO funciona com o usuário restrito.
--
-- Quem-vê-o-quê é decidido no PHP (require_login, checagem de dono da
-- propriedade). O papel do RLS aqui é só barrar acesso externo pela API REST
-- do Supabase. Por isso a policy da aplicação é liberada (true) e existe
-- SOMENTE para o papel agroamigo_app.
DO $$
DECLARE
    t text;
BEGIN
    FOREACH t IN ARRAY ARRAY[
        'usuarios','propriedades','animais','pesagens','vacinacoes',
        'ocorrencias_sanitarias','mortalidade','destinos_animais',
        'fichas_salvas','reset_senha','logs_acesso','logs_atividade','logs_erros'
    ] LOOP
        -- garante RLS ligado (protege contra anon/authenticated)
        EXECUTE format('ALTER TABLE public.%I ENABLE ROW LEVEL SECURITY', t);

        -- recria a policy da aplicação de forma idempotente
        EXECUTE format('DROP POLICY IF EXISTS app_acesso_total ON public.%I', t);
        EXECUTE format(
            'CREATE POLICY app_acesso_total ON public.%I
               FOR ALL TO agroamigo_app
               USING (true) WITH CHECK (true)', t);
    END LOOP;
END $$;


-- ── 8. Revogar o acesso público desnecessário ────────
-- A aplicação NÃO usa a API REST do Supabase (SUPABASE_URL/KEY não são
-- consumidos em lugar nenhum do código PHP). Sem isso, um vazamento da chave
-- anon dá acesso direto às tabelas caso alguém desligue o RLS por engano.
REVOKE ALL ON ALL TABLES IN SCHEMA public FROM anon, authenticated;
ALTER DEFAULT PRIVILEGES IN SCHEMA public REVOKE ALL ON TABLES FROM anon, authenticated;

-- ── 9. Trocar senha do postgres ──────────────────────
-- NÃO é possível via SQL no Supabase. Faça pelo painel:
-- Supabase → Settings → Database → Database Password → Reset


-- ── 10. Verificação: rode DEPOIS e confira o resultado ──
-- Esperado: 13 linhas, todas com rls_ligado = true e policies_do_app = 1
SELECT t.tablename,
       t.rowsecurity AS rls_ligado,
       (SELECT count(*) FROM pg_policies p
         WHERE p.schemaname = 'public'
           AND p.tablename  = t.tablename
           AND p.policyname = 'app_acesso_total') AS policies_do_app,
       has_table_privilege('agroamigo_app',
           ('public.' || t.tablename)::regclass, 'SELECT') AS app_pode_ler
FROM pg_tables t
WHERE t.schemaname = 'public'
ORDER BY t.tablename;

-- Teste real, OBRIGATÓRIO antes de trocar o DB_USER no Render:
--   SET ROLE agroamigo_app;
--   SELECT count(*) FROM usuarios;   -- precisa devolver o número real, não 0
--   RESET ROLE;
