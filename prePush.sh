#!/bin/bash

php vendor/bin/phpunit --coverage-html public/clover/
vendor/bin/phpstan analyze