<?php

require_once MY_PLUGIN_PATH . 'includes/volplus_Functions.php';

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
//print_r($querystring);
$returnstring = http_build_query($querystring,'', '&');
//echo '<br>Rebuilt:<br>'.$querystring;
//echo '<br>';
//echo '<br>';
//echo $_SERVER['QUERY_STRING'].'<br/>';
//$querystring = explode('&',$_SERVER['QUERY_STRING']);
//print_r($querystring);
//echo '<br>Rebuilt:'.http_build_query($querystring,'', '&');
//echo '<br/>';

$response_code = wp_remote_retrieve_response_code($opportunities);
$opportunities = json_decode($opportunities['body'], true);
$location = ["","No Location","Working from home","Organisational Address","Specific Address","Multiple Specific Addresses","Countrywide","Regional"];

//get_header();

if($response_code == 200) {
	
		foreach($opportunities['data'] as $opportunity) { ?>
			
			<div class="volplus-list">
								
				<h2><a href="/opportunities/<?php echo $opportunity['id'].'/?'.$querystring; ?>"><?php echo remove_brackets($opportunity['opportunity']); ?></a></h2>
				
				<p class="organisation"><?php echo remove_brackets($opportunity['organisation']); ?></p>
				
				<?php if(array_key_exists('distance', array_filter($opportunity))) {
					 echo 'Distance '.round($opportunity['distance'],1).' miles';
					 }else{ ?>
				<p class="location"><?php echo $location[$opportunity['location']]; ?> 
					 	
				<?php }?>
				</p>
				
				<a class="button" href="/opportunities/<?php echo $opportunity['id'].'/?'.$querystring; ?>">View Opportunity</a>
				
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
  public function form( $instance ) {
    $title = ! empty( $instance['title'] ) ? $instance['title'] : ''; 
    ?>
    <p>
      <label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
      <input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>" />
    </p>
     <?php
  }


  // Apply settings to the widget instance.
  public function update( $new_instance, $old_instance ) {
    $instance = $old_instance;
    $instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
    return $instance;
  }

}

// Register the widget.
function jpen_register_volplus_Opportunities_List_Widget() { 
  register_widget( 'volplus_Opportunities_List_Widget' );
}
add_action( 'widgets_init', 'jpen_register_volplus_Opportunities_List_Widget' );

?>