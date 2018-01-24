<?php

require_once VOLPLUS_PATH . 'includes/volplus_Functions.php';
require_once VOLPLUS_PATH . 'includes/volplus_License.php';

class volplus_Login_Widget extends WP_Widget {


  // Set up the widget name and description.
//  public function __construct() {
  function __construct() {
    $widget_options = array( 'classname' => 'Login_Widget', 'description' => 'Log into Volunteer Plus' );
    parent::__construct( 'Login_Widget', 'Volunteer Plus Login', $widget_options );
  }


// Create the widget output.
  function widget( $args, $instance ) { 	
    $loggedin_title = apply_filters( 'widget_title', $instance[ 'loggedin_title' ] );
    $title = apply_filters( 'widget_title', $instance[ 'title' ] );
// before and after widget arguments are defined by themes
    echo $args['before_widget'];

    if (is_volplus_user_logged_in()){
    	if ( ! empty( $loggedin_title )) echo $args['before_title'] . $loggedin_title . $args['after_title'];
		if(isset($instance['loggedin_intro_text'])) {
			$loggedin_intro_text = apply_filters('widget_title',$instance['loggedin_intro_text']);
			if(!empty($loggedin_intro_text)) {
				echo $loggedin_intro_text;
			}
		}
	} else { //not logged in
   	if ( ! empty( $title )) echo $args['before_title'] . $title . $args['after_title'];
		if(isset($instance['intro_text'])) {
			$intro_text = apply_filters('widget_title',$instance['intro_text']);
			if(!empty($intro_text)) {
				echo $intro_text;
			}
		}
	}		


	if(! volplus_licensed()) echo '<h3>'._e( 'Unlicensed Volunteer Plus Plugin', 'wp_volunteer-plus' ).'</h3>';
	?>


	<?php if (is_volplus_user_logged_in()) {
		$endpoint = API_URL . 'volunteers/'.(int) $_COOKIE['volplus_user_id'];
		$volunteer = wp_remote_get( $endpoint, array('headers' => array('Authorization' => 'Bearer '.API_KEY)));
		$volunteer = json_decode($volunteer['body']);
		echo "<div class='volplus-welcome'>Welcome back, <strong>".$volunteer->first_name . "</strong></div>";
	 } else { ?>
		<form id="login" action="login" method="post">
		<p class="status"></p>
			<label for="email_address"><?php _e( 'Email', 'wp_volunteer-plus' )?></label>
				<input id="email_address" type="email" name="email_address" placeholder="Your email address" onblur= "javascript:{this.value = this.value.toLowerCase();}" autocomplete="off" required>
			<label for="password">Password</label>
				<input id="password" type="password" name="password" placeholder="Your password" autocomplete="off" required />
<!--			<a class="lost" href="<?php echo wp_lostpassword_url(); ?>">Lost your password?</a>-->
			<input class="submit_button" type="submit" value="Login" name="submit">
<!--			<a class="close" href="">(close)</a>-->
			<?php wp_nonce_field( 'ajax-login-nonce', 'security' ); ?>
		</form>
	<?php } ?>

	<script type="text/css">
		div.volplus-welcome{
			font-size: 2em;
		}
		form#login{
   		display: none;
   		background-color: #FFFFFF;
   		position: fixed;
   		top: 200px;
   		padding: 40px 25px 25px 25px;
   		width: 350px;
   		z-index: 999;
   		left: 50%;
   		margin-left: -200px;
		}

		form#login p.status{
   		display: none;
		}

		.login_overlay{
   		height: 100%;
   		width: 100%;
	   	background-color: #F6F6F6;
   		opacity: 0.9;
   		position: fixed;
   		z-index: 998;
		}
	</script>


	<?php echo $args['after_widget'];
  }



  // Create the admin area widget settings form.
