<?php
/*
Plugin Name: Firebase Notification
Description: Notify users when a post is published with Firebase Cloud Messaging (FCM)
Version: 1.0
Author: BasitAli
Author URI: https://mundia.dev
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined("FCM_PLUGIN_NM")) {
    define("FCM_PLUGIN_NM", 'Firebase Settings');
}

if (!defined("FCM_TD")) {
    define("FCM_TD", 'fcm_td');
}

class Firebase_Push_Notification
{
    public function __construct()
    {
        // Installation and uninstallation hooks
        add_action('admin_menu', array($this, 'fcm_setup_admin_menu'));
        add_action('admin_init', array($this, 'fcm_settings'));
        add_action('publish_post', array($this, 'fcm_on_post_publish'), 10, 2);
    }

    public function fcm_setup_admin_menu()
    {
        add_submenu_page('options-general.php', __('Firebase Push Notification', FCM_TD), FCM_PLUGIN_NM, 'manage_options', 'fcm_slug', array($this, 'fcm_admin_page'));
    }

    public function fcm_admin_page()
    {
        include plugin_dir_path(__FILE__) . 'partials/wp-firebase-notifications-settings.php';
    }

    public function fcm_settings()
    { //register our settings
        register_setting('fcm_group', 'fcm_api');
        register_setting('fcm_group', 'fcm_topic');
        register_setting('fcm_group', 'fcm_disable', array('type' => 'boolean'));
    }

    public function fcm_on_post_publish($post_id, $post)
    {
        $content = $post->post_title;
        $post_categories = wp_get_post_categories($post_id);
        $category = '';
        foreach ($post_categories as $c) {
            $category = get_category($c)->name;
            break;
        }

        $disabled = get_option('fcm_disable');
        $topic = get_option('fcm_topic');
        $apiKey = get_option('fcm_api');

        if ($disabled || !$topic || !$apiKey) {
            return;
        }

        $published_at_least_once = get_post_meta($post_id, 'is_published', true);

        if ($published_at_least_once) {
            return;
        }

        $this->fcm_notification($content, $category, (string) $post_id);
        update_post_meta($post_id, 'is_published', true);
    }

    public function fcm_notification($content, $post_type, $post_id)
    {
        $topic = "'" . get_option('fcm_topic') . "' in topics";
        $apiKey = get_option('fcm_api');
        $url = 'https://fcm.googleapis.com/fcm/send';
        $headers = array(
            'Authorization: key=' . $apiKey,
            'Content-Type: application/json',
        );

        $notification_data = array(
            // when application open then post field 'data' parameter work so 'message' and 'body' key should have same text or value
            'message' => $content,
            'type' => $post_type,
            'post_id' => $post_id,
        );

        $notification = array(
            // when application close then post field 'notification' parameter work
            'body' => $content,
            'sound' => 'default',
        );

        $post = array(
            'condition' => $topic,
            'notification' => $notification,
            "content_available" => false,
            'priority' => 'high',
            'data' => $notification_data,
        );

        // POST notification to FCM
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}

$Firebase_Push_Notification_OBJ = new Firebase_Push_Notification();
