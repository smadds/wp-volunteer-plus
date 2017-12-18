<?php

require_once VOLPLUS_PATH . 'includes/volplus_Functions.php';
require_once VOLPLUS_PATH . 'includes/volplus_License.php';


class volplus_Search_Widget extends WP_Widget {


  // Set up the widget name and description.
//  public function __construct() {
  function __construct() {
    $widget_options = array( 'classname' => 'Search_Widget', 'description' => 'Search Volunteer Plus' );
    parent::__construct( 'Search_Widget', 'Volunteer Plus Search', $widget_options );
  }


// Create the widget output.
  function widget( $args, $instance ) { 	
    $title = apply_filters( 'widget_title', $instance[ 'title' ] );
// before and after widget arguments are defined by themes
    echo $args['before_widget'];
    if ( ! empty( $title ) )
    echo $args['before_title'] . $title . $args['after_title'];


	$interests = wp_remote_get(API_URL . 'interests', array('headers' => array('Authorization' => 'Bearer '.API_KEY)));
	$activities = wp_remote_get(API_URL . 'activities', array('headers' => array('Authorization' => 'Bearer '.API_KEY)));

	if(! volplus_licensed()) echo '<h3>'._e( 'Unlicensed Volunteer Plus Plugin', 'wp_volunteer-plus' ).'</h3>';

	if(!isset($instance[ 'show_5m' ])) {$instance['show_5m'] = 1; $show_5m = 1;}
	if(!isset($instance[ 'show_10m' ])) {$instance['show_10m'] = 1; $show_10m = 1;}
	if(!isset($instance[ 'show_15m' ])) {$instance['show_15m'] = 1; $show_15m = 1;}
	if(!isset($instance[ 'show_20m' ])) {$instance['show_20m'] = 1; $show_20m = 1;}
	if(!isset($instance[ 'show_25m' ])) {$instance['show_25m'] = 1; $show_25m = 1;}
	if(!isset($instance[ 'show_50m' ])) {$instance['show_50m'] = 1; $show_50m = 1;}

//var_dump($instance);

	?>

	<form method="GET" action="/search">
	
		<div class="form-col">
			<label for="postcode"><?php _e( 'Postcode', 'wp_volunteer-plus' )?></label>
			<input type="text" name="postcode" placeholder="Your Postcode" onblur= "javascript:{this.value = this.value.toUpperCase();}" value="<?php echo postcodeFormat(isset($_GET["postcode"]) ? $_GET["postcode"] : ''); ?>" autocomplete="off" required />
		</div>
		
		<?php
		if(isset($instance[ 'show_radius' ])){if($instance[ 'show_radius' ]){?>
			<div class="form-col">
				<?php 
				if($instance['show_5m']) {
					$radius = 5;
				} elseif($instance['show_10m']) {
					$radius = 10;
				} elseif($instance['show_15m']) {
					$radius = 15;
				} elseif($instance['show_20m']) {
					$radius = 20;
				} elseif($instance['show_25m']) {
					$radius = 25;
				} else {
					$radius = 50;
				}
				
				if(isset($_GET['radius'])) $radius = $_GET['radius'] ?>
				<label for="radius"><?php _e( 'Radius', 'wp_volunteer-plus' )?></label>
				<select name="radius">
					<?php
					if($instance['show_5m']) {echo '<option value="5"'; if($radius == 5) { echo ' selected'; } echo '>5 miles</option>';}
					if($instance['show_10m']) {echo '<option value="10"'; if($radius == 10) { echo ' selected'; } echo '>10 miles</option>';}
					if($instance['show_15m']) {echo '<option value="15"'; if($radius == 15) { echo ' selected'; } echo '>15 miles</option>';}
					if($instance['show_20m']) {echo '<option value="20"'; if($radius == 20) { echo ' selected'; } echo '>20 miles</option>';}
					if($instance['show_25m']) {echo '<option value="25"'; if($radius == 25) { echo ' selected'; } echo '>25 miles</option>';}
					if($instance['show_50m']) {echo '<option value="50"'; if($radius == 50) { echo ' selected'; } echo '>50 miles</option>';}
					?>
				</select>
			</div>
		<?php } ELSE { $radius = 10;}}?>

		<?php if(isset($instance[ 'show_keyword' ])){if($instance[ 'show_keyword' ]){?>
			<div class="form-col">
				<label for="keyword">Keyword</label>
				<input type="text" name="keyword" placeholder="Enter a keyword" value="<?php echo isset($_GET["keyword"]) ? $_GET["keyword"] : ''; ?>" autocomplete="off" />
			</div>
		<?php }}?>



		<?php if(isset($instance[ 'show_interests' ])){if($instance[ 'show_interests' ]){?>
			<div class="form-col">
				<label for="interests"><?php _e( 'Interests', 'wp_volunteer-plus' )?></label>
				<?php
					$response_code = wp_remote_retrieve_response_code($interests);
					if($response_code == 200) {
						$interests = json_decode($interests['body'], true);
						echo '<div class="colcontainer">';             
						foreach($interests as $interest) {
							if(isset($_GET['interests'])) {
								if(in_array($interest['id'], $_GET['interests'])) {
									echo"<label class='colitem-selected'><input type='checkbox' name='interests[".$interest['id']."]' value='".$interest['id']."' checked />".$interest['interest']."</label><br />";
								} else {
									// not filtered when page returned
									echo"<label class='colitem'><input type='checkbox' name='interests[".$interest['id']."]' value='".$interest['id']."' />".$interest['interest']."</label><br />";
								}
							} else {
									echo"<label class='colitem'><input type='checkbox' name='interests[".$interest['id']."]' value='".$interest['id']."' />".$interest['interest']."</label><br />";
							}
						}
						echo '</div>';
					} else {
						echo '(Error code '.$response_code.')';
					}
				?>
			</div>
		<?php }}?>


		<?php if(isset($instance[ 'show_activities' ])){if($instance[ 'show_activities' ]){?>
			<div class="form-col">
				<label for="activities"><?php _e( 'Activities', 'wp_volunteer-plus' )?></label>
				<?php
					$response_code = wp_remote_retrieve_response_code($activities);
					if($response_code == 200) {
						$activities = json_decode($activities['body'], true);
						echo '<div class="colcontainer">';             
						foreach($activities as $activity) {
							if(isset($_GET['activities'])) {
								if(in_array($activity['id'], $_GET['activities'])) {
									echo"<label class='colitem-selected'><input type='checkbox' name='activities[".$activity['id']."]' value='".$activity['id']."' checked />".$activity['activity']."</label><br />";
								} else {
									echo"<label class='colitem'><input type='checkbox' name='activities[".$activity['id']."]' value='".$activity['id']."' />".$activity['activity']."</label><br />";
								}
							} else {
									echo"<label class='colitem'><input type='checkbox' name='activities[".$activity['id']."]' value='".$activity['id']."' />".$activity['activity']."</label><br />";
							}
						}
						echo '</div>';
					} else {
						echo '(Error code '.$response_code.')';
					}
				?>
			</div>
		<?php }}?>

		
		<?php if(isset($instance[ 'show_availability_full' ])){if($instance[ 'show_availability_full' ]){?>
			<div class="form-col">
				<label for="availability"><?php _e( 'When are you available?', 'wp_widget_plugin' )?></label>
	
				<?php 

				$periods = array(
				'mon_mor','mon_aft','mon_eve',
				'tue_mor','tue_aft','tue_eve',
				'wed_mor','wed_aft','wed_eve',
				'thu_mor','thu_aft','thu_eve',
				'fri_mor','fri_aft','fri_eve',
				'sat_mor','sat_aft','sat_eve',
				'sun_mor','sun_aft','sun_eve'
				);

				$days = array('mon','tue','wed','thu','fri','sat','sun');
				$dayperiods = array('AM','PM','Eve');

				echo '<table><tr><th>&nbsp;</th><th>AM</th><th>PM</th><th>Eve</th></tr>';
	
				$index = 0;
				foreach($days as $day) {
					echo '<tr>';
					echo '<th>'.ucfirst($day).'</th>';
					foreach($dayperiods as $dayperiod) {
						echo '<td><input class="volplus-checkbox" type="checkbox" name="'.$periods[$index].'" value="1"';
						if(isset($_GET[$periods[$index]])){
							if($_GET[$periods[$index]]) {
								echo ' checked';
							} 
						}
						echo '/></td>';
						$index++;
					}
					echo '</tr>';
				}
				echo '</table>';
		
				?>
			</div>
		<?php }}?>
		
		
		
	<button type="submit">Search</button>
	
	</form>
	
 
    <?php echo $args['after_widget'];
  }



