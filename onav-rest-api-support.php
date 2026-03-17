<?php
/**
 * Plugin Name: OneNav REST API Meta Fields Support
 * Plugin URI: https://warpnav.com/
 * Description: 为 OneNav 主题的所有自定义文章类型和分类法提供完整的自定义字段（meta）REST API 读写支持，并提供后台开关控制。
 * Version: 2.1.1
 * Author: WarpNav
 * Author URI: https://warpnav.com/
 * Text Domain: onav-rest-api
 *
 * 使用方法：将本文件放入目录 wp-content/plugins/onenav-restapi-sp/，并在后台启用本插件。
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

//======================================================================
// 1. 注册 REST API 路由和字段
//======================================================================

add_action('rest_api_init', 'onav_register_rest_routes');

/**
 * 注册 REST API 路由和字段
 */
function onav_register_rest_routes() {
    $config = onav_get_api_meta_config();
    $options = get_option('onav_rest_api_settings');

    // 获取启用的文章类型，如果未设置则默认全部启用
    $enabled_post_types = isset($options['enabled_post_types']) 
        ? $options['enabled_post_types'] 
        : array_keys($config['post_types']);

    // 获取启用的分类法，如果未设置则默认全部启用
    $enabled_taxonomies = isset($options['enabled_taxonomies']) 
        ? $options['enabled_taxonomies'] 
        : array_keys($config['taxonomies']);

    // 1. 根据设置注册文章类型的 meta 字段
    foreach ($enabled_post_types as $post_type) {
        if (isset($config['post_types'][$post_type])) {
            register_rest_field($post_type, 'onav_meta', [
                'get_callback'    => 'onav_get_post_meta_callback',
                'update_callback' => 'onav_update_post_meta_callback',
                'schema'          => null,
            ]);
        }
    }

    // 2. 根据设置注册分类法的 meta 字段
    foreach ($enabled_taxonomies as $taxonomy) {
        if (isset($config['taxonomies'][$taxonomy])) {
            register_rest_field($taxonomy, 'onav_meta', [
                'get_callback'    => 'onav_get_term_meta_callback',
                'update_callback' => 'onav_update_term_meta_callback',
                'schema'          => null,
            ]);
        }
    }
}

//======================================================================
// 2. 字段配置中心
//======================================================================

/**
 * 集中管理所有需要通过 API 暴露的字段配置
 * 'type' 支持: string, url, textarea, integer, boolean, array, object
 */
