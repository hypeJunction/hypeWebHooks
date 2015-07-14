<?php

if (is_null(elgg_get_plugin_setting('hmac_key', 'hypeWebHooks'))) {
	elgg_set_plugin_setting('hmac_key', 'z' . _elgg_services()->crypto->getRandomString(31), 'hypeWebHooks');
}