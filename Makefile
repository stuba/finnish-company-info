run-tests:
	./vendor/bin/phpunit --testdox ./tests/

ecs:
	vendor/bin/ecs check src tests

ecs-fix:
	vendor/bin/ecs check src tests --fix