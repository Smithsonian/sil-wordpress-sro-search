<?php 
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class SROSettingsPage {
	/**
	 * Holds the values to be used in the fields callbacks
	 */
	public $options;

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
		print 'Please enter the URL for the SRO Server. (default: http://staff.research.si.edu/search/)';
	}

	public function print_query_instructions() {
		print 'Enter a prefix or some other data to be sent along with your query.';
	}

	/** 
	 * Get the settings option array and print one of its values
	 */
	public function server_callback() {
		printf(
			'<input type="text" id="server_url" name="sro_options[server_url]" size="100" value="%s" />',
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