  // Create the admin area widget settings form.
//  public function form( $instance ) {
  function form( $instance ) {
// Check values 
	if( $instance ) { 
		$title    = esc_attr( $instance['title'] ); 
		$show_radius = esc_attr( $instance['show_radius'] );
		$show_5m = esc_attr( $instance['show_5m'] );
		$show_10m = esc_attr( $instance['show_10m'] );
		$show_15m = esc_attr( $instance['show_15m'] );
		$show_20m = esc_attr( $instance['show_20m'] );
		$show_25m = esc_attr( $instance['show_25m'] );
		$show_50m = esc_attr( $instance['show_50m'] );
		$show_keyword = esc_attr( $instance['show_keyword'] );
		$show_interests = esc_attr( $instance['show_interests'] );
		$show_activities = esc_attr( $instance['show_activities'] );
		$show_availability_full = esc_attr( $instance['show_availability_full'] );
	} else { 
		$title    = ''; 
		$show_radius    = '1'; 
		$show_5m    = '1'; 
		$show_10m    = '1'; 
		$show_15m    = '1'; 
		$show_20m    = '1'; 
		$show_25m    = '1'; 
		$show_50m    = '1'; 
		$show_keyword = '1';
		$show_interests = '1';
		$show_activities = '1';
		$show_availability_full = '1';
	} ?>
	
	<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title', 'wp_volunteer-plus' ); ?></label>
	<input class='widefat' id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
	</p>
	<h2><?php _e( 'Show Sections', 'wp-volunteer-plus' ); ?></h2>
	<p><?php _e( '(Postcode is always shown)', 'wp-volunteer-plus' ); ?></p>
	<p>
	<input id="<?php echo esc_attr( $this->get_field_id( 'show_radius' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_radius' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $show_radius ); ?> />
	<label for="<?php echo esc_attr( $this->get_field_id( 'show_radius' ) ); ?>"><?php _e( 'Radius (defaults to 5 miles if not shown)', 'wp-volunteer-plus' ); ?></label>
	</p>
	<p>
	<input id="<?php echo esc_attr( $this->get_field_id( 'show_keyword' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_keyword' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $show_keyword ); ?> />
	<label for="<?php echo esc_attr( $this->get_field_id( 'show_keyword' ) ); ?>"><?php _e( 'Keyword', 'wp-volunteer-plus' ); ?></label>
	</p>
	<p>
	<input id="<?php echo esc_attr( $this->get_field_id( 'show_interests' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_interests' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $show_interests ); ?> />
	<label for="<?php echo esc_attr( $this->get_field_id( 'show_interests' ) ); ?>"><?php _e( 'Interests', 'wp-volunteer-plus' ); ?></label>
	</p>
	<p>
	<input id="<?php echo esc_attr( $this->get_field_id( 'show_activities' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_activities' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $show_activities ); ?> />
	<label for="<?php echo esc_attr( $this->get_field_id( 'show_activities' ) ); ?>"><?php _e( 'Activities', 'wp-volunteer-plus' ); ?></label>
	</p>
	<p>
	<input id="<?php echo esc_attr( $this->get_field_id( 'show_availability_full' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_availability_full' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $show_availability_full ); ?> />
	<label for="<?php echo esc_attr( $this->get_field_id( 'show_availability_full' ) ); ?>"><?php _e( 'Availability (full matrix)', 'wp-volunteer-plus' ); ?></label>
	</p>
	<h2><?php _e( 'Show Radius Options', 'wp-volunteer-plus' ); ?></h2>
	<p>
	<input id="<?php echo esc_attr( $this->get_field_id( 'show_5m' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_5m' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $show_5m ); ?> />
	<label for="<?php echo esc_attr( $this->get_field_id( 'show_5m' ) ); ?>"><?php _e( '5 miles', 'wp-volunteer-plus' ); ?></label>
	</p>
	<p>
	<input id="<?php echo esc_attr( $this->get_field_id( 'show_10m' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_10m' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $show_10m ); ?> />
	<label for="<?php echo esc_attr( $this->get_field_id( 'show_10m' ) ); ?>"><?php _e( '10 miles', 'wp-volunteer-plus' ); ?></label>
	</p>
	<p>
	<input id="<?php echo esc_attr( $this->get_field_id( 'show_15m' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_15m' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $show_15m ); ?> />
	<label for="<?php echo esc_attr( $this->get_field_id( 'show_15m' ) ); ?>"><?php _e( '15 miles', 'wp-volunteer-plus' ); ?></label>
	</p>
	<p>
	<input id="<?php echo esc_attr( $this->get_field_id( 'show_20m' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_20m' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $show_20m ); ?> />
	<label for="<?php echo esc_attr( $this->get_field_id( 'show_20m' ) ); ?>"><?php _e( '20 miles', 'wp-volunteer-plus' ); ?></label>
	</p>
	<p>
	<input id="<?php echo esc_attr( $this->get_field_id( 'show_25m' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_25m' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $show_25m ); ?> />
	<label for="<?php echo esc_attr( $this->get_field_id( 'show_25m' ) ); ?>"><?php _e( '25 miles', 'wp-volunteer-plus' ); ?></label>
	</p>
	<p>
	<input id="<?php echo esc_attr( $this->get_field_id( 'show_50m' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_50m' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $show_50m ); ?> />
	<label for="<?php echo esc_attr( $this->get_field_id( 'show_50m' ) ); ?>"><?php _e( '50 miles', 'wp-volunteer-plus' ); ?></label>
	</p>
	

	<?php
}

