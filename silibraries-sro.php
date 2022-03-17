<?php 
/*
Plugin Name: Smithsonian Libraries SRO
Plugin URI:  http://research.si.edu
Description: Basic support for searching the SRO publications database
Version:     20170727
Author:      Joel Richard
Author URI:  https://library.si.edu/staff/joel-richard
License:     Public Domain
Version:     1.3
*/

define("SRO_VERSION", "1.3");
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
			array('server_url' => 'http://staff.research.si.edu/search-api/publications/', 'query_extra' => '')
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

  private $current_item = null;
  
  /* 
   * Initialize and register the shortcode that handles the display of results.
   */ 
	public function __construct() {
		if(is_admin()) {
			$my_settings_page = new SROSettingsPage();
		}

		add_shortcode('sro-search-results', array($this, 'display_results'));
		add_shortcode('sro-item-details', array($this, 'display_item'));
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
      $ret .= '<div id="search-wrap">';
        $ret .= '<div id="search-field">';
          $ret .= '<label class="hidden" for="q">Enter Search Term</label>';
          $ret .= '<input type="text" id="q" name="q" value="'.esc_attr($query).'" placeholder="Enter Search Term" />';
          $ret .= '&nbsp;'.get_submit_button('Go', 'primary large', null, false);
        $ret .= '</div>';
        $ret .= '<div id="exact-wrapper"><input type="checkbox" id="exact" value="1" name="exact" '.($params['exact'] ? 'checked=""' : '').'>&nbsp;<label for="exact">Exact Phrase</label></div>';
        $ret .= '<div><a id="advanced-link" onClick="return sroToggleAdvancedSearch();">Advanced Search</a></div>';
      $ret .= '</div>';
			$hide = true;

			if (!empty($params['advanced']) ||
			    !empty($params['limit']) || 
			    !empty($params['date']) || 
			    !empty($params['dept']) || 
			    (!empty($params['sort']) && $params['sort'] != 'relevance') || 
			    (!empty($params['send_to']) && $params['send_to'] != 'screen')){
				$hide = false;
			}
			$ret .= '<div id="advanced-search"'.($hide ? ' style="display:none"' : '').'>';
			
				$ret .= '<div class="criteria">';
					$ret .= '<label for="limit">Limit&nbsp;Search&nbsp;Term&nbsp;to:</label>&nbsp;';
					$ret .= '<select id="limit" name="limit">';
						$ret .= '<option value=""'.($params['limit'] == '' ? ' selected' : '').'>(none)</option>';
						$ret .= '<option value="author"'.($params['limit'] == 'author' ? ' selected' : '').'>Author Name</option>';
						$ret .= '<option value="author_id"'.($params['limit'] == 'author_id' ? ' selected' : '').'>Author ID</option>';
						$ret .= '<option value="journal"'.($params['limit'] == 'journal' ? ' selected' : '').'>Journal Title</option>';
					$ret .= '</select>';
					$ret .= '&nbsp;&nbsp;&nbsp; ';
					$ret .= '<label for="date">Limit&nbsp;by&nbsp;date:</label>&nbsp;';
					$ret .= '<input id="date" name="date" maxlength="4" size="4" value="'.esc_attr($params['date']).'">';
					$ret .= '<br>';
					$ret .= '<label for="date">Limit&nbsp;to&nbsp;Museum&nbsp;or&nbsp;Department:</label>&nbsp;';
					$ret .= '<select name="dept" id="dept">';
						$ret .= '<option value="">All</option>';
						$opts = $this->_sro_get_departments();
						foreach ($opts as $o) {
							$ret .= '<option value="'.$o['dept_code'].'"'.($params['dept'] == $o['dept_code'] ? ' selected' : '').'>'.$o['name'].'</option>';
						}
					$ret .= '</select>';
				$ret .= '</div>';
				$ret .= '<div class="criteria export">';
					$ret .= '<label for="send_to">Send&nbsp;Results&nbsp;to:</label>&nbsp;';
					$ret .= '<select id="send_to" name="send_to">';
						$ret .= '<option value="screen">This Window</option>';
						$ret .= '<option value="download"'.($params['send_to'] == 'download' ? ' selected' : '').'>Download</option>';
					$ret .= '</select>';
					$ret .= '&nbsp;&nbsp;&nbsp; ';
					$ret .= '<label for="export_format">Download&nbsp;Format:</label>&nbsp;';
					$ret .= '<select id="export_format" name="export_format">';
						$ret .= '<option value="json"'.($params['export_format'] == 'json' ? ' selected' : '').'>JSON</option>';
						$ret .= '<option value="csv"'.($params['export_format'] == 'csv' ? ' selected' : '').'>Comma-Separated (CSV)</option>';
						$ret .= '<option value="ris"'.($params['export_format'] == 'ris' ? ' selected' : '').'>RIS (Zotero, Mendeley, etc)</option>';
					$ret .= '</select>';
				$ret .= '</div>';
			$ret .= '</div>';
		}
		$ret .= '</form>';
		return $ret;
	}

	public function get_results_form() {
	
		$params = $this->_clean_params();
		
		$ret = '<form name="sro_search" method="GET" action="/publications/" id="sro_results_search">';
		$ret .= '<input type="hidden" name="action" value="sro_search_results">';
    $ret .= '<input type="hidden" id="q" name="q" value="'.esc_attr($params['search_term']).'" />';
    $ret .= '<input type="hidden" id="limit" name="limit" value="'.esc_attr($params['limit']).'" />';
    $ret .= '<input type="hidden" id="date" name="date" value="'.esc_attr($params['date']).'" />';
    $ret .= '<input type="hidden" id="dept" name="dept" value="'.esc_attr($params['dept']).'" />';
    $ret .= '<input type="hidden" id="send_to" name="send_to" value="'.esc_attr($params['send_to']).'" />';
    $ret .= '<input type="hidden" id="export_format" name="export_format" value="'.esc_attr($params['export_format']).'" />';
    $ret .= '<input type="hidden" id="page" name="page" value="'.esc_attr($params['page']).'" />';
    $ret .= '<label for="sort">Sort By:</label> <select id="sort" name="sort" onChange="return reloadPage();"> ';
      $ret .= '<option value="relevance"'.($params['sort'] == 'relevance' ? ' selected' : '').'>Relevance</option>';
      $ret .= '<option value="published"'.($params['sort'] == 'published' ? ' selected' : '').'>Date Published</option>';
      $ret .= '<option value="author"'.($params['sort'] == 'author' ? ' selected' : '').'>Author Name</option>';
      $ret .= '<option value="journal"'.($params['sort'] == 'journal' ? ' selected' : '').'>Journal Name</option>';
      $ret .= '<option value="added"'.($params['sort'] == 'added' ? ' selected' : '').'>Date Added</option>';
    $ret .= '</select> ';
    $ret .= '<label for="perpage">Items Per Page:</label> <select id="perpage" name="perpage" onChange="return reloadPage();"> ';
      $ret .= '<option value="5"'.($params['perpage'] == '5' ? ' selected' : '').'>5</option>';
      $ret .= '<option value="20"'.($params['perpage'] == '20' ? ' selected' : '').'>20</option>';
      $ret .= '<option value="50"'.($params['perpage'] == '50' ? ' selected' : '').'>50</option>';
      $ret .= '<option value="100"'.($params['perpage'] == '100' ? ' selected' : '').'>100</option>';
      $ret .= '<option value="250"'.($params['perpage'] == '250' ? ' selected' : '').'>250</option>';
    $ret .= '</select>';
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
			array('server_url' => 'http://staff.research.si.edu/search-api/publications/', 'query_extra' => '')
		);

		$params = array(
			'search_term' => null,
			'advanced' => false,
			'limit' => null,
			'exact' => null,
			'date' => null,
			'dept' => null,
			'sort' => 'relevance',
			'send_to' => 'screen',
			'export_format' => 'json',
			'perpage' => 20,
			'page' => 1,
			'server_url' => $options['server_url'],
			'query_extra' => $options['query_extra'],
		);

		if (!isset($_GET['q'])) {
			$_GET['q'] = null;
		}
		if (!empty($_GET['q'])) {
			$params['search_term'] = trim($_GET['q']);
		}		

		if (!empty($_GET['advanced'])) {
			$params['advanced'] = ($_GET['advanced'] ? true : false);
		}		

		if (!empty($_GET['exact'])) {
			$params['exact'] = ($_GET['exact'] ? '1' : '');
		}		
		
		if (!empty($_GET['limit']) && $_GET['limit']) {
			if (in_array($_GET['limit'], array('author', 'author_id', 'journal'))) {
				$params['limit'] = $_GET['limit']; // It has to be one of these values
			}
		}
		
		if (!empty($_GET['year']) && $_GET['year']) {
			$params['date'] = (int)$_GET['date']; // Cast to int to sanitize
		} elseif (!empty($_GET['date']) && $_GET['date']) {
			$params['date'] = (int)$_GET['date']; // Cast to int to sanitize
		}

		if (!empty($_GET['dept']) && $_GET['dept']) {
			$opts = $this->_sro_get_departments();
			foreach ($opts as $o) {
				if ($_GET['dept'] == $o['dept_code']) {
					$params['dept'] = $o['dept_code'];
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

		wp_register_style('silibraries-sro-fa-all', plugins_url('/assets/font-awesome/css/all.min.css', __FILE__), array(), SRO_VERSION);
		wp_enqueue_style('silibraries-sro-fa-all');
		
		wp_register_script('sro-js', plugins_url('/js/silibraries-sro.js', __FILE__));
		wp_enqueue_script('sro-js');
		
		wp_register_script('altmetric', 'https://d1bxh8uas1mnw7.cloudfront.net/assets/embed.js');
		wp_enqueue_script('altmetric');
		
		print $this->get_form('advanced', $params['search_term']);

		if ($results) {

			// Print the output, includes all the components to make a full poage.
			print '<div id="sro">';
			print '<h2>Search Results<div class="spinner"></div></h2>';

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
				print $this->get_results_form();
				print '<div id="summary">Showing '.$min_this_page."-".$max_this_page.' of about '.$total_recs.' results.</div>';
			}

			$pagination = null;
			if ($results->count > $params['perpage']) {
				$pagination =  PaginationLinks::create(
					$params['page'], $total_pages, 2, 
					'<a class="page" href="?action=sro_search_results&q=XYZZY&pg=%d&perpage='.$params['perpage'].'&sort='.$params['sort'].'&limit='.$params['limit'].'&date='.$params['date'].'&dept='.$params['dept'].'">%d</a>',
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

  function _set_page_title($title, $post_id = null) {

    // Admin area, bail out
    if( is_admin() )
        return $title;

    // We only care about one page
    if ($post_id == null) {
      $post_id = get_queried_object_id();
    }
    if ($post_id) {
      $current_url = get_permalink( $post_id );
      if (preg_match('/publication-details/', $current_url)) {
        $item = $this->_get_current_item();
        if ($item) {
          return $item->title;    
        }
      }
    }

    return $title;
  }

  function _get_current_item() {
    if ($this->current_item) {
      return $this->current_item;
    }
	  $id = 0;

    global $wp;
    $current_url = home_url( add_query_arg( array(), $wp->request ) );
    $url = explode('/', $current_url);
    
    if (preg_match('/^[0-9]+$/',end($url))) {
      $id = (int)end($url);
    } elseif (isset($_GET['id'])) {
      $id = (int)trim($_GET['id']);
	  }
	  
    if (!$id) {
      return null;
    }
    
		$options = get_option(
			'sro_options',
			array('server_url' => 'http://staff.research.si.edu/search-api/publications/', 'query_extra' => '')
		);
		// Cold Fusion is 
		$id = intval($id & 0xfffffff);
		$query = array('search' => $id); 
		$results = $this->_execute_query($options['server_url'], $query);

		if ($results) {
			// Calculate the pages and records and stuff for pagination
			if ($results->count > 0) {
    		foreach ($results->records as $r) {
    		  $this->current_item = $r->reference;
    		  return $this->current_item;
        }  
      }
    }
  	return null;
  }	
  
	function display_item() {
  
    $item = $this->_get_current_item();
    if (!$item) {
      print '<div id="summary">No ID was supplied.</div>';
      return;
    }

		wp_register_style('silibraries-sro-fa-all', plugins_url('/assets/font-awesome/css/all.min.css', __FILE__), array(), SRO_VERSION);
		wp_enqueue_style('silibraries-sro-fa-all');
		
		wp_register_script('sro-js', plugins_url('/js/silibraries-sro.js', __FILE__));
		wp_enqueue_script('sro-js');
		
		wp_register_script('altmetric', 'https://d1bxh8uas1mnw7.cloudfront.net/assets/embed.js');
		wp_enqueue_script('altmetric');
		    
    print '<div id="sro">';
    print '<div id="results">';
    print $this->_format_html_entry($item, true);
    print '</div>';
    print '</div>';
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
		$output[] = 'THE DATA EXPORTED HERE IS LIMITED TO BOOKS, CHAPTERS AND ARTICLES IN THE SRO DATABASE';
		$output[] = '';
		$output[] = 'Copy the text below and save it as a *.txt file for importing to EndNote, RefWorks, Reference Manager, Zotero etc.';
		$output[] = '';
		foreach ($res->records as $r) {
			$r = $r->reference;
			if ($r->item_type == 'article' || $r->item_type == 'journal') {
				$output[] = 'TY  - JOUR';
				$first = true;
				foreach (_unique_authors($r->authors) as $a) {
					$output[] = ($first ? 'A1' : 'AU').'  - '.$a['name'];
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
				foreach ($r->keywords as $k) {
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
			
			} elseif ($r->item_type == 'chapter') {
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

			} elseif ($r->item_type == 'book') {
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
		$header = array('item_type', 'title', 'authors', 'editors', 'journal', 'book_title', 'series', 'smithsonian_author_id', 
		                'orcid', 'volume', 'issue', 'pages', 'start_page', 'end_page', 'publisher', 'publisher_place', 
		                'year', 'link', 'doi', 'issn_isbn', 'keywords', 'acknowledgement', 'funders', 'date_added', 'id');
		$output .= str_putcsv($header);
		foreach ($res->records as $r) {
			$rec = array();
			$r = $r->reference;
			$rec[] = (empty($r->item_type) ? '' : $r->item_type);
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
			$rec[] = (empty($r->year) ? '' : $r->year);
			$rec[] = (empty($r->link) ? '' : $r->link);
			$rec[] = (empty($r->doi) ? '' : $r->doi);
			$rec[] = (empty($r->issn_isbn) ? '' : $r->issn_isbn);
			$rec[] = (empty($r->keywords) ? '' : implode(', ',$r->keywords));
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
	function _format_html_results($res, $perpage = 20, $extras = false) {
		$ret = '';
		$c = 1;
		$ret .= '<div id="results">';
		foreach ($res->records as $r) {
			$ret .= $this->_format_html_entry($r->reference, $extras);
			$c++;
			if ($perpage > 0) {
				if ($c > $perpage) {
					break;
				}
			}
		}
		$ret .= '</div>';
		return $ret;
	}	

	/* 
   * Formall all results for sending to RSS
   */ 
	function _format_rss_results($res) {
		$ret = [];

		$ret[] = '<?xml version="1.0" encoding="UTF-8"?>';
		$ret[] = '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:sy="http://purl.org/rss/1.0/modules/syndication/" xmlns:slash="http://purl.org/rss/1.0/modules/slash/">';
		$ret[] = '  <channel>';
		$ret[] = '    <title>Recent Additions to Smithsonian Research Online</title>';
		$ret[] = '    <link>https://research.si.edu/</link>';
		$ret[] = '    <description></description>';
		$ret[] = '    <language>en-us</language>';
		$ret[] = '    <managingEditor>Research-Online@si.edu (Smithsonian Research Online)</managingEditor>';
		$ret[] = '    <atom:link href="https://research.si.edu/feed/latest-publications" rel="self" type="application/rss+xml" />';
		$ret[] = '    <sy:updatePeriod>'.apply_filters( 'rss_update_period', 'daily' ).'</sy:updatePeriod>';
		$ret[] = '    <sy:updateFrequency>1</sy:updateFrequency>';
      
		date_default_timezone_set('EST');
		foreach ($res->records as $r) {
			$r = $r->reference;
			$date = strtotime($r->date_submitted.' EST'); // Use the accessioned date if we have it
			if (!$date) {
				$date = strtotime($r->date_added.' EST'); // Use the database date if we have it
			}
			if (!$date) {
				$date = time(); // We MUST have a date, so... use the current time.
			}

		  $ret[] = '    <item>';
			$ret[] = '      <title>'.$r->title.'</title>';
			if (isset($r->doi)) {
				$ret[] = '      <link>https://doi.org/'.$r->doi.'</link>';
			} elseif (isset($r->url)) {
				$ret[] = '      <link>'.$r->url.'</link>';
			}
			$ret[] = '      <description><![CDATA['.htmlspecialchars($r->citation, ENT_QUOTES | ENT_XML1, 'UTF-8', true).']]></description>';
		  $ret[] = '      <pubDate>'.date("r", $date).'</pubDate>';
		  $ret[] = '      <guid isPermaLink="false">sro_id:'.$r->id.'</guid>';
		  $ret[] = '    </item>';
		}
		$ret[] = '  </channel>';
		$ret[] = '</rss>';

		return implode("\n", $ret)."\n";
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
	function _format_html_entry($rec, $include_extras = false, $include_schema = true) {
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
		$coins[] = 'rft.date='.urlencode($rec->year);

		// Normalization
		if (!empty($rec->title)) {
			$rec->title = preg_replace('/<[^>].*>/', '', $rec->title);
		}
		if ($include_extras) {
			if ($rec->item_type == 'article') {
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
			} elseif ($rec->item_type == 'chapter') {
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
			} elseif ($rec->item_type == 'book') {
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
		$schema = '';
		if ($rec->item_type == 'article') { // and (k1 does not contain 'Book review')
			$itemtype = 'http://schema.org/ScholarlyArticle';
		} elseif ($rec->item_type == 'chapter') {
			$itemtype = 'http://schema.org/Chapter';
		} elseif ($rec->item_type == 'book')  {
			$itemtype = 'http://schema.org/Book';
		}			

		$schema .= '<div class="schema-dot-org">';
			$schema .= '<div itemscope="" itemtype="'.$itemtype.'">';
				if (!empty($rec->title)) {
					$schema .= '<span property="name">'.$rec->title.'</span>';
				}
				if ($rec->item_type == 'book') {
					if (!empty($rec->book_title)) {
						$schema .= '<span itemprop="name">'.$rec->book_title.'</span>';
					}
				}
				if (!empty($rec->authors)) {
					foreach (_unique_authors($rec->authors) as $a) {
						$schema .= '<span property="author" itemscope="" itemtype="http://schema.org/Person"><span itemprop="name">'.$a['name'].'</span></span>';
					}
				}
				if (!empty($rec->editors) && !empty($rec->editors)) {
						$schema .= '<span itemscope="" itemtype="http://schema.org/Person">';
							$schema .= '<span itemprop="editor">';
								foreach (_unique_authors($rec->editors) as $a) {
									$schema .= '<span itemprop="name">'.$a['name'].'</span>';
								}
							$schema .= '</span>';
					 $schema .= '</span>';
				}
				if (!empty($rec->date)) {
					$schema .= '<span property="datePublished">'.$rec->date.'</span>';
				}
				if (!empty($rec->doi) && !empty($rec->doi)) {
					$schema .= 'DOI: <a property="sameAs" href="https://doi.org/'.$rec->doi.'">info:'.$rec->doi.'</a>';
				}
				$schema .= '<span property="isPartOf" typeof="Periodical">';
				if (!empty($rec->journal)) {
					$schema .= '<span property="name">'.$rec->journal.'</span>';
				}
				if (!empty($rec->volume) && $rec->volume > 0) {
					$schema .= 'v. <span property="volumeNumber">'.$rec->volume.'</span>';
				}
				if (!empty($rec->issue) && $rec->issue > 0) {
					$schema .= 'No. <span property="issueNumber">'.$rec->issue.'</span>';
				}
				if (!empty($rec->start_page)) {
					$schema .= '<span itemprop="pageStart">'.$rec->start_page.'</span>';
				}
				if (!empty($rec->end_page)) {
					$schema .= '<span itemprop="pageEnd">'.$rec->end_page.'</span>';
				}
				if (!empty($rec->publisher_place)) {
					$schema .= '<span itemprop="location">'.$rec->publisher_place.'</span>';
				}
				if (!empty($rec->publisher)) {
					$schema .= '<span itemprop="publisher">'.$rec->publisher.'</span>';
				}
				if (!empty($rec->pages)) {
					$schema .= '<span itemprop="numberOfPages">'.$rec->pages.'</span>';
				}
				if (!empty($rec->issn_isbn)) {
					$schema .= '<span itemprop="ISBN">'.$rec->issn_isbn.'</span>';
				}
				$schema .= '</span>';
			$schema .= '</div>';
		$schema .= '</div>';


		$type = 'Other';
		$icon = 'fa-question-circle';
		if ($rec->item_type == 'abstract')               { $type = 'Abstract';               $icon = 'fa-align-justify'; } 
		elseif ($rec->item_type == 'forum_blog')         { $type = 'Forum/Blog Post';        $icon = 'fa-comments'; } 
		elseif ($rec->item_type == 'blog')               { $type = 'Forum/Blog Post';        $icon = 'fa-comments'; } 
		elseif ($rec->item_type == 'book')               { $type = 'Book';                   $icon = 'fa-book'; } 
		elseif ($rec->item_type == 'chapter')            { $type = 'Book Chapter';           $icon = 'fa-bookmark'; } 
		elseif ($rec->item_type == 'book_review')        { $type = 'Book Review';            $icon = 'fa-book-reader'; }
		elseif ($rec->item_type == 'computer_program')   { $type = 'Computer Program';       $icon = 'fa-laptop-code'; }
		elseif ($rec->item_type == 'conference')         { $type = 'Conference Proceedings'; $icon = 'fa-users-class'; } 
		elseif ($rec->item_type == 'dataset')            { $type = 'Dataset';                $icon = 'fa-database'; } 
		elseif ($rec->item_type == 'thesis')             { $type = 'Thesis';                 $icon = 'fa-file-edit'; } 
		elseif ($rec->item_type == 'exhibition_catalog') { $type = 'Exhibition Catalog';     $icon = 'fa-file-invoice'; }
		elseif ($rec->item_type == 'article')            { $type = 'Article';                $icon = 'fa-file-alt'; } 
		elseif ($rec->item_type == 'magazine_article')   { $type = 'Magazine Article';       $icon = 'fa-file'; } 
		elseif ($rec->item_type == 'map')                { $type = 'Map';                    $icon = 'fa-map'; } 
		elseif ($rec->item_type == 'newspaper_article')  { $type = 'Newspaper Article';      $icon = 'fa-newspaper'; } 
		elseif ($rec->item_type == 'patent')             { $type = 'Patent';                 $icon = 'fa-file-certificate'; }
		elseif ($rec->item_type == 'poster')             { $type = 'Poster';                 $icon = 'fa-user-chart'; }
		elseif ($rec->item_type == 'presentation')       { $type = 'Presentation';           $icon = 'fa-presentation'; } 
		elseif ($rec->item_type == 'report')             { $type = 'Report';                 $icon = 'fa-list-alt'; } 
		elseif ($rec->item_type == 'sound_recording')    { $type = 'Sound Recording';        $icon = 'fa-volume-up'; } 
		elseif ($rec->item_type == 'video_dvd')          { $type = 'Video/DVD';              $icon = 'fa-video'; } 
		elseif ($rec->item_type == 'web_page')           { $type = 'Web Page';               $icon = 'fa-globe'; } 
		elseif ($rec->item_type == 'motion_picture')     { $type = 'Motion Picture';         $icon = 'fa-film'; } 
		elseif ($rec->item_type == 'artwork')            { $type = 'Artwork';                $icon = 'fa-image'; } 
		elseif ($rec->item_type == 'exhibition')         { $type = 'Exhibition';             $icon = 'fa-landmark'; } 
		elseif ($rec->item_type == 'generic')            { $type = 'Generic';                $icon = 'fa-rectangle-portrait'; } 

		/* 
			From Suzanne: 
			author_display. date. <ACTIONABLE LINK the words in title> title. [journal]<OR>[in [editor_display}. book_title]. publisher_place, publisher, (series). volume(issue):start_page-end_page. doi<ACTIONABLE DOI>
			My interpretation
			- Author(s) followed by a period.
			- Date followed by a period. (what format? year? Month/year? Whatever's in the database?)=
			- The title, linked to somewhere else if there's a URL in the database.
			- One of the two following:
			- The journal name followed by a period, in italics, if provided 
			- The word "in" followed by the editors followed by a period, if provided, followed by the book title followed by a period in italics, if provided.
			- Publisher place, followed by a comma.
			- Publisher name, followed by a comma.
			- Series in parentheses, followed by a period.
			- Volume field
			- Issue field, in parentheses followed by a colon:
			- Start page
			- If end page provided, a hyphen and the end page.
			- A period. (to end the volume/issue/pages)
			- The DOI linked to the DOI url, if provided.
	  */
		if ($include_extras) {
			if (!empty($rec->doi)) {
				$ret[] =  '<div class="show_metric altmetric-embed" data-badge-type="donut" data-badge-popover="left" data-hide-no-mentions="true" data-doi="'.$rec->doi.'"></div>';
			}
			if (!empty($rec->issn_isbn)) {
				$ret[] =  '<div class="show_metric altmetric-embed" data-badge-type="donut" data-badge-popover="left" data-hide-no-mentions="true" data-isbn="'.$rec->issn_isbn.'"></div>';
			}
		}
		if ($include_schema) {
			$ret[] = $schema;
		}
 		$ret[] .=  '<div class="result '.$icon.'" title="'.$type.'">';			

    // Something else has made the citation for us. Yay!
    $ret[] .= $rec->citation;

    // Display the abstract, etc if necessary
    if ($include_extras) {
     // if (!empty($rec->abstract) || !empty($rec->keywords) || !empty($rec->orcid_list) || !empty($rec->funders)) {
        $ret[] = '<button class="view-abstract" data-id="'.$rec->id.'" onClick="sroToggleAbstract(this);">More...</button>';
        $ret[] = '<div class="abstract" id="abstract-'.$rec->id.'">';
        if (!empty($rec->id)) {
          $ret[] = '<strong>ID:</strong> <a href="/publication-details/?id='.$rec->id.'">'.$rec->id.'</a><br>';
        }
        $ret[] = '<strong>Type:</strong> '.$rec->item_type.'</a><br>';
        if (!empty($rec->authors)) {
          $authors = [];
          foreach ($rec->authors as $a) {
            if ($a->profiles_id) {
              $authors[] = '<a href="/publications/?action=sro_search_results&q='.$a->profiles_id.'">'.$a->name.'</a>';
            } elseif ($a->orcid) {
              $authors[] = '<a href="/publications/?action=sro_search_results&q='.$a->profiles_id.'">'.$a->name.'</a>';
            } else {
              $authors[] = $a->name;
            }            
          }
          $ret[] = '<strong>Authors:</strong> '.implode($authors, '; ').'<br>';
        }
        if (!empty($rec->keywords)) {
          $ret[] = '<strong>Keywords:</strong> '.implode($rec->keywords, '; ').'<br>';
        }
        if (!empty($rec->abstract)) {
          $ret[] = '<strong>Abstract:</strong> '.$rec->abstract.'<br>';
        }
        if (!empty($rec->orcid_list)) {
          $ret[] = '<strong>ORCID(s):</strong> '.$rec->orcid_list.'<br>';
        }
        if (!empty($rec->funders)) {
          $ret[] = '<strong>Funders:</strong> '.$rec->funders.'<br>';
        }
        $ret[] = '</div>';
     // }
    }
		$ret[] =  '</div>';
		return implode('', $ret);
	}
		
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
		$results = null;
		if (count($args) > 0) {
			if (!preg_match('/\/$/', $url)) {
				$url .= '/';
			}			
			try {
				print "<!-- QUERY = ".$url.'?'.$query." -->";
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url.'?'.$query);
			        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			        curl_setopt($ch, CURLOPT_FAILONERROR, true);
			        curl_setopt($ch, CURLOPT_USERAGENT, 'PHP Code (research.si.edu)');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$results = curl_exec($ch);
				if ($results === false) {
					return null;
				}
			} catch (Exception $e) {
				// Handle exception
				print '<h5 class="error">'.($e->getMessage()).'</h5>';
			}
			$results = preg_replace("/^\/\//",'',$results);
			$results = json_decode($results);
		} else {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url.'?'.$query);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_FAILONERROR, true);
			curl_setopt($ch, CURLOPT_USERAGENT, 'PHP Code (research.si.edu)');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$results = curl_exec($ch);

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

				$options = get_option(
					'server_altmetrics_url',
					array('server_altmetrics_url' => 'http://staff.research.si.edu/search-api/altmetrics_pubs.cfm', 'query_extra' => '')
				);

				$results = $this->_execute_query($options['server_altmetrics_url'], array());
				return $results;
			}					

			// Do the search at SRO in JSON
			$results = null;
			if (!empty($params['search_term']) || !empty($params['dept'])) {
				$query = array(
					'search'  => $params['search_term'],
					'limit'   => $params['limit'],
					'exact'   => $params['exact'],
					'year'    => $params['date'],
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
		$options = get_option(
			'sro_options',
			array('depts_url' => 'http://staff.research.si.edu/search-api/departments/', 'query_extra' => '')
		);
		$json = file_get_contents($options['depts_url']);
		$json = preg_replace("/^\/\//",'',$json);
		return json_decode($json, true);
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

/* Custom RSS feed for the most recent publications
 * Perform a fixed search, print RSS to the screen
 */
function _get_latestPubsRSS() {
	global $wpSROSearch;

	header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . get_option( 'blog_charset' ), true );

	// Do the search with the parameters, format and return the results
	// This is where you run the code and display the output
	$options = get_option(
		'sro_options',
		array('server_url' => 'http://staff.research.si.edu/search-api/publications/', 'query_extra' => '')
	);

	$json = $wpSROSearch->_execute_query(
		$options['server_url'], 
		array('full_query' => 'count=25&override=1&sort=added')
	);

	$rss = $wpSROSearch->_format_rss_results($json);
	print($rss);
}

/* ------------------------------ */
/*    WORDPRESS API ACTIVITIES    */
/* ------------------------------ */

/* Add our CSS to the page output */
function my_scripts() {
  wp_register_style('silibraries-sro', plugins_url('/css/style.css', __FILE__), array(), SRO_VERSION);
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

add_filter( 'pre_get_document_title', array($wpSROSearch, '_set_page_title'), 10, 1);

add_filter( 'the_title', array($wpSROSearch, '_set_page_title'), 10, 2);

add_action('init', 'customRSS');
function customRSS() { add_feed('latest-publications', '_get_latestPubsRSS'); }
