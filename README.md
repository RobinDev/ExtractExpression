# Text Analyser : Expression in a text per Usage

## Install

Via [Packagist](https://packagist.org/packages/ropendev/expression)

## Usage

```php

use \rOpenDev\curl\CurlRequest;

...
// Configure
$test = new \rOpenDev\ExtractExpression\ExtractExpression();
$test->onlyInSentence     = true; // Default value: FALSE
$test->expressionMaxWords = 5;    // Max expression size in words
$test->keepTrail          = 5; // Don't keep trail for less than 3 occurences found ine one text
$test->addContent("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed...");

// Get Results
$test->getExpressions(int max = 0); // @return array with expression => number
$test->getTrail('expression'); // @return array with sentence where we find expression (best with onlyInSentence = true)
$test->getTrails(); // @return array with expression => array trails

$test->getWordNumber(); // @return int
```

## Contribute


### To send a pull resquest

1. Please check if test are still running without error (`phpunit`)
2. Check coding standard before to commit : `php-cs-fixer fix src --rules=@Symfony --verbose && php-cs-fixer fix src --rules='{"array_syntax": {"syntax": "short"}}' --verbose`


### Contributors

* [Robin](https://www.robin-d.fr/) / [Pied Web](https://piedweb.com)
* ...


[![Latest Version](https://img.shields.io/github/tag/RobinDev/ExtractExpression.svg?style=flat&label=release)](https://github.com/RobinDev/ExtractExpression/tags)
[![Build Status](https://img.shields.io/travis/PiedWeb/CMS/master.svg?style=flat)](https://travis-ci.org/RobinDev/ExtractExpression)
[![Quality Score](https://img.shields.io/scrutinizer/g/RobinDev/ExtractExpression.svg?style=flat)](https://scrutinizer-ci.com/g/RobinDev/ExtractExpression)
[![Total Downloads](https://img.shields.io/packagist/dt/ropendev/expression.svg?style=flat)](https://packagist.org/packages/ropendev/expression)