function onav_get_api_meta_config() {
    return [
        // 文章类型字段
        'post_types' => [
            'post' => [
                '_seo_title'    => ['type' => 'string'],
                '_seo_metakey'  => ['type' => 'string'],
                '_seo_desc'     => ['type' => 'textarea'],
                '_thumbnail'    => ['type' => 'string'],
                'views'         => ['type' => 'integer'],
                '_like_count'   => ['type' => 'integer'],
            ],
            'page' => [
                '_seo_title'    => ['type' => 'string'],
                '_seo_metakey'  => ['type' => 'string'],
                '_seo_desc'     => ['type' => 'textarea'],
                '_thumbnail'    => ['type' => 'string'],
                'views'         => ['type' => 'integer'],
                '_like_count'   => ['type' => 'integer'],
            ],
            'sites' => [
                '_sites_link'         => ['type' => 'url'],
                '_sites_sescribe'     => ['type' => 'textarea'],
                '_sites_language'     => ['type' => 'string'],
                '_sites_country'      => ['type' => 'string'],
                '_sites_preview'      => ['type' => 'url'],
                '_thumbnail'          => ['type' => 'string'],
                '_seo_title'          => ['type' => 'string'],
                '_seo_metakey'        => ['type' => 'string'],
                '_seo_desc'           => ['type' => 'textarea'],
                '_sites_order'        => ['type' => 'integer'],
                '_sites_type'         => ['type' => 'string'],
                '_wechat_id'          => ['type' => 'string'],
                '_is_min_app'         => ['type' => 'boolean'],
                '_goto'               => ['type' => 'boolean'],
                '_nofollow'           => ['type' => 'boolean'],
                '_spare_sites_link'   => ['type' => 'array'],
                'views'               => ['type' => 'integer'],
                '_like_count'         => ['type' => 'integer'],
            ],
            'app' => [
                '_app_type'       => ['type' => 'string'],
                '_app_name'       => ['type' => 'string'],
                '_app_sescribe'   => ['type' => 'textarea'],
                '_app_ico'        => ['type' => 'string'],
                '_app_platform'   => ['type' => 'array'],
                '_down_formal'    => ['type' => 'url'],
                '_screenshot'     => ['type' => 'array'],
                '_down_default'   => ['type' => 'object'],
                'app_down_list'   => ['type' => 'array'],
                '_seo_title'      => ['type' => 'string'],
                '_seo_metakey'    => ['type' => 'string'],
                '_seo_desc'       => ['type' => 'textarea'],
                'views'           => ['type' => 'integer'],
                '_like_count'     => ['type' => 'integer'],
                '_down_count'     => ['type' => 'integer'],
            ],
            'book' => [
                '_book_type'    => ['type' => 'string'],
                '_thumbnail'    => ['type' => 'string'],
                '_summary'      => ['type' => 'textarea'],
                '_journal'      => ['type' => 'string'],
                '_score_type'   => ['type' => 'string'],
                '_score'        => ['type' => 'string'],
                '_books_data'   => ['type' => 'array'],
                '_buy_list'     => ['type' => 'array'],
                '_down_list'    => ['type' => 'array'],
                '_seo_title'    => ['type' => 'string'],
                '_seo_metakey'  => ['type' => 'string'],
                '_seo_desc'     => ['type' => 'textarea'],
                'views'         => ['type' => 'integer'],
                '_like_count'   => ['type' => 'integer'],
            ],
            'bulletin' => [
                '_goto'         => ['type' => 'url'],
                '_is_go'        => ['type' => 'boolean'],
                '_nofollow'     => ['type' => 'boolean'],
                '_seo_title'    => ['type' => 'string'],
                '_seo_metakey'  => ['type' => 'string'],
                '_seo_desc'     => ['type' => 'textarea'],
                'views'         => ['type' => 'integer'],
                '_like_count'   => ['type' => 'integer'],
            ],
        ],
        // 分类法字段
        'taxonomies' => [
            'category'  => [
                'seo_title'   => ['type' => 'string'],
                'seo_metakey' => ['type' => 'string'],
                'seo_desc'    => ['type' => 'textarea'],
                'thumbnail'   => ['type' => 'string'],
                'card_mode'   => ['type' => 'string'],
                'columns'     => ['type' => 'object'],
            ],
            'post_tag'  => [
                'seo_title'   => ['type' => 'string'],
                'seo_metakey' => ['type' => 'string'],
                'seo_desc'    => ['type' => 'textarea'],
            ],
            'favorites' => [
                'seo_title'   => ['type' => 'string'],
                'seo_metakey' => ['type' => 'string'],
                'seo_desc'    => ['type' => 'textarea'],
                'thumbnail'   => ['type' => 'string'],
                'card_mode'   => ['type' => 'string'],
                'columns'     => ['type' => 'object'],
            ],
            'sitetag'   => [
                'seo_title'   => ['type' => 'string'],
                'seo_metakey' => ['type' => 'string'],
                'seo_desc'    => ['type' => 'textarea'],
            ],
            'apps'      => [
                'seo_title'   => ['type' => 'string'],
                'seo_metakey' => ['type' => 'string'],
                'seo_desc'    => ['type' => 'textarea'],
                'thumbnail'   => ['type' => 'string'],
                'card_mode'   => ['type' => 'string'],
                'columns'     => ['type' => 'object'],
            ],
            'apptag'    => [
                'seo_title'   => ['type' => 'string'],
                'seo_metakey' => ['type' => 'string'],
                'seo_desc'    => ['type' => 'textarea'],
            ],
            'books'     => [
                'seo_title'   => ['type' => 'string'],
                'seo_metakey' => ['type' => 'string'],
                'seo_desc'    => ['type' => 'textarea'],
                'thumbnail'   => ['type' => 'string'],
                'card_mode'   => ['type' => 'string'],
                'columns'     => ['type' => 'object'],
            ],
            'booktag'   => [
                'seo_title'   => ['type' => 'string'],
                'seo_metakey' => ['type' => 'string'],
                'seo_desc'    => ['type' => 'textarea'],
            ],
            'series'    => [
                'seo_title'   => ['type' => 'string'],
                'seo_metakey' => ['type' => 'string'],
                'seo_desc'    => ['type' => 'textarea'],
            ],
        ],
    ];
}

