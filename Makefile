phpcsfixer-audit: vendor ## Run php-cs-fixer audit
	@$(PHP) ./vendor/bin/php-cs-fixer fix --diff --dry-run --no-interaction --ansi --verbose

phpcsfixer-fix: vendor ## Run php-cs-fixer fix
	@$(PHP) ./vendor/bin/php-cs-fixer fix --verbose
