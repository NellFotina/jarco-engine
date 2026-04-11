<?php
/*
Plugin Name: Jarco Engine
Description: Управление продуктами Jarco с интеграцией WooCommerce (только вариативные товары)
Author: Nelia Fotina
Version: 1.7
Requires PHP: 7.4
Text Domain: jarco-engine
Domain Path: /languages
*/

if (!defined('ABSPATH')) {
    exit;
}

// Подключаем файл с Meta Box
require_once plugin_dir_path(__FILE__) . 'jarco-engine-meta-boxes.php';
require_once plugin_dir_path(__FILE__) . 'inc/plugin-update-config.php';

class JarcoPlugin {
    public function __construct() {
        // Увеличить количество товаров на странице архива
        add_filter('loop_shop_per_page', function($posts) {
            if (is_tax('pa_lineika')) {
                return 12; // Количество товаров
            }
            return $posts;
        }, 20);        
        // Управление колонками в админке
        add_filter('manage_product_posts_columns', [$this, 'modify_product_columns'], 25);
        // Убираем колонку "Описание" у таксономии pa_lineika
        add_filter('manage_edit-pa_lineika_columns', function($columns) {
            if (isset($columns['description'])) {
                unset($columns['description']);
            }
            return $columns;
        }, 20);        
        // Стили для админки
        add_action('admin_head-edit.php', [$this, 'admin_custom_styles']);
        // Шорткод для вывода всех линков на термы атрибута "pa_lineika"
        add_shortcode('lineika_links', function() {
            $terms = get_terms([
                'taxonomy' => 'pa_lineika',
                'hide_empty' => false,
            ]);

            if (empty($terms) || is_wp_error($terms)) {
                return '<p>' . __('Термы не найдены', 'jarco-engine') . '</p>';
            }

            $output = '<ul class="lineika-links">';
            foreach ($terms as $term) {
                $url = get_term_link($term, 'pa_lineika');
                if (!is_wp_error($url)) {
                    $output .= '<li><a href="' . esc_url($url) . '">' . esc_html($term->name) . '</a></li>';
                }
            }
            $output .= '</ul>';

            return $output;
        });        
    }
    
    /**
     * Загрузка переводов плагина
     */
    public function load_textdomain() {
        load_plugin_textdomain('jarco-engine', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /**
     * Модификация колонок в списке товаров
     */
    public function modify_product_columns($columns) {
        unset($columns['product_tag']);
        unset($columns['sku']);
        unset($columns['taxonomy-product_brand']);
        return $columns;
    }

    /**
     * Стили для админки
     */
    public function admin_custom_styles() {
        $screen = get_current_screen();
        if ($screen && $screen->post_type === 'product') {
            echo '<style>
                table.wp-list-table .column-product_cat, table.wp-list-table .column-product_tag {
                    width: 7% !important;
                }
            </style>';
        }
    }
}

class Simple_Nav_Menu_Walker extends Walker_Nav_Menu {
    function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
        $output .= '<div class="menu-brand"><a href="' . $item->url . '" class="brand-name">' . $item->title . '</a></div>';
    }
}

// Инициализация плагина на правильном хуке
add_action('init', function() {
    $plugin = new JarcoPlugin();
    $plugin->load_textdomain(); // Загружаем переводы только на init
}, 20);