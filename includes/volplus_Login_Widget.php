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
 
 	$display_in = is_volplus_user_logged_in() ? 'inherit' : 'none';
 	$display_out = is_volplus_user_logged_in() ? 'none' : 'inherit';
	?>


	<div id="login_widget">
   	<div id="logged_in" style="display:<?php echo $display_in ?>"><?php
	    	if ( ! empty( $loggedin_title )) echo $args['before_title'] . $loggedin_title . $args['after_title'];
			if(isset($instance['loggedin_intro_text'])) {
				$loggedin_intro_text = apply_filters('widget_title',$instance['loggedin_intro_text']);
				if(!empty($loggedin_intro_text)) {
					echo $loggedin_intro_text;
				}
			}?>
		</div>

    	<div id="not_logged_in" style="display:<?php	echo $display_out ?>"><?php
	    	if ( ! empty( $title )) echo $args['before_title'] . $title . $args['after_title'];
			if(isset($instance['intro_text'])) {
				$intro_text = apply_filters('widget_title',$instance['intro_text']);
				if(!empty($intro_text)) {
					echo $intro_text;
				}
			}?>
		</div>

		<?php if(! volplus_licensed()) echo '<h3>'._e( 'Unlicensed Volunteer Plus Plugin', 'wp_volunteer-plus' ).'</h3>';?>


		<div id='welcome' class='volplus-welcome' style='display:<?php echo $display_in?>'>Welcome back, 
			<div id='welcome_name' class='volplus_welcome_name' style='display:inline'>
				<?php if(isset($_COOKIE['volplus_user_id'])) echo " " . $_COOKIE['volplus_user_first_name'] . " " . $_COOKIE['volplus_user_last_name'];?>
			</div>
			<p><div id='logout' class='button'> Log Out </div></p>
		</div>


		<form id='login' action='login' method='post' style='display:<?php echo $display_out?>'>
			<label for="email_address"><?php _e( 'Email', 'wp_volunteer-plus' )?></label>
				<input id="email_address" type="email" name="email_address" placeholder="Your email address" onblur= "javascript:{this.value = this.value.toLowerCase();}" autocomplete="off" required>
			<label for="password">Password</label>
				<input id="password" type="password" name="password" placeholder="Your password" autocomplete="off" required />
<!--			<a class="lost" href="<?php echo wp_lostpassword_url(); ?>">Lost your password?</a>-->
			<input class="button" type="submit" value="Login" name="submit">
<!--			<a class="close" href="">(close)</a>-->
			<?php wp_nonce_field( 'ajax-login-nonce', 'security' ); ?>
		<p id='volplus_login_status' class="status"></p>
		</form>

	</div>
	
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
  // Create the admin area widget settings form.--------------------------------------------------------------------------------------
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
// Register the widget.
function register_volplus_Login_Widget() { 
  register_widget( 'volplus_Login_Widget' );
}
add_action('wp_enqueue_scripts', 'ajax_login_init');
add_action( 'wp_ajax_volplusajaxlogin', 'ajax_login' );
add_action( 'wp_ajax_nopriv_volplusajaxlogin', 'ajax_login' );
add_action( 'widgets_init', 'register_volplus_Login_Widget' );

?>