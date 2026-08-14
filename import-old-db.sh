#!/bin/bash
# =============================================================
#  import-old-db.sh
#  Run this on the production server to import old DB data.
#  Usage:
#    bash import-old-db.sh /path/to/skincollection-structure-data.sql
#    bash import-old-db.sh /path/to/skincollection-structure-data.sql --clinic-id=2
# =============================================================

set -e

PROJECT_DIR="/www/wwwroot/default/skincollection"
OLD_DB_NAME="skincollection_old"
CLINIC_ID=1
SQL_FILE=""

# Parse arguments
for arg in "$@"; do
  case $arg in
    --clinic-id=*)
      CLINIC_ID="${arg#*=}"
      ;;
    --old-db=*)
      OLD_DB_NAME="${arg#*=}"
      ;;
    *.sql)
      SQL_FILE="$arg"
      ;;
  esac
done

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo ""
echo "=============================================="
echo "  Skin Collections — Old DB Import"
echo "=============================================="
echo "  SQL file   : ${SQL_FILE:-'(skip — use existing)'}"
echo "  Old DB     : $OLD_DB_NAME"
echo "  Clinic ID  : $CLINIC_ID"
echo "  Project dir: $PROJECT_DIR"
echo "=============================================="
echo ""

cd "$PROJECT_DIR" || { echo -e "${RED}Cannot cd to $PROJECT_DIR${NC}"; exit 1; }

# ---------- Step 1: Run any pending migrations ----------
echo -e "${GREEN}[1/5] Running migrations...${NC}"
sudo -u www php artisan migrate --force
echo ""

# ---------- Step 2: Load the SQL dump ----------
if [ -n "$SQL_FILE" ]; then
  echo -e "${GREEN}[2/5] Creating old database '$OLD_DB_NAME'...${NC}"
  read -s -p "MySQL root password: " MYSQL_PASS
  echo ""

  mysql -u root -p"$MYSQL_PASS" -e \
    "CREATE DATABASE IF NOT EXISTS \`$OLD_DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

  echo -e "${GREEN}      Loading SQL dump (this may take a minute)...${NC}"
  mysql -u root -p"$MYSQL_PASS" "$OLD_DB_NAME" < "$SQL_FILE"
  echo "      ✓ SQL dump loaded."

  # Grant the Laravel DB user access to old DB
  DB_USER=$(grep "^DB_USERNAME=" .env | cut -d '=' -f2)
  DB_PASS=$(grep "^DB_PASSWORD=" .env | cut -d '=' -f2)
  if [ -n "$DB_USER" ]; then
    echo "      Granting access to '$DB_USER' on '$OLD_DB_NAME'..."
    mysql -u root -p"$MYSQL_PASS" -e \
      "GRANT ALL PRIVILEGES ON \`$OLD_DB_NAME\`.* TO '$DB_USER'@'localhost'; FLUSH PRIVILEGES;" 2>/dev/null || true
  fi
else
  echo -e "${YELLOW}[2/5] Skipping SQL dump load (no .sql file provided).${NC}"
  echo "      Assuming '$OLD_DB_NAME' already exists on this server."
fi
echo ""

# ---------- Step 3: Dry run ----------
echo -e "${GREEN}[3/5] Running dry run...${NC}"
sudo -u www php artisan import:old-db \
  --old-db="$OLD_DB_NAME" \
  --clinic-id="$CLINIC_ID" \
  --dry-run \
  --no-interaction
echo ""

# ---------- Step 4: Confirm and run real import ----------
read -p "Dry run complete. Run the REAL import now? [y/N] " CONFIRM
if [[ "$CONFIRM" != "y" && "$CONFIRM" != "Y" ]]; then
  echo "Aborted. Run the real import manually with:"
  echo "  php artisan import:old-db --old-db=$OLD_DB_NAME --clinic-id=$CLINIC_ID --force"
  exit 0
fi

echo ""
echo -e "${GREEN}[4/5] Running real import...${NC}"
sudo -u www php artisan import:old-db \
  --old-db="$OLD_DB_NAME" \
  --clinic-id="$CLINIC_ID" \
  --force \
  --no-interaction
echo ""

# ---------- Step 5: Clear caches ----------
echo -e "${GREEN}[5/5] Clearing caches...${NC}"
sudo -u www php artisan cache:clear
sudo -u www php artisan view:clear
sudo -u www php artisan config:clear
echo ""

# ---------- Summary ----------
echo "=============================================="
echo -e "${GREEN}  Import complete!${NC}"
echo "=============================================="
echo ""
echo "Row counts:"
sudo -u www php artisan tinker --execute="
collect(['patients','photos','weights','appointments','pharmacies',
         'purchases','treatments','records','invoices','invoice_items','sales','users'])
->each(fn(\\\$t) => print(sprintf('  %-20s %d'.PHP_EOL, \\\$t, DB::table(\\\$t)->count())));
" 2>/dev/null
echo ""