//======================================================================
// 3. API 回调函数 (GET, UPDATE)
//======================================================================

/**
 * GET 回调: 获取文章 meta
 */
function onav_get_post_meta_callback($post) {
    $post_id = $post['id'];
    $post_type = $post['type'];
    $config = onav_get_api_meta_config();
    $allowed_fields = isset($config['post_types'][$post_type]) ? $config['post_types'][$post_type] : [];

    $meta = [];
    foreach (array_keys($allowed_fields) as $field) {
        $value = get_post_meta($post_id, $field, true);
        $meta[$field] = $value;
    }
    return $meta;
}

/**
 * UPDATE 回调: 更新文章 meta
 */
function onav_update_post_meta_callback($value, $post, $key) {
    if (!current_user_can('edit_post', $post->ID)) {
        return new WP_Error('rest_forbidden_context', __('Sorry, you are not allowed to edit this post.', 'onav-rest-api'), ['status' => 403]);
    }

    if (!is_array($value)) {
        return new WP_Error('rest_invalid_data', __('Invalid data format. Expecting an object of meta fields.', 'onav-rest-api'), ['status' => 400]);
    }

    $config = onav_get_api_meta_config();
    $allowed_fields = isset($config['post_types'][$post->post_type]) ? $config['post_types'][$post->post_type] : [];

    foreach ($value as $field_key => $field_value) {
        if (array_key_exists($field_key, $allowed_fields)) {
            $field_config = $allowed_fields[$field_key];
            $sanitized_value = onav_sanitize_meta_value($field_value, $field_config);
            update_post_meta($post->ID, $field_key, $sanitized_value);
        }
    }

    return true;
}

/**
 * GET 回调: 获取分类/标签 meta (已修复, 支持序列化)
 */
function onav_get_term_meta_callback($term) {
    $term_id = $term['id'];
    $taxonomy = $term['taxonomy'];

    $seo_meta_key = 'term_io_seo';
    $display_meta_key = 'term_io_' . $taxonomy;

    $seo_meta = get_term_meta($term_id, $seo_meta_key, true);
    if (!is_array($seo_meta)) {
        $seo_meta = [];
    }

    $display_meta = get_term_meta($term_id, $display_meta_key, true);
    if (!is_array($display_meta)) {
        $display_meta = [];
    }

    $merged_meta = array_merge($seo_meta, $display_meta);

    $config = onav_get_api_meta_config();
    $allowed_fields = isset($config['taxonomies'][$taxonomy]) ? $config['taxonomies'][$taxonomy] : [];
    $final_meta = [];

    foreach (array_keys($allowed_fields) as $field_key) {
        $final_meta[$field_key] = isset($merged_meta[$field_key]) ? $merged_meta[$field_key] : '';
    }

    return $final_meta;
}

/**
 * UPDATE 回调: 更新分类/标签 meta (已修复, 支持序列化)
 */
function onav_update_term_meta_callback($value, $term_object, $key) {
    $term_id = $term_object->term_id;
    $taxonomy = $term_object->taxonomy;

    if (!current_user_can('edit_term', $term_id)) {
        return new WP_Error('rest_forbidden_context', __('Sorry, you are not allowed to edit this term.', 'onav-rest-api'), ['status' => 403]);
    }

    if (!is_array($value)) {
        return new WP_Error('rest_invalid_data', __('Invalid data format. Expecting an object of meta fields.', 'onav-rest-api'), ['status' => 400]);
    }

    $config = onav_get_api_meta_config();
    $allowed_fields = isset($config['taxonomies'][$taxonomy]) ? $config['taxonomies'][$taxonomy] : [];

    $seo_field_keys = ['seo_title', 'seo_metakey', 'seo_desc'];
    $display_field_keys = ['thumbnail', 'card_mode', 'columns'];

    $seo_meta_key = 'term_io_seo';
    $display_meta_key = 'term_io_' . $taxonomy;

    $current_seo_meta = get_term_meta($term_id, $seo_meta_key, true);
    if (!is_array($current_seo_meta)) $current_seo_meta = [];

    $current_display_meta = get_term_meta($term_id, $display_meta_key, true);
    if (!is_array($current_display_meta)) $current_display_meta = [];

    $seo_updated = false;
    $display_updated = false;

    foreach ($value as $field_key => $field_value) {
        if (array_key_exists($field_key, $allowed_fields)) {
            $field_config = $allowed_fields[$field_key];
            $sanitized_value = onav_sanitize_meta_value($field_value, $field_config);

            if (in_array($field_key, $seo_field_keys)) {
                $current_seo_meta[$field_key] = $sanitized_value;
                $seo_updated = true;
            } elseif (in_array($field_key, $display_field_keys)) {
                $current_display_meta[$field_key] = $sanitized_value;
                $display_updated = true;
            }
        }
    }

    if ($seo_updated) {
        update_term_meta($term_id, $seo_meta_key, $current_seo_meta);
    }
    if ($display_updated) {
        update_term_meta($term_id, $display_meta_key, $current_display_meta);
    }

    return true;
}