//  public function form( $instance ) {
  function form( $instance ) {
// Check values 
	if( $instance ) { 
		$title    = esc_attr( $instance['title'] ); 
		$intro_text    = esc_attr( $instance['intro_text'] ); 
		$loggedin_title    = esc_attr( $instance['loggedin_title'] ); 
		$loggedin_intro_text    = esc_attr( $instance['loggedin_intro_text'] ); 
	} else { 
		$title    = ''; 
		$intro_text    = ''; 
		$loggedin_title    = ''; 
		$loggedin_intro_text    = ''; 
	} ?>
	
	<h2>When not logged in</h2>
	<p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title', 'wp_volunteer-plus' ); ?></label>
	<input class='widefat' id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
	</p>
	<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'intro_text' ) ); ?>"><?php _e( 'Intro Text', 'wp_volunteer-plus' ); ?></label><br>
	<textarea id="<?php echo esc_attr( $this->get_field_id( 'intro_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'intro_text' ) ); ?>" rows = "5" cols = "40"><?php echo esc_attr( $intro_text ); ?> </textarea>
	</p>

	<h2>When logged in</h2>
	<p><label for="<?php echo esc_attr( $this->get_field_id( 'loggedin_title' ) ); ?>"><?php _e( 'Logged in Title', 'wp_volunteer-plus' ); ?></label>
	<input class='widefat' id="<?php echo esc_attr( $this->get_field_id( 'loggedin_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'loggedin_title' ) ); ?>" type="text" value="<?php echo esc_attr( $loggedin_title ); ?>" />
	</p>
	<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'loggedin_intro_text' ) ); ?>"><?php _e( 'Logged in Intro Text', 'wp_volunteer-plus' ); ?></label><br>
	<textarea id="<?php echo esc_attr( $this->get_field_id( 'loggedin_intro_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'loggedin_intro_text' ) ); ?>" rows = "5" cols = "40"><?php echo esc_attr( $loggedin_intro_text ); ?> </textarea>
	</p>

	<?php
}

// Apply settings to the widget instance.
//  public function update( $new_instance, $old_instance ) {
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
		$instance[ 'intro_text' ] = strip_tags( $new_instance[ 'intro_text' ] );
		$instance[ 'loggedin_title' ] = strip_tags( $new_instance[ 'loggedin_title' ] );
		$instance[ 'loggedin_intro_text' ] = strip_tags( $new_instance[ 'loggedin_intro_text' ] );
		return $instance;
	}

}

function ajax_login_init(){

    wp_register_script('ajax-login-script', VOLPLUS_URL . 'includes/ajax-login-script.js', array('jquery') ); 
    wp_enqueue_script('ajax-login-script');

    wp_localize_script( 'ajax-login-script', 'ajax_login_object', array( 
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'redirecturl' => home_url(),
        'loadingmessage' => __('Sending user info, please wait...')
    ));

    // Enable the user with no privileges to run ajax_login() in AJAX
    add_action( 'wp_ajax_nopriv_ajaxlogin', 'ajax_login' );
}

// Execute the action only if the user isn't logged in
if (!is_volplus_user_logged_in()) {
    add_action('widgets_init', 'ajax_login_init');
}

function ajax_login(){
	// First check the nonce, if it fails the function will break
	check_ajax_referer( 'ajax-login-nonce', 'security' );
	// Nonce is checked, get the POST data and sign user on
	$volpluslogin = array(
		'email_address' => $_POST['email_address'],
		'password' => $_POST['password'],
		'type' => 1 // 1=volunteer, 2=organisation
	);
	$response = wp_remote_post(API_URL . 'login', array(
		'timeout' => 45,
		'redirection' => 5,
		'httpversion' => '1.0',
		'blocking' => true,
		'headers' => array('Authorization' => 'Bearer '.API_KEY),
		'body' => (array) $volpluslogin,
		'cookies' => array()
		)
	);
			
	$responsebody = (array) json_decode($response['body']);
	if ( $response['response']['code'] !== 200) {
		$error_message = $response['response']['message'];
		echo "Something went wrong: <em>".$response['response']['message']." (Code ".$response['response']['code'].")</em>";
		foreach($responsebody as $key=>$data){
			echo "<br/>".$key.": ";
			foreach($data as $data2){
				echo "<em>".$data2."</em>, ";
			}
		}
		echo json_encode(array('loggedin'=>false, 'message'=>__('Wrong username or password.')));
		setcookie('volplus_user_id',0 , time()-60, COOKIEPATH, COOKIE_DOMAIN );
	} else {
		echo json_encode(array('loggedin'=>true, 'message'=>__('Login successful, redirecting...')));
		setcookie('volplus_user_id', $responsebody['id'], time()+(3600 * get_option('volplus_voltimeout',1)), COOKIEPATH, COOKIE_DOMAIN );
	}

	die();
}


// Register the widget.
function register_volplus_Login_Widget() { 
  register_widget( 'volplus_Login_Widget' );
}
add_action( 'widgets_init', 'register_volplus_Login_Widget' );

?>