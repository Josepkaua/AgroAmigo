-- Mensagens recebidas pelo formulário "Falar com Técnico" (contato.php)
-- Antes disso o formulário só fingia enviar — agora persiste de verdade.
-- Execute no Supabase SQL Editor

CREATE TABLE IF NOT EXISTS mensagens_contato (
    id           UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    usuario_id   UUID REFERENCES usuarios(id) ON DELETE SET NULL, -- null = visitante não logado
    nome         VARCHAR(150) NOT NULL,
    telefone     VARCHAR(20),
    animal       VARCHAR(50),
    topico       VARCHAR(50),
    mensagem     TEXT NOT NULL,
    ip           VARCHAR(45),
    status       VARCHAR(20) NOT NULL DEFAULT 'pendente'
                     CHECK (status IN ('pendente', 'respondida', 'arquivada')),
    criado_em    TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_mensagens_contato_criado ON mensagens_contato(criado_em DESC);
CREATE INDEX IF NOT EXISTS idx_mensagens_contato_status ON mensagens_contato(status);
