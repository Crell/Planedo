
# Force update the DB schema, to hell with existing data.
schema:
	php bin/console doctrine:schema:update --force --complete

# Create a migration file to evolve the schema.
migration:
	php bin/console doctrine:migrations:diff

# Run pending migrations.
migrate:
	php bin/console doctrine:migrations:migrate

# Load fixtures into the main DB.
fixtures: schema
	php bin/console doctrine:fixtures:load --group=manual

# Create the test database.
test-db:
	php bin/console --env=test doctrine:database:create

# Update the schema in the test database.
test-schema:
	php bin/console --env=test doctrine:schema:update --force --complete

# Load fixtures into the test DB.
test-fixtures: test-schema
	php bin/console --env=test doctrine:fixtures:load --group=tests --group=manual
