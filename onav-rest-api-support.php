<?php
/**
 * Plugin Name: OneNav REST API Meta Fields Support
 * Plugin URI: https://warpnav.com/
 * Description: 为 OneNav 主题的 sites 自定义文章类型提供自定义字段（meta）的 REST API 读写支持。
 * Version: 1.1.2
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

/**
 * 注册需要在 REST API 中暴露的自定义字段
 */
add_action('rest_api_init', 'onav_register_rest_meta_fields');

function onav_register_rest_meta_fields() {
    // 为 sites 自定义文章类型注册字段
    register_rest_field('sites', 'onav_meta', array(
        'get_callback' => 'onav_get_meta_callback',
        'update_callback' => 'onav_update_meta_callback',
        'schema' => null,
    ));
    
    // 为 post 普通文章类型注册字段
    register_rest_field('post', 'onav_meta', array(
        'get_callback' => 'onav_get_meta_callback',
        'update_callback' => 'onav_update_meta_callback',
        'schema' => null,
    ));
}

/**
 * 获取 meta 字段的回调函数
 */
function onav_get_meta_callback($post) {
    $post_id = $post['id'];
    $meta_fields = array(
        '_sites_link',
        '_sites_sescribe',
        '_sites_language',
        '_sites_country',
        '_sites_preview',
        '_seo_metakey',
        '_seo_title',
        '_seo_desc',
        '_thumbnail',
    );

    $meta = array();
    foreach ($meta_fields as $field) {
        $value = get_post_meta($post_id, $field, true);
        if ($value) {
            $meta[$field] = $value;
        }
    }

    return $meta;
}

/**
 * 更新 meta 字段的回调函数
 */
function onav_update_meta_callback($value, $post, $key) {
    if (!current_user_can('edit_post', $post->ID)) {
        return new WP_Error(
            'rest_forbidden_meta',
            __('Sorry, you are not allowed to update meta fields.', 'onav-rest-api'),
            array('status' => 403)
        );
    }

    // 允许更新多个字段
    if (is_array($value)) {
        foreach ($value as $field => $field_value) {
            if (in_array($field, array_keys(onav_get_allowed_meta_fields()))) {
                update_post_meta($post->ID, $field, sanitize_text_field($field_value));
            }
        }
        return true;
    }

    // 单个字段更新
    if (in_array($key, array_keys(onav_get_allowed_meta_fields()))) {
        return update_post_meta($post->ID, $key, sanitize_text_field($value));
    }

    return false;
}

/**
 * 获取允许的 meta 字段列表
 */
function onav_get_allowed_meta_fields() {
    return array(
        '_sites_link' => 'Sites Link URL',
        '_sites_sescribe' => 'Sites Short Description',
        '_sites_language' => 'Sites Language',
        '_sites_country' => 'Sites Country',
        '_sites_preview' => 'Sites Preview Image',
        '_seo_metakey' => 'SEO Meta Keywords',
        '_seo_title' => 'SEO Title',
        '_seo_desc' => 'SEO Description',
        '_thumbnail' => 'Featured Image ID',
    );
}

/**
 * 验证并清理 meta 输入 - sites 类型
 */
add_filter('rest_pre_insert_sites', 'onav_validate_meta_input_sites', 10, 2);

function onav_validate_meta_input_sites($prepared_post, $request) {
    // $prepared_post 是 WP_Post 对象，不是数组
    if (is_object($prepared_post)) {
        $prepared_post = get_object_vars($prepared_post);
    }
    if (isset($request['onav_meta']) && is_array($request['onav_meta'])) {
        $meta = $request['onav_meta'];
        $cleaned_meta = array();
        foreach ($meta as $key => $value) {
            // 验证字段名
            $allowed_fields = onav_get_allowed_meta_fields();
            if (array_key_exists($key, $allowed_fields)) {
                // 根据字段类型进行清理
                switch ($key) {
                    case '_sites_link':
                    case '_sites_preview':
                        // URL 字段
                        $cleaned_meta[$key] = esc_url_raw($value);
                        break;
                    case '_seo_desc':
                    case '_sites_sescribe':
                        // 描述字段（允许更多字符）
                        $cleaned_meta[$key] = sanitize_textarea_field($value);
                        break;
                    default:
                        // 默认文本清理
                        $cleaned_meta[$key] = sanitize_text_field($value);
                }
            }
        }
        // 将清理后的 meta 存储到 post meta
        if (!empty($cleaned_meta)) {
            // 使用 meta_input 数组格式
            $prepared_post['meta_input'] = $cleaned_meta;
        }
    }
    // 返回转换后的数组（WP_REST_Posts_Controller 需要数组）
    return (object) $prepared_post;
}

/**
 * 验证并清理 meta 输入 - post 类型
 */
add_filter('rest_pre_insert_post', 'onav_validate_meta_input_post', 10, 2);

function onav_validate_meta_input_post($prepared_post, $request) {
    // $prepared_post 是 WP_Post 对象，不是数组
    if (is_object($prepared_post)) {
        $prepared_post = get_object_vars($prepared_post);
    }
    
    // 处理 onav_meta 中的 SEO 字段
    if (isset($request['onav_meta']) && is_array($request['onav_meta'])) {
        $meta = $request['onav_meta'];
        $cleaned_meta = array();
        foreach ($meta as $key => $value) {
            // 处理 SEO 相关字段
            $seo_fields = array('_seo_metakey', '_seo_title', '_seo_desc');
            if (in_array($key, $seo_fields)) {
                switch ($key) {
                    case '_seo_desc':
                        $cleaned_meta[$key] = sanitize_textarea_field($value);
                        break;
                    default:
                        $cleaned_meta[$key] = sanitize_text_field($value);
                }
            }
        }
        if (!empty($cleaned_meta)) {
            $prepared_post['meta_input'] = $cleaned_meta;
        }
    }
    
    // 处理 post_excerpt（摘要字段）
    if (isset($request['post_excerpt'])) {
        $prepared_post['excerpt'] = sanitize_textarea_field($request['post_excerpt']);
    }
    
    // 处理 _thumbnail_id（特色图片）
    if (isset($request['_thumbnail_id'])) {
        $prepared_post['featured_media'] = intval($request['_thumbnail_id']);
    }
    
    // 返回转换后的数组
    return (object) $prepared_post;
}
