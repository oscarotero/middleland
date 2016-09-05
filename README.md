# Middleland\Dispatcher

Simple middleware dispatcher for server and client side:

Example:

```php
use Middleland\ClientDispatcher;

$dispatcher = new ClientDispatcher([
	new Middleware1(),
	new Middleware2(),
	new Middleware3(),

	//You can nest middleware frames
	new ClientDispatcher([
		new Middleware4(),
		new Middleware5(),
	]),

	new Middleware6(),
]);

$response = $dispatcher->dispatch(new Request());
```