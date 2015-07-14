<?php

/**
 * Web hooks for Elgg
 *
 * @package hypeJunction
 * @subpackage hypeWebHooks
 *
 * @author Ismayil Khayredinov <ismayil.khayredinov@gmail.com>
 */
elgg_register_event_handler('init', 'system', 'webhooks_init');

/**
 * Init
 * @return void
 */
function webhooks_init() {
	
	$events = array('create', 'created', 'update', 'delete', 'enable', 'disable', 'join', 'leave', 'login', 'profileudpate');
	$events = elgg_trigger_plugin_hook('events', 'webhooks', null, $events);
	foreach ($events as $event) {
		elgg_register_event_handler($event, 'all', 'webhooks_send', 999);
		elgg_register_event_handler("$event:after", 'all', 'webhooks_send', 999);
	}
}

/**
 * Send event data to payload URLs
 *
 * @param string $event Event name
 * @param string $type  Event type
 * @param mixed  $obj   Event object
 * @return void
 */
function webhooks_send($event, $type, $obj) {

	$hmac_key = elgg_get_plugin_setting('hmac_key', 'hypeWebHooks');
	$payload_urls = explode(PHP_EOL, elgg_get_plugin_setting('payload_urls', 'hypeWebHooks', ''));

	if (empty($hmac_key) || empty($payload_urls)) {
		return;
	}

	$data = json_encode(array(
		'event' => $event,
		'type' => $type,
		'data' => webhooks_prepare_value($obj),
	));
	$length = strlen($data);

	$opts = array(
		'http' => array(
			'method' => 'POST',
			'header' => "Content-type: application/json\r\nContent-Length: $length\r\n",
			'content' => $data,
			'timeout' => 60,
			'ignore_errors' => true,
		)
	);

	$context = stream_context_create($opts);

	foreach ($payload_urls as $url) {
		$ts = time();
		$hmac = hash_hmac('sha256', $data, $hmac_key);
		$signed_url = elgg_http_add_url_query_elements($url, array(
			'hmac' => $hmac,
		));
		$post = file_get_contents($signed_url, false, $context);
		if ($post) {
			error_log("Webhooks (payload sent): $data");
		} else {
			error_log("Webhooks (error): [$url] $post");
		}
	}
}

/**
 * Prepares value for payload
 * 
 * @param mixed $value Value to prepare
 * @return mixed
 */
function webhooks_prepare_value($value = null) {

	$return = array();

	if ($value instanceof ElggEntity) {
		$return[] = array(
			'type' => $value->getType(),
			'subtype' => $value->getSubtype(),
			'guid' => $value->guid,
		);
	} else if ($value instanceof ElggRelationship || $value instanceof ElggExtender || $value instanceof ElggRiverItem) {
		$return[] = array(
			'type' => $value->getType(),
			'subtype' => $value->getSubtype(),
			'id' => $value->id,
		);
	} else if (is_array($value)) {
		foreach ($value as $key => $val) {
			$return[$key] = webhooks_prepare_value($val);
		}
	} else {
		$return[] = $value;
	}

	return $return;
}
