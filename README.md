# Simple upload handle plugin for CakePHP3

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.txt)
[![Build Status](https://img.shields.io/travis/dala00/cakephp-simple-upload/master.svg?style=flat-square)](https://travis-ci.org/dala00/cakephp-simple-upload)
[![Coverage Status](https://img.shields.io/codecov/c/github/dala00/cakephp-simple-upload.svg?style=flat-square)](https://codecov.io/github/dala00/cakephp-simple-upload)

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
Plugin::load('Dala00/Upload');
```

## Usage

Load UploadBehavior with options.

```php
class SomeTable extends Table {
	public function initialize(array $config) {
		$this->addBehavior('Dala00/Upload.Upload', [
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

```php
// In Controller
public $helpers = ['Dala00/Upload.Upload'];
```
```php
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
```php
// Output hidden tag with UploadHelper on templates
<?= $this->Upload->hidden($entity, $fieldName) ?>
```

## Licence

[MIT](https://github.com/dala00/cakephp-simple-upload/blob/master/LICENCE)