/**
 * 递归清理 meta 数据
 */
function onav_sanitize_meta_value($value, $config) {
    $type = isset($config['type']) ? $config['type'] : 'string';

    if (is_array($value) && in_array($type, ['array', 'object'])) {
        $sanitized_array = [];
        foreach ($value as $key => $item) {
            $item_config = ['type' => is_array($item) ? 'array' : 'string'];
            $sanitized_array[$key] = onav_sanitize_meta_value($item, $item_config);
        }
        return $sanitized_array;
    }

    switch ($type) {
        case 'url':
            return esc_url_raw($value);
        case 'textarea':
            return sanitize_textarea_field($value);
        case 'integer':
            return intval($value);
        case 'boolean':
            return rest_sanitize_boolean($value);
        case 'string':
        default:
            return sanitize_text_field($value);
    }
}

//======================================================================
// 4. 后台设置页面
//======================================================================

add_action('admin_menu', 'onav_rest_api_add_admin_menu');
add_action('admin_init', 'onav_rest_api_settings_init');

/**
 * 添加后台菜单
 */
function onav_rest_api_add_admin_menu() {
    add_options_page(
        'OneNav REST API Settings',
        'OneNav REST API',
        'manage_options',
        'onenav_rest_api',
        'onav_rest_api_options_page'
    );
}

/**
 * 注册设置
 */
function onav_rest_api_settings_init() {
    register_setting('onavRestApi', 'onav_rest_api_settings');

    add_settings_section(
        'onav_rest_api_section_post_types',
        __('启用文章类型', 'onav-rest-api'),
        null,
        'onavRestApi'
    );

    add_settings_section(
        'onav_rest_api_section_taxonomies',
        __('启用分类法', 'onav-rest-api'),
        null,
        'onavRestApi'
    );

    $config = onav_get_api_meta_config();
    $options = get_option('onav_rest_api_settings');

    // 为文章类型创建复选框
    foreach (array_keys($config['post_types']) as $post_type) {
        add_settings_field(
            'enabled_post_types_' . $post_type,
            $post_type,
            'onav_rest_api_checkbox_callback',
            'onavRestApi',
            'onav_rest_api_section_post_types',
            [
                'option_name' => 'enabled_post_types',
                'value' => $post_type,
                'options' => $options
            ]
        );
    }

    // 为分类法创建复选框
    foreach (array_keys($config['taxonomies']) as $taxonomy) {
        add_settings_field(
            'enabled_taxonomies_' . $taxonomy,
            $taxonomy,
            'onav_rest_api_checkbox_callback',
            'onavRestApi',
            'onav_rest_api_section_taxonomies',
            [
                'option_name' => 'enabled_taxonomies',
                'value' => $taxonomy,
                'options' => $options
            ]
        );
    }
}

/**
 * 渲染复选框的回调函数
 */
function onav_rest_api_checkbox_callback($args) {
    $option_name = $args['option_name'];
    $value = $args['value'];
    $options = $args['options'];

    // 如果选项从未保存过，则默认全部勾选
    if (empty($options)) {
        $checked = 'checked';
    } else {
        $checked = isset($options[$option_name]) && in_array($value, $options[$option_name]) ? 'checked' : '';
    }

    echo "<input type='checkbox' name='onav_rest_api_settings[{$option_name}][]' value='{$value}' {$checked} />";
}

/**
 * 渲染设置页面的 HTML
 */
function onav_rest_api_options_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('onavRestApi');
            do_settings_sections('onavRestApi');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
