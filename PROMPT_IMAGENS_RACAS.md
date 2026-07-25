Cole este prompt inteiro no Claude Code (VS Code), dentro da pasta do projeto AgroAmigo.

---

## Contexto

O site AgroAmigo (projeto ATERPEC) é um site PHP para pequenos produtores rurais do Maranhão, com páginas por espécie animal (`bovinos.php`, `aves.php`, `suinos.php`, `caprinos.php`, `ovinos.php`, `peixes.php`). Cada página tem um array `$animal['racas']` com as raças daquela espécie, e cada raça tem um campo `'imagem' => 'URL'`.

Hoje essas imagens são hotlinks para fotos genéricas do Unsplash, escolhidas por busca genérica (ex.: "cow", "pig", "chicken") — não são fotos da raça específica. Já auditei visualmente (via screenshot) as 18 fotos de raças atualmente usadas e confirmei que várias mostram o animal errado. O pior caso: a foto usada para "Anglo-nubiano" (cabra) mostra uma **rena** com chifres enormes.

## Tarefa

Baixar uma imagem correta e verificável para cada raça abaixo, salvar localmente dentro do projeto (não mais hotlink do Unsplash) e atualizar o campo `'imagem'` correspondente no arquivo PHP da espécie.

### 1. Estrutura de pastas a criar

```
img/
  racas/
    bovinos/nelore.jpg
    bovinos/girolando.jpg
    bovinos/gir.jpg
    aves/isa-brown.jpg
    aves/pescoco-pelado.jpg
    suinos/landrace.jpg
    suinos/piau.jpg
    caprinos/anglo-nubiano.jpg
    ovinos/santa-ines.jpg
    ovinos/dorper.jpg
    peixes/tilapia-do-nilo.jpg
    peixes/tambaqui.jpg
    CREDITOS.md
```

### 2. Raças com imagem confirmadamente ERRADA (prioridade alta)

Para cada uma: baixar a imagem da fonte indicada (Wikimedia Commons — checar a licença exata na página do arquivo, geralmente CC BY-SA; registrar autor/licença em `img/racas/CREDITOS.md`), redimensionar para ~600px de largura (mesmo padrão das imagens atuais, JPG qualidade ~80%, para não pesar no deploy do Render), salvar no caminho indicado, e substituir o valor de `'imagem'` no arquivo PHP indicado.

| Arquivo | Raça | Imagem atual (errada) | Problema visual confirmado | Fonte correta sugerida |
|---|---|---|---|---|
| `bovinos.php` | Nelore | `photo-1566040924976-f837330d1a5b` | Mostra gado leiteiro europeu (preto/branco malhado) em pasto verde — não é zebuíno | https://commons.wikimedia.org/wiki/File:Nelore_beef_cattle.jpg |
| `bovinos.php` | Girolando | `photo-1498191923457-88552caeccb3` | Mostra silhueta de gado de corte genérico ao entardecer, sem características da raça | https://commons.wikimedia.org/wiki/File:Bezerros_Girolando_Pastando.jpg (ou ver outras opções em https://commons.wikimedia.org/wiki/Category:Girolando) |
| `bovinos.php` | Gir | `photo-1583364428520-fa6c5013c0c3` | Mostra gado Highland escocês peludo — orelhas e chifres nada a ver com Gir | https://commons.wikimedia.org/wiki/File:Gir_01.JPG |
| `aves.php` | ISA Brown | `photo-1532978089407-0fd95ff9abf7` | Mostra galinha rajada/pintada escura — ISA Brown é lisa, marrom-avermelhada | https://commons.wikimedia.org/wiki/File:Isa_brown_chicken.jpg (checar também https://commons.wikimedia.org/wiki/Category:ISA_Brown por uma foto de ave adulta com cor mais forte) |
| `aves.php` | Pescoço Pelado | `photo-1750957262505-bf9ede300507` | Mostra galo com pescoço 100% emplumado — o ponto da raça é o pescoço pelado/sem penas | https://commons.wikimedia.org/wiki/File:Vlad_the_transylvanian_naked_neck.JPG |
| `suinos.php` | Landrace | `photo-1537033206914-9d3551ff8103` | Orelhas eretas na foto atual — o traço mais marcante do Landrace é orelha caída cobrindo o rosto | https://commons.wikimedia.org/wiki/Category:Danish_Landrace_(pig) — usar "Danish Landrace boar and sow, 1909.jpg" (foto antiga mas correta) ou outra do mesmo grupo que mostre bem a orelha caída |
| `suinos.php` | Piau | `photo-1567463087469-192a295e77fd` | Mostra um javali selvagem (cinza, cerdoso, presas) — Piau é porco doméstico malhado creme/preto | Ver "casos especiais" abaixo — não encontrei foto livre confiável |
| `caprinos.php` | Anglo-nubiano | `photo-1536090373681-8e240a4866cf` | Mostra uma **rena/caribu** com galhada — espécie totalmente errada | https://commons.wikimedia.org/wiki/File:RAS_Nubian_goat.JPG |
| `ovinos.php` | Santa Inês | `photo-1772215842204-3e5838af499c` | Mostra ovelha lanada (com lã) britânica — Santa Inês é deslanada (pelo curto, sem lã) | https://commons.wikimedia.org/wiki/File:Ovinos_Santa_Ines.jpg |
| `ovinos.php` | Dorper | `photo-1613238219222-85676ffb11ec` | Mostra ovelha muito lanuda com chifres grandes (parece Scottish Blackface) — Dorper não tem chifre e tem pelo curto | https://commons.wikimedia.org/wiki/Category:Dorper_Sheep — escolher uma com corpo branco, cabeça preta, pelo curto, sem chifre (ex. tentar "Borrego dorper.png") |
| `peixes.php` | Tilápia do Nilo | `photo-1607629194532-53c98b8180da` | Mostra tilápias na cor vermelha (morfo ornamental/comercial "tilápia vermelha"), não a Tilápia do Nilo selvagem (cinza/oliva com listras) | https://commons.wikimedia.org/wiki/File:Oreochromis-niloticus-Nairobi.JPG |
| `peixes.php` | Tambaqui | `photo-1628859742240-269783f56d17` | Foto aérea de tanques-rede no mar, sem nenhum peixe visível. Repare que essa é a MESMA foto usada como banner da página inteira — está duplicada | https://commons.wikimedia.org/wiki/File:Tambaqui_(Colossoma_macropomum).jpg |
| `peixes.php` | Tambacu (híbrido) | `photo-1723134085909-19da487ac9bd` | Foto aérea de tanques-rede offshore com barco (parece criação de salmão) — sem peixe visível, nada a ver com piscicultura de tambacu | Ver "casos especiais" abaixo — não encontrei foto livre confiável |

