# MvcCore Extension - Nette Tracy Adapter

[![Latest Stable Version](https://img.shields.io/badge/Stable-v3.2.0-brightgreen.svg?style=plastic)](https://github.com/mvccore/example-helloworld/releases)
[![License](https://img.shields.io/badge/Licence-BSD-brightgreen.svg?style=plastic)](https://mvccore.github.io/docs/mvccore/3.0.0/LICENCE.md)
![PHP Version](https://img.shields.io/badge/PHP->=5.3-brightgreen.svg?style=plastic)

Extension for MvcCore_Debug class to use nette/tracy library.

## Installation
```shell
composer require mvccore/ext-tracy
```

## Usage
Add this to Bootstrap.php or to very application beginning:
```php
MvcCore::GetInstance()->SetDebugClass(MvcCoreExt_Tracy::class);
```
