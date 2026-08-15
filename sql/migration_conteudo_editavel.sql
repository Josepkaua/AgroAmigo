-- =====================================================
-- AgroAmigo — Conteúdo editável pelo painel
-- Permite que admin e técnico alterem raças e textos do site
-- sem mexer em código. Execute no SQL Editor do Supabase.
-- Seguro rodar mais de uma vez.
-- =====================================================

-- ── 1. Raças ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS racas (
    id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    especie     VARCHAR(20)  NOT NULL
                    CHECK (especie IN ('bovinos','aves','suinos','caprinos','ovinos','peixes')),
    ordem       SMALLINT     NOT NULL DEFAULT 1,
    emoji       VARCHAR(12)  NOT NULL DEFAULT '🐾',
    nome        VARCHAR(100) NOT NULL,
    tipo        VARCHAR(50),
    descricao   TEXT,
    imagem      VARCHAR(255),
    ativo       BOOLEAN      NOT NULL DEFAULT TRUE,
    created_at  TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_racas_especie ON racas (especie, ordem) WHERE ativo;

-- ── 2. Blocos de texto/imagem do site ────────────────
CREATE TABLE IF NOT EXISTS conteudo_site (
    chave         VARCHAR(80) PRIMARY KEY,
    grupo         VARCHAR(40)  NOT NULL,
    rotulo        VARCHAR(120) NOT NULL,
    ajuda         VARCHAR(255),
    tipo          VARCHAR(20)  NOT NULL DEFAULT 'texto'
                      CHECK (tipo IN ('texto','texto_longo','imagem','telefone')),
    valor         TEXT,
    ordem         SMALLINT     NOT NULL DEFAULT 1,
    atualizado_em TIMESTAMPTZ,
    atualizado_por UUID REFERENCES usuarios(id) ON DELETE SET NULL
);

-- ── 3. Carrega as 18 raças que já estão no site ──────
-- Só insere se a tabela estiver vazia, pra não sobrescrever edições feitas.
INSERT INTO racas (especie, ordem, emoji, nome, tipo, descricao, imagem)
SELECT * FROM (VALUES
    ('bovinos', 1, '🐂', 'Nelore', 'Corte', 'Zebuíno de origem indiana (raça Ongole), pelagem branco-acinzentada, pele escura e corcova proeminente. Resistência natural ao carrapato (Rhipicephalus microplus) e à tristeza parasitária. Ganho a pasto: 400–600 g/dia. Abate com 450–520 kg (rendimento de carcaça 52–54%). Padrão dominante de corte no Maranhão e Tocantins. Abate médio: 30–36 meses a pasto.', 'assets/img/racas/bovinos/nelore.jpg'),
    ('bovinos', 2, '🐄', 'Girolando', 'Leite/Misto', 'Síntese brasileira entre Gir (37,5–87,5%) e Holandês, desenvolvida pela Embrapa. Combina rusticidade tropical com produção leiteira: 10–20 litros/dia em sistema semi-intensivo no Maranhão. Gordura 3,5–4,2%. Resiste ao carrapato e ao calor (ITGU conforto até 79). Raça mais criada na bacia leiteira maranhense. Padrão recomendado: 5/8 Holandês × 3/8 Gir.', 'assets/img/racas/bovinos/girolando.jpg'),
    ('bovinos', 3, '🐃', 'Gir', 'Leite', 'Zebuíno indiano (origem Gujarat/Rajastão), coloração variável de amarelo-claro a vermelho-escuro, orelhas longas tubuladas e chanfro convexo (perfil de "papagaio"). Machos 600–750 kg, fêmeas 350–450 kg. Produção de leite: 8–14 litros/dia. Transpira mais que taurinos — melhor adaptação ao calor. Base genética do Girolando e do Guzerá leiteiro. Alta resistência à mosca-dos-chifres e ao berne.', 'assets/img/racas/bovinos/gir.jpg'),
    ('aves', 1, '🐔', 'Galinha Caipira (SRD)', 'Corte/Ovos', 'Aves mestiças sem padrão racial, adaptadas ao manejo extensivo do Maranhão. Produção: 130–180 ovos/ano com comportamento forrageador ativo — reduz custo de ração em 20–30% em piquetes. Peso vivo ao abate: 1,8–2,5 kg entre 90–120 dias. Alta resistência a ectoparasitas (piolhos, carrapatos). Valorização crescente no mercado de ovos e carne caipira.', 'https://images.unsplash.com/photo-1588597989061-b60ad0eefdbf?w=600&q=80&auto=format&fit=crop'),
    ('aves', 2, '🥚', 'ISA Brown', 'Poedeira', 'Linhagem comercial híbrida poedeira de plumagem marrom-avermelhada. Produção: 300–320 ovos/ano, conversão 2,0–2,2 kg de ração/dúzia de ovos. Pico de postura entre 24 e 36 semanas, período produtivo de 70–80 semanas. Em sistema semi-intensivo com piquete, reduz custo de ração em 15–20% com pequena redução de postura. Principal linhagem de postura do Brasil.', 'assets/img/racas/aves/isa-brown.jpg'),
    ('aves', 3, '🐓', 'Pescoço Pelado', 'Corte/Ovos', 'Variedade com gene Na (naked neck), que elimina 40% das penas do pescoço e peito, reduzindo a produção de calor corporal em ~30% — melhor adaptação fisiológica ao calor do Maranhão. Menor dispêndio energético com termorregulação resulta em mais energia para postura e crescimento. Produção: 160–200 ovos/ano, peso ao abate 2,0–2,8 kg. Ideal para regiões com temperatura acima de 30°C.', 'assets/img/racas/aves/pescoco-pelado.jpg'),
    ('suinos', 1, '🐷', 'Large White', 'Corte', 'Raça britânica (Yorkshire), orelhas eretas, corpo longo e pelagem branca uniforme com capa de toucinho fina. Ganho médio de 850–950 g/dia, conversão alimentar 2,2–2,5:1. Abate com 100–120 kg aos 150–170 dias. Prolificidade: 12–14 leitões/parto. Exige nebulização ou aspersão no telhado acima de 28°C no clima maranhense.', 'https://images.unsplash.com/photo-1616109259043-fd30a7663a5d?w=600&q=80&auto=format&fit=crop'),
    ('suinos', 2, '🐖', 'Landrace', 'Corte', 'Raça escandinava com carcaça extra-longa (76–81 cm) e orelhas grandes totalmente caídas para a frente — distinção visual principal. Base do presunto e lombo industriais. Conversão 2,3–2,6:1, abate com 95–115 kg. Altamente sensível ao calor (zona de conforto: 16–22°C) — exige sombra, aspersão e ventilação cruzada obrigatórias no Maranhão.', 'assets/img/racas/suinos/landrace.jpg'),
    ('suinos', 3, '🐗', 'Piau', 'Misto', 'Raça nacional de origem ibérica (nome tupi: "malhado"), pelagem creme com manchas pretas irregulares. Ganho diário de 469 g e carcaça resfriada de 71 kg (Embrapa). Alta rusticidade ao calor e à alimentação alternativa (resíduos de mandioca, milho, farelo de coco). Risco de extinção — menos de 1.000 animais puros registrados no PBB. Ideal para sistemas extensivos do Nordeste.', 'assets/img/racas/suinos/piau.jpg'),
    ('caprinos', 1, '🐐', 'Anglo-nubiano', 'Leite/Misto', 'Raça de dupla aptidão originada no Reino Unido, com orelhas longas e pendentes e perfil nasal convexo (ariano). Produção: 2–4 litros/dia com 4,5–5,0% de gordura — leite superior para queijo artesanal. Machos 70–100 kg, fêmeas 50–70 kg. Prolificidade 1,6–2,0 crias/parto. Bem distribuída no Nordeste e base dos cruzamentos leiteiros do Maranhão.', 'assets/img/racas/caprinos/anglo-nubiano.jpg'),
    ('caprinos', 2, '🐑', 'Boer', 'Corte', 'Raça sul-africana especializada em carne: corpo largo e musculoso, pelagem branca com cabeça e pescoço marrom-avermelhados. F1 com SRD: abate com 30–35 kg em 6–8 meses, rendimento de carcaça 48–52%. Conversão alimentar 4–5:1. Principal ferramenta de melhoramento do rebanho caprino nordestino — usado exclusivamente em cruzamento terminal.', 'https://images.unsplash.com/photo-1528127044085-fdef44dd867c?w=600&q=80&auto=format&fit=crop'),
    ('caprinos', 3, '🐐', 'SRD (Comum)', 'Misto', 'Caprinos sem padrão racial definido, descendentes de raças ibéricas introduzidas no século XVI, com séculos de seleção natural para o semiárido. Peso adulto 25–45 kg, prolificidade 1,4–1,7 crias/parto. Extremamente rústicos: toleram seca, calor extremo e pastagens de caatinga/cerrado maranhense. Custo de manutenção muito baixo — base da caprinocultura familiar do Maranhão.', 'https://images.unsplash.com/photo-1593750439808-958d28558592?w=600&q=80&auto=format&fit=crop'),
    ('ovinos', 1, '🐑', 'Santa Inês', 'Corte/Misto', 'Raça deslanada brasileira formada no Nordeste (Morada Nova × Somalis Brasileira × Bergamácia). Machos 80–100 kg, fêmeas 60–70 kg. Cordeiros atingem 28–35 kg ao abate em 90–120 dias. Poliéstrica anual: reproduz em qualquer época do ano. Prolificidade 1,1–1,4 crias/parto. Maior resistência a helmintos gastrointestinais que raças lanadas em clima tropical.', 'assets/img/racas/ovinos/santa-ines.jpg'),
    ('ovinos', 2, '🐏', 'Dorper', 'Corte', 'Raça sul-africana deslanada: corpo branco com cabeça e pescoço negros, pele pigmentada. Maturidade precoce — F1 Dorper × Santa Inês atinge 35–40 kg em 90–100 dias. Rendimento de carcaça 48–55% com excelente proporção músculo:osso. Cruzamento mais eficaz para elevar produtividade sem perda de rusticidade no Nordeste brasileiro.', 'assets/img/racas/ovinos/dorper.jpg'),
    ('ovinos', 3, '🐑', 'SRD (Comum)', 'Misto', 'Ovinos mestiços sem padrão racial definido, selecionados empiricamente por gerações de criadores maranhenses. Peso adulto 30–50 kg, prolificidade 1,0–1,3 crias/parto. Elevada rusticidade ao clima tropical úmido e ao cerrado-caatinga maranhense. Aptidão predominantemente para carne e subsistência — baixíssimo custo de manutenção.', 'https://images.unsplash.com/photo-1613573057422-ec1f77c60028?w=600&q=80&auto=format&fit=crop'),
    ('peixes', 1, '🐟', 'Tilápia do Nilo', 'Corte', 'Oreochromis niloticus. Espécie mais cultivada no Brasil (mais de 60% da produção aquícola nacional). Atingem 700 g–1 kg em 6–8 meses com ração de 28–32% PB. Temperatura ótima: 27–30°C — toleram até 15°C com redução de crescimento. Suportam baixa oxigenação (mínimo 3 mg/L). Linhagem GIFT e chitralada são as mais indicadas para viveiros no Maranhão.', 'assets/img/racas/peixes/tilapia-do-nilo.jpg'),
    ('peixes', 2, '🐠', 'Tambaqui', 'Corte', 'Colossoma macropomum. Peixe nativo do Amazonas, muito consumido no Nordeste. Crescimento excelente em viveiros. Resistente e de fácil manejo. Ciclo de 10 a 14 meses.', 'assets/img/racas/peixes/tambaqui.jpg'),
    ('peixes', 3, '🐡', 'Tambacu (híbrido)', 'Corte', 'Cruzamento entre Tambaqui e Pacu. Combina o crescimento rápido do tambaqui com a resistência do pacu. Popular em pisciculturas familiares do Nordeste.', 'assets/img/racas/peixes/tambacu.jpg')
) AS v(especie, ordem, emoji, nome, tipo, descricao, imagem)
WHERE NOT EXISTS (SELECT 1 FROM racas);

-- ── 4. Blocos de texto editáveis ─────────────────────
INSERT INTO conteudo_site (chave, grupo, rotulo, ajuda, tipo, ordem) VALUES
  ('home_titulo',      'Página inicial', 'Título principal',      'A frase grande que aparece no topo do site.', 'texto', 1),
  ('home_titulo_2',    'Página inicial', 'Segunda linha do título','Aparece em verde claro, embaixo da primeira linha.', 'texto', 2),
  ('home_descricao',   'Página inicial', 'Texto de apresentação', 'O parágrafo abaixo do título.', 'texto_longo', 3),
  ('home_badge',       'Página inicial', 'Selo dos parceiros',    'A tarjinha acima do título.', 'texto', 4),
  ('contato_whatsapp', 'Contato',        'Número do WhatsApp',    'Só números, com DDD. Ex: 98991538604', 'telefone', 1),
  ('contato_texto',    'Contato',        'Texto da página de contato', NULL, 'texto_longo', 2),
  ('banner_bovinos',   'Banners',        'Banner da página Bovinos',  'Imagem larga do topo. Ideal 1400x500.', 'imagem', 1),
  ('banner_aves',      'Banners',        'Banner da página Aves',     'Imagem larga do topo. Ideal 1400x500.', 'imagem', 2),
  ('banner_suinos',    'Banners',        'Banner da página Suínos',   'Imagem larga do topo. Ideal 1400x500.', 'imagem', 3),
  ('banner_caprinos',  'Banners',        'Banner da página Caprinos', 'Imagem larga do topo. Ideal 1400x500.', 'imagem', 4),
  ('banner_ovinos',    'Banners',        'Banner da página Ovinos',   'Imagem larga do topo. Ideal 1400x500.', 'imagem', 5),
  ('banner_peixes',    'Banners',        'Banner da página Peixes',   'Imagem larga do topo. Ideal 1400x500.', 'imagem', 6)
ON CONFLICT (chave) DO NOTHING;

-- ── 5. Segurança: RLS como nas outras tabelas ────────
ALTER TABLE racas         ENABLE ROW LEVEL SECURITY;
ALTER TABLE conteudo_site ENABLE ROW LEVEL SECURITY;
REVOKE ALL ON TABLE racas, conteudo_site FROM anon, authenticated;

-- ── 6. Conferência ───────────────────────────────────
SELECT especie, count(*) AS racas FROM racas GROUP BY especie ORDER BY especie;
SELECT grupo, count(*) AS blocos FROM conteudo_site GROUP BY grupo ORDER BY grupo;

-- ── 7. Imagens enviadas pelo painel ──────────────────
-- POR QUE NO BANCO E NÃO EM ARQUIVO:
-- o disco do Render é efêmero. Todo deploy ou reinício do serviço apaga o que
-- foi gravado em disco em tempo de execução. Se as fotos enviadas pelo técnico
-- fossem para uploads/, sumiriam no próximo deploy e ninguém entenderia por quê.
-- Guardadas no Postgres, sobrevivem a deploy e entram no backup do banco junto
-- com o resto. São poucas imagens (24 no total, ~2 MB), então cabe folgado.
CREATE TABLE IF NOT EXISTS imagens (
    id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    nome        VARCHAR(150) NOT NULL,
    mime        VARCHAR(30)  NOT NULL CHECK (mime IN ('image/jpeg','image/png','image/webp')),
    largura     SMALLINT,
    altura      SMALLINT,
    tamanho     INTEGER      NOT NULL,
    conteudo    BYTEA        NOT NULL,
    enviado_por UUID REFERENCES usuarios(id) ON DELETE SET NULL,
    criado_em   TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

ALTER TABLE imagens ENABLE ROW LEVEL SECURITY;
REVOKE ALL ON TABLE imagens FROM anon, authenticated;
