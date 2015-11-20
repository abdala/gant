# Generic API client

## Resources

* [User Guide][docs-guide] – For both getting started and in-depth SDK usage information
* [API Docs][docs-api] – For details about operations, parameters, and responses
* [Blog][sdk-blog] – Tips & tricks, articles, and announcements
* [Sample Project][sdk-sample] - A quick, sample project to help get you started

## Features

* Provides easy-to-use HTTP client
* Is built on [Guzzle][guzzle-docs], and utilizes many of its features,
  including persistent connections, asynchronous requests, middlewares, etc.
* Provides convenience features including easy result pagination via
  [Paginators][docs-paginators], [Waiters][docs-waiters], and simple
  [Result objects][docs-results].
* Provides a multipart uploader tool

## Install

```bash
composer require abdala/generic-api-client
```

## Quick Examples

### CloudFlare Client

Sample project: https://github.com/abdala/cloudflare-client

```php
<?php
$client = new CloudFlare\Client([
    'version' => '4',
    'key' => 'your key',
    'email' => 'email@domain.com'
]);
echo $client->listZones();
```

### Related Projects

* [AWS Service Provider for Laravel][mod-laravel]
* [AWS SDK ZF2 Module][mod-zf2]
* [AWS Service Provider for Silex][mod-silex]
* [AWS SDK Bundle for Symfony][mod-symfony]
* [Guzzle Version 6][guzzle-docs] – PHP HTTP client and framework

[sdk-website]: http://aws.amazon.com/sdkforphp
[sdk-forum]: https://forums.aws.amazon.com/forum.jspa?forumID=80
[sdk-issues]: https://github.com/aws/aws-sdk-php/issues
[sdk-license]: http://aws.amazon.com/apache2.0/
[sdk-blog]: http://blogs.aws.amazon.com/php
[sdk-twitter]: https://twitter.com/awsforphp
[sdk-sample]: http://aws.amazon.com/developers/getting-started/php

[docs-api]: http://docs.aws.amazon.com/aws-sdk-php/v3/api/index.html
[docs-guide]: http://docs.aws.amazon.com/aws-sdk-php/v3/guide/index.html
[docs-paginators]: http://docs.aws.amazon.com/aws-sdk-php/v3/guide/guide/paginators.html
[docs-waiters]: http://docs.aws.amazon.com/aws-sdk-php/v3/guide/guide/waiters.html
[docs-results]: http://docs.aws.amazon.com/aws-sdk-php/v3/guide/getting-started/basic-usage.html#result-objects

[aws]: http://aws.amazon.com
[guzzle-docs]: http://guzzlephp.org
[composer]: http://getcomposer.org
[packagist]: http://packagist.org
[psr-7]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-7-http-message.md
[psr-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md
[psr-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[psr-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md

[mod-laravel]: https://github.com/aws/aws-sdk-php-laravel
[mod-zf2]: https://github.com/aws/aws-sdk-php-zf2
[mod-silex]: https://github.com/aws/aws-sdk-php-silex
[mod-symfony]: https://github.com/aws/aws-sdk-php-symfony
