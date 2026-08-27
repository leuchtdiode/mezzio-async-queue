# Laminas-AsyncQueue

Laminas module for an async queue

## Monitoring

`AsyncQueue\Health\StaleItemsCheck` reports unhealthy as soon as an item was due for processing longer than a
configurable threshold ago but is still pending or processing, which means the queue is not processed anymore.
Pending and processing items are reported separately, because they mean different things: stale pending items
mean nothing drains the queue at all, a stale processing item means a worker died after claiming it and nobody
ever picks it up again. Failed and successful items are ignored, because those are terminal anyway.

Due time is `processAfter`, the same date the processor selects by, so items which are deliberately scheduled
for later or which backed off after a retry are not reported before they are actually due.

It requires `leuchtdiode/mezzio-monitoring` and registers itself, so the application only has to enable the
health endpoint:

```php
'monitoring' => [
	'health' => [
		'enabled' => true,
	],
],
```

The threshold defaults to 60 minutes and can be adapted:

```php
'async-queue' => [
	'monitoring' => [
		'staleItems' => [
			'thresholdMinutes' => 60,
		],
	],
],
```
