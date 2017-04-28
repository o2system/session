<<<<<<< HEAD
=======
# O2System Session
>>>>>>> origin/master
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

Installation
------------
The best way to install [O2System Session](https://packagist.org/packages/o2system/session) is to use [Composer](http://getcomposer.org)
```
composer require o2system/session
```

Manual Installation
------------
1. Download the [master zip file](https://github.com/o2system/session/archive/master.zip).
2. Extract into your project folder.
3. Require the autoload.php file.<br>
```php
require your_project_folder_path/session/src/autoload.php
```

Usage Example
-------------
```php
use O2System\Session;

// Initialize O2Session Instance using APC Storage Engine
$session = new Session(['handler' => 'apc']);

// Set session userdata
$session->set('foo', ['bar' => 'something']);

// Get session userdata
$foo = $session->get('foo');
```

Documentation is available on this repository [wiki](https://github.com/o2system/session/wiki) or visit this repository [github page](https://o2system.github.io/session).

Ideas and Suggestions
---------------------
Please kindly mail us at [o2system.framework@gmail.com](mailto:o2system.framework@gmail.com).

Bugs and Issues
---------------
Please kindly submit your [issues at Github](http://github.com/o2system/session/issues) so we can track all the issues along development and send a [pull request](http://github.com/o2system/session/pulls) to this repository.

System Requirements
-------------------
- PHP 5.6+
- [Composer](http://getcomposer.org)

Credits
-------
* Founder and Lead Projects: [Steeven Andrian Salim](http://steevenz.com)
* Github Pages Designer and Writer: [Teguh Rianto](http://teguhrianto.tk)

Supported By
------------
* [Zend Technologies Ltd.](http://zend.com)
