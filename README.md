# Desafio Técnico — Processamento de Pedidos em PHP

Este projeto foi desenvolvido como solução para um desafio técnico de PHP Júnior.

A proposta é simular um fluxo simples de processamento de pedidos em um mini sistema de e-commerce. A aplicação lê uma lista de pedidos, verifica o estoque disponível dos produtos, registra cada pedido com o status adequado e permite consultar o faturamento total dos pedidos pagos.

O foco da solução é demonstrar lógica de programação, organização de código, uso de PHP puro com PDO, SQL e boas práticas básicas para manter o fluxo consistente.

---

## Objetivo

Implementar uma ferramenta em linha de comando para:

- criar uma base de produtos;
- ler pedidos simulados a partir de um arquivo JSON;
- verificar se existe estoque suficiente para cada pedido;
- deduzir o estoque quando o pedido puder ser pago;
- registrar pedidos sem estoque como `cancelado_sem_estoque`;
- calcular o faturamento total dos pedidos com status `pago`.

---

## Tecnologias utilizadas

- PHP 8.3
- SQLite
- PDO
- Composer
- Programação Orientada a Objetos
- Scripts CLI

O projeto foi desenvolvido sem frameworks, sem Laravel, sem WordPress e sem ORMs como Eloquent ou Doctrine.

---

## Requisitos

Para executar o projeto, é necessário ter instalado:

- PHP 8.x
- Composer

Também é necessário que as extensões abaixo estejam habilitadas no PHP:

- `pdo_sqlite`
- `sqlite3`

Para conferir se as extensões estão habilitadas:

~~~bash
php -m
~~~

---

## Instalação

Clone o repositório:

~~~bash
git clone https://github.com/MatheusMMarques/desafio-CannaCare-ecommerce.git
~~~

Acesse a pasta do projeto:

~~~bash
cd desafio-CannaCare-ecommerce
~~~

Instale o autoload do Composer:

~~~bash
composer install
~~~

---

## Como executar

### 1. Criar ou resetar o banco de dados

~~~bash
php scripts/setup.php
~~~

Esse comando cria o banco SQLite em `storage/database.sqlite`, recria as tabelas e insere os produtos iniciais.

Saída esperada:

~~~text
Banco de dados configurado com sucesso.
~~~

---

### 2. Processar os pedidos

~~~bash
php scripts/process-orders.php
~~~

Esse comando lê os pedidos do arquivo `data/pedidos.json`, verifica o estoque e grava cada pedido com o status adequado.

Saída esperada:

~~~text
Processando pedidos...

Pedido #1
Produto: Camiseta Básica
Quantidade solicitada: 2
Estoque antes: 10
Estoque depois: 8
Status: pago
Resultado: Pedido criado com sucesso

Pedido #2
Produto: Tênis Casual
Quantidade solicitada: 2
Estoque antes: 5
Estoque depois: 3
Status: pago
Resultado: Pedido criado com sucesso

Pedido #3
Produto: Mochila Urbana
Quantidade solicitada: 5
Estoque antes: 3
Estoque depois: 3
Status: cancelado_sem_estoque
Resultado: Estoque insuficiente

Pedido #4
Produto: Garrafa Térmica
Quantidade solicitada: 1
Estoque antes: 0
Estoque depois: 0
Status: cancelado_sem_estoque
Resultado: Estoque insuficiente
~~~

---

### 3. Consultar o faturamento total

~~~bash
php scripts/revenue.php
~~~

Saída esperada:

~~~text
Faturamento total dos pedidos pagos: R$ 519.60
~~~

---

## Fluxo recomendado para validação

Para executar o projeto do zero:

~~~bash
composer install
php scripts/setup.php
php scripts/process-orders.php
php scripts/revenue.php
~~~

Caso queira repetir o teste, execute novamente o setup antes de processar os pedidos:

~~~bash
php scripts/setup.php
php scripts/process-orders.php
php scripts/revenue.php
~~~

O `setup.php` recria a base inicial, limpa pedidos anteriores e restaura o estoque dos produtos. Isso evita que execuções anteriores interfiram no resultado.

