<?php 
/*
Plugin Name: Smithsonian Libraries SRO
Plugin URI:  http://research.si.edu
Description: Basic support for searching the SRO publications database
Version:     20170727
Author:      Joel Richard
Author URI:  https://library.si.edu/staff/joel-richard
License:     Public Domain
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
require_once(ABSPATH . 'wp-admin/includes/template.php');
require_once(ABSPATH .'wp-content/plugins/silibraries-sro/class.PaginationLinks.php');
require_once(ABSPATH .'wp-content/plugins/silibraries-sro/admin.php');


/* 
 * Create the widget that will allow us to add the search form to the sidebar
 */
class SROSearchWidget extends WP_Widget {

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

  /* 
   * Creating widget front-end
   */ 
	public function widget( $args, $instance ) {
		global $wpSROSearch;

		$title = apply_filters( 'widget_title', $instance['title'] );

		// before and after widget arguments are defined by themes
		print $args['before_widget'];
		if ( ! empty( $title ) ) {
			print $args['before_title'] . $title . $args['after_title'];
		}

		// This is where you run the code and display the output
		print __( $wpSROSearch->get_form() , 'sro_widget_domain' );
		print $args['after_widget'];
	}

  /* 
   * Widget Backend
   */ 
	public function form( $instance ) {
		if ( !empty( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		} else {
			$title = __( 'New title', 'sro_widget_domain' );
		}
		// Widget admin form
		echo '<p>';
		echo '	<label for="'.$this->get_field_id( 'title' ).'"'._e( 'Title:' ).'</label>';
		echo '	<input class="widefat" id="'.$this->get_field_id( 'title' ).'" name="'.$this->get_field_name( 'title' ).'" type="text" value="'.esc_attr( $title ).'" />';
		echo '</p>';
	}

  /* 
   * Updating widget replacing old instances with new
   */ 
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}
} 

/* 
 * Create the widget that will allow us to add a predefined search results
 * to a page. The widget will collect the search parameters that are 
 * sent directly to the search API.
 */
class SROFixedSearchWidget extends WP_Widget {

	function __construct() {
		parent::__construct(

			// Base ID of your widget
			'sro_fixed_search_widget',

			// Widget name will appear in UI
			__('SRO Fixed Search', 'sro_fixed_widget_domain'),

			// Widget description
			array( 'description' => __( 'Static Search Results from SRO', 'sro_fixed_widget_domain' ), )
		);
	}

  /* 
   * Creating widget front-end
   */ 
	public function widget( $args, $instance ) {
		global $wpSROSearch;

		$title = apply_filters( 'widget_title', $instance['title'] );

		// before and after widget arguments are defined by themes
		print $args['before_widget'];
		if ( ! empty( $title ) ) {
			print $args['before_title'] . $title . $args['after_title'];
		}
		// Do the search with the parameters, format and return the results
		// This is where you run the code and display the output
		$options = get_option(
			'sro_options',
			array('server_url' => 'http://research.si.edu/search/', 'query_extra' => '')
		);

		$json = $wpSROSearch->_execute_query(
			$options['server_url'], 
			array('full_query' => $instance['search_query'])
		);

		$html = $wpSROSearch->_format_html_results(
			$json, $instance['max']
		);

		print '<div id="sro">';
		print __($html, 'sro_widget_domain');
		print '</div>';

		print $args['after_widget'];
	}

  /* 
   * Widget Backend
   */ 
	public function form( $instance ) {
		if ( !empty( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		} else {
			$title = __( 'New title', 'sro_fixed_widget_domain' );
		}
		if ( !empty( $instance[ 'search_query' ] ) ) {
			$search_query = $instance[ 'search_query' ];
		} else {
			$search_query = __( 'a=b&c=d', 'sro_fixed_widget_domain' );
		}
		if ( !empty( $instance[ 'max' ] ) ) {
			$max = $instance[ 'max' ];
		} else {
			$max = __( '5', 'sro_fixed_widget_domain' );
		}
		// Widget admin form
		echo '<p>';
		echo '	<label for="'.$this->get_field_id( 'title' ).'"'._e( 'Title:' ).'</label>';
		echo '	<input class="widefat" id="'.$this->get_field_id( 'title' ).'" name="'.$this->get_field_name( 'title' ).'" type="text" value="'.esc_attr( $title ).'" />';
		echo '<p>';
		echo '</p>';
		echo '	<label for="'.$this->get_field_id( 'search_query' ).'"'._e( 'Query String:' ).'</label>';
		echo '	<input class="widefat" id="'.$this->get_field_id( 'search_query' ).'" name="'.$this->get_field_name( 'search_query' ).'" type="text" value="'.esc_attr( $search_query ).'" />';
		echo '</p>';
		echo '</p>';
		echo '	<label for="'.$this->get_field_id( 'max' ).'"'._e( '# of Results:' ).'</label>';
		echo '	<input class="widefat" id="'.$this->get_field_id( 'max' ).'" name="'.$this->get_field_name( 'max' ).'" type="text" value="'.esc_attr( $max ).'" />';
		echo '</p>';
	}

  /* 
   * Updating widget replacing old instances with new
   */ 
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['search_query'] = ( ! empty( $new_instance['search_query'] ) ) ? strip_tags( $new_instance['search_query'] ) : '';
		$instance['max'] = ( ! empty( $new_instance['max'] ) ) ? strip_tags( $new_instance['max'] ) : '';
		return $instance;
	}
}

/* The bulk of the functionality of the searching.
 * This contains the logic to process and populate the search results
 * as well as the advanced search and export.
 */
class SROSearch {

  /* 
   * Initialize and register the shortcode that handles the display of results.
   */ 
	public function __construct() {
		if(is_admin()) {
			$my_settings_page = new SROSettingsPage();
		}

		add_shortcode('sro-search-results', array($this, 'display_results'));
	}

  /* 
   * Builds a search form to display on the web page. 
   *
   * This builds the basic or advanced form, fills it in and returns it.
   * this does not print the form to the page.
   */ 
	public function get_form($type = 'basic', $query = '') {
	
		$params = $this->_clean_params();
		
		$ret = '<form name="sro_search" method="GET" action="/publications/" id="sro_'.esc_attr($type).'_search">';
		$ret .= '<input type="hidden" name="action" value="sro_search_results">';
		if ($type == 'basic') {
			$ret .= '<label class="hidden" for="q">Enter Search Term</label>';
			$ret .= '<p class="note">Search our database of over 80,000 publications and datasets.<p>';
			$ret .= '<input type="text" id="q" name="q" placeholder="Enter Search Term" />';
			$ret .= '<p class="advanced-link"><a href="/publications/?advanced=1">Advanced Search</a></p>';
			$ret .= get_submit_button('Go', 'primary large', null, false);
		}
		if ($type == 'advanced') {
// 			$ret .= "<h4>Advanced Search</h4>";
			$ret .= '<label class="hidden" for="q">Enter Search Term</label>';
			$ret .= '<input type="text" id="q" name="q" value="'.esc_attr($query).'" style="width: 60%" placeholder="Enter Search Term" />';
			$ret .= '&nbsp;'.get_submit_button('Go', 'primary large', null, false);
		}
		if ($type == 'advanced') {
			$ret .= '<br><a id="advanced-link" onClick="sroToggleAdvancedSearch();">Advanced Search</a>';
			$hide = true;
			if (!empty($params['advanced']) ||
			    !empty($params['limit']) || 
			    !empty($params['year']) || 
			    !empty($params['dept']) || 
			    (!empty($params['sort']) && $params['sort'] != 'published') || 
			    (!empty($params['send_to']) && $params['send_to'] != 'screen')){
				$hide = false;
			}
			$ret .= '<div id="advanced-search"'.($hide ? ' style="display:none"' : '').'>';
			
				$ret .= '<div class="criteria">';
					$ret .= '<label for="limit">Limit&nbsp;Search&nbsp;Term&nbsp;to:</label>&nbsp;';
					$ret .= '<select id="limit" name="limit">';
						$ret .= '<option value="">(none)</option>';
						$ret .= '<option value="author"'.($params['limit'] == 'author' ? ' selected' : '').'>Author Name</option>';
						$ret .= '<option value="author_id"'.($params['limit'] == 'author_id' ? ' selected' : '').'>Author ID</option>';
						$ret .= '<option value="journal"'.($params['limit'] == 'journal' ? ' selected' : '').'>Journal Title</option>';
					$ret .= '</select>';
					$ret .= '&nbsp;&nbsp;&nbsp; ';
					$ret .= '<label for="date">Limit&nbsp;by&nbsp;date:</label>&nbsp;';
					$ret .= '<input id="date" name="date" maxlength="4" size="4" value="'.esc_attr($params['year']).'">';
					$ret .= '&nbsp;&nbsp;&nbsp; ';
					$ret .= '<label for="date">Limit&nbsp;to&nbsp;Museum&nbsp;or&nbsp;Department:</label>&nbsp;';
					$ret .= '<select name="dept" id="dept">';
						$ret .= '<option value="" selected="selected">All</option>';
						$opts = $this->_sro_get_departments();
						foreach ($opts as $o) {
							$ret .= '<option value="'.$o['id'].'"'.($params['dept'] == $o['id'] ? ' selected' : '').'>'.$o['name'].'</option>';
						}
					$ret .= '</select>';
				$ret .= '</div>';
				$ret .= '<div class="criteria">';
					$ret .= '<label for="sort">Sort&nbsp;results&nbsp;by:</label>&nbsp;';
					$ret .= '<select id="sort" name="sort"> ';
						$ret .= '<option value="published">Date Published</option>';
						$ret .= '<option value="author"'.($params['sort'] == 'author' ? ' selected' : '').'>Author Name</option>';
						$ret .= '<option value="journal"'.($params['sort'] == 'journal' ? ' selected' : '').'>Journal Name</option>';
						$ret .= '<option value="added"'.($params['sort'] == 'added' ? ' selected' : '').'>Date Added</option>';
					$ret .= '</select>';
				$ret .= '</div>';
				$ret .= '<div class="criteria export">';
					$ret .= '<label for="send_to">Send&nbsp;Results&nbsp;to:</label>&nbsp;';
					$ret .= '<select id="send_to" name="send_to">';
						$ret .= '<option value="screen">This Window</option>';
						$ret .= '<option value="download"'.($params['send_to'] == 'download' ? ' selected' : '').'>Download</option>';
					$ret .= '</select>';
					$ret .= '&nbsp;&nbsp;&nbsp; ';
					$ret .= '<label for="export_format">Export&nbsp;Format:</label>&nbsp;';
					$ret .= '<select id="export_format" name="export_format">';
						$ret .= '<option value="json">JSON</option>';
						$ret .= '<option value="csv"'.($params['export_format'] == 'csv' ? ' selected' : '').'>Comma-Separated (CSV)</option>';
						$ret .= '<option value="ris"'.($params['export_format'] == 'ris' ? ' selected' : '').'>RIS (Zotero, Mendeley, etc)</option>';
// 						$ret .= '<option value="text"'.($_GET['export_format'] == 'text' ? ' selected' : '').'>Text Citation</option>';
					$ret .= '</select>';
				$ret .= '</div>';
			$ret .= '</div>';
		}
		$ret .= '</form>';
		return $ret;
	}
	
