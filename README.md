<h1 align="center">MarkShust_SimpleData</h1> 

<div align="center">
  <p>Simplifies calling Magento data structures.</p>
  <img src="https://img.shields.io/badge/magento-2.2%20|%202.3-brightgreen.svg?logo=magento&longCache=true&style=flat-square" alt="Supported Magento Versions" />
  <a href="https://packagist.org/packages/markshust/magento2-module-simpledata" target="_blank"><img src="https://img.shields.io/packagist/v/markshust/magento2-module-simpledata.svg?style=flat-square" alt="Latest Stable Version" /></a>
  <a href="https://packagist.org/packages/markshust/magento2-module-simpledata" target="_blank"><img src="https://poser.pugx.org/markshust/magento2-module-simpledata/downloads" alt="Composer Downloads" /></a>
  <a href="https://GitHub.com/Naereen/StrapDown.js/graphs/commit-activity" target="_blank"><img src="https://img.shields.io/badge/maintained%3F-yes-brightgreen.svg?style=flat-square" alt="Maintained - Yes" /></a>
  <a href="https://opensource.org/licenses/MIT" target="_blank"><img src="https://img.shields.io/badge/license-MIT-blue.svg" /></a>
</div>

## Table of contents

- [Summary](#summary)
- [Installation](#installation)
- [Usage](#usage)
- [License](#license)

## Summary

Calling Magento data structures can be confusing. There are many classes available, and knowing which to call and when can be confusing & overwhelming.

This module aims to simplify calling these data structures. All classes are prefixed with `Simple` so they are easy to lookup within IDEs. They also follow a pretty standard naming convention which aligns with Magento's way of naming modules. It also provides a `SimpleDataPatch` class which greatly simplifies writing data patch scripts.

For example, here is a data patch script to update the content of a CMS page with and without `SimpleData`:

![Demo](https://raw.githubusercontent.com/markshust/magento2-module-simpledata/master/docs/demo.png)

## Installation

```sh
composer require markshust/magento2-module-simpledata
bin/magento module:enable MarkShust_SimpleData
bin/magento setup:upgrade
```

## Usage

Here are the signatures of the simplified data structures classes:

`MarkShust\SimpleData\Api\Data\Cms\SimpleBlock`

```php
/**
 * Delete a block from a given identifier and optional store id.
 * @param string $identifier
 * @param int $storeId
 */
public function delete(string $identifier, int $storeId = Store::DEFAULT_STORE_ID): void

/**
 * If the CMS block identifier is found, attempt to update the record.
 * If it is not found, attempt to create a new record.
 * @param array $data
 */
public function save(array $data): void
```

`MarkShust\SimpleData\Api\Data\Cms\SimplePage`

```php
/**
 * Delete a page from a given identifier and optional store id.
 * @param string $identifier
 * @param int $storeId
 */
public function delete(string $identifier, int $storeId = Store::DEFAULT_STORE_ID): void

/**
 * If the CMS page identifier is found, attempt to update the record.
 * If it is not found, attempt to create a new record.
 * @param array $data
 */
public function save(array $data): void
```

### Examples using SimpleDataPatch

#### Create block

```php
<?php
declare(strict_types = 1);

namespace MarkShust\Data\Setup\Patch\Data;

use MarkShust\SimpleData\Setup\Patch\SimpleDataPatch;

class BlockFooBarCreate extends SimpleDataPatch
{
    public function apply()
    {
        $this->block->save([
            'identifier' => 'foo_bar',
            'title' => 'Foo bar',
            'content' => <<<CONTENT
<div class="foo-bar">
    Foo bar
</div>
CONTENT,
        ]);
    }
}
```

#### Delete block

```php
<?php
declare(strict_types = 1);

namespace MarkShust\Data\Setup\Patch\Data;

use MarkShust\SimpleData\Setup\Patch\SimpleDataPatch;

class BlockFooBarDelete extends SimpleDataPatch
{
    public function apply()
    {
        $this->block->delete('foo_bar');
    }
}
```

#### Update block

```php
<?php
declare(strict_types = 1);

namespace MarkShust\Data\Setup\Patch\Data;

use MarkShust\SimpleData\Setup\Patch\SimpleDataPatch;

class BlockFooBarUpdate extends SimpleDataPatch
{
    public function apply()
    {
        $this->block->save([
            'identifier' => 'foo_bar',
            'title' => 'Foo bar 1',
        ]);
    }
}
```

#### Create page

```php
<?php
declare(strict_types = 1);

namespace MarkShust\Data\Setup\Patch\Data;

use MarkShust\SimpleData\Setup\Patch\SimpleDataPatch;

class PageFooBarCreate extends SimpleDataPatch
{
    public function apply()
    {
        $this->page->save([
            'identifier' => 'foo_bar',
            'title' => 'Foo bar',
            'content' => <<<CONTENT
<div class="foo-bar">
    Foo bar
</div>
CONTENT,
        ]);
    }
}
```

#### Update page

```php
<?php
declare(strict_types = 1);

namespace MarkShust\Data\Setup\Patch\Data;

use MarkShust\SimpleData\Setup\Patch\SimpleDataPatch;

class MyDataPatch extends SimpleDataPatch
{
    public function apply()
    {
        $this->page->save([
            'identifier' => 'foo_bar',
            'title' => 'Foo bar 1',
        ]);
    }
}
```

#### Delete page

```php
<?php
declare(strict_types = 1);

namespace MarkShust\Data\Setup\Patch\Data;

use MarkShust\SimpleData\Setup\Patch\SimpleDataPatch;

class PageFooBarDelete extends SimpleDataPatch
{
    public function apply()
    {
        $this->page->delete('foo_bar');
    }
}
```

#### Create or update config

```php
<?php

namespace MarkShust\Data\Setup\Patch\Data;

use MarkShust\SimpleData\Setup\Patch\SimpleDataPatch;

class ConfigFooBarCreate extends SimpleDataPatch
{
    public function apply()
    {
        $this->config->save('foo/bar', 'baz');
    }
}

```

#### Delete config

```php
<?php

namespace MarkShust\Data\Setup\Patch\Data;

use MarkShust\SimpleData\Setup\Patch\SimpleDataPatch;

class ConfigFooBarDelete extends SimpleDataPatch
{
    public function apply()
    {
        $this->config->delete('foo/bar');
    }
}
```

### Example using dependency injection

```php
<?php
declare(strict_types = 1);

namespace MarkShust\SimpleData;

use MarkShust\SimpleData\Api\Cms\SimpleBlock;

class MyClass
{
    /** @var SimpleBlock */
    protected $block;

    /**
     * SimpleDataPatch constructor.
     * @param SimpleBlock $simpleBlock
     */
    public function __construct(
        SimpleBlock $simpleBlock
    ) {
        $this->block = $simpleBlock;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
        $this->block->save([
            'identifier' => 'foo_bar',
            'title' => 'Foo bar',
            'content' => <<<CONTENT
<div class="foo-bar">
    Foo bar
</div>
CONTENT,
        ]);

        // Carry out other actions...
    }
}
```

## License

[MIT](https://opensource.org/licenses/MIT)
