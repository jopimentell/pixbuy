# 🚀 PixBuy - Sistema de Pedidos com PIX

## TDE - Prática DevOps
**Centro Universitário de Ciências e Tecnologia do Maranhão – UNIFACEMA**  
**Curso:** Análise e Desenvolvimento de Sistemas - 5º Período  
**Professor:** Marcos Gomes da Silva Rocha  
**Data de Entrega:** 31/03/2026


## Sobre o Projeto

O **PixBuy** é um sistema de geração de pedidos com pagamento via PIX, desenvolvido como Trabalho Discente Efetivo (TDE) para a disciplina de Prática DevOps. O projeto demonstra a aplicação prática dos conceitos de conteinerização, versionamento e padronização de ambientes utilizando Docker e Git.

### Objetivo do TDE
Desenvolver um ambiente de microserviços conteinerizado com:
- ✅ **3 containers interconectados** (Web, API, Banco de Dados)
- ✅ **Persistência de dados** com Docker Volumes
- ✅ **Controle de versão** com Git
- ✅ **Automação de testes** para validação do ambiente


## 🏗️ Arquitetura do Projeto

```
┌─────────────────────────────────────────────────────────┐
│                    DOCKER COMPOSE                        │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │   CONTAINER  │  │   CONTAINER  │  │   CONTAINER  │  │
│  │      WEB     │  │      DB      │  │   phpMyAdmin │  │
│  │   PHP 8.2    │◄─┤   MySQL 8.0  │  │              │  │
│  │   Apache     │  │              │  │              │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
│         │                 │                  │          │
│         ▼                 ▼                  ▼          │
│  Porta: 8080      Porta: 3307        Porta: 8081       │
│  (Site)           (MySQL)            (phpMyAdmin)      │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

### 📦 Containers

| Serviço | Imagem | Porta | Descrição |
|-||-|
| **web** | PHP 8.2 + Apache | 8080 | Aplicação PHP (site e API) |
| **db** | MySQL 8.0 | 3307 | Banco de dados persistente |
| **phpmyadmin** | phpMyAdmin | 8081 | Interface gráfica do banco |


## 📁 Estrutura do Projeto

```
pixbuy/
├── docker-compose.yml          # Orquestração dos containers
├── Dockerfile                   # Configuração do PHP com mysqli
├── tests/
│   ├── test.ps1                 # Testes automatizados (Windows)
│   └── test.sh                  # Testes automatizados (Linux/Mac)
├── www/                         # Código fonte da aplicação
│   ├── index.php                # Página inicial
│   ├── config/
│   │   └── database.php         # Configuração do banco
│   ├── admin/                   # Área administrativa
│   │   ├── index.php            # Dashboard
│   │   ├── login.php            # Login
│   │   ├── produtos.php         # Gerenciar produtos
│   │   ├── pedidos.php          # Gerenciar pedidos
│   │   ├── config-pix.php       # Configuração PIX
│   │   └── logs.php             # Logs do sistema
│   ├── produto/
│   │   └── ver.php              # Página do produto
│   ├── pagamento/
│   │   ├── pix.php              # Geração do PIX
│   │   └── confirmar.php        # Confirmação de pagamento
│   └── api/
│       └── pedido-detalhes.php  # API para detalhes
└── README.md                    # Este arquivo
```


## Como Executar o Projeto

### Pré-requisitos
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) instalado
- Git instalado
- Windows 11 (com WSL2) / Linux / macOS

### Passo a Passo

#### 1. Clone o repositório
```bash
git clone https://github.com/jopimentell/pixbuy.git
cd pixbuy
```

#### 2. Suba os containers
```bash
docker-compose up -d --build
```

#### 3. Verifique se tudo está rodando
```bash
docker-compose ps
```

Saída esperada:
```
NAME                STATUS          PORTS
pixbuy_web          Up              0.0.0.0:8080->80/tcp
pixbuy_db           Up              0.0.0.0:3307->3306/tcp
pixbuy_phpmyadmin   Up              0.0.0.0:8081->80/tcp
```

#### 4. Acesse o sistema

| Serviço | URL | Credenciais |
|-||
| **Site PixBuy** | http://localhost:8080 | - |
| **phpMyAdmin** | http://localhost:8081 | Servidor: `db`<br>Usuário: `pixbuy_user`<br>Senha: `pixbuy123` |
| **MySQL (externo)** | localhost:3307 | Usuário: `pixbuy_user`<br>Senha: `pixbuy123` |

#### 5. Acesse a área administrativa
- URL: http://localhost:8080/admin/login.php
- Usuário: `admin`
- Senha: `admin123`


## Validação dos Requisitos do TDE

### 1. ✅ Docker Compose com 3 Containers

```yaml
services:
  web:      # Servidor Web (PHP + Apache)
  db:       # Banco de Dados (MySQL)
  phpmyadmin: # Interface de Gerenciamento