  // Apply settings to the widget instance.
//  public function update( $new_instance, $old_instance ) {
  function update( $new_instance, $old_instance ) {
    $instance = $old_instance;
    $instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
    $instance[ 'show_radius' ] = strip_tags( $new_instance[ 'show_radius' ] );
    $instance[ 'show_5m' ] = strip_tags( $new_instance[ 'show_5m' ] );
    $instance[ 'show_10m' ] = strip_tags( $new_instance[ 'show_10m' ] );
    $instance[ 'show_15m' ] = strip_tags( $new_instance[ 'show_15m' ] );
    $instance[ 'show_20m' ] = strip_tags( $new_instance[ 'show_20m' ] );
    $instance[ 'show_25m' ] = strip_tags( $new_instance[ 'show_25m' ] );
    $instance[ 'show_50m' ] = strip_tags( $new_instance[ 'show_50m' ] );
    $instance[ 'show_keyword' ] = strip_tags( $new_instance[ 'show_keyword' ] );
    $instance[ 'show_interests' ] = strip_tags( $new_instance[ 'show_interests' ] );
    $instance[ 'show_activities' ] = strip_tags( $new_instance[ 'show_activities' ] );
    $instance[ 'show_availability_full' ] = strip_tags( $new_instance[ 'show_availability_full' ] );
    return $instance;
  }

}

// Register the widget.
function register_volplus_Search_Widget() { 
  register_widget( 'volplus_Search_Widget' );
}
add_action( 'widgets_init', 'register_volplus_Search_Widget' );

?>