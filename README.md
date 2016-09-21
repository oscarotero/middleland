# Middleland\Dispatcher

Simple PSR-15 middleware dispatcher:

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

	new Middleware6(),
]);

$response = $dispatcher->dispatch(new Request());
```
