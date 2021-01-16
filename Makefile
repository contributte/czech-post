.PHONY: install qa cs csf phpstan tests coverage-clover coverage-html

install:
	composer update

qa: phpstan cs

cs:
ifdef GITHUB_ACTION
	vendor/bin/codesniffer -q --report=checkstyle src tests  | cs2pr
else
	vendor/bin/codesniffer src tests
endif

csf:
	vendor/bin/codefixer src tests

phpstan:
	vendor/bin/phpstan analyse -l max -c phpstan.neon src

tests:
	vendor/bin/tester -s -p php --colors 1 -C tests/cases

coverage-clover:
	phpdbg -qrr vendor/bin/phpunit tests --colors=always -c tests/coverage.xml

coverage-html:
	phpdbg -qrr vendor/bin/phpunit tests --colors=always -c tests/coverage.xml
