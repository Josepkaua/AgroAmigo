-- =====================================================
-- AgroAmigo ATERPEC — Schema PostgreSQL (Supabase)
-- Execute no SQL Editor do Supabase
-- =====================================================

-- Extensão para UUID
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- =====================================================
-- USUÁRIOS (produtores, técnicos, admins)
-- =====================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id                  UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    nome                VARCHAR(150)  NOT NULL,
    email               VARCHAR(200)  NOT NULL UNIQUE,
    senha_hash          VARCHAR(255)  NOT NULL,
    telefone            VARCHAR(20),
    cpf                 VARCHAR(14),
    role                VARCHAR(20)   NOT NULL DEFAULT 'produtor'
                            CHECK (role IN ('produtor', 'tecnico', 'admin')),
    status              VARCHAR(20)   NOT NULL DEFAULT 'ativo'
                            CHECK (status IN ('ativo', 'inativo', 'suspenso', 'pendente')),
    tentativas_login    INTEGER       NOT NULL DEFAULT 0,
    bloqueado_ate       TIMESTAMPTZ,
    ultimo_login        TIMESTAMPTZ,
    created_at          TIMESTAMPTZ   NOT NULL DEFAULT NOW(),
    updated_at          TIMESTAMPTZ   NOT NULL DEFAULT NOW()
);

