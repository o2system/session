[![Latest Stable Version](https://poser.pugx.org/o2system/session/v/stable)](https://packagist.org/packages/o2system/session) [![Total Downloads](https://poser.pugx.org/o2system/session/downloads)](https://packagist.org/packages/o2system/session) [![Latest Unstable Version](https://poser.pugx.org/o2system/session/v/unstable)](https://packagist.org/packages/o2system/session) [![License](https://poser.pugx.org/o2system/session/license)](https://packagist.org/packages/o2system/session)

[O2System Session](https://github.com/o2system/session) is an Open Source Native PHP Session Management Handler Library. 
Allows different cache storage platform to be used. 
All but file-based storage require specific server requirements, and a Fatal Exception will be thrown if server requirements are not met.

[O2System Session](https://github.com/o2system/session) is build for working more powerful with [O2System PHP Framework](https://github.com/o2system/o2system), but also can be integrated with other frameworks as standalone PHP Classes Library with limited features.

### Supported Storage Engines Handlers
| Engine | Support | Tested  | &nbsp; |
| ------------- |:-------------:|:-----:| ----- |
| APC | ```Yes``` | ```Yes``` | http://php.net/apc |
| File | ```Yes``` | ```Yes``` | http://php.net/file |
| Memcached | ```Yes``` | ```Yes``` | http://php.net/memcached |
| Redis | ```Yes``` | ```Yes``` | http://redis.io |
| Wincache | ```Yes``` | ```Yes``` | http://php.net/wincache |
| XCache | ```Yes``` | ```Yes``` | https://xcache.lighttpd.net/ |
| Zend OPCache | ```Yes``` | ```Yes``` | http://php.net/opcache |

### Composer Instalation
The best way to install O2Session is to use [Composer](https://getcomposer.org)
```
composer require o2system/session
```
> Packagist: [https://packagist.org/packages/o2system/session](https://packagist.org/packages/o2system/session)

### Usage Example
```php
use O2System\Session;

// Initialize O2Session Instance using APC Storage Engine
$session = new Session(['handler' => 'apc']);

// Set session userdata
$session->set('foo', ['bar' => 'something']);

// Get session userdata
$foo = $session->get('foo');
```
> More details at the [Documentation](https://www.gitbook.com/book/o2system/session).

### Ideas and Suggestions
Please kindly mail us at [o2system.framework@gmail.com](mailto:o2system.framework@gmail.com])

### Bugs and Issues
Please kindly submit your [issues at Github](https://github.com/o2system/session/issues) so we can track all the issues along development.

### System Requirements
- PHP 5.4+
- [Composer](https://getcomposer.org)
- [O2System Core](https://github.com/o2system/core)
- [O2System Database](https://github.com/o2system/db) (optional - required when using database handler)

### Credits
|Role|Name|
|----|----|
|Founder and Lead Projects|[Steeven Andrian Salim](http://steevenz.com)|
|Documentation|[Steeven Andrian Salim](http://steevenz.com), [Ayun G. Aribowo](http://ayun.co)|
| Github Pages Designer and Writer | [Teguh Rianto](http://teguhrianto.tk)

### Supported By
* Zend 