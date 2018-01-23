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
    $title = apply_filters( 'widget_title', $instance[ 'title' ] );
// before and after widget arguments are defined by themes
    echo $args['before_widget'];
    if ( ! empty( $title ) )
    echo $args['before_title'] . $title . $args['after_title'];
    
    if(isset($instance['intro_text'])) {
    	$intro_text = apply_filters('widget_title',$instance['intro_text']);
	    if(!empty($intro_text)) {
		    echo $intro_text;
		 }
	}


	if(! volplus_licensed()) echo '<h3>'._e( 'Unlicensed Volunteer Plus Plugin', 'wp_volunteer-plus' ).'</h3>';
	?>

	<form id="login" action="login" method="post">
		<label for="user_email"><?php _e( 'Email', 'wp_volunteer-plus' )?></label>
			<input id="user_email" type="email" name="user_email" placeholder="Your email address" onblur= "javascript:{this.value = this.value.toLowerCase();}" autocomplete="off" required>
		<label for="password">Password</label>
			<input id="password" type="password" name="password" placeholder="Your password" autocomplete="off" required />
<!--		<a class="lost" href="<?php echo wp_lostpassword_url(); ?>">Lost your password?</a>
		<input class="submit_button" type="submit" value="Login" name="submit">-->
<!--		<a class="close" href="">(close)</a>-->
		<?php wp_nonce_field( 'ajax-login-nonce', 'security' ); ?>
	</form>

	<?php if (is_volplus_user_logged_in()) { ?>
		<a class="button" href="<?php echo wp_logout_url( home_url() ); ?>">Logout</a>
	<?php } else { ?>
		<a class="button" id="show_login" href="">Login</a>
	<?php } ?>

	<script type="text/css">
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
	} else { 
		$title    = ''; 
		$intro_text    = ''; 
	} ?>
	
	
	<p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title', 'wp_volunteer-plus' ); ?></label>
	<input class='widefat' id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
	</p>
	<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'intro_text' ) ); ?>"><?php _e( 'Intro Text', 'wp_volunteer-plus' ); ?></label><br>
	<textarea id="<?php echo esc_attr( $this->get_field_id( 'intro_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'intro_text' ) ); ?>" rows = "5" cols = "40"><?php echo esc_attr( $intro_text ); ?> </textarea>
	</p>

	<?php
}

// Apply settings to the widget instance.
//  public function update( $new_instance, $old_instance ) {
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
		$instance[ 'intro_text' ] = strip_tags( $new_instance[ 'intro_text' ] );
		return $instance;
	}

}

function is_volplus_user_logged_in(){
	if(isset($_COOKIE['volplus_user_id'])){
		setcookie('volplus_user_id', $_COOKIE['volplus_user_id'], time()+(60* get_option('volplus_voltimeout',60)), COOKIEPATH, COOKIE_DOMAIN );
		return true;
	} else {
		return false;
	}
}


// Register the widget.
function register_volplus_Login_Widget() { 
  register_widget( 'volplus_Login_Widget' );
}
add_action( 'widgets_init', 'register_volplus_Login_Widget' );

?>