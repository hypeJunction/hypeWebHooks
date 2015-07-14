<?php
$entity = elgg_extract('entity', $vars);
?>

<div>
	<label><?php echo elgg_echo('webhooks:settings:payload_urls') ?></label>
	<?php
	echo elgg_view('input/plaintext', array(
		'name' => 'params[payload_urls]',
		'value' => $entity->payload_urls,
	));
	?>
</div>

<div>
	<label><?php echo elgg_echo('webhooks:settings:hmac_key') ?></label>
	<?php
	echo elgg_view('input/text', array(
		'name' => 'params[hmac_key]',
		'value' => $entity->hmac_key,
	));
	?>
</div>
