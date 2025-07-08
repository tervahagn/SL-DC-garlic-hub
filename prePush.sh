#!/bin/bash

php vendor/bin/phpunit --coverage-html public/clover/ --coverage-clover  public/clover/clover.xml
vendor/bin/phpstan analyze
vendor/bin/coverage-badge public/clover/clover.xml misc/coverage.svg coverage