	/* 
	 * Santize and standardize the parameters we got from the URL. 
	 * 
	 * This is called by everyone and should be sufficient to prevent 
	 * users from being naughty.
	 *
	 * If we change any values of parameters then we should probably 
	 * update the arrays here, too.
	 */
	function _clean_params() {
		$options = get_option(
			'sro_options',
			array('server_url' => 'http://research.si.edu/search/', 'query_extra' => '')
		);

		$params = array(
			'search_term' => null,
			'advanced' => false,
			'limit' => null,
			'year' => null,
			'dept' => null,
			'sort' => 'published',
			'send_to' => 'screen',
			'export_format' => 'json',
			'perpage' => 20,
			'page' => 1,
			'server_url' => $options['server_url'],
			'query_extra' => $options['query_extra'],
		);

		if (!empty($_GET['q'])) {
			$params['search_term'] = trim($_GET['q']);
		}		

		if (!empty($_GET['advanced'])) {
			$params['advanced'] = ($_GET['advanced'] ? true : false);
		}		
		
		if (!empty($_GET['limit']) && $_GET['limit']) {
			if (in_array($_GET['limit'], array('author', 'auhtor_id', 'journal'))) {
				$params['limit'] = $_GET['limit']; // It has to be one of these values
			}
		}
		
		if (!empty($_GET['year']) && $_GET['year']) {
			$params['year'] = (int)$_GET['year']; // Cast to int to sanitize
		} elseif (!empty($_GET['date']) && $_GET['date']) {
			$params['year'] = (int)$_GET['date']; // Cast to int to sanitize
		}

		if (!empty($_GET['dept']) && $_GET['dept']) {
			$opts = $this->_sro_get_departments();
			foreach ($opts as $o) {
				if ($_GET['dept'] == $o['id']) {
					$params['dept'] = $o['id'];
					break;
				}
			}
		}

		if (!empty($_GET['sort']) && $_GET['sort']) {
			if (in_array($_GET['sort'], array('published', 'author', 'journal', 'added'))) {
				$params['sort'] = $_GET['sort']; // It has to be one of these values
			}
		}

		if (!empty($_GET['perpage']) && $_GET['perpage']) {
			$params['perpage'] = (int)$_GET['perpage']; // Cast to int to sanitize
		}

		if (!empty($_GET['pg']) && $_GET['pg']) {
			$params['page'] = (int)$_GET['pg']; // Cast to int to sanitize
		}

		if (!empty($_GET['send_to']) && $_GET['send_to']) {
			if (in_array($_GET['send_to'], array('screen', 'download'))) {
				$params['send_to'] = $_GET['send_to']; // It has to be one of these values
			}
		}
		if ($params['send_to'] == 'download') {
			$params['page'] = 'all';
			$params['perpage'] = 'all';			
		}

		if (!empty($_GET['export_format']) && $_GET['export_format']) {
			if (in_array($_GET['export_format'], array('json', 'csv', 'ris', 'text', 'altmetrics'))) {
				$params['export_format'] = $_GET['export_format']; // It has to be one of these values
			}
		}
		
		return $params;
	}

  /*
   *  Print the results to the browser page 
   */
	function display_results() {
		$params = $this->_clean_params();
		
		$results = $this->_do_search($params);

		wp_register_style('silibraries-sro-fa-reg', plugins_url('/css/font-awesome-regular.css', __FILE__));
		wp_enqueue_style('silibraries-sro-fa-reg');
		wp_register_style('silibraries-sro-fa-core', plugins_url('/css/font-awesome-core.css', __FILE__));
		wp_enqueue_style('silibraries-sro-fa-core');
		wp_register_style('silibraries-sro-fa-light', plugins_url('/css/font-awesome-light.css', __FILE__));
		wp_enqueue_style('silibraries-sro-fa-light');
		wp_register_style('silibraries-sro-fa-solid', plugins_url('/css/font-awesome-solid.css', __FILE__));
		wp_enqueue_style('silibraries-sro-fa-solid');
		wp_register_style('silibraries-sro-fa-solid', plugins_url('/css/font-awesome-solid.css', __FILE__));
		wp_enqueue_style('silibraries-sro-fa-solid');
		
		wp_register_script('sro-js', plugins_url('/js/silibraries-sro.js', __FILE__));
		wp_enqueue_script('sro-js');
		
		wp_register_script('altmetric', 'https://d1bxh8uas1mnw7.cloudfront.net/assets/embed.js');
		wp_enqueue_script('altmetric');
		
		print $this->get_form('advanced', $params['search_term']);

		if ($results) {

			// Print the output, includes all the components to make a full poage.
			print '<div id="sro">';
			print "<h2>Search Results</h2>";				

			// Calculate the pages and records and stuff for pagination
			$total_recs = $results->count;
			$total_pages = floor($total_recs / $params['perpage']);
			if ($total_recs % $params['perpage'] != 0) {
				$total_pages++;
			}				
			$min_this_page = (($params['page']-1) * $params['perpage'])+1;
			$max_this_page = min(array($params['page'] * $params['perpage'], $total_recs));
			$remaining_records = $total_recs - $max_this_page;
			if ($total_recs == 0) {
				print '<div id="summary">No results were found.</div>';				
			} else {
				print '<div id="summary">Showing '.$min_this_page."-".$max_this_page.' of about '.$total_recs.' results.</div>';
			}

			$pagination = null;
			if ($results->count > $params['perpage']) {
				$pagination =  PaginationLinks::create(
					$params['page'], $total_pages, 2, 
					'<a class="page" href="?action=sro_search_results&q=XYZZY&pg=%d&perpage='.$params['perpage'].'&sort='.$params['sort'].'&limit='.$params['limit'].'&date='.$params['year'].'&dept='.$params['dept'].'">%d</a>',
					'<span class="current">%d</span>'
				);
				$pagination = preg_replace('/XYZZY/', urlencode($_GET['q']), $pagination);
				print '<div id="pagination">'.$pagination.'</div>';
			}

			print $this->_format_html_results($results, $params['perpage'], true);
			
			print '<div id="pagination">'.$pagination.'</div>';
			print '</div>';
		}
	}
	
	/* 
	 * Export the results to a file and make the browser download it. 
	 * This is called by the api hook for 'template_redirect' because we need to
	 * export this before any output is sent to the browser. The API hook short-circuits
	 * that process.
	 */
	function download_results() {

		$params = $this->_clean_params();

		if (is_page('publications') && $params['send_to'] == 'download') {

			$results = $this->_do_search($params);

			$output = null;
			$filename = null;

			if ($params['export_format'] == 'ris') {
				$output = $this->_format_ris_results($results);
				$filename = 'search_results.ris';
				$header = 'Content-Type: text/plain';

			} elseif ($params['export_format'] == 'csv') {
				$output = trim($this->_format_csv_results($results));
				$filename = 'search_results.csv';
				$header = 'Content-Type: text/csv';

			} elseif ($params['export_format'] == 'text') {
				$output = $this->_format_text_results($results);
				$filename = 'search_results.txt';
				$header = 'Content-Type: text/plain';

			} elseif ($params['export_format'] == 'altmetrics') {
				$output = $this->_format_altmetrics_results($results);
				$filename = 'sro_altmetrics.csv';
				$header = 'Content-Type: text/html';

			} else { // ($$params['export_format'] == 'json') {
				// There is no JSON format function. We just send back what we got from the API.
				$output = json_encode($results, JSON_PRETTY_PRINT);
				$filename = 'search_results.json';
				$header = 'Content-Type: application/json';
			}

			if ($output && $filename) {				
				header($header);
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				if ($params['export_format'] != 'altmetrics') {
					header('Content-Disposition: attachment; filename="'.$filename.'"');
				}
				header('Content-Length: ' . strlen($output));
				ob_clean();
				flush();
				print($output);
				exit();
			} else {
				print '<div class="error">Error exporting data. Check the criteria and try again.</div>';
			}
			exit();
		}
	}
	
