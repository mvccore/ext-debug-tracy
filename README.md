# MvcCore - Extension - Debug - Nette Tracy Adapter

[![Latest Stable Version](https://img.shields.io/badge/Stable-v5.1.0-brightgreen.svg?style=plastic)](https://github.com/mvccore/ext-debug-tracy/releases)
[![License](https://img.shields.io/badge/License-BSD%203-brightgreen.svg?style=plastic)](https://mvccore.github.io/docs/mvccore/5.0.0/LICENSE.md)
![PHP Version](https://img.shields.io/badge/PHP->=5.4-brightgreen.svg?style=plastic)

MvcCore Debug Extension to replace internal MvcCore variables dumping with Nette Tracy library (`tracy/tracy`).

## Installation
```shell
composer require mvccore/ext-debug-tracy
```

## Usage
Add this to `Bootstrap.php` or to very application beginning:
```php
\MvcCore\Application::GetInstance()->SetDebugClass('\\MvcCore\\Ext\\Debugs\\Tracy');
```
