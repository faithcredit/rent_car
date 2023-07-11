<?php

namespace Duplicator\Utils\CachesPurge;

class CachesPurge
{
    /**
     * purge all and return purge messages
     *
     * @return string[]
     */
    public static function purgeAll()
    {
        $globalMessages = array();
        $items          = array_merge(
            self::getPurgePlugins(),
            self::getPurgeHosts()
        );


        foreach ($items as $item) {
            $message = '';
            $result  = $item->purge($message);
            if (strlen($message) > 0 && $result) {
                $globalMessages[] = $message;
            }
        }

        return $globalMessages;
    }

    /**
     * get list to cache items to purge
     *
     * @return CacheItem[]
     */
    protected static function getPurgePlugins()
    {
        $items   = array();
        $items[] = new CacheItem(
            'Elementor',
            function () {
                return class_exists("\\Elementor\\Plugin");
            },
            function () {
                \Elementor\Plugin::$instance->files_manager->clear_cache(); // @phpstan-ignore-line
            }
        );
        $items[] = new CacheItem(
            'W3 Total Cache',
            function () {
                return function_exists('w3tc_pgcache_flush');
            },
            'w3tc_pgcache_flush' // @phpstan-ignore-line
        );
        $items[] = new CacheItem(
            'WP Super Cache',
            function () {
                return function_exists('wp_cache_clear_cache');
            },
            'wp_cache_clear_cache' // @phpstan-ignore-line
        );
        $items[] = new CacheItem(
            'WP Rocket',
            function () {
                return function_exists('rocket_clean_domain');
            },
            'rocket_clean_domain' // @phpstan-ignore-line
        );
        $items[] = new CacheItem(
            'Fast velocity minify',
            function () {
                return function_exists('fvm_purge_static_files');
            },
            'fvm_purge_static_files' // @phpstan-ignore-line
        );
        $items[] = new CacheItem(
            'Cachify',
            function () {
                return function_exists('cachify_flush_cache');
            },
            'cachify_flush_cache' // @phpstan-ignore-line
        );
        $items[] = new CacheItem(
            'Comet Cache',
            function () {
                return class_exists('\\comet_cache');
            },
            array('\\comet_cache', 'clear')
        );
        $items[] = new CacheItem(
            'Zen Cache',
            function () {
                return class_exists('\\zencache');
            },
            array('\\zencache', 'clear')
        );
        $items[] = new CacheItem(
            'LiteSpeed Cache',
            function () {
                return has_action('litespeed_purge_all');
            },
            function () {
                return  do_action('litespeed_purge_all');
            }
        );
        $items[] = new CacheItem(
            'WP Cloudflare Super Page Cache',
            function () {
                return class_exists('\\SW_CLOUDFLARE_PAGECACHE');
            },
            function () {
                return  do_action("swcfpc_purge_everything");
            }
        );
        $items[] = new CacheItem(
            'Hyper Cache',
            function () {
                return class_exists('\\HyperCache');
            },
            function () {
                return  do_action('autoptimize_action_cachepurged');
            }
        );
        $items[] = new CacheItem(
            'Cache Enabler',
            function () {
                return has_action('ce_clear_cache');
            },
            function () {
                return  do_action('ce_clear_cache');
            }
        );
        $items[] = new CacheItem(
            'WP Fastest Cache',
            function () {
                return function_exists('wpfc_clear_all_cache');
            },
            function () {
                wpfc_clear_all_cache(true); // @phpstan-ignore-line
            }
        );
        $items[] = new CacheItem(
            'Breeze',
            function () {
                return class_exists("\\Breeze_PurgeCache");
            },
            array('\\Breeze_PurgeCache', 'breeze_cache_flush')
        );
        $items[] = new CacheItem(
            'Swift Performance',
            function () {
                return class_exists("\\Swift_Performance_Cache");
            },
            array('\\Swift_Performance_Cache', 'clear_all_cache')
        );
        $items[] = new CacheItem(
            'Hummingbird',
            function () {
                return has_action('wphb_clear_page_cache');
            },
            function () {
                return  do_action('wphb_clear_page_cache');
            }
        );
        $items[] = new CacheItem(
            'WP-Optimize',
            function () {
                return has_action('wpo_cache_flush');
            },
            function () {
                return  do_action('wpo_cache_flush');
            }
        );
        $items[] = new CacheItem(
            'Wordpress default',
            function () {
                return function_exists('wp_cache_flush');
            },
            'wp_cache_flush'
        );
        $items[] = new CacheItem(
            'Wordpress permalinks',
            function () {
                return function_exists('flush_rewrite_rules');
            },
            'flush_rewrite_rules'
        );
        $items[] = new CacheItem(
            'NinjaForms Maintenance Mode',
            function () {
                return class_exists('WPN_Helper') && is_callable('WPN_Helper', 'set_forms_maintenance_mode');  // @phpstan-ignore-line
            },
            array('WPN_Helper', 'set_forms_maintenance_mode')
        );
        return $items;
    }