  /* 
   * Format all results for exporting to a RIS file
   */ 
	function _format_ris_results($res) {
		$output = array();
		foreach ($res->records as $r) {
			$r = $r->reference;
			if ($r->pubtype == 'article' && $r->pubtype == 'journal') {
				$output[] = 'TY  - JOUR';
				$first = true;
				foreach (_unique_authors($r->authors) as $a) {
					$output[] = ($first ? 'A1' : 'AU').'  - '.$a->name;
					$first = false;
				}
				$output[] = 'JF  - '.$r->journal;
				$output[] = 'T1  - '.$r->title;
				if ($r->volume) {
					$output[] = 'VL  - '.$r->volume;				
				}
				if ($r->issue) {
					$output[] = 'IS  - '.$r->issue;								
				}
				if ($r->start_page) {
					$output[] = 'SP  - '.$r->start_page;
				}
				if ($r->end_page) {
					$output[] = 'EP  - '.$r->end_page;
				}
				if ($r->date) {
					$output[] = 'PY  - '.$r->date;
				}
				foreach (explode(';', $r->keywords) as $k) {
					$output[] = 'KW  - '.trim($k);				
				}
				if ($r->link && strpos(strtolower($r->link), 'http')) {
					$output[] = 'UR  - '.$r->link;
								}
				if ($r->doi  && strpos($r->doi, '10')) {
					$output[] = 'DO  - '.$r->doi;
				}
				$output[] = 'ID  - '.$r->id;
				$output[] = 'U2  - '.date('Y/m/d', strtotime($r->date_added));
				$output[] = 'ER  - ';
			
			} elseif ($r->pubtype == 'chapter') {
				$output[] = 'TY  - CHAP';
				$first = true;
				foreach (_unique_authors($r->authors) as $a) {
					$output[] = ($first ? 'A1' : 'AU').'  - '.$a->name;
					$first = false;
				}
				$first = true;
				foreach ($r->editors as $a) {
					$output[] = ($first ? 'A2' : 'ED').'  - '.$a->name;
					$first = false;
				}
				$output[] = 'T1  - '.$r->title;
				$output[] = 'BT  - '.$r->book_title;
				if ($r->start_page) {
					$output[] = 'SP  - '.$r->start_page;
				}
				if ($r->end_page) {
					$output[] = 'EP  - '.$r->end_page;
				}
				if ($r->date) {
					$output[] = 'PY  - '.$r->date;
				}
				if ($r->link && strpos(strtolower($r->link), 'http')) {
					$output[] = 'UR  - '.$r->link;
								}
				if ($r->doi  && strpos($r->doi, '10')) {
					$output[] = 'DO  - '.$r->doi;
				}
				foreach (explode(';', $r->keywords) as $k) {
					$output[] = 'KW  - '.trim($k);				
				}
				$output[] = 'ID  - '.$r->id;
				$output[] = 'U2  - '.date('Y/m/d', strtotime($r->date_added));
				$output[] = 'ER  - ';

			} elseif ($r->pubtype == 'book') {
				$output[] = 'TY  - BOOK';
				$first = true;
				foreach (_unique_authors($r->authors) as $a) {
					$output[] = ($first ? 'A1' : 'AU').'  - '.$a->name;
					$first = false;
				}
				$first = true;
				foreach ($r->editors as $a) {
					$output[] = ($first ? 'A2' : 'ED').'  - '.$a->name;
					$first = false;
				}
				$output[] = 'T1  - '.$r->title;
				$output[] = 'BT  - '.$r->book_title;
				if ($r->start_page) {
					$output[] = 'SP  - '.$r->start_page;
				}
				if ($r->publisher) {
					$output[] = 'PB  - '.$r->publisher;
				}
				if ($r->publisher_place) {
					$output[] = 'CY  - '.$r->publisher_place;
				}
				if ($r->date) {
					$output[] = 'PY  - '.$r->date;
				}
				if ($r->link && strpos(strtolower($r->link), 'http')) {
					$output[] = 'UR  - '.$r->link;
								}
				if ($r->doi  && strpos($r->doi, '10')) {
					$output[] = 'DO  - '.$r->doi;
				}
				foreach (explode(';', $r->keywords) as $k) {
					$output[] = 'KW  - '.trim($k);				
				}
				$output[] = 'ID  - '.$r->id;
				$output[] = 'U2  - '.date('Y/m/d', strtotime($r->date_added));
				$output[] = 'ER  - ';
			}
		}	
		return implode("\r\n",$output);
	}

  /* 
   * Format all results for exporting to a CSV file
   */ 
	function _format_csv_results($res) {
		$output = '';
		$header = array('pubtype', 'title', 'authors', 'editors', 'journal', 'book_title', 'series', 'smithsonian_author_id', 
		                'orcid', 'volume', 'issue', 'pages', 'start_page', 'end_page', 'publisher', 'publisher_place', 
		                'date', 'link', 'doi', 'issn_isbn', 'keywords', 'acknowledgement', 'funders', 'date_added', 'id');
		$output .= str_putcsv($header);
		foreach ($res->records as $r) {
			$rec = array();
			$r = $r->reference;
			$rec[] = (empty($r->pubtype) ? '' : $r->pubtype);
			$rec[] = (empty($r->title) ? '' : $r->title);
			$authors = array();
			
			if (!empty($r->authors)) {
				foreach (_unique_authors($r->authors) as $a) {
					$authors[] = $a['name'];
				}
			}
			$rec[] = implode(';', $authors);
			$editors = array();
			if (!empty($r->editors)) {
				foreach ($r->editors as $e) {
					$editors[] = $e->name;
				}
			}
			$rec[] = implode(';', $editors);
			$rec[] = (empty($r->journal) ? '' : $r->journal);
			$rec[] = (empty($r->book_title) ? '' : $r->book_title);
			$rec[] = (empty($r->series) ? '' : $r->series);
			$rec[] = (empty($r->smithsonian_author_id) ? '' : $r->smithsonian_author_id);
			$rec[] = (empty($r->orcid) ? '' : $r->orcid);
			$rec[] = (empty($r->volume) ? '' : $r->volume);
			$rec[] = (empty($r->issue) ? '' : $r->issue);
			$rec[] = (empty($r->pages) ? '' : $r->pages);
			$rec[] = (empty($r->start_page) ? '' : $r->start_page);
			$rec[] = (empty($r->end_page) ? '' : $r->end_page);
			$rec[] = (empty($r->publisher) ? '' : $r->publisher);
			$rec[] = (empty($r->publisher_place) ? '' : $r->publisher_place);
			$rec[] = (empty($r->date) ? '' : $r->date);
			$rec[] = (empty($r->link) ? '' : $r->link);
			$rec[] = (empty($r->doi) ? '' : $r->doi);
			$rec[] = (empty($r->issn_isbn) ? '' : $r->issn_isbn);
			$rec[] = (empty($r->keywords) ? '' : $r->keywords);
			$rec[] = (empty($r->acknowledgement) ? '' : $r->acknowledgement);
			$funders = array();
			if (!empty($r->funders)) {
				foreach ($r->funders as $f) {
					$funders[] = $f->name;
				}
			}
			$rec[] = implode(';', $funders);
			$rec[] = (empty($r->date_added) ? '' : $r->date_added);
			$rec[] = $r->id;
			$output .= str_putcsv($rec);
		}
		return $output;
	}

  /* 
   * Format all results for exporting to a CSV file
   */ 
	function _format_altmetrics_results($res) {
		$output = '';		
		$output .= '<!DOCTYPE html>'."\n";
		$output .= '<html>'."\n";
		$output .= '<head>'."\n";
		$output .= '  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'."\n";
		$output .= '  <meta name="robots" content="noindex, nofollow">'."\n";
		$output .= '  <title>Smithsonian Altmetric Data Ingest</title>'."\n";
		$output .= '</head>'."\n";
		$output .= '<body>'."\n";
		$output .= preg_replace("/([\r\n]+)/", "<br>\n", $res);
		$output .= '</body>'."\n";
		$output .= '</html>'."\n";
		return $output;
	}

  /* 
   * Format all results for exporting to a text file.
   *
   * This is not currently used.
   */ 
	function _format_text_results($res) {
		// Start with the html
		$output = $this->_format_html_results($res, 1000000, false);
		// Strip out what we don't want
		$output = preg_replace('/<\/div>/', "\r\n", $output);
		$output = preg_replace('/<[^>]+>/', " ", $output);
		return $output;
	}
	
  /* 
   * Formall all results for displaying on a webpage
   */ 
	function _format_html_results($res, $perpage, $extras = false) {
		$ret = '';
		$c = 1;
		$ret .= '<div id="results">';
		foreach ($res->records as $r) {
			$ret .= $this->_format_html_entry($r->reference, $extras);
			$c++;
			if ($c > $perpage) {
				break;
			}
		}
		$ret .= '</div>';
		return $ret;
	}	

