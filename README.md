# Simple upload handle plugin for CakePHP3

## Description

* Handle file post to save and upload automatically.
* UploadHelper can output URL and img tag on template.
* Methods for confirm page are available.

## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```
composer require dala00/cakephp-simple-upload
```

And load plugin on bootstrap.php.

```php
Plugin::load('dala00/Upload');
```

## Usage

Load UploadBehavior with options.

```php
<?php
class SomeTable extends Table {
	public function initialize(array $config) {
		$this->addBehavior('Upload.Upload', [
			'fields' => [
				'photo' => [
					'path' => 'webroot{DS}files{DS}{model}{DS}{primaryKey}{DS}{field}{DS}'
				],
			],
		]);
	}
}
```

### UploadHelper
You can output URL or img tag with UploadHelper.

```
(In Controller)
public $helpers = ['Upload.Upload'];
```
```
(In Templates)
<img src="<?= $this->Upload->url($entity, $fieldName) ?>">
or
<?= $this->Upload->image($entity, $fieldName) ?>
<?= $this->Upload->image($entity, $fieldName, $options) ?>
```

### Using confirm page

If you want show confirm page before saving post, next method saves files as cache.

```php
// Call in action when confirm page will be shown
$this->SomeTable->uploadTmpFile($entity);
```
```
// Output hidden tag with UploadHelper on templates
<?= $this->Upload->hidden($entity, $fieldName) ?>
```

## Licence

[MIT](https://github.com/dala00/cakephp-simple-upload/blob/master/LICENCE)
