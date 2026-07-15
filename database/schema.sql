PRAGMA foreign_keys = ON;

DROP TABLE IF EXISTS pedidos;
DROP TABLE IF EXISTS produtos;

CREATE TABLE produtos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    preco REAL NOT NULL,
    estoque INTEGER NOT NULL
);

CREATE TABLE pedidos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    produto_id INTEGER NOT NULL,
    quantidade INTEGER NOT NULL,
    status TEXT NOT NULL,
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
);