  /* 
   * Format one record while printing to a webpage
   * 
   * $include_extras will print COINS data and Altmetric 
   * Badges onto the page.
   *
   * This just encapsultates a lot of code that would otherwise
   * make the _format_html_results() messier.
   */ 
	function _format_html_entry($rec, $include_extras = false) {
		$ret = array();
		$coins = array();
		$coins[] = 'url_ver=Z39.88-2004';
		$coins[] = 'ctx_ver=Z39.88-2004';
		$coins[] = 'rfr_id=info%3Asid%2Fzotero.org%3A2';
		if (!empty($rec->authors)) {
			foreach (_unique_authors($rec->authors) as $a) {
				$coins[] = 'rft.au='.urlencode($a['name']);
			}
		}
		$coins[] = 'rft.date='.urlencode($rec->date);

		// Normalization
		if (!empty($rec->title)) {
			$rec->title = preg_replace('/<[^>].*>/', '', $rec->title);
		}
		if ($include_extras) {
			if ($rec->pubtype == 'article') {
				// COinS DATA FOR ZOTERO IMPORT
				if (!empty($rec->doi) && !empty($rec->doi)) {
					$coins[] = 'rft_id=info%3Adoi%2F'.urlencode($rec->doi);
				}
				$coins[] = 'rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Ajournal';
				$coins[] = 'rft.genre=article';
				if (!empty($rec->title)) {
					$coins[] = 'rft.atitle='.urlencode($rec->title);
				}
				if (!empty($rec->journal)) {
					$coins[] = 'rft.jtitle='.urlencode($rec->journal);
				}
				if (!empty($rec->volume) && !empty($rec->volume)) {
					$coins[] = 'rft.volume='.urlencode($rec->volume);
				}
				if (!empty($rec->issue) && !empty($rec->issue)) {
					$coins[] = 'rft.issue='.urlencode($rec->issue);
				}
				if (!empty($rec->journal)) {
				$coins[] = 'rft.stitle='.urlencode($rec->journal);
				}
				if (!empty($rec->pages) && !empty($rec->pages)) {
					if (strpos($rec->pages, '-')) {
						$p = explode('-', $rec->pages);
						$coins[] = 'rft.spage='.$p[0];
						$coins[] = 'rft.epage='.$p[1];
					} else {
						$coins[] = 'rft.spage='.$rec->pages;
					}
				}
				if (!empty($rec->issn) && !empty($rec->issn)) {
					$coins[] = 'rft.issn='.urlencode($rec->issn);
				}
			} elseif ($rec->pubtype == 'chapter') {
				if (!empty($rec->isbn)) {
					$coins[] = 'rft_id=urn%3Aisbn%3A'.$rec->isbn;
				}
				$coins[] = 'rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Abook';
				$coins[] = 'rft.genre=bookitem';
				
				if (!empty($rec->title)) {
					$coins[] = 'rft.atitle='.urlencode($rec->title);
				}
				if (!empty($book_title)) {
					$coins[] = 'rft.btitle='.$rec->book_title;
				}
				if (!empty($rec->publisher)) {
					$coins[] = 'rft.publisher='.urlencode($rec->publisher);
				}
				if (!empty($rec->pages)) {
					$coins[] = 'rft.pages='.urlencode($rec->pages);
				}
			} elseif ($rec->pubtype == 'book') {
				if (!empty($rec->isbn)) {
					$coins[] = 'rft_id=urn%3Aisbn%3A'.$rec->isbn;
				}
				$coins[] = 'rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Abook';
				$coins[] = 'rft.genre=book';
				if (!empty($rec->title)) {
					$coins[] = 'rft.btitle='.urlencode($rec->title);
				}
				if (!empty($rec->pages)) {
					$coins[] = 'rft.tpages='.urlencode($rec->pages);
				}
			}
			$ret[] =  '<span class="Z3988" title="'.implode('&amp;',$coins).'"></span>'."\n";
		} 
		
		// ------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------
		
		$itemtype = '';
		$sechma = '';
		if ($rec->pubtype == 'article') { // and (k1 does not contain 'Book review')
			$itemtype = 'http://schema.org/ScholarlyArticle';
		} elseif ($rec->pubtype == 'chapter') {
			$itemtype = 'http://schema.org/Chapter';
		} elseif ($rec->pubtype == 'book')  {
			$itemtype = 'http://schema.org/Book';
		}			

		$sechma .= '<div class="schema-dot-org">';
			$sechma .= '<div itemscope="" itemtype="'.$itemtype.'">';
				if (!empty($rec->title)) {
					$sechma .= '<span property="name">'.$rec->title.'</span>';
				}
				if ($rec->pubtype == 'book') {
					if (!empty($rec->book_title)) {
						$sechma .= '<span itemprop="name">'.$rec->book_title.'</span>';
					}
				}
				if (!empty($rec->authors)) {
					foreach (_unique_authors($rec->authors) as $a) {
						$sechma .= '<span property="author" itemscope="" itemtype="http://schema.org/Person"><span itemprop="name">'.$a['name'].'</span></span>';
					}
				}
				if (!empty($rec->editors) && !empty($rec->editors)) {
						$sechma .= '<span itemscope="" itemtype="http://schema.org/Person">';
							$sechma .= '<span itemprop="editor">';
								foreach (_unique_authors($rec->editors) as $a) {
									$sechma .= '<span itemprop="name">'.$a['name'].'</span>';
								}
							$sechma .= '</span>';
					 $sechma .= '</span>';
				}
				if (!empty($rec->date)) {
					$sechma .= '<span property="datePublished">'.$rec->date.'</span>';
				}
				if (!empty($rec->doi) && !empty($rec->doi)) {
					$sechma .= 'DOI: <a property="sameAs" href="http://dx.doi.org/'.$rec->doi.'">info:'.$rec->doi.'</a>';
				}
				$sechma .= '<span property="isPartOf" typeof="Periodical">';
				if (!empty($rec->journal)) {
					$sechma .= '<span property="name">'.$rec->journal.'</span>';
				}
				if (!empty($rec->volume) && $rec->volume > 0) {
					$sechma .= 'v. <span property="volumeNumber">'.$rec->volume.'</span>';
				}
				if (!empty($rec->issue) && $rec->issue > 0) {
					$sechma .= 'No. <span property="issueNumber">'.$rec->issue.'</span>';
				}
				if (!empty($rec->start_page)) {
					$sechma .= '<span itemprop="pageStart">'.$rec->start_page.'</span>';
				}
				if (!empty($rec->end_page)) {
					$sechma .= '<span itemprop="pageEnd">'.$rec->end_page.'</span>';
				}
				if (!empty($rec->publisher_place)) {
					$sechma .= '<span itemprop="location">'.$rec->publisher_place.'</span>';
				}
				if (!empty($rec->publisher)) {
					$sechma .= '<span itemprop="publisher">'.$rec->publisher.'</span>';
				}
				if (!empty($rec->pages)) {
					$sechma .= '<span itemprop="numberOfPages">'.$rec->pages.'</span>';
				}
				if (!empty($rec->issn_isbn)) {
					$sechma .= '<span itemprop="ISBN">'.$rec->issn_isbn.'</span>';
				}
				$sechma .= '</span>';
			$sechma .= '</div>';
		$sechma .= '</div>';


		$type = 'Other';
		$icon = 'question-circle';
		if ($rec->pubtype == 'article') { $type = 'Article'; $icon = 'file-alt'; } 
		elseif ($rec->pubtype == 'ejournal_article') { $type = 'E-Journal Article'; $icon = 'file-pdf'; } 
		elseif ($rec->pubtype == 'chapter') { $type = 'Book Chapter'; $icon = 'bookmark'; } 
		elseif ($rec->pubtype == 'book') { $type = 'Book'; $icon = 'book'; } 
		elseif ($rec->pubtype == 'book_edited') { $type = 'Book, Edited'; $icon = 'book'; } 
		elseif ($rec->pubtype == 'thesis') { $type = 'Thesis'; $icon = 'file-edit'; } 
		elseif ($rec->pubtype == 'web_page') { $type = 'Web Page'; $icon = 'globe'; } 
		elseif ($rec->pubtype == 'magazine_article') { $type = 'Magazine Article'; $icon = 'file'; } 
		elseif ($rec->pubtype == 'video_dvd') { $type = 'Video/DVD'; $icon = 'video'; } 
		elseif ($rec->pubtype == 'audio') { $type = 'Audio Recording'; $icon = 'volume-up'; } 
		elseif ($rec->pubtype == 'report') { $type = 'Report'; $icon = 'list-alt'; } 
		elseif ($rec->pubtype == 'newspaper_article') { $type = 'Newspaper Article'; $icon = 'newspaper'; } 
		elseif ($rec->pubtype == 'motion_picture') { $type = 'Motion Picture'; $icon = 'film'; } 
		elseif ($rec->pubtype == 'monograph') { $type = 'Monograph'; $icon = 'book'; } 
		elseif ($rec->pubtype == 'map') { $type = 'Map'; $icon = 'map'; } 
		elseif ($rec->pubtype == 'artwork') { $type = 'Artwork'; $icon = 'image'; } 
		elseif ($rec->pubtype == 'abstract') { $type = 'Abstract'; $icon = 'align-justify'; } 
		elseif ($rec->pubtype == 'forum_blog') { $type = 'Forum/Blog Post'; $icon = 'comments'; } 
		elseif ($rec->pubtype == 'generic') { $type = 'Generic'; $icon = 'rectangle-portrait'; } 

	// 	From Suzanne: 
	// 		author_display. date. <ACTIONABLE LINK the words in title> title. [journal]<OR>[in [editor_display}. book_title]. publisher_place, publisher, (series). volume(issue):start_page-end_page. doi<ACTIONABLE DOI>
	// 	
	// 	My interpretation
	// 	 - Author(s) followed by a period.
	// 	 - Date followed by a period. (what format? year? Month/year? Whatever's in the database?)=
	// 	 - The title, linked to somewhere else if there's a URL in the database.
	// 	 - One of the two following:
	// 		- The journal name followed by a period, in italics, if provided 
	// 		- The word "in" followed by the editors followed by a period, if provided, followed by the book title followed by a period in italics, if provided.
	// 	 - Publisher place, followed by a comma.
	// 	 - Publisher name, followed by a comma.
	// 	 - Series in parentheses, followed by a period.
	// 	 - Volume field
	// 	 - Issue field, in parentheses followed by a colon:
	// 	 - Start page
	// 	 - If end page provided, a hyphen and the end page.
	// 	 - A period. (to end the volume/issue/pages)
	// 	 - The DOI linked to the DOI url, if provided.
	 
		if ($include_extras) {
			if (!empty($rec->doi)) {
				$ret[] =  '<div class="show_metric altmetric-embed" data-badge-type="donut" data-badge-popover="left" data-hide-no-mentions="true" data-doi="'.$rec->doi.'"></div>';
			}
		}
		$ret[] =  '<div class="result fa-'.$icon.'" title="'.$type.'">';			
			// #a1# -- Author(s) followed by a period
			if (!empty($rec->author_display)) {
				$ret[] =  $rec->author_display;
			}
			// #yr# -- Date followed by a period
			if (!empty($rec->date)) {
				if (!empty($rec->author_display)) {
					if (!preg_match('/\.$/', $rec->author_display)) {
						$ret[] =  '.';
					}
				}
				$ret[] =  ' '.$rec->date;
			}

			// #ul# #t1# -- The title, linked to somewhere else if there's a URL in the database
			if (!empty($rec->link) && preg_match('/http/', $rec->link)) {
				$ret[] =  '. <a href="'.$rec->link.'">'.$rec->title.'</a>';
			} else {
				$ret[] =  '. '.$rec->title;
			}
		
			// #jf# -- One of the two following
			if (!empty($rec->journal)) {
				// -- The journal name followed by a period, in italics, if provided
				$ret[] =  ' <em>'.$rec->journal.'</em>';
			} else {
				if (!empty($rec->editor_display)) {
					$ret[] =  " in";
					// -- The word "in" followed by the editors followed by a period, if provided, 
					//    followed by the book title followed by a period in italics, if provided.
					// QUESTION FOR SUZANNE - Title and book title different?
					$ret[] =  ' '.$rec->editor_display;
					if (!empty($rec->book_title)) {
						if (!preg_match('/\.$/', $rec->editor_display)) {
							$ret[] =  '.';
						}
						$ret[] =  ' <em>'.$rec->book_title.'</em>.';
					}
				}
			}
			// 	 - Publisher place, followed by a comma.
			if (!empty($rec->publisher_place)) {
				$ret[] =  ' '.$rec->publisher_place;
			}
			// 	 - Publisher name, followed by a comma.
			if (!empty($rec->publisher)) {
				$ret[] =  ', '.$rec->publisher;
			}
			// 	 - Series in parentheses, followed by a period.
			// QUESTION FOR SUZANNE: Will we ever have series and issue but no volume?
			if (!empty($rec->series)) {
				$ret[] =  '. ('.$rec->series.').';
			}
			// 	 - Volume field
			if (!empty($rec->volume)) {
				$ret[] =  '. '.$rec->volume;
			}
			// 	 - Issue field, in parentheses followed by a colon:
			if (!empty($rec->issue)) {
				$ret[] =  ' ('.$rec->issue.')';
			}
			// 	 - Start page
			if (!empty($rec->start_page)) {
				$ret[] =  ':'.$rec->start_page;
			}
			// 	 - If end page provided, a hyphen and the end page.
			if (!empty($rec->end_page)) {
				$ret[] =  '-'.$rec->end_page;
			}
			// 	 - The DOI linked to the DOI url, if provided.
			if (!empty($rec->doi)) {
				$ret[] =  '. <a href="https://doi.org/'.$rec->doi.'">doi:'.$rec->doi.'</a>';
			}
			// 	 - A period. (to end everything)
			$ret[] =  '.';
		$ret[] =  '</div>';
		return implode('', $ret);
	}
	
