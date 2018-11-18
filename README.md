# php-class-facebook
PHP image or text post on facebook as mobile login with username and password.

----------
## Installation

### Using Composer

```sh
composer require atiksoftware/php-class-facebook
```

```php
require __DIR__.'/../vendor/autoload.php';

use \Atiksoftware\Facebook\Facebook;
$fb = new Facebook();
```
#### _login and post data_
```php
	try { 
		$fb->login("username","password");
	} catch (Exception $e) {
		echo $e->getMessage(); 
	}
	
	try { 
		$fb->post("pageid","message_text","image_file_path");
	} catch (Exception $e) {
		echo $e->getMessage(); 
	} 
```
