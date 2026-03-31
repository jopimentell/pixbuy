-- init-db/01-init.sql
-- Apenas estrutura, sem dados de usuário

CREATE DATABASE IF NOT EXISTS pixbuy_db;
USE pixbuy_db;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de configuração PIX
CREATE TABLE IF NOT EXISTS config_pix (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave_pix VARCHAR(255) NOT NULL,
    tipo_chave VARCHAR(50) DEFAULT 'telefone',
    nome_titular VARCHAR(255) NOT NULL,
    cidade VARCHAR(255),
    valor_padrao DECIMAL(10,2) DEFAULT NULL,
    info_adicional TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de produtos
CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) UNIQUE NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    valor DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
);

-- Tabela de pedidos
CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    nome_cliente VARCHAR(255) NOT NULL,
    email_cliente VARCHAR(255) NOT NULL,
    cpf_cliente VARCHAR(14) NOT NULL,
    telefone_cliente VARCHAR(20) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid', 'expired', 'cancelled') DEFAULT 'pending',
    pix_qr_code TEXT,
    pix_codigo TEXT,
    pix_txid VARCHAR(100),
    data_pagamento DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
);

-- Tabela de logs
CREATE TABLE IF NOT EXISTS logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    acao VARCHAR(100) NOT NULL,
    descricao TEXT,
    ip VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Apenas configuração PIX (sem usuário)
INSERT INTO config_pix (chave_pix, tipo_chave, nome_titular, cidade, info_adicional) 
SELECT '+5599991313341', 'telefone', 'JO B PIMENTEL', 'CODO', 'Pagamento via Pix - PixBuy'
WHERE NOT EXISTS (SELECT 1 FROM config_pix);