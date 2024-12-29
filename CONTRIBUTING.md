## Code quality

The following checks are performed when you create a pull request:
- Tests: [PHPUnit](https://phpunit.de/)
- CodeStyle: checked by [PHPCS](https://github.com/PHPCSStandards/PHP_CodeSniffer/), using the [PSR-12 codestyle](https://www.php-fig.org/psr/psr-12/)
- Static analysis: checked by [PHPStan](https://phpstan.org/)

You can avoid failing builds by running these tools locally:

- PHPUnit: `./vendor/bin/phpunit`
- PHPCS: `./vendor/bin/phpcs`
- PHPStan: `./vendor/bin/phpstan analyze`
