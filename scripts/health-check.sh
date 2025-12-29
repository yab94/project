#!/bin/bash

# Health Check Script
# Vérifie que l'application est correctement déployée et fonctionne

set -e

echo "🏥 Health Check - CRM Application"
echo "=================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

ERRORS=0
WARNINGS=0

# Check 1: PHP Version
echo -n "✓ Checking PHP version... "
PHP_VERSION=$(php -r "echo PHP_VERSION;")
if [[ $(echo "$PHP_VERSION" | cut -d. -f1) -ge 8 ]]; then
    echo -e "${GREEN}OK${NC} ($PHP_VERSION)"
else
    echo -e "${RED}FAILED${NC} (Need PHP 8.x, got $PHP_VERSION)"
    ERRORS=$((ERRORS + 1))
fi

# Check 2: Required PHP extensions
echo -n "✓ Checking PHP extensions... "
REQUIRED_EXTS=("pdo" "pdo_mysql" "mbstring" "intl")
MISSING_EXTS=()

for ext in "${REQUIRED_EXTS[@]}"; do
    if ! php -m | grep -q "^$ext$"; then
        MISSING_EXTS+=("$ext")
    fi
done

if [ ${#MISSING_EXTS[@]} -eq 0 ]; then
    echo -e "${GREEN}OK${NC}"
else
    echo -e "${RED}FAILED${NC} (Missing: ${MISSING_EXTS[*]})"
    ERRORS=$((ERRORS + 1))
fi

# Check 3: Composer dependencies
echo -n "✓ Checking Composer dependencies... "
if [ -d "vendor" ] && [ -f "vendor/autoload.php" ]; then
    echo -e "${GREEN}OK${NC}"
else
    echo -e "${RED}FAILED${NC} (Run: composer install)"
    ERRORS=$((ERRORS + 1))
fi

# Check 4: Directory permissions
echo -n "✓ Checking directory permissions... "
if [ -w "." ]; then
    echo -e "${GREEN}OK${NC}"
else
    echo -e "${YELLOW}WARNING${NC} (Directory not writable)"
    WARNINGS=$((WARNINGS + 1))
fi

# Check 5: Database connection
echo -n "✓ Checking database connection... "
if [ -f ".env" ]; then
    DB_CHECK=$(php -r "
        \$env = parse_ini_file('.env');
        try {
            \$pdo = new PDO(
                \"mysql:host={\$env['DB_HOST']};port={\$env['DB_PORT']};dbname={\$env['DB_NAME']}\",
                \$env['DB_USER'],
                \$env['DB_PASSWORD']
            );
            echo 'OK';
        } catch (PDOException \$e) {
            echo 'FAILED: ' . \$e->getMessage();
        }
    " 2>&1)
    
    if [[ "$DB_CHECK" == "OK" ]]; then
        echo -e "${GREEN}OK${NC}"
    else
        echo -e "${RED}FAILED${NC} ($DB_CHECK)"
        ERRORS=$((ERRORS + 1))
    fi
else
    echo -e "${YELLOW}WARNING${NC} (.env file not found)"
    WARNINGS=$((WARNINGS + 1))
fi

# Check 6: HTTP Server
echo -n "✓ Checking web server... "
if curl -s -o /dev/null -w "%{http_code}" http://localhost:8080 > /dev/null 2>&1; then
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8080)
    if [ "$HTTP_CODE" -eq 200 ]; then
        echo -e "${GREEN}OK${NC} (HTTP $HTTP_CODE)"
    else
        echo -e "${YELLOW}WARNING${NC} (HTTP $HTTP_CODE)"
        WARNINGS=$((WARNINGS + 1))
    fi
else
    echo -e "${YELLOW}WARNING${NC} (Server not responding on :8080)"
    WARNINGS=$((WARNINGS + 1))
fi

# Check 7: Required tables
echo -n "✓ Checking database tables... "
if [ -f ".env" ]; then
    TABLES_CHECK=$(php -r "
        \$env = parse_ini_file('.env');
        try {
            \$pdo = new PDO(
                \"mysql:host={\$env['DB_HOST']};port={\$env['DB_PORT']};dbname={\$env['DB_NAME']}\",
                \$env['DB_USER'],
                \$env['DB_PASSWORD']
            );
            \$stmt = \$pdo->query('SHOW TABLES');
            \$tables = \$stmt->fetchAll(PDO::FETCH_COLUMN);
            \$required = ['bank_accounts', 'bank_transactions'];
            \$missing = array_diff(\$required, \$tables);
            if (empty(\$missing)) {
                echo 'OK';
            } else {
                echo 'MISSING: ' . implode(', ', \$missing);
            }
        } catch (PDOException \$e) {
            echo 'FAILED: ' . \$e->getMessage();
        }
    " 2>&1)
    
    if [[ "$TABLES_CHECK" == "OK" ]]; then
        echo -e "${GREEN}OK${NC}"
    elif [[ "$TABLES_CHECK" == MISSING* ]]; then
        echo -e "${YELLOW}WARNING${NC} ($TABLES_CHECK)"
        WARNINGS=$((WARNINGS + 1))
    else
        echo -e "${RED}FAILED${NC} ($TABLES_CHECK)"
        ERRORS=$((ERRORS + 1))
    fi
else
    echo -e "${YELLOW}SKIPPED${NC}"
fi

# Summary
echo ""
echo "=================================="
echo "Summary:"
echo -e "  Errors:   ${RED}$ERRORS${NC}"
echo -e "  Warnings: ${YELLOW}$WARNINGS${NC}"

if [ $ERRORS -eq 0 ]; then
    echo -e "\n${GREEN}✓ Application is healthy!${NC}"
    exit 0
else
    echo -e "\n${RED}✗ Application has critical issues!${NC}"
    exit 1
fi
