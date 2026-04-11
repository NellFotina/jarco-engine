<?php
/**
 * Файл для работы с Meta Box и кастомными полями WooCommerce
 * Description: Все мета-поля для продуктов и страниц
 */

if (!defined('ABSPATH')) {
    exit;
}

class JarcoMetaBoxes {
    public static function init() {
        // MetaBox для главной страницы
        add_filter('rwmb_meta_boxes', [__CLASS__, 'add_homepage_meta_fields']);

        // Кастомные поля для вариаций WooCommerce
        add_action('woocommerce_product_after_variable_attributes', [__CLASS__, 'variation_fields'], 10, 3);
        add_action('woocommerce_save_product_variation', [__CLASS__, 'save_variation_fields'], 10, 2);

        // Кастомные поля для таксономии pa_lineika
        add_action('pa_lineika_edit_form_fields', [__CLASS__, 'pa_lineika_edit_field']);
        add_action('edited_pa_lineika', [__CLASS__, 'pa_lineika_save_field']);
    }

    /**
     * Кастомные поля для вариаций
     */
    public static function variation_fields($loop, $variation_data, $variation) {        
        // Вес
        woocommerce_wp_text_input([
            'id'    => "_variation_weight[$variation->ID]",
            'label' => 'Вес упаковки (г)',
            'type'  => 'number',
            'value' => get_post_meta($variation->ID, '_variation_weight', true),
        ]);

        // Состав
        woocommerce_wp_textarea_input([
            'id'          => "_variation_ingredients[$variation->ID]",
            'label'       => 'Состав',
            'value'       => get_post_meta($variation->ID, '_variation_ingredients', true),
            'description' => 'Разделяйте ингредиенты запятыми',
        ]);

        echo '<p><strong>Энергетическая ценность (на 100 г)</strong></p>';

        woocommerce_wp_text_input([
            'id'          => "_variation_energy_kj[$variation->ID]",
            'label'       => 'Энергия (кДж)',
            'type'        => 'number',
            'value'       => get_post_meta($variation->ID, '_variation_energy_kj', true),
        ]);

        woocommerce_wp_text_input([
            'id'    => "_variation_energy_kcal[$variation->ID]",
            'label' => 'Калории (ккал)',
            'type'  => 'number',
            'value' => get_post_meta($variation->ID, '_variation_energy_kcal', true),
        ]);

        // Жиры
        woocommerce_wp_text_input([
            'id'    => "_variation_fats_total[$variation->ID]",
            'label' => 'Жиры (всего, г)',
            'type'  => 'number',
            'value' => get_post_meta($variation->ID, '_variation_fats_total', true),
        ]);

        woocommerce_wp_text_input([
            'id'    => "_variation_fats_saturated[$variation->ID]",
            'label' => '→ насыщенные (г)',
            'type'  => 'number',
            'value' => get_post_meta($variation->ID, '_variation_fats_saturated', true),
        ]);

        woocommerce_wp_text_input([
            'id'    => "_variation_fats_mono[$variation->ID]",
            'label' => '→ мононенасыщенные (г)',
            'type'  => 'number',
            'value' => get_post_meta($variation->ID, '_variation_fats_mono', true),
        ]);

        woocommerce_wp_text_input([
            'id'    => "_variation_fats_poly[$variation->ID]",
            'label' => '→ полиненасыщенные (г)',
            'type'  => 'number',
            'value' => get_post_meta($variation->ID, '_variation_fats_poly', true),
        ]);

        // Углеводы
        woocommerce_wp_text_input([
            'id'    => "_variation_carbs_total[$variation->ID]",
            'label' => 'Углеводы (всего, г)',
            'type'  => 'number',
            'value' => get_post_meta($variation->ID, '_variation_carbs_total', true),
        ]);

        woocommerce_wp_text_input([
            'id'    => "_variation_carbs_sugars[$variation->ID]",
            'label' => '→ сахара (г)',
            'type'  => 'number',
            'value' => get_post_meta($variation->ID, '_variation_carbs_sugars', true),
        ]);

        // Белки, соль, клетчатка
        woocommerce_wp_text_input([
            'id'    => "_variation_proteins[$variation->ID]",
            'label' => 'Белки (г)',
            'type'  => 'number',
            'value' => get_post_meta($variation->ID, '_variation_proteins', true),
        ]);

        woocommerce_wp_text_input([
            'id'          => "_variation_salt[$variation->ID]",
            'label'       => 'Соль (г)',
            'type'        => 'number',
            'description' => 'Рассчитывать как Na × 2.5',
            'value'       => get_post_meta($variation->ID, '_variation_salt', true),
        ]);

        woocommerce_wp_text_input([
            'id'    => "_variation_fiber[$variation->ID]",
            'label' => 'Клетчатка (г)',
            'type'  => 'number',
            'value' => get_post_meta($variation->ID, '_variation_fiber', true),
        ]);

        woocommerce_wp_textarea_input([
            'id'    => "_variation_extra[$variation->ID]",
            'label' => 'Примечание',
            'value' => get_post_meta($variation->ID, '_variation_extra', true),
        ]);
    }