  /* 
   * Format one record while printing to a webpage
   * 
   * $include_extras will print COINS data and Altmetric 
   * Badges onto the page.
   *
   * This just encapsultates a lot of code that would otherwise
   * make the _format_html_results() messier.
   */ 
  /*
	function _format_html_entry_old($rec, $include_extras = false) {
		$coins = array();
		$coins[] = 'url_ver=Z39.88-2004';
		$coins[] = 'ctx_ver=Z39.88-2004';
		$coins[] = 'rfr_id=info%3Asid%2Fzotero.org%3A2';
		if (!empty($rec->authors)) {
			foreach (_unique_authors($rec->authors) as $a) {
				$coins[] = 'rft.au='.urlencode($a->name);
			}
		}
		$coins[] = 'rft.date='.urlencode($rec->date);

		// Normalization
		if (!empty($rec->title)) {
			$rec->title = preg_replace('/<[^>].*>/', '', $rec->title);
		}
		if ($include_extras) {
			if ($rec->pubtype == 'article') {
				// COinS DATA FOR ZOTERO IMPORT
				if (!empty($rec->doi)) {
					$coins[] = 'rft_id=info%3Adoi%2F'.urlencode($rec->doi);
				}
				$coins[] = 'rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Ajournal';
				$coins[] = 'rft.genre=article';
				$coins[] = 'rft.atitle='.urlencode($rec->title);
				$coins[] = 'rft.jtitle='.urlencode($rec->journal);
				if (!empty($rec->volume)) {
					$coins[] = 'rft.volume='.urlencode($rec->volume);
				}
				if (!empty($rec->issue)) {
					$coins[] = 'rft.issue='.urlencode($rec->issue);
				}
				$coins[] = 'rft.stitle='.urlencode($rec->journal);
				$coins[] = 'rft.pages='.urlencode($rec->pages);
				if (!empty($rec->pages)) {
					if (strpos($rec->pages, '-')) {
						$p = explode('-', $rec->pages);
						$coins[] = 'rft.spage='.$p[0];
						$coins[] = 'rft.epage='.$p[1];
					} else {
						$coins[] = 'rft.spage='.$rec->pages;
					}
				}
				if (!empty($rec->issn)) {
					$coins[] = 'rft.issn='.urlencode($rec->issn);
				}
			} elseif ($rec->pubtype == 'chapter') {
				$coins[] = 'rft_id=urn%3Aisbn%3A'.$rec->isbn;
				$coins[] = 'rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Abook';
				$coins[] = 'rft.genre=bookitem';
				$coins[] = 'rft.atitle='.urlencode($rec->title);
				$coins[] = 'rft.btitle='.$rec->book_title;
				$coins[] = 'rft.publisher='.urlencode($rec->publisher);
				$coins[] = 'rft.pages='.urlencode($rec->pages);
			} elseif ($rec->pubtype == 'book') {
				$coins[] = 'rft_id=urn%3Aisbn%3A'.$rec->isbn;
				$coins[] = 'rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Abook';
				$coins[] = 'rft.genre=book';
				$coins[] = 'rft.btitle='.urlencode($rec->title);
				$coins[] = 'rft.tpages='.urlencode($rec->pages);
			}
			// $ret .= '<span class="Z3988" title="'.implode('&amp;',$coins).'"></span>';
		} 
		
		// ------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------
		if ($rec->pubtype == 'article') { // and (k1 does not contain 'Book review')
			$ret .= '<div class="schema-dot-org">';
				$ret .= '<div itemscope="" itemtype="http://schema.org/ScholarlyArticle">';
					$ret .= '<span property="name">'.$rec->title.'</span>';
					foreach (_unique_authors($rec->authors) as $a) {
                                                $ret .= '<span property="author" itemscope="" itemtype="http://schema.org/Person"><span itemprop="name">'.$a->name.'</span></span>';
					}
					$ret .= '<span property="datePublished">'.$rec->date.'</span>';
					if (!empty($rec->doi)) {
						$ret .= 'DOI: <a property="sameAs" href="http://dx.doi.org/'.$rec->doi.'">info:'.$rec->doi.'</a>';
					}
					$ret .= '<span property="isPartOf" typeof="Periodical">';
					$ret .= '<span property="name">'.$rec->journal.'</span>';
					if ($rec->volume > 0) {
						$ret .= 'v. <span property="volumeNumber">'.$rec->volume.'</span>';
					}
					if ($rec->issue > 0) {
						$ret .= 'No. <span property="issueNumber">'.$rec->issue.'</span>';
					}
					$ret .= '</span>';
				$ret .= '</div>';
			$ret .= '</div>';

			$ret .= '<div class="result fa-file-alt" title="Article">';
				if ($include_extras) {
					if (!empty($rec->doi)) {
						$ret .= '<div class="show_metric" class="altmetric-embed" data-badge-type="donut" data-badge-popover="left" data-hide-no-mentions="true" data-doi="'.$rec->doi.'"></div>';
					}
				}
				// #a1#
				$ret .= $rec->author_display;
				if (!preg_match('/\.$/', $rec->author_display)) {
					$ret .= '.';
				}
				// #yr#
				$ret .= ' <span class="date_highlight date_display">'.$rec->date.'</span>.';

				// #ul# #t1#
				if (preg_match('/http/', $rec->link)) {
					$ret .= ' <a href="'.$rec->link.'">'.$rec->title.'</a>';
				} else {
					$ret .= ' '.$rec->title;
				}
				if (!preg_match('/[.?]$/', $rec->title)) {
					$ret .= '.';
				}
				// #jf#
				$ret .= ' <span class="journal_bold journal_display"><em>'.$rec->journal.'</em></span>,';

				// #vo# #is_no#
				if ($rec->issue > 0 && $rec->volume > 0) {
					$ret .= ' '.$rec->volume.'('.$rec->issue.')';
				} elseif ($rec->issue > 0 && $rec->volume == 0) {
					$ret .= ' '.$rec->issue;
				} elseif ($rec->issue == 0 && $rec->volume > 0) {
					$ret .= ' '.$rec->volume;
				}
				// #sp# #op#
				$ret .= ' '.$rec->pages;
				// #doi#
				if (!empty($rec->doi)) {
					$ret .= ' <a href="http://dx.doi.org/'.$rec->doi.'">doi:'.$rec->doi.'</a>';
				}
			$ret .= '</div>';
			
		// ------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------
		} elseif ($rec->pubtype == 'ejournal_article') {
			if ($include_extras) {
				if (!empty($rec->doi)) {
					$ret .= '<div class="show_metric" class="altmetric-embed" data-badge-type="donut" data-badge-popover="left" data-hide-no-mentions="true" data-doi="'.$rec->doi.'"></div>';
				}
			}
			$ret .= '<div class="result fa-file-pdf" title="E-Journal Article">';
				$ret .= $rec->author_display;
				if (!preg_match('/\.$/', $rec->author_display)) {
					$ret .= '.';
				}
				$ret .= ' <span class="date_highlight date_display">'.$rec->date.'</span>.';
				if (preg_match('/http/', $rec->link)) {
					$ret .= '<a href="'.$rec->link.'">'.$rec->title.'</a>';
				} else {
					$ret .= $rec->title;
				}
				if (!preg_match('/[.?]$/', $rec->title)) {
					$ret .= '.';
				}
				$ret .= ' <span class="journal_bold journal_display"><em>'.$rec->journal.'</em></span>,';
				if ($rec->issue > 0 && $rec->volume > 0) {
					$ret .= ' '.$rec->volume.'('.$rec->issue.')';
				} elseif ($rec->issue > 0 && $rec->volume == 0) {
					$ret .= ' '.$rec->issue;
				} elseif ($rec->issue == 0 && $rec->volume > 0) {
					$ret .= ' '.$rec->volume;
				}
				$ret .= ' '.$rec->pages;
				if (!empty($rec->doi)) {
					$ret .= ' <a href="http://dx.doi.org/'.$rec->doi.'">doi:'.$rec->doi.'</a>';
				}
			$ret .= '</div>';

		// ------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------
		} elseif ($rec->pubtype == 'chapter') {

			$ret .= '<div class="result fa-bookmark" title="Chapter">';
				$ret .= '<div class="schema-dot-org">';
					$ret .= '<div itemscope="" itemtype="http://schema.org/Chapter">';
						$ret .= '<span itemprop="name">'.$rec->title.'</span>';
						foreach (_unique_authors($rec->authors) as $a) {
	                                                $ret .= '<span property="author" itemscope="" itemtype="http://schema.org/Person"><span itemprop="name">'.$a->name.'</span></span>';
						}
						$ret .= '<span itemprop="pageStart">'.$rec->start_page.'</span>';
						$ret .= '<span itemprop="pageEnd">'.$rec->end_page.'</span>';
						$ret .= '<span itemprop="ISBN">'.$rec->issn_isbn.'</span>';
						$ret .= '<span itemprop="isPartOf"></span>';
						if (!empty($rec->editors)) {
								$ret .= '<span itemscope="" itemtype="http://schema.org/Person">';
									$ret .= '<span itemprop="editor">';
										foreach ($rec->editors as $a) {
											$ret .= '<span itemprop="name">'.$a->name.'</span>';
										}
									$ret .= '</span>';
							 $ret .= '</span>';
						}
						$ret .= '<span itemprop="name">'.$rec->book_title.'</span>';
						$ret .= '<span property="datePublished">'.$rec->year.'</span>';
						$ret .= '<span itemprop="location">'.$rec->publisher_place.'</span>';
						$ret .= '<span itemprop="publisher">'.$rec->publisher.'</span>';
					$ret .= '</div>';
				$ret .= '</div>';
				# BOOK CHAPTER DISPLAY

				if ($include_extras) {
					if (!empty($rec->doi)) {
						$ret .= '<div class="show_metric" class="altmetric-embed" data-badge-type="donut" data-badge-popover="left" data-hide-no-mentions="true" data-doi="'.$rec->doi.'"></div>';
					}
				}
				$ret .= $rec->author_display;
				if (!preg_match('/\.$/', $rec->author_display)) {
					$ret .= '.';
				}
				$ret .= ' <span class="date_highlight date_display">'.$rec->date.'</span>.';
				if (preg_match('/http/', $rec->link)) {
					$ret .= ' <a href="'.$rec->link.'">'.$rec->title.'</a>';
				} else {
					$ret .= ' '.$rec->title;
				}
				if (!preg_match('/[.?]$/', $rec->title)) {
					$ret .= '.';
				}
				$ret .= ' In: ';
				if (!empty($rec->editor_display)) {
					$ret .= $rec->editor_display.',';
				}
				$ret .= ' <i>'.$rec->book_title.'.</i>';
				if (!empty($rec->publisher_place)) {
					$ret .= ' '.$rec->publisher_place.':';
				}
				if (!empty($rec->publisher)) {
					$ret .= ' '.$rec->publisher;
				}
				if (!empty($rec->series)) {
					$ret .= ', '.$rec->series;
				}
				if (!empty($rec->pages)) {
					$ret .= ' pp. '.$rec->pages;
				}
				if (!empty($rec->doi)) {
					$ret .= ' <a href="http://dx.doi.org/'.$rec->doi.'">doi:'.$rec->doi.'</a>';
				}
			$ret .= '</div>';

		// ------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------
		} elseif ($rec->pubtype == 'book') {
			$ret .= '<div class="schema-dot-org">';
				$ret .= '<div itemscope="" itemtype="http://schema.org/Book">';
					$ret .= '<span property="name">'.$rec->title.'</span>';
					foreach (_unique_authors($rec->authors) as $a) {
						$ret .= '<span property="author" itemscope="" itemtype="http://schema.org/Person"><span itemprop="name">'.$a->name.'</span></span>';
					}
					$ret .= '<span property="datePublished">'.$rec->date.'</span>';
					if (!empty($rec->doi)) {
						$ret .= 'DOI: <a property="sameAs" href="http://dx.doi.org/'.$rec->doi.'">info:'.$rec->doi.'</a>';
					}
					$ret .= '<span itemprop="location">'.$rec->publisher_place.'</span>';
					$ret .= '<span itemprop="publisher">'.$rec->publisher.'</span>';
					$ret .= '<span itemprop="numberOfPages">'.$rec->pages.'</span>';
					$ret .= '<span itemprop="ISBN">'.$rec->issn_isbn.'</span>';
					$ret .= '</span>';
				$ret .= '</div>';
			$ret .= '</div>';

			# BOOK DISPLAY

			$ret .= '<div class="result fa-book" title="Book">';
				$ret .= $rec->author_display;
				if (!preg_match('/\.$/', $rec->author_display)) {
					$ret .= '.';
				}
				$ret .= ' <span class="date_highlight date_display">'.$rec->date.'</span>.';
				if (preg_match('/http/', $rec->link)) {
					$ret .= ' <a href="'.$rec->link.'">'.$rec->title.'</a>';
				} else {
					$ret .= ' '.$rec->title;
				}
				if (!preg_match('/[.?]$/', $rec->title)) {
					$ret .= '.';
				}
				if (!empty($rec->editor_display)) {
					$ret .= ' '.$rec->editor_display;
				}
				if (!empty($rec->publisher_place)) {
					$ret .= ' '.$rec->publisher_place.':';
				}
				if (!empty($rec->publisher)) {
					$ret .= ' '.$rec->publisher.'.';
				}
				if (!empty($rec->book_title)) {
					$ret .= ' '.$rec->book_title;
					if (!empty($rec->book_title)) {
						$ret .= ' ('.$rec->volume.')';
					}
				}
				if (!empty($rec->book_title)) {
					$ret .= ' '.$rec->pages.' pages.';
				}
				if (!empty($rec->doi)) {
					$ret .= ' <a href="http://dx.doi.org/'.$rec->doi.'">doi:'.$rec->doi.'</a>';
				}
			$ret .= '</div>';

		// ------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------
		} elseif ($rec->pubtype == 'book_edited') {
			$ret .= '<div class="result fa-book" title="Book, Edited">';
				if (preg_match('/http/', $rec->link)) {
					$ret .= '<em><a href="'.$rec->link.'">'.$rec->title.'</a></em>';
				} else {
					$ret .= '<em>'.$rec->title.'</em>';
				}
				if (!preg_match('/[.?]$/', $rec->title)) {
					$ret .= '.';
				}
				if (!empty($rec->editor_display)) {
					$ret .= $rec->editor_display.',';
					if (!preg_match('/[.?]$/', $rec->editor_display)) {
						$ret .= '.';
					}
				}
				$ret .= ' <span class="date_highlight date_display">'.$rec->date.'</span>.';
				if (!empty($rec->publisher_place)) {
					$ret .= ' '.$rec->publisher_place.':';
				}
				if (!empty($rec->publisher)) {
					$ret .= ' '.$rec->publisher.'.';
				}
				if (!empty($rec->book_title)) {
					$ret .= ' '.$rec->book_title;
					if (!empty($rec->book_title)) {
						$ret .= ' ('.$rec->volume.')';
					}
				}
				if (!empty($rec->book_title)) {
					$ret .= ' '.$rec->pages.' pages.';
				}
				if (!empty($rec->doi)) {
					$ret .= ' <a href="http://dx.doi.org/'.$rec->doi.'">doi:'.$rec->doi.'</a>';
				}
			$ret .= '</div>';

		// ------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------
		} elseif ($rec->pubtype == 'thesis') {
			$ret .= '<div class="result fa-file-edit" title="Thesis">';
				$ret .= $rec->author_display;
				if (!preg_match('/\.$/', $rec->author_display)) {
					$ret .= '.';
				}
				$ret .= ' <span class="date_highlight date_display">'.$rec->date.'</span>.';
				if (preg_match('/http/', $rec->link)) {
					$ret .= ' <a href="'.$rec->link.'">'.$rec->title.'</a>';
				} else {
					$ret .= ' '.$rec->title;
				}
				if (!preg_match('/[.?]$/', $rec->title)) {
					$ret .= '.';
				}
				if (!empty($rec->publisher_place)) {
					$ret .= ' '.$rec->publisher_place.':';
				}
				if (!empty($rec->publisher)) {
					$ret .= ' '.$rec->publisher.'.';
				}
				if (!empty($rec->book_title)) {
					$ret .= ' '.$rec->pages.' pages.';
				}
			$ret .= '</div>';

		// ------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------
		} elseif ($rec->pubtype == 'web_page') {
			$ret .= '<div class="result fa-globe" title="Web Page">';

				$ret .= $rec->author_display;
				if (!preg_match('/\.$/', $rec->author_display)) {
					$ret .= '.';
				}
				$ret .= ' <span class="date_highlight date_display">'.$rec->date.'</span>.';
				if (preg_match('/http/', $rec->link)) {
					$ret .= ' <a href="'.$rec->link.'">'.$rec->title.'</a>';
				} else {
					$ret .= ' '.$rec->title;
				}
				if (!preg_match('/[.?]$/', $rec->title)) {
					$ret .= '.';
				}
				if (!empty($rec->publisher)) {
					$ret .= ' '.$rec->publisher.'.';
				}
				if (!empty($rec->link)) {
					$ret .= ' (<a href="'.$rec->link.'">'.$rec->link.'</a>).';
				}
				if (!empty($rec->date)) {
					$ret .= ' '.$rec->date.'';
				}
			$ret .= '</div>';

		// ------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------
		} elseif ($rec->pubtype == 'magazine_article') {
			$ret .= '<div class="result fa-file" title="Magazine Article">';
				$ret .= $rec->author_display;
				if (!preg_match('/\.$/', $rec->author_display)) {
					$ret .= '.';
				}
				$ret .= ' <span class="date_highlight date_display">'.$rec->date.'</span>.';
				if (preg_match('/http/', $rec->link)) {
					$ret .= ' <a href="'.$rec->link.'">'.$rec->title.'</a>';
				} else {
					$ret .= ' '.$rec->title;
				}
				if (!preg_match('/[.?]$/', $rec->title)) {
					$ret .= '.';
				}
				$ret .= ' <span class="journal_bold journal_display"><em>'.$rec->journal.'</em></span>.';
				if ($rec->issue > 0 && $rec->volume > 0) {
					$ret .= ' '.$rec->volume.'('.$rec->issue.')';
				} elseif ($rec->issue > 0 && $rec->volume == 0) {
					$ret .= ' '.$rec->issue;
				} elseif ($rec->issue == 0 && $rec->volume > 0) {
					$ret .= ' '.$rec->volume;
				}
				$ret .= ' pp. '.$rec->pages;
			$ret .= '</div>';

		// ------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------
		} elseif ($rec->pubtype == 'video_dvd') {
			$ret .= '<div class="result fa-video" title="Video/DVD">';
				if (preg_match('/http/', $rec->link)) {
					$ret .= '<em><a href="'.$rec->link.'">'.$rec->title.'</a></em>';
				} else {
					$ret .= '<em>'.$rec->title.'</em>';
				}
				if (!preg_match('/[.?]$/', $rec->title)) {
					$ret .= '.';
				}
				$ret .= ' '.$rec->pubtype.'.';
				$ret .= ' '.$rec->author_display.';';
				if (!empty($rec->publisher_place)) {
					$ret .= ' '.$rec->publisher_place.':';
				}
				if (!empty($rec->publisher)) {
					$ret .= ' '.$rec->publisher;
				}
				$ret .= ' <span class="date_highlight date_display">'.$rec->date.'</span>.';
			$ret .= '</div>';

		// ------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------
		} elseif ($rec->pubtype == 'audio') {
			$ret .= '<div class="result fa-volume-up" title="Audio Recording">';
				$ret .= $rec->author_display;
				if (!preg_match('/\.$/', $rec->author_display)) {
					$ret .= '.';
				}
				if (preg_match('/http/', $rec->link)) {
					$ret .= '<em><a href="'.$rec->link.'">'.$rec->title.'</a></em>';
				} else {
					$ret .= '<em>'.$rec->title.'</em>';
				}
				if (!preg_match('/[.?]$/', $rec->title)) {
					$ret .= '.';
				}
				$ret .= ' '.$rec->pubtype.'.';
				if (!empty($rec->publisher_place)) {
					$ret .= ' '.$rec->publisher_place.':';
				}
				if (!empty($rec->publisher)) {
					$ret .= ' '.$rec->publisher;
				}
				$ret .= ' <span class="date_highlight date_display">'.$rec->date.'</span>.';
			$ret .= '</div>';

		// ------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------
		} elseif ($rec->pubtype == 'report') {
			$ret .= '<div class="result fa-list-alt" title="Report">';
				$ret .= $rec->author_display;
				if (!preg_match('/\.$/', $rec->author_display)) {
					$ret .= '.';
				}
				$ret .= ' <span class="date_highlight date_display">'.$rec->date.'</span>.';
				$ret .= '"'.$rec->title.'".';
				$ret .= ' <em>'.$rec->journal.'</em>.';
				if (!empty($rec->editor_display)) {
					$ret .= ' ed.'.$rec->editor_display.'.';
				}
				if (!empty($rec->volume)) {
					$ret .= ' '.$rec->volume.'.';
				}
				if (!empty($rec->publisher_place)) {
					$ret .= ' '.$rec->publisher_place.':';
				}
				if (!empty($rec->publisher)) {
					$ret .= ' '.$rec->publisher.',';
				}
				if (!empty($rec->pages)) {
					$ret .= ' '.$rec->pages.'.';
				}
				if (!empty($rec->link)) {
					$ret .= ' (<a href="'.$rec->link.'">'.$rec->link.'</a>).';
				}
			$ret .= '</div>';

		// ------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------
		} elseif ($rec->pubtype == 'newspaper_article') {
			$ret .= '<div class="result fa-newspaper" title="Newspaper Article">';
				$ret .= $rec->author_display;
				if (!preg_match('/\.$/', $rec->author_display)) {
					$ret .= '.';
				}
				if (preg_match('/http/', $rec->link)) {
					$ret .= ' <a href="'.$rec->link.'">"'.$rec->title.'"</a>';
				} else {
					$ret .= " '".$rec->title.'"';
				}
				if (!preg_match('/[.?]$/', $rec->title)) {
					$ret .= '.';
				}
				$ret .= ' <em>'.$rec->journal.'</em>.';
				if (!empty($rec->issue)) {
					$ret .= ' '.$rec->issue.',';
				}
				$ret .= ' <span class="date_highlight date_display">'.$rec->date.'</span>.';
			$ret .= '</div>';

		// ------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------
		} elseif ($rec->pubtype == 'motion_picture') {
			$ret .= '<div class="result fa-film" title="Motion Picture">';

				$ret .= $rec->author_display;
				if (preg_match('/http/', $rec->link)) {
					$ret .= '<em><a href="'.$rec->link.'">'.$rec->title.'</a></em>.';
				} else {
					$ret .= '<em>'.$rec->title.'</em>.';
				}
				$ret .= ' '.$rec->pubtype.'.';
				if (!empty($rec->acknowledgement)) {
					$ret .= ' '.$rec->acknowledgement.':';
				}
				if (!empty($rec->publisher_place)) {
					$ret .= ' '.$rec->publisher_place.':';
				}
				if (!empty($rec->publisher)) {
					$ret .= ' '.$rec->publisher.'.';
				}
				$ret .= ' <span class="date_highlight date_display">'.$rec->date.'</span>.';
			$ret .= '</div>';

		// ------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------
		} elseif ($rec->pubtype == 'monograph') {
			$ret .= '<div class="result fa-book" title="Monograph">';

				if (!empty($rec->author_display)) {
					$ret .= $rec->author_display;
					if (!preg_match('/\.$/', $rec->author_display)) {
						$ret .= '.';
					}
				} else {
					$ret .= $rec->editor_display;
					if (!preg_match('/\.$/', $rec->editor_display)) {
						$ret .= '.';
					}
				}
				if (preg_match('/http/', $rec->link)) {
					$ret .= '<em><a href="'.$rec->link.'">'.$rec->title.'</a></em>.';
				} else {
					$ret .= '<em>'.$rec->title.'</em>.';
				}
				if (!empty($rec->issue)) {
					$ret .= ' '.$rec->issue.'.';
				}
				if (!empty($rec->publisher_place)) {
					$ret .= ' '.$rec->publisher_place.':';
				}
				if (!empty($rec->publisher)) {
					$ret .= ' '.$rec->publisher.'.';
				}
				$ret .= ' <span class="date_highlight date_display">'.$rec->date.'</span>,';
				if (!empty($rec->book_title)) {
					$ret .= ' pp.'.$rec->pages;
				}
			$ret .= '</div>';

		// ------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------
		} elseif ($rec->pubtype == 'map') {
			$ret .= '<div class="result fa-map" title="Map">';
				$ret .= $rec->author_display;
				if (!preg_match('/\.$/', $rec->author_display)) {
					$ret .= '.';
				}
				$ret .= '<em>'.$rec->title.'</em>.';
				if (!empty($rec->book_title)) {
					$ret .= ' '.$rec->book_title.'.';
				}
				if (!empty($rec->volume)) {
					$ret .= ' '.$rec->volume.',';
				}
				if (!empty($rec->issue)) {
					$ret .= ' '.$rec->issue.'.';
				}
				if (!empty($rec->publisher_place)) {
					$ret .= ' '.$rec->publisher_place.':';
				}
				if (!empty($rec->publisher)) {
					$ret .= ' '.$rec->publisher.',';
				}
				$ret .= ' <span class="date_highlight date_display">'.$rec->date.'</span>.';
			$ret .= '</div>';

		// ------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------
		} elseif ($rec->pubtype == 'artwork') {
			$ret .= '<div class="result fa-image" title="Artwork">';
				$ret .= $rec->author_display;
				if (!preg_match('/\.$/', $rec->author_display)) {
					$ret .= '.';
				}
				$ret .= ' <span class="date_highlight date_display">'.$rec->date.'</span>.';
			$ret .= '</div>';

		// ------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------
		} elseif ($rec->pubtype == 'abstract') {
			$ret .= '<div class="result fa-align-justify" title="Abstract">';
				$ret .= $rec->author_display;
				$ret .= ' <span class="date_highlight date_display">'.$rec->date.'</span>.';
				$ret .= '[Abstract:] "'.$rec->title.'".';
				if (!empty($rec->book_title)) {
					$ret .= ' <em>'.$rec->book_title.'</em>,';
				}
				if (!empty($rec->book_title)) {
					$ret .= ' :'.$rec->pages.'.';
				}
				if (!empty($rec->link)) {
					$ret .= '<a href="'.$rec->link.'">'.$rec->link.'</a>';
				}
			$ret .= '</div>';

		// ------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------
		} elseif ($rec->pubtype == 'forum_blog') {
			$ret .= '<div class="result fa-comments" title="Forum/Blog Post">';
				$ret .= $rec->author_display;
				if (!preg_match('/\.$/', $rec->author_display)) {
					$ret .= '.';
				}
				$ret .= ' <span class="date_highlight date_display">'.$rec->date.'</span>.';
				$ret .= ' "'.$rec->title.'".';
				if (!empty($rec->journal)) {
					$ret = ' <em>'.$rec->journal.'</em>.';
				} elseif (!empty($rec->book_title)) {
					$ret = ' '.$rec->book_title.'.';
				}
				if (!empty($rec->link)) {
					$ret .= '<a href="'.$rec->link.'">'.$rec->link.'</a>';
				}
			$ret .= '</div>';

		// ------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------
		} elseif ($rec->pubtype == 'generic') {
			$ret .= '<div class="result fa-rectangle-portrait" title="Generic">';
				$ret .= $rec->author_display;
				if (!preg_match('/\.$/', $rec->author_display)) {
					$ret .= '.';
				}
				$ret .= ' <span class="date_highlight date_display">'.$rec->date.'</span>.';
				$ret .= '[Presentation:] "'.$rec->title.'".';
				if (!empty($rec->journal)) {
					$ret = ' <em>'.$rec->journal.'</em>.';
				} elseif (!empty($rec->book_title)) {
					$ret = ' '.$rec->book_title.'.';
				}
				if (!empty($rec->link)) {
					$ret .= '<a href="'.$rec->link.'">'.$rec->link.'</a>';
				}
			$ret .= '</div>';

		// ------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------
		} else {
			$ret .= '<div class="result fa-question-circle" title="Other">';
				$ret .= $rec->author_display;
				if (!preg_match('/\.$/', $rec->author_display)) {
					$ret .= '.';
				}
				$ret .= ' <span class="date_highlight date_display">'.$rec->date.'</span>.';
				if (preg_match('/http/', $rec->link)) {
					$ret .= '<a href="'.$rec->link.'">'.$rec->title.'</a>.';
				} else {
					$ret .= $rec->title.'.';
				}
				if (!preg_match('/[.?]$/', $rec->title)) {
					$ret .= '.';
				}
				if (!empty($rec->publisher_place)) {
					$ret .= ' '.$rec->publisher_place.':';
				}
				if (!empty($rec->publisher)) {
					$ret .= ' '.$rec->publisher.'.';
				}
				if (!empty($rec->doi)) {
					$ret .= ' <a href="http://dx.doi.org/'.$rec->doi.'">doi:'.$rec->doi.'</a>';
				}
			$ret .= '</div>';

//    ALTHOUGH THIS IS REFERENCE TYPE: JOURNAL ARTICLE, THIS SEGMENT HOPEFULLY IDENTIFIES IT AS A BOOK REVIEW.
// 		REMOVED PER DISCUSSION W/SCP 2016-11-8
//    Dashes added by JMR 2017/08/03
// 		 } elseif ((rt eq 'Journal Article') and (k1 contains 'Book review')) {
// 			#-a1-#
// 				if (REfind("\.$","#-a1-#")) {  } else { . }<span class="date_highlight date_display">#-yr-#</span>.
// 				if (REfind ("[R|r]eview","#-t1-#")) { } else { [Review]:}
// 				if (ul contains 'http') {<a href="#ul#">#-t1-#</a> } else { #-t1-#}.
//        <span class="journal_bold journal_display"><i>#jf#</i></span>,
// 				if (is_no ge '0' and vo ge '0') {#vo#(#is_no#) } elseif (vo eq ' ' and is_no ge '0') { #is_no# } elseif (vo ge '0' and is_no eq ' ') { #vo#}: #sp#
// 				if (op ge '0' and op neq sp) {-#op#. } else { }
// 				if (doi contains '10.') {<a href="http://dx.doi.org/#doi#"> doi:#doi#</a>}<br />
// 			$ret .= '</div>';


		}
		return $ret;
	}
  */

