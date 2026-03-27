#!/bin/bash

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${CYAN}========================================${NC}"
echo -e "${CYAN}     INICIANDO TESTES AUTOMATIZADOS${NC}"
echo -e "${CYAN}     Projeto: PixBuy${NC}"
echo -e "${CYAN}========================================${NC}"
echo ""

ERROS=0

# TESTE 1: Verificar containers
echo -e "${YELLOW}[TESTE 1] Verificando containers Docker...${NC}"
if docker ps | grep -q pixbuy_web && docker ps | grep -q pixbuy_db; then
    echo -e "${GREEN}  Web e DB estão rodando${NC}"
else
    echo -e "${RED}  ERRO: Containers não estão rodando${NC}"
    ERROS=$((ERROS+1))
fi

# TESTE 2: Verificar site
echo ""
echo -e "${YELLOW}[TESTE 2] Verificando site (http://localhost:8080)...${NC}"
if curl -s -f -o /dev/null http://localhost:8080; then
    echo -e "${GREEN}   Site está acessível${NC}"
else
    echo -e "${RED}  ERRO: Site não respondeu${NC}"
    ERROS=$((ERROS+1))
fi

# TESTE 3: Verificar phpMyAdmin
echo ""
echo -e "${YELLOW}[TESTE 3] Verificando phpMyAdmin (http://localhost:8081)...${NC}"
if curl -s -f -o /dev/null http://localhost:8081; then
    echo -e "${GREEN}   phpMyAdmin está acessível${NC}"
else
    echo -e "${RED}  ERRO: phpMyAdmin não respondeu${NC}"
    ERROS=$((ERROS+1))
fi

# TESTE 4: Verificar volume
echo ""
echo -e "${YELLOW}[TESTE 4] Verificando volume de persistência...${NC}"
if docker volume ls | grep -q pixbuy_db_data; then
    echo -e "${GREEN}   Volume db_data encontrado${NC}"
else
    echo -e "${RED}  ERRO: Volume db_data não encontrado${NC}"
    ERROS=$((ERROS+1))
fi

# TESTE 5: Verificar arquivos PHP
echo ""
echo -e "${YELLOW}[TESTE 5] Verificando arquivos do projeto...${NC}"
ARQUIVOS=(
    "www/index.php"
    "www/config/database.php"
    "www/admin/index.php"
    "www/admin/login.php"
)

for arquivo in "${ARQUIVOS[@]}"; do
    if [ -f "$arquivo" ]; then
        echo -e "${GREEN}   $arquivo encontrado${NC}"
    else
        echo -e "${RED}  ERRO: $arquivo não encontrado${NC}"
        ERROS=$((ERROS+1))
    fi
done

# RESUMO
echo ""
echo -e "${CYAN}========================================${NC}"
echo -e "${CYAN}        RESULTADO DOS TESTES${NC}"
echo -e "${CYAN}========================================${NC}"

if [ $ERROS -eq 0 ]; then
    echo -e "${GREEN} TODOS OS TESTES PASSARAM!${NC}"
    echo -e "${GREEN} Sistema está funcionando corretamente${NC}"
    exit 0
else
    echo -e "${RED}$ERROS TESTE(S) FALHARAM!${NC}"
    echo -e "${RED}Verifique os erros acima e corrija${NC}"
    exit 1
fi