    /**
     * Сохранение кастомных полей вариаций
     */
    public static function save_variation_fields($variation_id, $i) {
        $fields = [
            '_variation_weight',
            '_variation_ingredients',
            '_variation_energy_kj',
            '_variation_energy_kcal',
            '_variation_fats_total',
            '_variation_fats_saturated',
            '_variation_fats_mono',
            '_variation_fats_poly',
            '_variation_carbs_total',
            '_variation_carbs_sugars',
            '_variation_proteins',
            '_variation_salt',
            '_variation_fiber',
            '_variation_extra',
        ];

        foreach ($fields as $field) {
            if (isset($_POST[$field][$variation_id])) {
                update_post_meta($variation_id, $field, sanitize_text_field($_POST[$field][$variation_id]));
            }
        }
    }

    /**
     * Мета-поля для главной страницы (через Meta Box)
     */
    public static function add_homepage_meta_fields($meta_boxes) {
        $meta_boxes[] = [
            'title'      => 'Блок Intro',
            'post_types' => ['page'], // можно добавить 'product', если нужно для товаров
            'fields'     => [
                [
                    'name' => 'Заголовок H2 блока Intro',
                    'id'   => '_intro_custom_title',
                    'type' => 'text',
                    'desc' => 'Оставьте пустым, чтобы использовать заголовок страницы или таксономии',
                ],
                [
                    'name' => 'Текст блока Intro',
                    'id'   => '_home_intro_text',
                    'type' => 'wysiwyg',
                ],
            ],
        ];
        return $meta_boxes;
    }

    /**
     * Поле в форме редактирования терма pa_lineika
     */
    public static function pa_lineika_edit_field($term) {
        $custom_h2   = get_term_meta($term->term_id, 'pa_lineika_custom_h2', true);
        $value = get_term_meta($term->term_id, 'pa_lineika_extra_text', true);
        ?>
        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="pa_lineika_custom_h2"><?php _e('Кастомный заголовок H2', 'jarco-engine'); ?></label>
            </th>
            <td>
                <input type="text"
                    name="pa_lineika_custom_h2"
                    id="pa_lineika_custom_h2"
                    value="<?php echo esc_attr($custom_h2); ?>"
                    class="regular-text" />
                <p class="description"><?php _e('Заголовок H2 на странице атрибута для дополнительного текстового поля.', 'jarco-engine'); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="pa_lineika_extra_text"><?php _e('Дополнительный текст', 'jarco-engine'); ?></label>
            </th>
            <td>
                <?php 
                wp_editor(
                    $value,
                    'pa_lineika_extra_text', // уникальный ID редактора
                    [
                        'textarea_name' => 'pa_lineika_extra_text',
                        'textarea_rows' => 10,
                        'media_buttons' => false,
                        'teeny'         => false, // если true — урезанный тулбар, если false — полный
                    ]
                ); 
                ?>
                <p class="description"><?php _e('Этот текст будет выводиться внизу страницы атрибута.', 'jarco-engine'); ?></p>
            </td>
        </tr>
        <?php
    }

    /**
     * Сохранение поля при обновлении терма
     */
    public static function pa_lineika_save_field($term_id) {
        // Кастомный H2
        if (isset($_POST['pa_lineika_custom_h2'])) {
            update_term_meta(
                $term_id,
                'pa_lineika_custom_h2',
                sanitize_text_field($_POST['pa_lineika_custom_h2'])
            );
        }
        // Доп. текст (wysiwyg)
        if (isset($_POST['pa_lineika_extra_text'])) {
            $raw = wp_unslash($_POST['pa_lineika_extra_text']); // убираем слеши
            update_term_meta(
                $term_id,
                'pa_lineika_extra_text',
                wp_kses_post($raw)
            );
        }        
    }
}

JarcoMetaBoxes::init();