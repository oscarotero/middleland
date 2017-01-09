# Middleland\Dispatcher

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
	[new RequestIsHttpsMatcher(), new MiddlewareHttps()]

	//And use several custom matchers
	[ENV === 'DEV', new RequestIsHttpsMatcher(), new MiddlewareHttps()],
]);

$response = $dispatcher->dispatch(new Request());
```
