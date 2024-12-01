<p align="center">
    <img src="./public/images/forumify.svg" width="250" height="250">
</p>

<div align="center">
    
[![Latest Stable Version](https://poser.pugx.org/forumify/forumify-platform/v)](https://packagist.org/packages/forumify/forumify-platform)
[![Total Downloads](https://poser.pugx.org/forumify/forumify-platform/downloads)](https://packagist.org/packages/forumify/forumify-platform)
[![License](https://poser.pugx.org/forumify/forumify-platform/license)](https://packagist.org/packages/forumify/forumify-platform)
[![PHP Version Require](https://poser.pugx.org/forumify/forumify-platform/require/php)](https://packagist.org/packages/forumify/forumify-platform)
![PHPStan](https://img.shields.io/badge/CodeStyle-PSR12-green.svg)
![PHPStan](https://img.shields.io/badge/PHPStan-level%205-yellow.svg)
    
</div>

# Forumify Platform

**A modern open-source forum experience**

Forumify platform is a community platform that brings forums back to the modern era. Built on top of [Symfony](https://symfony.com/what-is-symfony), and using a simple barebones front-end, it's easy to get into for both community owners and developers alike.

## Project Overview

Forumify consists of several different open-source repositories, **forumify-platform** being the main one.

- [Forumify Platform](https://github.com/forumify/forumify-platform): The platform itself.
- [Forumify Docs](https://github.com/forumify/forumify-docs): Official documentation.
- [Forumify Production Template](https://github.com/forumify/forumify-production-template): A template to easily launch forumify.
- [Forumify Docker Image](https://github.com/forumify/forumify-docker): A docker image that wraps the production template.
- [Flex Recipes](https://github.com/forumify/flex-recipes): Overrides standard Symfony bundle configurations.

### Customizing the platform

Learn how to customize your own forumify [here](https://docs.forumify.net/guides/customization/introduction).

If you want created something you think others may benefit from, and you would like to share your customizations, you can move your customization to a plugin and publish it on packagist and promote it on the [forumify marketplace](https://forumify.net/marketplace).

### Contributing

When you believe you've found a bug or think the platform could be expanded with a new feature. You can start by creating an issue [here on GitHub](https://github.com/forumify/forumify-platform/issues).

The easiest way to contribute to forumify is by using the [production template](https://github.com/forumify/forumify-production-template) and installing the platform with composer using a symlink.

Add the following to the template's `composer.json`:
```json
    "repositories": [
        {
            "type": "path",
            "url": "../forumify-platform"
        }
    ]
```

Now when you run `composer install`, the platform will be symlinked and any changes you do will immediatly be available in your project.

#### Code quality

The following checks are performed when you create a pull request:
- Tests: [PHPUnit](https://phpunit.de/)
- CodeStyle: checked by [PHPCS](https://github.com/PHPCSStandards/PHP_CodeSniffer/), using the [PSR-12 codestyle](https://www.php-fig.org/psr/psr-12/)
- Static analysis: checked by [PHPStan](https://phpstan.org/)

You can avoid failing builds by running these tools locally:

- PHPCS: `./vendor/bin/phpcs`
- PHPStan: `./vendor/bin/phpstan analyze`
