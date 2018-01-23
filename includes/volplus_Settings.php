<?php

require_once VOLPLUS_PATH . 'includes/volplus_Functions.php';
require_once VOLPLUS_PATH . 'includes/volplus_License.php';

add_action('admin_menu', 'volplus_plugin_menu');

function volplus_plugin_menu() {
	add_options_page('Volunteer Plus', 'Volunteer Plus', 'administrator', 'volunteer-plus', 'volplus_plugin_settings_page', 'dashicons-admin-generic');
}

function volplus_plugin_settings_page() {
?>  
<div class="wrap">
<h2>Volunteer Plus Settings</h2>

<form method="post" action="options.php">
    <?php settings_fields( 'volplus-plugin-settings-group' ); ?>
    <?php do_settings_sections( 'volplus-plugin-settings-group' ); ?>
    <p>This plugin is for the use of existing customers of Volunteer Plus software by Pipe Media.</p>
    <p>For more information visit the <a href="https://volunteerplus.org.uk" target="_blank">Volunteer Plus</a> website.</p>
    <table class="form-table">
        <tr valign="top">
        <th scope="row"><h3>Volunteer Plus API Credentials</h3></th>
        </tr>
        
        <tr valign="top">
        <th scope="row">API Endpoint URL</th>
        <td><input type="text" name="volplus_endpoint" size="40" value="<?php echo esc_attr( get_option('volplus_endpoint') ); ?>" /></td>
        </tr>
         
        <tr valign="top">
        <th scope="row">API Key</th>
        <td><input type="password" name="volplus_api_key" size="40" value="<?php echo esc_attr( get_option('volplus_api_key') ); ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row"><h3>Plugin Licensing</h3></th>
        </tr>
        <tr valign="top">
        <th scope="row">Domain</th>
        <td><?php echo $_SERVER['HTTP_HOST'] ?></td>
        </tr>
        <th scope="row">Plugin License Code</th>
        <td><input type="password" name="volplus_license_code" size="40" value="<?php echo esc_attr( get_option('volplus_license_code') ); ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row"><h3>Google Maps</h3></th>
        </tr>
        
        <tr valign="top">
        <th scope="row">Show Google Map on Opportunity Detail page</th>
        <td><input type="checkbox" name="volplus_showmap" <?php if(get_option('volplus_showmap') == 'on') echo 'checked'; ?> /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Google Maps API key</th>
        <td><input type="text" name="volplus_googlemapkey" size="40" value="<?php echo esc_attr( get_option('volplus_googlemapkey') ); ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Map default centre</th>
        <td><input type="text" name="volplus_googlemapcentre" size="40" value="<?php echo esc_attr( get_option('volplus_googlemapcentre') ); ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Google Maps Zoom</th>
        <td><input type="number" name="volplus_googlemapzoom" min="0" max="20" value="<?php echo esc_attr( get_option('volplus_googlemapzoom', 10) ); ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row"><h3>Other Settings</h3></th>
        </tr>
 
        <tr valign="top">
        <th scope="row">Response Form Content (e.g. shortcode to contact form)</th>
        <td><input type="text" name="volplus_responseformcontent" size="40" value="<?php echo esc_attr(get_option('volplus_responseformcontent')); ?>" /></td>
        </tr>

       <tr valign="top">
        <th scope="row">Hide bracketed text in Opportunity titles & Organisation names</th>
        <td><input type="checkbox" name="volplus_hidebrackets" <?php if(get_option('volplus_hidebrackets') == 'on') echo 'checked'; ?> /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Volunteer login timeout (minutes)</th>
        <td><input type="number" name="volplus_voltimeout" min="5" max="3600" value="<?php echo esc_attr(get_option('volplus_voltimeout', 60)); ?>" /></td>
        </tr>

    </table>
    
    <?php submit_button();?> 
</form>

<?php
	$authresponse = volplus_checkauth();
//	print_r($authresponse);
	if($authresponse == 200) { 
		echo '<div class="updated notice"><p>The Volunteer Plus API credentials are working</p></div>';
	} else {
		echo '<div class="error notice"><p><strong>The Volunteer Plus API credentials failure. Error code'.$authresponse.'</strong></p></div>';
	}
	
	if(volplus_licensed()) {
		echo '<div class="updated notice"><p>The WP Volunteer Plus plugin is licensed</p></div>';
	}else {
		echo '<div class="error notice"><p><strong><strong>Unlicensed WP Volunteer Plus plugin. Please contact <a href:"mail:info@maddox.co.uk">info@maddox.co.uk</a> quoting your organisation name and your Domain as shown above.</strong></p></div>';
	}

?>
</div>
<?php
}  

add_action( 'admin_init', 'volplus_plugin_settings' );

function volplus_plugin_settings() {
	register_setting( 'volplus-plugin-settings-group', 'volplus_endpoint' );
	register_setting( 'volplus-plugin-settings-group', 'volplus_api_key' );
	register_setting( 'volplus-plugin-settings-group', 'volplus_showmap' );
	register_setting( 'volplus-plugin-settings-group', 'volplus_googlemapkey' );
	register_setting( 'volplus-plugin-settings-group', 'volplus_googlemapcentre' );
	register_setting( 'volplus-plugin-settings-group', 'volplus_googlemapzoom' );
	register_setting( 'volplus-plugin-settings-group', 'volplus_license_code' );
	register_setting( 'volplus-plugin-settings-group', 'volplus_responseformcontent' );	
	register_setting( 'volplus-plugin-settings-group', 'volplus_hidebrackets' );	
	register_setting( 'volplus-plugin-settings-group', 'volplus_voltimeout' );	
}

function volplus_checkauth(){
  $authcheck = wp_remote_get(get_option('volplus_endpoint') . 'interests', array('headers' => array('Authorization' => 'Bearer ' . get_option('volplus_api_key'))));
  $response_code = wp_remote_retrieve_response_code($authcheck);
  return $response_code;
//  return $authcheck;
}