### 3. Casos especiais — sem fonte gratuita confiável encontrada

**Piau (suínos)** e **Tambacu (peixes)** são raças/híbridos muito específicos do Brasil e não achei foto verificada e de uso livre no Wikimedia Commons para nenhum dos dois. Opções, em ordem de preferência:

1. Buscar mais a fundo (Google Imagens / bancos de imagem) por "porco piau" e "tambacu peixe" com licença reutilizável.
2. Usar a galeria de imagens da Embrapa (https://www.embrapa.br/busca-de-imagens — pesquisar "Suíno Piau"), mas **confirmar os termos de uso** antes de publicar, pois nem toda imagem da Embrapa é de licença aberta.
3. Se nada confiável for encontrado, deixar a imagem atual como placeholder e avisar o José para tirar ou fornecer uma foto real (inclusive pode ser foto de propriedade real cadastrada, já que o Piau é criado por pequenos produtores do projeto).

Não invente ou baixe uma imagem só "parecida" sem confirmar que é do animal certo — isso é exatamente o problema que estamos corrigindo.

### 4. Raças com imagem aceitável — NÃO mexer

Já verifiquei visualmente e estas estão OK ou são genéricas o suficiente para não precisar de troca agora: Galinha Caipira/SRD (aves), Large White (suínos), Boer (caprinos), SRD/Comum (caprinos e ovinos). Não gastar tempo nelas.

### 5. O que NÃO fazer

- Não mudar layout, CSS, estrutura do array `$animal`, nem qualquer outro conteúdo/texto das páginas.
- Não mexer em `home.php` nem nas imagens de banner de cada espécie (`'imagem'` no nível raiz do array `$animal`, fora de `'racas'`) — o pedido é só sobre as fotos de raça.
- Não trocar hospedagem de imagem por outro serviço externo — o objetivo é justamente parar de depender de link externo.

### 6. Checklist final

- [ ] Pasta `img/racas/...` criada com as imagens baixadas e redimensionadas.
- [ ] `img/racas/CREDITOS.md` com autor/licença/link de cada imagem baixada do Wikimedia Commons.
- [ ] Cada `'imagem'=>'...'` trocado para o caminho local relativo (ex.: `'imagem'=>'img/racas/bovinos/nelore.jpg'`).
- [ ] Testar localmente (XAMPP, `localhost/AgroAmigo/bovinos.php` etc.) e conferir visualmente se cada card de raça mostra o animal certo.
- [ ] Confirmar que nenhuma outra parte do site foi alterada (`git diff` antes de commitar).
