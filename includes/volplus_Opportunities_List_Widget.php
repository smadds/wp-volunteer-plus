<?php

require_once VOLPLUS_PATH . 'includes/volplus_Functions.php';

class volplus_Opportunities_List_Widget extends WP_Widget {


  // Set up the widget name and description.
  public function __construct() {
    $widget_options = array( 'classname' => 'Opportunities_List_Widget', 'description' => 'Opportunities List Volunteer Plus' );
    parent::__construct( 'Opportunities_List_Widget', 'Volunteer Plus Opportunities List', $widget_options );
  }


// Create the widget output.
  public function widget( $args, $instance ) {

    $title = apply_filters( 'widget_title', $instance[ 'title' ] );
// before and after widget arguments are defined by themes
    echo $args['before_widget'];
    if ( ! empty( $title ) )
    echo $args['before_title'] . $title . $args['after_title'];


//	volplus_list_opportunities_func();
$opportunities = wp_remote_get(API_URL . 'opportunities?'.strtolower($_SERVER['QUERY_STRING']), array('headers' => array('Authorization' => 'Bearer '.API_KEY)));

//echo $_SERVER['QUERY_STRING'].'<br/>';
parse_str($_SERVER['QUERY_STRING'],$querystring);

$response_code = wp_remote_retrieve_response_code($opportunities);
$opportunities = json_decode($opportunities['body'], true);
$location = ["","No Location","Working from home","Organisational Address","Specific Address","Multiple Specific Addresses","Countrywide","Regional"];

//get_header();

if($response_code == 200) {
	
		foreach($opportunities['data'] as $opportunity) { ?>
			<?php $querystring['id'] = $opportunity['id'];$returnstring = http_build_query($querystring,'', '&');?>			
			<div class="volplus-list">
				<h2><a href="/opportunities/?<?php echo $returnstring; ?>"><?php echo remove_brackets($opportunity['opportunity']); ?></a></h2>
				
				<?php if(isset($instance[ 'show_organisation' ])){if($instance[ 'show_organisation' ]){?>
					<p class="organisation"><?php echo remove_brackets($opportunity['organisation']); ?></p>
				<?php }}?>
				
				<?php if(isset($instance[ 'show_location' ])){if($instance[ 'show_location' ]){?>
					<?php if(array_key_exists('distance', array_filter($opportunity))) {
						 echo 'Distance '.round($opportunity['distance'],1).' miles';
						 }else{ ?>
					<p class="location"><?php echo $location[$opportunity['location']]; ?> 
					<?php }?>
					</p>
				<?php }}?>

				<?php if(isset($instance[ 'show_button' ])){if($instance[ 'show_button' ]){?>
					<a class="button" href="/opportunities/?<?php echo $returnstring; ?>">View Opportunity</a>
				<?php }}?>
				
			</div>
			
		<?php 
	  }?>
	  
	  		<ul class="volplus-pagination">
			<?php
				if($opportunities['last_page']!== 1) { //don't bother if just 1 page
				
					if($opportunities['current_page'] > 1){ //not on 1st page
						$querystring['Page'] = $opportunities['current_page']-1;
						echo "<li><a href='/search?".http_build_query($querystring,'', '&')."'>Previous</a></li>";
					}
					
					foreach (range(max(1,$opportunities['current_page']-5), min($opportunities['current_page']+5,$opportunities['last_page'])) as $number) {
						$querystring['Page'] = $number;
						if($opportunities['current_page'] == $number) { // current page
							echo "<li><a href='/search?".http_build_query($querystring,'', '&')."' class='current'>".$number."</a></li>";
						} else { //not current page(s))
							echo "<li><a href='/search?".http_build_query($querystring,'', '&')."'>".$number."</a></li>";
						}
					}
					
					if($opportunities['current_page'] < $opportunities['last_page']){
						$querystring['Page'] = $opportunities['current_page']+1;
						echo "<li><a href='/search?".http_build_query($querystring,'', '&')."'>Next</a></li>";
					}
					
				}
				
			?>
		</ul>
		
	
  <?php } elseif($response_code == 204) { ?>
		
		<h2>No opportunities found</h2>
		
		<p>Sorry, no opportunities could be found for your search criteria. Please amend your search and try again.</p>
		
	<?php 
	} elseif($response_code == 422) { ?>
		
		<ul class="volunteer-plus-error">
			<li>Sorry, an error occurred. Please try again later.</li>
		</ul>
		
  <?php
  }

  echo $args['after_widget'];
}






  // Create the admin area widget settings form.

function form( $instance ) { 
// Check values 
	if( $instance ) { 
		$title    = esc_attr( $instance['title'] ); 
		$show_organisation = esc_attr( $instance['show_organisation'] );
		$show_location    = esc_attr( $instance['show_location'] ); 
		$show_button    = esc_attr( $instance['show_button'] ); 
	} else { 
		$title    = ''; 
		$show_organisation = '1';
		$show_location = '1';
		$show_button = '1';
	} ?>
	
	<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title', 'wp_widget_plugin' ); ?></label>
	<input class='widefat' id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
	</p>
	<h2><?php _e( 'Show Sections', 'wp_widget_plugin' ); ?></h2>
	<p>
	<input id="<?php echo esc_attr( $this->get_field_id( 'show_organisation' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_organisation' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $show_organisation ); ?> />
	<label for="<?php echo esc_attr( $this->get_field_id( 'show_organisation' ) ); ?>"><?php _e( 'Organisation', 'wp-volunteer-plus' ); ?></label>
	</p>
	<p>
	<input id="<?php echo esc_attr( $this->get_field_id( 'show_location' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_location' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $show_location ); ?> />
	<label for="<?php echo esc_attr( $this->get_field_id( 'show_location' ) ); ?>"><?php _e( 'Location', 'wp-volunteer-plus' ); ?></label>
	</p>
	<p>
	<input id="<?php echo esc_attr( $this->get_field_id( 'show_button' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_button' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $show_button ); ?> />
	<label for="<?php echo esc_attr( $this->get_field_id( 'show_button' ) ); ?>"><?php _e( 'View Opportunity button', 'wp-volunteer-plus' ); ?></label>
	</p>

	<?php
}


  // Apply settings to the widget instance.
  public function update( $new_instance, $old_instance ) {
    $instance = $old_instance;
    $instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
    $instance[ 'show_organisation' ] = strip_tags( $new_instance[ 'show_organisation' ] );
    $instance[ 'show_location' ] = strip_tags( $new_instance[ 'show_location' ] );
    $instance[ 'show_button' ] = strip_tags( $new_instance[ 'show_button' ] );
    return $instance;
  }

}

// Register the widget.
function register_volplus_Opportunities_List_Widget() { 
  register_widget( 'volplus_Opportunities_List_Widget' );
}
add_action( 'widgets_init', 'register_volplus_Opportunities_List_Widget' );

?>