```

**Verificação:**
```bash
docker-compose ps
# Deve mostrar 3 containers rodando
```

### 2. Mapeamento de Portas

| Container | Porta Host | Porta Container | Acesso |
|-|-|-|
| web | 8080 | 80 | http://localhost:8080 |
| db | 3307 | 3306 | localhost:3307 |
| phpmyadmin | 8081 | 80 | http://localhost:8081 |

**Verificação:**
```bash
docker ps --format "table {{.Names}}\t{{.Ports}}"
```

### 3. Persistência de Dados (Volumes)

```yaml
volumes:
  - db_data:/var/lib/mysql  # Dados do banco persistem

volumes:
  db_data:  # Volume nomeado
```

**Teste de persistência:**
```bash
# 1. Crie um produto no sistema
# 2. Remova os containers
docker-compose down
# 3. Suba novamente
docker-compose up -d
# 4. Verifique - o produto ainda existe!
```

**Verificação:**
```bash
docker volume ls | grep pixbuy_db_data
# Deve mostrar o volume
```

### 4. Dockerfile Personalizado

```dockerfile
FROM php:8.2-apache
RUN docker-php-ext-install mysqli
RUN a2enmod rewrite
```

**Verificação:**
```bash
docker exec pixbuy_web php -m | grep mysqli
# Deve mostrar "mysqli"
```

### 5. Controle de Versão (Git)

```bash
git log --oneline
# Deve mostrar histórico de commits
```

**Estrutura do repositório no GitHub:**
- Commits regulares
- README documentado
- Código fonte organizado

### 6. Automação de Testes

Execute os testes automatizados:

**Windows (PowerShell):**
```powershell
.\tests\test.ps1
```

**Linux/Mac:**
```bash
chmod +x tests/test.sh
./tests/test.sh
```

**O que é testado:**
| Teste | Descrição |
||-|
| 1 | Containers estão rodando |
| 2 | Site está acessível (HTTP 200) |
| 3 | MySQL está conectando |
| 4 | phpMyAdmin está acessível |
| 5 | Volume de persistência existe |
| 6 | Arquivos do projeto existem |



## 🧪 Executando os Testes

### Teste Manual Rápido

```bash
# Verificar containers
docker-compose ps

# Testar site
curl http://localhost:8080

# Testar conexão MySQL via PHP
curl http://localhost:8080/test-db.php

# Verificar volume
docker volume ls | grep pixbuy_db_data
```

### Teste Automatizado Completo

```bash
# Windows
powershell -ExecutionPolicy Bypass -File .\tests\test.ps1

# Saída esperada:
# ✅ TODOS OS TESTES PASSARAM!
# ✅ Sistema está funcionando corretamente
```

## Funcionalidades do Sistema

### Área Pública
- ✅ Listagem de produtos
- ✅ Página individual do produto
- ✅ Formulário de compra (nome, email, CPF, telefone)
- ✅ Geração de QR Code PIX
- ✅ Confirmação de pedido com WhatsApp

### Área Administrativa
- ✅ Login seguro
- ✅ Dashboard com estatísticas
- ✅ CRUD de produtos
- ✅ Gerenciamento de pedidos
- ✅ Alteração de status (pendente/confirmado/cancelado)
- ✅ Visualização de detalhes do pedido
- ✅ Configuração de dados PIX
- ✅ Logs do sistema


## 🐛 Solução de Problemas

### Portas já em uso
Se as portas 8080, 8081 ou 3307 estiverem ocupadas, edite o `docker-compose.yml`:

```yaml
web:
  ports:
    - "8082:80"  # Mude para outra porta
```

### Erro "mysqli not found"
O Dockerfile já instala a extensão. Se persistir:
```bash
docker-compose build --no-cache
docker-compose up -d
```



## 📚 Referências

- [Documentação Docker](https://docs.docker.com/)
- [Documentação PHP](https://www.php.net/docs.php)
- [Documentação MySQL](https://dev.mysql.com/doc/)
- [Pix helper](https://www.mco2.com.br/artigos/aprenda-como-gerar-qr-code-e-codigo-pix-em-php.html)


## 👨‍🎓 Autor

**Nome:** Jó Pimentel 
**Curso:** Análise e Desenvolvimento de Sistemas  
**Período:** 4º  
**Disciplina:** Prática DevOps  
**Professor:** Marcos Gomes da Silva Rocha


