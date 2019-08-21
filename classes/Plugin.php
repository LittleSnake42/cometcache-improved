<?php

namespace CometCacheImproved;

class Plugin
{
    private static $_instance;

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new Plugin();
        }
        return self::$_instance;
    }

    public function init()
    {
        if (!function_exists('is_plugin_active')) {
            require_once __DIR__ . '/../../../../wp-admin/includes/plugin.php';
        }
        if (\is_plugin_active('comet-cache/comet-cache.php')) {
            add_action('wp_before_admin_bar_render', [$this, 'addAdminBarLink'], 99);
            add_action('deleted_post', [$this, 'autoClearCacheOnDeletion'], 99);
            add_action('transition_post_status', [$this, 'autoClearCacheOnTransitionPostStatus'], 99, 3);
            add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
            add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
            add_action('admin_head', [$this, 'adminBarMetaTags'], 0);
            add_action('admin_head', [$this, 'adminBarMetaTags'], 0);

        }
    }

    public function enqueueScripts()
    {
        // Si on est admin mais pas super admin (deja présent) -> et donc sur un multisite
        if (is_multisite() && current_user_can('manage_options') && !current_user_can('create_sites')) {
            $base_src = plugin_dir_url(__DIR__) . '../comet-cache';
            $js_src   = $base_src . '/src/client-s/js/admin-bar.min.js';
            $css_src  = $base_src . '/src/client-s/css/admin-bar.min.css';

            wp_enqueue_script('comet_cache-admin-bar', $js_src, ['jquery', 'admin-bar'], false, true);
            wp_enqueue_style('comet_cache-admin-bar', $css_src);
        }
    }

    public function addAdminBarLink()
    {
        /**
         * @var \WP_Admin_Bar $wp_admin_bar
         */
        global $wp_admin_bar;

        // Si on est admin mais pas super admin (deja présent) -> et donc sur un multisite
        if (is_multisite() && current_user_can('manage_options') && !current_user_can('create_sites')) {

            $wp_admin_bar->remove_menu('comet_cache-clear'); //todo try
            $wp_admin_bar->add_menu(
                [
                    'parent' => 'top-secondary',
                    'id'     => 'comet_cache-clear',
                    'title'  => __('Clear Cache', 'comet-cache'),
                    'href'   => '#',
                    'meta'   => [
                        'title'    => __('Clear Cache', 'comet-cache'),
                        'class'    => '-clear',
                        'tabindex' => -1,
                    ],
                ]
            );
        }
    }

    public function autoClearCacheOnDeletion($post_id)
    {
        if (class_exists('\WebSharks\CometCache\Classes\Plugin') && isset($GLOBALS['comet_cache'])) {
            $cometcache = $GLOBALS['comet_cache'];
            try {
                $cometcache->clearCache(true);
            } catch (\Exception $e) {
            }
        }
    }

    public function autoClearCacheOnTransitionPostStatus($new_status, $old_status, $post)
    {
        if (class_exists('\WebSharks\CometCache\Classes\Plugin') && isset($GLOBALS['comet_cache'])) {
            if ($new_status != 'publish' && $old_status == 'publish') {
                $cometcache = $GLOBALS['comet_cache'];
                try {
                    $cometcache->clearCache(true);
                } catch (\Exception $e) {
                }
            }
        }
    }

    public function adminBarMetaTags()
    {
        $vars = [
            '_wpnonce'                 => wp_create_nonce(),
            'isMultisite'              => is_multisite(),
            'currentUserHasCap'        => true,
            'currentUserHasNetworkCap' => true,
            'htmlCompressorEnabled'    => true,
            'ajaxURL'                  => site_url('/wp-load.php', is_ssl() ? 'https' : 'http'),
            'i18n'                     => [
                'name'             => 'Comet Cache Improved',
                'perSymbol'        => __('%', 'comet-cache'),
                'file'             => __('file', 'comet-cache'),
                'files'            => __('files', 'comet-cache'),
                'pageCache'        => __('Page Cache', 'comet-cache'),
                'htmlCompressor'   => __('HTML Compressor', 'comet-cache'),
                'currentTotal'     => __('Current Total', 'comet-cache'),
                'currentSite'      => __('Current Site', 'comet-cache'),
                'xDayHigh'         => __('%s Day High', 'comet-cache'),
                'enterSpecificUrl' => __('Enter a specific URL to clear the cache for that page:', 'comet-cache'),
            ],
        ];
        echo '<meta property="' . esc_attr('comet_cache') . ':admin-bar-vars" content="data-json"' .
            ' data-json="' . esc_attr(json_encode($vars)) . '" id="' . esc_attr('comet_cache') . '-admin-bar-vars" />' . "\n";
    }
}
