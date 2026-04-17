## Code quality

The following checks are performed when you create a pull request:
- Tests: [PHPUnit](https://phpunit.de/)
- CodeStyle: checked by [PHPCS](https://github.com/PHPCSStandards/PHP_CodeSniffer/), using the [PSR-12 codestyle](https://www.php-fig.org/psr/psr-12/)
- Static analysis: checked by [PHPStan](https://phpstan.org/)

You can avoid failing builds by running these tools locally:

- PHPUnit: `./vendor/bin/phpunit`
- PHPCS: `./vendor/bin/phpcs`
- PHPStan: `./vendor/bin/phpstan analyze`

## AI Tools

We have a basic copilot instructions file in `.github/copilot-instructions.md`. This can also be used by other tools, or be
helpful in general for newcommers to understand our conventions and code style.

**[!] WE DO NOT ACCEPT FULLY AI GENERATED PRs!**

All pull requests and issues shall be submitted **BY HUMANS**, and any conversation that comes from it must be with a human.
While we welcome contributors of any level, using any tools, we will not be wasting our time wading through AI agent slop.
If we suspect we're talking to a chatbot, you will be banned from any further interactions with our repository.