---

## Estrutura do projeto

~~~text
desafio-php-ecommerce/
├── data/
│   └── pedidos.json
├── database/
│   ├── schema.sql
│   └── seed.sql
├── docs/
│   └── SPEC.md
├── scripts/
│   ├── setup.php
│   ├── process-orders.php
│   └── revenue.php
├── src/
│   ├── Database/
│   │   └── Connection.php
│   ├── Repository/
│   │   ├── ProductRepository.php
│   │   └── OrderRepository.php
│   └── Service/
│       └── OrderProcessor.php
├── composer.json
├── README.md
└── .gitignore
~~~

---

## Modelagem do banco

### Tabela `produtos`

| Campo | Tipo | Descrição |
|---|---|---|
| `id` | INTEGER | Identificador do produto |
| `nome` | TEXT | Nome do produto |
| `preco` | REAL | Preço unitário |
| `estoque` | INTEGER | Quantidade disponível em estoque |

### Tabela `pedidos`

| Campo | Tipo | Descrição |
|---|---|---|
| `id` | INTEGER | Identificador do pedido |
| `produto_id` | INTEGER | Produto relacionado ao pedido |
| `quantidade` | INTEGER | Quantidade solicitada |
| `status` | TEXT | Status do pedido |

Status utilizados no fluxo principal:

- `pago`
- `cancelado_sem_estoque`

---

## Produtos iniciais

O projeto utiliza os seguintes produtos de exemplo:

| ID | Produto | Preço | Estoque inicial |
|---|---|---:|---:|
| 1 | Camiseta Básica | R$ 59.90 | 10 |
| 2 | Tênis Casual | R$ 199.90 | 5 |
| 3 | Mochila Urbana | R$ 149.90 | 3 |
| 4 | Garrafa Térmica | R$ 79.90 | 0 |

Esses dados são inseridos pelo arquivo `database/seed.sql`.

---

## Pedidos simulados

Os pedidos de entrada ficam no arquivo:

~~~text
data/pedidos.json
~~~

Conteúdo utilizado no cenário principal:

~~~json
[
  {
    "produto_id": 1,
    "quantidade": 2
  },
  {
    "produto_id": 2,
    "quantidade": 2
  },
  {
    "produto_id": 3,
    "quantidade": 5
  },
  {
    "produto_id": 4,
    "quantidade": 1
  }
]
~~~

Esses pedidos cobrem os principais cenários do desafio:

| Pedido | Cenário | Resultado esperado |
|---|---|---|
| Produto 1, quantidade 2 | Há estoque suficiente | Pedido `pago` e estoque reduzido |
| Produto 2, quantidade 2 | Há estoque suficiente | Pedido `pago` e estoque reduzido |
| Produto 3, quantidade 5 | Quantidade maior que o estoque | Pedido `cancelado_sem_estoque` |
| Produto 4, quantidade 1 | Produto com estoque zero | Pedido `cancelado_sem_estoque` |

---

## Regras de negócio

Para cada pedido recebido:

1. O sistema valida o `produto_id` e a `quantidade`.
2. O produto é buscado no banco de dados.
3. Se o produto não existir, o pedido não é gravado.
4. Se a quantidade for inválida, o pedido não é gravado.
5. Se houver estoque suficiente:
   - o estoque do produto é reduzido;
   - o pedido é registrado com status `pago`.
6. Se não houver estoque suficiente:
   - o estoque não é alterado;
   - o pedido é registrado com status `cancelado_sem_estoque`.

---

## Exemplo do resultado esperado

Após executar:

~~~bash
php scripts/setup.php
php scripts/process-orders.php
~~~

O estoque esperado dos produtos é:

| Produto | Estoque inicial | Pedido | Estoque final |
|---|---:|---:|---:|
| Camiseta Básica | 10 | 2 | 8 |
| Tênis Casual | 5 | 2 | 3 |
| Mochila Urbana | 3 | 5 | 3 |
| Garrafa Térmica | 0 | 1 | 0 |

