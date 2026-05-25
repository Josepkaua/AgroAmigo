-- =====================================================
-- AgroAmigo — Migração: Fichas Salvas
-- Execute no SQL Editor do Supabase
-- =====================================================

CREATE TABLE IF NOT EXISTS fichas_salvas (
    id           UUID         PRIMARY KEY DEFAULT gen_random_uuid(),
    usuario_id   UUID         NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    tipo         VARCHAR(30)  NOT NULL
                     CHECK (tipo IN ('zootecnica', 'vacinacao', 'mortalidade', 'controle')),
    nome_arquivo VARCHAR(100) NOT NULL,
    caminho_json VARCHAR(500) NOT NULL,
    dados        JSONB        NOT NULL DEFAULT '{}',
    salvo_em     TIMESTAMPTZ  NOT NULL DEFAULT NOW(),

    UNIQUE (usuario_id, tipo)
);

CREATE INDEX IF NOT EXISTS idx_fichas_salvas_usuario
    ON fichas_salvas (usuario_id);

COMMENT ON TABLE fichas_salvas IS
    'Uma ficha salva por tipo por usuário (UPSERT). Dados em JSONB + arquivo JSON em disco.';
