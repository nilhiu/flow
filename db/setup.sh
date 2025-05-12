#!/bin/bash

if ! command -v sqlite3 &> /dev/null; then
    echo "Error: Required dependancy 'sqlite3' is not installed"
fi

DB_NAME="sqlite.db"
MIGRATIONS_DIR="migrations"

if [ ! -d "$MIGRATIONS_DIR" ]; then
  echo "Error: Migrations directory '$MIGRATIONS_DIR' not found"
  exit 1
fi

MIGRATION_FILES=$(find "$MIGRATIONS_DIR" -maxdepth 1 -name "*.sql" | sort)

if [ -z "$MIGRATION_FILES" ]; then
  echo "No SQL migration files found in '$MIGRATIONS_DIR'."
  exit 0
fi

if [ -f "$DB_NAME" ]; then
  read -p "Database file '$DB_NAME' already exists. Overwrite? (y/n) " -n 1 -r
  echo
  if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "Overwriting existing database..."
    rm "$DB_NAME"
  else
    echo "Database creation aborted."
    exit 0
  fi
fi

echo "Creating database '$DB_NAME' and applying migrations from '$MIGRATIONS_DIR'..."

sqlite3 "$DB_NAME" ""

for MIGRATION_FILE in $MIGRATION_FILES; do
  echo "Applying migration: $MIGRATION_FILE"
  sqlite3 "$DB_NAME" ".read $MIGRATION_FILE"

  if [ $? -ne 0 ]; then
    echo "Error applying migration '$MIGRATION_FILE'."
    echo "Database creation failed."
    exit 1
  fi
done

echo "All migrations applied successfully."
echo "Database '$DB_NAME' is ready."

exit 0