A mochila permanece com estoque 3 porque o pedido solicitou 5 unidades, mas não havia estoque suficiente. A garrafa permanece com estoque 0 porque já iniciou sem estoque.

---

## Consulta de faturamento

O faturamento total considera apenas pedidos com status `pago`.

A consulta soma a quantidade de cada pedido pago multiplicada pelo preço do produto relacionado:

~~~sql
SELECT COALESCE(SUM(p.quantidade * pr.preco), 0) AS faturamento_total
FROM pedidos p
INNER JOIN produtos pr ON pr.id = p.produto_id
WHERE p.status = :status;
~~~

No cenário atual:

| Pedido | Cálculo |
|---|---:|
| 2 Camisetas Básicas | 2 × 59.90 = 119.80 |
| 2 Tênis Casual | 2 × 199.90 = 399.80 |
| Total | 519.60 |

Pedidos com status `cancelado_sem_estoque` não entram no faturamento.

---

## Decisões técnicas

### Uso de SQLite

O SQLite foi escolhido para simplificar a execução local do desafio.

Apesar de o enunciado permitir MySQL, o SQLite evita a necessidade de configurar servidor, usuário, senha ou container. Assim, o avaliador consegue rodar o projeto apenas com PHP e Composer instalados.

O banco é criado automaticamente em:

~~~text
storage/database.sqlite
~~~

Esse arquivo não é versionado no Git, pois é gerado localmente pelo script `setup.php`.

---

### Uso de PDO

A comunicação com o banco foi feita com PDO, conforme solicitado no desafio.

As operações de leitura, criação e atualização usam prepared statements para manter as queries organizadas e evitar interpolação direta de valores no SQL.

---

### Separação de responsabilidades

O projeto foi dividido em camadas simples:

| Arquivo | Responsabilidade |
|---|---|
| `Connection.php` | Criar e configurar a conexão com o banco |
| `ProductRepository.php` | Buscar produtos e atualizar estoque |
| `OrderRepository.php` | Criar pedidos e calcular faturamento |
| `OrderProcessor.php` | Centralizar a regra de negócio do processamento |
| `setup.php` | Criar/resetar banco e dados iniciais |
| `process-orders.php` | Ler o JSON e processar pedidos |
| `revenue.php` | Consultar e exibir o faturamento |

Essa separação mantém o projeto simples, mas evita concentrar conexão, SQL, regra de negócio e saída de terminal em um único arquivo.

---

### Transação por pedido

O processamento de cada pedido válido utiliza transação.

A baixa de estoque e a criação do pedido fazem parte da mesma operação de negócio. Caso ocorra uma falha inesperada durante o processamento, a transação é revertida para evitar inconsistência entre estoque e pedidos.

Fluxo simplificado:

~~~text
validar entrada
abrir transacao
buscar produto
verificar estoque
baixar estoque, se aplicavel
criar pedido
confirmar transacao
~~~

---

## Validações implementadas

Além do fluxo principal pedido no desafio, o processamento também valida:

- `produto_id` ausente ou inválido;
- `quantidade` ausente ou inválida;
- produto inexistente no banco.

Nesses casos, o pedido não é gravado, pois não existe uma relação válida com um produto cadastrado.

---

## Arquivos gerados localmente

Durante a execução, o projeto gera localmente:

~~~text
storage/database.sqlite
~~~

Esse arquivo representa o banco SQLite criado pelo setup e está ignorado no Git.

A pasta `vendor/` também não é versionada, pois é gerada pelo Composer.

---

## Observações sobre cenário real

Em um e-commerce real, alguns pontos poderiam ser evoluídos, como:

- controle mais robusto de concorrência;
- reserva de estoque;
- logs persistentes;
- testes automatizados;
- processamento assíncrono por filas;
- histórico de preço do produto no momento da compra;
- auditoria de alterações de estoque.

Para o escopo do desafio, a solução prioriza clareza, organização, uso de PHP puro com PDO, SQL e consistência básica com transação por pedido.