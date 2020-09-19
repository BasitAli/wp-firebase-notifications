<?php
if (!defined('ABSPATH')) {exit;}
?>

<h1><?php echo __(FCM_PLUGIN_NM, FCM_TD); ?></h1>

<form action="options.php" method="post">
    <?php settings_fields('fcm_group');?>
    <?php do_settings_sections('fcm_group');?>
    <table class="form-table" role="presentation">
        <tbody>
            <tr>
                <th><label for="fcm_api"><?php echo __("API Key", FCM_TD); ?></label></th>
                <td><input id="fcm_api" name="fcm_api" type="text" placeholder="API Key" value="<?php echo get_option('fcm_api'); ?>" required="required" /></td>
            </tr>
            <tr>
                <th><label for="fcm_topic"><?php echo __("Topic", FCM_TD); ?></label></th>
                <td><input id="fcm_topic" name="fcm_topic" type="text" placeholder="Topic" value="<?php echo get_option('fcm_topic'); ?>" required="required" /></td>
            </tr>
            <tr>
                <th><label for="fcm_disable"><?php echo __("Disable Push Notifications", FCM_TD); ?></label></th>
                <td><input id="fcm_disable" name="fcm_disable" type="checkbox" value="1" <?php checked('1', get_option('fcm_disable'));?>  /></td>
            </tr>
        </tbody>
    </table>
    <p class="submit">
        <?php submit_button();?>
    </p>
</form>
