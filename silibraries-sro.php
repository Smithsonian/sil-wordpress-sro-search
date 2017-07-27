<?php 
/*
Plugin Name: Smithsonian Libraries SRO
Plugin URI:  http://research.si.edu
Description: Basic support for searching the SRO publications database
Version:	 20170727
Author:	  Joel Richard
Author URI:  https://library.si.edu/staff/joel-richard
License:	 Public Domain
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
require_once(ABSPATH . 'wp-admin/includes/template.php'); 

class SROSettingsPage {
	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;

	/**
	 * Start up
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}

	/**
	 * Add options page
	 */
	public function add_plugin_page() {
		// This page will be under "Settings"
		add_options_page(
			'SRO Settings Admin', 
			'SRO', 
			'manage_options', 
			'sro-settings', 
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Options page callback
	 */
	public function create_admin_page() {
		// Set class property
		$this->options = get_option( 'sro_options' );
		?>
		<div class="wrap">
			<h1>SRO Search Settings</h1>
			<form method="post" action="options.php">
			<br>
			<?php
				// This prints out all hidden setting fields
				settings_fields( 'sro_option_group' );
				do_settings_sections( 'sro-settings' );
				submit_button();
			?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register and add settings
	 */
	public function page_init() {		
		register_setting(
			'sro_option_group', // Option group
			'sro_options', // Option name
			array( $this, 'sanitize' ) // Sanitize
		);

		add_settings_section(
			'server_section', // ID
			'', // Title
			array( $this, 'print_server_instructions' ), // Callback
			'sro-settings' // Page
		);  

		add_settings_field(
			'server_url', // ID
			'Server URL', // Title 
			array( $this, 'server_callback' ), // Callback
			'sro-settings', // Page
			'server_section' // Section		   
		);	  


		add_settings_section(
			'query_section', // ID
			'', // Title
			array( $this, 'print_query_instructions' ), // Callback
			'sro-settings' // Page
		);  

		add_settings_field(
			'query_extra', 
			'Query Extra Paramaters', 
			array( $this, 'query_extra_callback' ), 
			'sro-settings', 
			'query_section'
		);	  
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public function sanitize( $input ) {
		$new_input = array();
		if( isset( $input['server_url'] ) )
			$new_input['server_url'] = sanitize_text_field($input['server_url']);

		if( isset( $input['query_extra'] ) )
			$new_input['query_extra'] = sanitize_text_field($input['query_extra']);

		return $new_input;
	}

	/** 
	 * Print the Section text
	 */
	public function print_server_instructions() {
		print 'Please enter the URL for the SRO Server.';
	}

	public function print_query_instructions() {
		print 'Enter a prefix or some other data to be sent along with your query.';
	}

	/** 
	 * Get the settings option array and print one of its values
	 */
	public function server_callback() {
		printf(
			'<input type="text" id="server_url" name="sro_options[server_url]" size="30" value="%s" />',
			isset( $this->options['server_url'] ) ? esc_attr( $this->options['server_url']) : ''
		);
	}

	/** 
	 * Get the settings option array and print one of its values
	 */
	public function query_extra_callback() {
		printf(
			'<input type="text" id="query_extra" name="sro_options[query_extra]" size="30" value="%s" />',
			isset( $this->options['query_extra'] ) ? esc_attr( $this->options['query_extra']) : ''
		);
	}
}

// Creating the widget 
class sro_search_widget extends WP_Widget {
	function __construct() {
		parent::__construct(
			
			// Base ID of your widget
			'sro_search_widget', 
			
			// Widget name will appear in UI
			__('SRO Search', 'sro_widget_domain'), 
			
			// Widget description
			array( 'description' => __( 'Simple Search Box for searching publications.', 'sro_widget_domain' ), ) 
		);
	}
	
	// Creating widget front-end
	
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		
		// before and after widget arguments are defined by themes
		print $args['before_widget'];
		if ( ! empty( $title ) ) {
			print $args['before_title'] . $title . $args['after_title'];
		}
		
		// This is where you run the code and display the output
		print __( sro_get_form() , 'sro_widget_domain' );
		print $args['after_widget'];
	}
	
	// Widget Backend 
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'New title', 'sro_widget_domain' );
		}
		// Widget admin form
		?>
		<p>
			<label for="<?php print $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php print $this->get_field_id( 'title' ); ?>" name="<?php print $this->get_field_name( 'title' ); ?>" type="text" value="<?php print esc_attr( $title ); ?>" />
		</p>
		<?php 
	}
	
	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}
} // Class wpb_widget ends here

	 function sro_get_form($type = 'basic') {
		$ret = '<form name="sro_basic_search" method="GET" action="/">';
		$ret .= '<input type="hidden" name="action" value="sro_search_results">';
		if ($type == 'basic') {
			$ret .= '<input type="text" id="sro_q" name="sro_q" />';
		}
		if ($type == 'advanced') {
			$ret .= "<h2>Advanced Search</h2>";
			$ret .= '<input type="text" id="sro_q" name="sro_q" style="width: 60%" />';		
		}
		$ret .= get_submit_button('Go', 'primary large','sro_submit', false);
		$ret .= '</form>';
		return $ret;
	}
	
	

function sro_search_submit() {
	
	if (isset($_GET['action']) && $_GET['action'] === 'sro_search_results') {
		// Do the search at SRO in JSON
		$results = null;

		
		if (isset($_GET['sro_q']) && $_GET['sro_q']) {
			$results = file_get_contents('http://research.si.edu/export/srb_search_export_action.cfm?search_term='.$_GET['sro_q'].'&submit=Export+data&date=&format=JSON&Unit=All&count=2500');
		}
			
		
		// Print some output	
		get_header();
		print '<div id="primary" class="content-area">';
		print '<main id="main" class="site-main" role="main">';
		print "<h1>Publications Search</h1>";
		print sro_get_form('advanced');

		if ($results) {
			print "<h2>Search Results</h2>";
			$results = json_decode($results);
			$count = 1;
			foreach ($results as $r) {
				$r = $r->reference->article;
// 				print "<strong>id:</strong> ".$r->id."<br>";
				print "<strong>title:</strong> ".$r->title."<br>";
				print "<strong>author_display:</strong> ".$r->author_display."<br>";
				print "<strong>journal:</strong> ".$r->journal."<br>";
// 				print "<strong>smithsonian_author_id:</strong> ".$r->smithsonian_author_id."<br>";
				print "<strong>date:</strong> ".$r->date."<br>";
				print "<strong>volume:</strong> ".$r->volume."<br>";
				print "<strong>issue:</strong> ".$r->issue."<br>";
// 				print "<strong>doi:</strong> ".$r->doi."<br>";
// 				print "<strong>pubtype:</strong> ".$r->pubtype."<br>";
				print "<hr>";
				$count++;
				if ($count > 10) {
					break;
				}
			}
		}

		print '</main><!-- .site-main -->';
		get_sidebar( 'content-bottom' );
		print '</div><!-- .content-area -->';
		get_sidebar();
		get_footer(); 
		die;
	}
}


if( is_admin() )
	$my_settings_page = new SROSettingsPage();
	
function sro_load_widgets() {
	register_widget( 'sro_search_widget' );
}

add_action( 'widgets_init', 'sro_load_widgets' );

add_action( 'init', 'sro_search_submit' );



