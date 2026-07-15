# SPEC — Desafio Técnico PHP Júnior

## 1. Objetivo

Desenvolver uma ferramenta simples em PHP 8.x para simular o processamento de pedidos de um mini e-commerce.

O sistema deve ler uma lista de novos pedidos, verificar o estoque disponível dos produtos, atualizar o estoque quando possível e registrar cada pedido com o status adequado.

Também deve existir uma consulta para retornar o faturamento total dos pedidos pagos.

---

## 2. Escopo do desafio

### Deve conter

- PHP 8.x.
- Programação Orientada a Objetos.
- Banco de dados SQLite usando PDO.
- Tabela `produtos`.
- Tabela `pedidos`.
- Entrada de pedidos via arquivo JSON.
- Processamento de pedidos por linha de comando.
- Dedução de estoque quando houver disponibilidade.
- Registro de pedido com status `pago` quando houver estoque.
- Registro de pedido com status `cancelado_sem_estoque` quando não houver estoque suficiente.
- Consulta de faturamento total considerando apenas pedidos pagos.
- README com instruções de execução.

### Não deve conter

- Frameworks.
- Laravel.
- WordPress.
- ORMs como Eloquent ou Doctrine.
- Interface web.
- Front-end.
- Integrações externas.

---

## 3. Decisão técnica principal

O banco escolhido será SQLite para simplificar a execução local do avaliador.

Apesar de o desafio aceitar MySQL, o SQLite permite que o projeto rode sem configuração de servidor, usuário, senha ou container, mantendo o uso de SQL e PDO conforme solicitado.

---

## 4. Estrutura prevista

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
├── storage/
│   └── database.sqlite
├── composer.json
├── README.md
└── .gitignore
~~~

---

## 5. Modelagem do banco

### Tabela `produtos`

Campos:

- `id`
- `nome`
- `preco`
- `estoque`

Regras:

- `id` deve ser a chave primária.
- `nome` deve ser obrigatório.
- `preco` deve representar o valor unitário do produto.
- `estoque` deve representar a quantidade disponível.

### Tabela `pedidos`

Campos:

- `id`
- `produto_id`
- `quantidade`
- `status`

Regras:

- `id` deve ser a chave primária.
- `produto_id` deve referenciar um produto.
- `quantidade` deve representar a quantidade solicitada.
- `status` deve ser `pago` ou `cancelado_sem_estoque`.

---

## 6. Fluxo de processamento

Para cada pedido recebido no JSON:

1. Buscar o produto pelo `produto_id`.
2. Verificar se o produto existe.
3. Verificar se a quantidade solicitada é válida.
4. Verificar se há estoque suficiente.
5. Se houver estoque:
   - deduzir a quantidade do estoque do produto;
   - registrar o pedido com status `pago`.
6. Se não houver estoque:
   - registrar o pedido com status `cancelado_sem_estoque`.

---

## 7. Consistência dos dados

O processamento de cada pedido deve ocorrer dentro de uma transação.

Motivo:

A baixa de estoque e a criação do pedido fazem parte da mesma operação de negócio. Caso uma etapa falhe, a outra não deve ficar persistida sozinha, evitando inconsistência entre estoque e pedidos.

Fluxo esperado:

~~~text
beginTransaction
buscar produto
validar estoque
atualizar estoque, se aplicável
criar pedido
commit
~~~

Em caso de erro:

~~~text
rollback
~~~

---

## 8. Entrada de dados

A entrada será feita por um arquivo JSON em:

~~~text
data/pedidos.json
~~~

Formato esperado:

~~~json
[
  {
    "produto_id": 1,
    "quantidade": 2
  },
  {
    "produto_id": 2,
    "quantidade": 5
  }
]
~~~

---

## 9. Scripts CLI

### `scripts/setup.php`

Responsável por:

- criar o banco SQLite;
- executar o schema;
- inserir produtos iniciais de exemplo.

Comando esperado:

~~~bash
php scripts/setup.php
~~~

### `scripts/process-orders.php`

Responsável por:

- ler o arquivo `data/pedidos.json`;
- processar cada pedido;
- exibir no terminal o resultado de cada processamento.

Comando esperado:

~~~bash
php scripts/process-orders.php
~~~

### `scripts/revenue.php`

Responsável por:

- consultar o faturamento total dos pedidos com status `pago`;
- exibir o resultado no terminal.

Comando esperado:

~~~bash
php scripts/revenue.php
~~~

---

## 10. Consulta de faturamento

O faturamento total deve considerar apenas pedidos com status `pago`.

A query deve multiplicar a quantidade do pedido pelo preço atual do produto relacionado.

Exemplo lógico:

~~~sql
SELECT SUM(p.quantidade * pr.preco) AS faturamento_total
FROM pedidos p
INNER JOIN produtos pr ON pr.id = p.produto_id
WHERE p.status = 'pago';
~~~

---

## 11. Critérios de aceite

O projeto será considerado correto quando:

- o banco for criado com as tabelas `produtos` e `pedidos`;
- os produtos iniciais forem inseridos;
- o JSON de pedidos for lido corretamente;
- pedidos com estoque suficiente forem registrados como `pago`;
- pedidos sem estoque suficiente forem registrados como `cancelado_sem_estoque`;
- o estoque for reduzido apenas nos pedidos pagos;
- o faturamento total considerar apenas pedidos pagos;
- o projeto puder ser executado seguindo o README;
- o código estiver organizado em classes;
- o projeto não utilizar frameworks ou ORMs.

---

## 12. Pontos fora do escopo

Não serão implementados nesta versão:

- autenticação;
- painel administrativo;
- front-end;
- API HTTP;
- múltiplos usuários;
- concorrência avançada;
- reserva de estoque;
- logs persistentes;
- testes automatizados obrigatórios.

---

## 13. Observações sobre cenário real

Em um cenário real de e-commerce com múltiplos processamentos simultâneos, seria importante tratar concorrência com maior rigor, usando mecanismos como locks no banco, filas ou controle transacional mais robusto.

Para o desafio, será usada uma transação por pedido para manter a consistência entre a baixa de estoque e o registro do pedido.