-- =====================================================
-- PROPRIEDADES
-- =====================================================
CREATE TABLE IF NOT EXISTS propriedades (
    id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    usuario_id  UUID         NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    nome        VARCHAR(200) NOT NULL,
    municipio   VARCHAR(100),
    uf          CHAR(2),
    area_ha     NUMERIC(10,2),
    created_at  TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

-- =====================================================
-- ANIMAIS
-- =====================================================
CREATE TABLE IF NOT EXISTS animais (
    id                  UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    propriedade_id      UUID         NOT NULL REFERENCES propriedades(id) ON DELETE CASCADE,
    brinco              VARCHAR(50),
    especie             VARCHAR(100),
    raca                VARCHAR(100),
    sexo                CHAR(1)      CHECK (sexo IN ('M', 'F')),
    data_nascimento     DATE,
    peso_nascimento_kg  NUMERIC(8,2),
    pelagem             VARCHAR(100),
    mae_id              UUID         REFERENCES animais(id),
    pai_nome            VARCHAR(100),
    status              VARCHAR(20)  NOT NULL DEFAULT 'ativo'
                            CHECK (status IN ('ativo', 'vendido', 'abatido', 'morto', 'transferido')),
    created_at          TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    updated_at          TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

-- =====================================================
-- PESAGENS
-- =====================================================
CREATE TABLE IF NOT EXISTS pesagens (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    animal_id       UUID         NOT NULL REFERENCES animais(id) ON DELETE CASCADE,
    data_pesagem    DATE         NOT NULL,
    peso_kg         NUMERIC(8,2) NOT NULL,
    escore_corporal NUMERIC(3,1),
    ganho_medio_g   INTEGER,
    responsavel     VARCHAR(150),
    observacao      TEXT,
    created_at      TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

-- =====================================================
-- VACINAÇÕES
-- =====================================================
CREATE TABLE IF NOT EXISTS vacinacoes (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    animal_id       UUID         NOT NULL REFERENCES animais(id) ON DELETE CASCADE,
    nome_vacina     VARCHAR(200) NOT NULL,
    data_aplicacao  DATE,
    dose_ml         NUMERIC(6,2),
    via             VARCHAR(50),
    lote            VARCHAR(50),
    validade        DATE,
    proximo_reforco DATE,
    aplicador       VARCHAR(150),
    created_at      TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

-- =====================================================
-- OCORRÊNCIAS SANITÁRIAS
-- =====================================================
CREATE TABLE IF NOT EXISTS ocorrencias_sanitarias (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    animal_id       UUID         NOT NULL REFERENCES animais(id) ON DELETE CASCADE,
    data_ocorrencia DATE,
    diagnostico     TEXT,
    medicamento     VARCHAR(200),
    dose            VARCHAR(50),
    responsavel     VARCHAR(150),
    created_at      TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

-- =====================================================
-- MORTALIDADE (rebanho — não por animal individual)
-- =====================================================
CREATE TABLE IF NOT EXISTS mortalidade (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    propriedade_id  UUID         NOT NULL REFERENCES propriedades(id) ON DELETE CASCADE,
    animal_id       UUID         REFERENCES animais(id),
    data_morte      DATE,
    especie         VARCHAR(100),
    categoria       VARCHAR(100),
    causa           TEXT,
    medidas         TEXT,
    created_at      TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

-- =====================================================
-- DESTINO FINAL
-- =====================================================
CREATE TABLE IF NOT EXISTS destinos_animais (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    animal_id       UUID         NOT NULL REFERENCES animais(id),
    destino         VARCHAR(20)  CHECK (destino IN ('venda','abate','morte','transferencia')),
    data_destino    DATE,
    peso_final_kg   NUMERIC(8,2),
    valor_obtido    NUMERIC(10,2),
    descricao       TEXT,
    created_at      TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

-- =====================================================
-- LOG DE ACESSO (login / logout / falhas)
-- =====================================================
CREATE TABLE IF NOT EXISTS logs_acesso (
    id              BIGSERIAL    PRIMARY KEY,
    usuario_id      UUID         REFERENCES usuarios(id) ON DELETE SET NULL,
    email_tentado   VARCHAR(200),
    ip              VARCHAR(45),
    user_agent      TEXT,
    acao            VARCHAR(30)  NOT NULL
                        CHECK (acao IN ('login_ok','login_falhou','logout','bloqueado')),
    created_at      TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

-- =====================================================
-- LOG DE ATIVIDADE (CRUD auditável)
-- =====================================================
CREATE TABLE IF NOT EXISTS logs_atividade (
    id              BIGSERIAL    PRIMARY KEY,
    usuario_id      UUID         REFERENCES usuarios(id) ON DELETE SET NULL,
    entidade        VARCHAR(50)  NOT NULL,
    entidade_id     UUID,
    acao            VARCHAR(30)  NOT NULL
                        CHECK (acao IN ('criar','editar','excluir','visualizar')),
    dados_antes     JSONB,
    dados_depois    JSONB,
    ip              VARCHAR(45),
    created_at      TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

-- =====================================================
-- LOG DE ERROS
-- =====================================================
CREATE TABLE IF NOT EXISTS logs_erros (
    id              BIGSERIAL    PRIMARY KEY,
    usuario_id      UUID         REFERENCES usuarios(id) ON DELETE SET NULL,
    nivel           VARCHAR(20)  NOT NULL DEFAULT 'error',
    mensagem        TEXT         NOT NULL,
    arquivo         VARCHAR(255),
    linha           INTEGER,
    url             VARCHAR(500),
    ip              VARCHAR(45),
    created_at      TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

-- =====================================================
-- ÍNDICES
-- =====================================================
CREATE INDEX IF NOT EXISTS idx_propriedades_usuario    ON propriedades(usuario_id);
CREATE INDEX IF NOT EXISTS idx_animais_propriedade     ON animais(propriedade_id);
CREATE INDEX IF NOT EXISTS idx_animais_status          ON animais(status);
CREATE INDEX IF NOT EXISTS idx_pesagens_animal         ON pesagens(animal_id);
CREATE INDEX IF NOT EXISTS idx_pesagens_data           ON pesagens(data_pesagem DESC);
CREATE INDEX IF NOT EXISTS idx_vacinacoes_animal       ON vacinacoes(animal_id);
CREATE INDEX IF NOT EXISTS idx_ocorrencias_animal      ON ocorrencias_sanitarias(animal_id);
CREATE INDEX IF NOT EXISTS idx_mortalidade_prop        ON mortalidade(propriedade_id);
CREATE INDEX IF NOT EXISTS idx_destinos_animal         ON destinos_animais(animal_id);
CREATE INDEX IF NOT EXISTS idx_logs_acesso_usuario     ON logs_acesso(usuario_id);
CREATE INDEX IF NOT EXISTS idx_logs_acesso_created     ON logs_acesso(created_at DESC);
CREATE INDEX IF NOT EXISTS idx_logs_acesso_ip          ON logs_acesso(ip);
CREATE INDEX IF NOT EXISTS idx_logs_atividade_usuario  ON logs_atividade(usuario_id);
CREATE INDEX IF NOT EXISTS idx_logs_atividade_created  ON logs_atividade(created_at DESC);
CREATE INDEX IF NOT EXISTS idx_logs_erros_created      ON logs_erros(created_at DESC);

-- =====================================================
-- TRIGGER updated_at
-- =====================================================
CREATE OR REPLACE FUNCTION trg_set_updated_at()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$;

CREATE OR REPLACE TRIGGER trg_usuarios_upd
    BEFORE UPDATE ON usuarios
    FOR EACH ROW EXECUTE FUNCTION trg_set_updated_at();

CREATE OR REPLACE TRIGGER trg_propriedades_upd
    BEFORE UPDATE ON propriedades
    FOR EACH ROW EXECUTE FUNCTION trg_set_updated_at();

CREATE OR REPLACE TRIGGER trg_animais_upd
    BEFORE UPDATE ON animais
    FOR EACH ROW EXECUTE FUNCTION trg_set_updated_at();

-- =====================================================
-- ADMIN PADRÃO
-- IMPORTANTE: Troque a senha pelo hash gerado em gestao/setup.php
-- Senha padrão temporária: Admin@2025 (ALTERE IMEDIATAMENTE)
-- =====================================================
INSERT INTO usuarios (nome, email, senha_hash, role, status)
VALUES (
    'Administrador',
    'admin@agroamigo.local',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    'ativo'
) ON CONFLICT (email) DO NOTHING;