  /* 
   * Send the query to the Search API and decode the results. 
   * 
   * This is not combined with _do_search because there is a case
   * where we can add the exact parameters for a hardcoded query
   * to the search server, which goes directly to this function.
   */ 
	function _execute_query($url, $args) {
		if (!empty($args['full_query'])) {
			$query = $args['full_query'];
		} else {
			$query = [];
			foreach ($args as $name => $val) {
				$query[] = $name.'='.urlencode($val);
			}
			$query = implode('&', $query);
		}	
		if (count($args) > 0) {
			if (!preg_match('/\/$/', $url)) {
				$url .= '/';
			}
			$results = file_get_contents($url.'?'.$query);
			$results = json_decode($results);
		} else {
			$results = file_get_contents($url);
		}
		return $results;		
	}

  /* 
   * Build the search query to send it to the search API.
   */ 
	function _do_search($params) {
		if (!empty($_GET['action']) && $_GET['action'] === 'sro_search_results') {

			// Do the altmetrics search at SRO in CSV
			if ($params['send_to'] == 'download' && $params['export_format'] == 'altmetrics') {
				$results = $this->_execute_query($params['server_url'].'/altmetrics_pubs.cfm', array());
				return $results;
			}					

			// Do the search at SRO in JSON
			$results = null;
			if (!empty($params['search_term']) || !empty($params['dept'])) {
				$query = array(
					'search'  => $params['search_term'],
					'limit'   => $params['limit'],
					'year'    => $params['year'],
					'dept'    => $params['dept'],
					'count'   => $params['perpage'],
					'pagenum' => $params['page'],
					'sort'    => $params['sort']
				);
				$results = $this->_execute_query($params['server_url'], $query);
				return $results;
			}
		}
		return null;
	}
	
