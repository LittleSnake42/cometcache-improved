<?php
/*
Plugin Name: CometCacheImproved - Addon for Comet Cache
Description: Auto flush cache on post deletion. Also add adminbar menu for administrators on multisite network (by default, only super admin can clear cache)
Version:     1.0.0
Author:      LittleSnake42
Author URI:  https://github.com/LittleSnake42/
*/

require_once __DIR__ . '/classes/Plugin.php';

CometCacheImproved\Plugin::getInstance()->init();