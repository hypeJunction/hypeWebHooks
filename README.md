hypeWebHooks
============
![Elgg 1.11](https://img.shields.io/badge/Elgg-1.11.x-orange.svg?style=flat-square)

Dispatches basic webhooks informing external services about content updates in Elgg.

## Payloads

Webhooks are sent as POST data. Request body contains JSON with the details of the event.

```sh
POST /webhooks HTTP/1.1

Content-Type: application/json
Content-Length: 84

{"event":"update","type":"user","data":[{"type":"user","subtype":"","guid":350335}]}
```

## Processing Web Hooks

Requests are signed with sha256 HMAC, which can be used to validate the payload.
HMAC is added to the payload URL as a query parameter, e.g.
```http://example.com/webhooks?hmac=a86745592dcfb40d522d958b7bc528b53247566ca5e7dac2c6f1529e3d095655```
Key used to generate HMAC hashes can be set in plugin settings.

Sample processor on another Elgg site would look similar to this:

```php

elgg_register_page_handler('webhooks', 'webhooks_page_handler');

/**
 * Accept payload
 * @return bool
 */
function webhooks_page_handler() {

	$hmac_key = elgg_get_plugin_setting('hmac_key', 'hypeWebHooks');

	$hmac = get_input('hmac');

	$payload = file_get_contents("php://input");

	if (hash_hmac('sha256', $payload, $hmac_key) !== $hmac) {
		header('HTTP/1.0 400 Bad Request');
		echo "HMAC validation failed";
		exit;
	} else {
		error_log("Webhooks (payload received): $payload");

		// do stuff
		$data = json_decode($payload);

		header('HTTP/1.0 200 OK');
		echo "Payload received";
		exit;
	}
}
```

## Acknowledgements

* Plugin has been partially sponsored by Bodyology School of Massage.