	/* Return a list of SI Departments. 
	 * This should really be an API call to somewhere else 
	 */
	function _sro_get_departments() {
		// TODO: This should really be an API Call
		return array(
			array('id' => '690000', 'name' => 'Anacostia Community Museum'), 
			array('id' => '480000', 'name' => 'Archives of American Art'), 
			array('id' => '710000', 'name' => 'Asian Pacific American Center'), 
			array('id' => '510000', 'name' => 'Center for Folklife and Cultural Heritage'), 
			array('id' => '580000', 'name' => 'Cooper-Hewitt National Design Museum'), 
			array('id' => '540000', 'name' => 'Freer-Sackler Galleries'), 
			array('id' => '560000', 'name' => 'Hirshhorn Museum and Sculpture Garden'), 
			array('id' => '640000', 'name' => 'Museum Conservation Institute'), 
			array('id' => '380000', 'name' => 'National Air and Space Museum'), 
			array('id' => '382010', 'name' => 'NASM-Aeronautics'), 
			array('id' => '382020', 'name' => 'NASM-Space History'), 
			array('id' => '382050', 'name' => 'NASM-CEPS'), 
			array('id' => '680000', 'name' => 'National Museum of African American History and Culture'), 
			array('id' => '570000', 'name' => 'National Museum of African Art'), 
			array('id' => '550000', 'name' => 'National Museum of American History'), 
			array('id' => '330000', 'name' => 'NMNH'), 
			array('id' => '331040', 'name' => 'NMNH Encyclopedia of Life'), 
			array('id' => '332010', 'name' => 'NH-Mineral Science'), 
			array('id' => '332020', 'name' => 'NH-Anthropology'), 
			array('id' => '332031', 'name' => 'NH-Invertebrate Zoology'), 
			array('id' => '332032', 'name' => 'NH-Vertebrate Zoology'), 
			array('id' => '332033', 'name' => 'NH-Botany'), 
			array('id' => '332034', 'name' => 'NH-Entomology'), 
			array('id' => '332040', 'name' => 'NH-Paleobiology'), 
			array('id' => '332050', 'name' => 'NH-Smithsonian Marine Station'), 
			array('id' => '500000', 'name' => 'National Museum of the American Indian'), 
			array('id' => '520000', 'name' => 'National Portrait Gallery'), 
			array('id' => '301000', 'name' => 'National Postal Museum'), 
			array('id' => '350000', 'name' => 'National Zoological Park'), 
			array('id' => '770000', 'name' => 'Office of Policy and Analysis'), 
			array('id' => '250001', 'name' => 'Office of the Under Secretary for History, Art &amp; Culture'), 
			array('id' => '110000', 'name' => 'Secretary\'s Cabinet'), 
			array('id' => '100000', 'name' => 'SI-Other'), 
			array('id' => '530000', 'name' => 'Smithsonian American Art Museum'), 
			array('id' => '404000', 'name' => 'Smithsonian Astrophysical Observatory'), 
			array('id' => '390000', 'name' => 'Smithsonian Environmental Research Center'), 
			array('id' => '733400', 'name' => 'Smithsonian Gardens'), 
			array('id' => '170000', 'name' => 'Smithsonian Institution Archives'), 
			array('id' => '360000', 'name' => 'Smithsonian Latino Center'), 
			array('id' => '630000', 'name' => 'Smithsonian Institution Libraries'), 
			array('id' => '340000', 'name' => 'Smithsonian Tropical Research Institute'), 
			array('id' => '250000', 'name' => 'Arts and Humanities'), 
			array('id' => '590000', 'name' => 'Science'), 
			array('id' => '941000', 'name' => 'Smithsonian Institution Scholarly Press'), 
			array('id' => '960000', 'name' => 'DUSCIS')
		);
	}
}


