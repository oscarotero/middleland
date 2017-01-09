# Middleland

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-scrutinizer]][link-scrutinizer]

Simple (but powerful) PSR-15 middleware dispatcher:

Example:

```php
use Middleland\Dispatcher;

$dispatcher = new Dispatcher([
	new Middleware1(),
	new Middleware2(),
	new Middleware3(),

	//You can nest middleware frames
	new Dispatcher([
		new Middleware4(),
		new Middleware5(),
	]),

	//Or use closures
	function ($request, $next) {
		$response = $next($request);
		return $response->withHeader('X-Foo', 'Bar');
	},

	//USE AN ARRAY TO ADD CONDITIONS:

	//This middleware is processed only in paths starting by "/admin"
	['/admin', new MiddlewareAdmin()],

	//and this is processed in DEV 
	[ENV === 'DEV', new MiddlewareAdmin()],

	//we can create more custom matchers
	[new RequestIsHttps(), new MiddlewareHttps()]

	//And use several for each middleware component
	[ENV === 'DEV', new RequestIsHttps(), new MiddlewareHttps()],
]);

$response = $dispatcher->dispatch(new Request());
```

## How to create matchers

Just use the `Middleland\Matchers\MatcherInterface`. Example:

```php
use Middleland\Matchers\MatcherInterface;
use Psr\Http\Message\ServerRequestInterface;

class IsAjax implements MatcherInterface
{
    public function match(ServerRequestInterface $request): bool
    {
    	return $request->getHeaderLine('X-Requested-With') === 'xmlhttprequest';
	}
}
```

---

Please see [CHANGELOG](CHANGELOG.md) for more information about recent changes.

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/oscarotero/middleland.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/middlewares/oscarotero/middleland.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/g/oscarotero/middleland.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/oscarotero/middleland
[link-travis]: https://travis-ci.org/oscarotero/middleland
[link-scrutinizer]: https://scrutinizer-ci.com/g/oscarotero/middleland