    /**
     * get list to cache items to purge
     *
     * @return CacheItem[]
     */
    protected static function getPurgeHosts()
    {
        $items   = array();
        $items[] = new CacheItem(
            'Godaddy Managed WordPress Hosting',
            function () {
                return class_exists('\\WPaaS\\Plugin') && method_exists('\\WPass\\Plugin', 'vip');  // @phpstan-ignore-line
            },
            function () {
                $method = 'BAN';
                $url    = home_url();
                $host   = wpraiser_get_domain(); // @phpstan-ignore-line
                $url    = set_url_scheme(str_replace($host, \WPaas\Plugin::vip(), $url), 'http'); // @phpstan-ignore-line
                update_option('gd_system_last_cache_flush', time(), 'no'); # purge apc
                wp_remote_request(
                    esc_url_raw($url),
                    array(
                        'method' => $method,
                        'blocking' => false,
                        'headers' =>
                        array(
                            'Host' => $host
                        )
                    )
                );
            }
        );
        $items[] = new CacheItem(
            'SG Optimizer (Siteground)',
            function () {
                return function_exists('sg_cachepress_purge_everything');
            },
            'sg_cachepress_purge_everything' // @phpstan-ignore-line
        );
        $items[] = new CacheItem(
            'WP Engine',
            function () {
                return (
                    class_exists(\WpeCommon::class) &&
                    (
                        method_exists(\WpeCommon::class, 'purge_memcached') ||  // @phpstan-ignore-line
                        method_exists(\WpeCommon::class, 'purge_varnish_cache')  // @phpstan-ignore-line
                    )
                );
            },
            function () {
                if (method_exists(\WpeCommon::class, 'purge_memcached')) {  // @phpstan-ignore-line
                    \WpeCommon::purge_memcached();
                }
                if (method_exists(\WpeCommon::class, 'purge_varnish_cache')) {  // @phpstan-ignore-line
                    \WpeCommon::purge_varnish_cache();
                }
            }
        );
        $items[] = new CacheItem(
            'Kinsta',
            function () {
                global $kinsta_cache;
                return (
                    (isset($kinsta_cache) &&
                        class_exists('\\Kinsta\\CDN_Enabler')) &&
                    !empty($kinsta_cache->kinsta_cache_purge));
            },
            function () {
                global $kinsta_cache;
                $kinsta_cache->kinsta_cache_purge->purge_complete_caches();
            }
        );
        $items[] = new CacheItem(
            'Pagely',
            function () {
                return class_exists('\\PagelyCachePurge');
            },
            function () {
                $purge_pagely = new \PagelyCachePurge(); // @phpstan-ignore-line
                $purge_pagely->purgeAll(); // @phpstan-ignore-line
            }
        );
        $items[] = new CacheItem(
            'Pressidum',
            function () {
                return defined('WP_NINUKIS_WP_NAME') && class_exists('\\Ninukis_Plugin');
            },
            function () {
                $purge_pressidum = \Ninukis_Plugin::get_instance(); // @phpstan-ignore-line
                $purge_pressidum->purgeAllCaches();
            }
        );

        $items[] = new CacheItem(
            'Pantheon Advanced Page Cache plugin',
            function () {
                return function_exists('pantheon_wp_clear_edge_all');
            },
            'pantheon_wp_clear_edge_all' // @phpstan-ignore-line
        );
        return $items;
    }
}