/* Creates a Page in wordpress to contain the shortcode 
 * to display the search form and results. Called when this
 * plugin is activated for a site.
 */
function sro_insert_search_results_page() {
	if (is_multisite() && $network_wide) { 
		global $wpdb;
		
		foreach ($wpdb->get_col("SELECT blog_id FROM $wpdb->blogs") as $blog_id) {
			switch_to_blog($blog_id);
			_sro_insert_page();
			restore_current_blog();
		} 
	} else {
		// Create post object
		_sro_insert_page();
	}
}

/* Creates a Page in wordpress to contain the shortcode 
 * to display the search form and results. Called when a new
 * site is added to this wordpress multisite installation.
 */
function sro_insert_search_results_page_new_blog($blog_id, $user_id, $domain, $path, $site_id, $meta) {
	//replace with your base plugin path E.g. dirname/filename.php
	if ( is_plugin_active_for_network( 'silibraries-sro/silibraries-sro.php' ) ) {
		switch_to_blog($blog_id);
		_sro_insert_page();
		restore_current_blog();
	} 
}

/* Attempts to remove the page a Page in that contains 
 * the shortcode to display the search form and results 
 */
function sro_remove_search_results_page() {
	if (is_multisite() && $network_wide) { 
		global $wpdb;
		
		foreach ($wpdb->get_col("SELECT blog_id FROM $wpdb->blogs") as $blog_id) {
			switch_to_blog($blog_id);
			_sro_delete_page();
			restore_current_blog();
		} 
	} else {
		_sro_delete_page();
	}
}

/* Actually insert the page giving it a 
 * path slug of "publications" 
 */
function _sro_insert_page() {
	// Create post object
	$my_post = array(
		'post_title'    => 'Publication Search Results',
		'post_name'     => 'publications',
		'post_content'  => '[sro-search-results]',
		'post_status'   => 'publish',
		'post_author'   => get_current_user_id(),
		'post_type'     => 'page',
	);

	// Insert the post into the database
	wp_insert_post($my_post, '');
}

/* Actually insert the page to the datasbase, as 
 * long as the path slug is "publications".
 */
function _sro_delete_page() {
	$post = get_page_by_path('publications', OBJECT, 'page');
	wp_delete_post($post->ID);	
}

/* Given a one-dimnsional array, return a 
 * properly formatted CSV string. 
 */
function str_putcsv($data) {
	$fh = fopen('php://temp', 'w');
	fputcsv($fh, $data);
	rewind($fh);
	$csv = stream_get_contents($fh);
	fclose($fh);
	return $csv;
}

/* Remove duplicate elements from the 
 * authors or editors 
 */
function _unique_authors($authors) {
	$unique = array();
	foreach ($authors as $a) {
		$unique[] = $a->name;
	}
	$unique = array_unique($unique);
	$ret = array();
	foreach ($unique as $a) {
		$ret[] = array('name' => $a);
	}
	return $ret;
}

/* ------------------------------ */
/*    WORDPRESS API ACTIVITIES    */
/* ------------------------------ */

/* Add our CSS to the page output */
function my_scripts() {
  wp_register_style('silibraries-sro', plugins_url('/css/style.css', __FILE__));
  wp_enqueue_style('silibraries-sro');
}

/* Register our search widgets with an anonymous function that registers the classes we byilt. */
add_action( 'widgets_init', function() { register_widget('SROSearchWidget');});
add_action( 'widgets_init', function() { register_widget('SROFixedSearchWidget');});

/* Create our object and make magic happen.*/
$wpSROSearch = new SROSearch();
add_action( 'template_redirect', array($wpSROSearch, 'download_results') );

register_activation_hook( __FILE__, 'sro_insert_search_results_page' );
register_deactivation_hook( __FILE__, 'sro_remove_search_results_page' );
add_action('wpmu_new_blog', 'sro_insert_search_results_page_new_blog', 10, 6 );

add_action('wp_enqueue_scripts', 'my_scripts');

