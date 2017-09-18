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
require_once(ABSPATH .'wp-content/plugins/silibraries-sro/class.PaginationLinks.php');
require_once(ABSPATH .'wp-content/plugins/silibraries-sro/admin.php');
// require_once(ABSPATH . 'wp-content/plugins/silibraries-sro/settings.php');
// require_once(ABSPATH . 'wp-content/plugins/silibraries-sro/misc.php');

/*
	Create the widget that will allow us to add the search form to the sidebar
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

	// Creating widget front-end

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

	// Widget Backend
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
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

	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}
} 

/*
	Create the widget that will allow us to add the search form to the sidebar
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

	// Creating widget front-end
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

		$json = $wpSROSearch->_perform_query(
			$options['server_url'], 
			array('full_query' => $instance['search_query'])
		);

		$html .= $wpSROSearch->_format_results(
			$json, $instance['max']
		);

		print '<div id="sro">';
		print __($html, 'sro_widget_domain');
		print '</div>';

		print $args['after_widget'];
	}

	// Widget Backend
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		} else {
			$title = __( 'New title', 'sro_fixed_widget_domain' );
		}
		if ( isset( $instance[ 'search_query' ] ) ) {
			$search_query = $instance[ 'search_query' ];
		} else {
			$search_query = __( 'a=b&c=d', 'sro_fixed_widget_domain' );
		}
		if ( isset( $instance[ 'max' ] ) ) {
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

	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['search_query'] = ( ! empty( $new_instance['search_query'] ) ) ? strip_tags( $new_instance['search_query'] ) : '';
		$instance['max'] = ( ! empty( $new_instance['max'] ) ) ? strip_tags( $new_instance['max'] ) : '';
		return $instance;
	}
} // Class wpb_widget ends here


/*
	The bulk of the functionality of the searching.
*/

class SROSearch {
	public function __construct() {
		if(is_admin()) {
			$my_settings_page = new SROSettingsPage();
		}
		// add_action('init', array($this, 'search_submit'));
		add_shortcode('sro-search-results', array($this, 'search_submit'));
	}

	public function get_form($type = 'basic', $query = '') {
		$ret = '<form name="sro_basic_search" method="GET" action="/publications/">';
		$ret .= '<input type="hidden" name="action" value="sro_search_results">';
		if ($type == 'basic') {
			$ret .= '<label class="hidden" for="q">Enter Search Term</label>';
			$ret .= '<input type="text" id="q" name="q" placeholder="Enter Search Term" />';
		}
		if ($type == 'advanced') {
			$ret .= "<h4>Advanced Search</h4>";
			$ret .= '<label class="hidden" for="q">Enter Search Term</label>';
			$ret .= '<input type="text" id="q" name="q" value="'.$query.'" style="width: 60%" placeholder="Enter Search Term" />';
		}
		$ret .= '&nbsp;'.get_submit_button('Go', 'primary large', null, false);
		if ($type == 'advanced') {
			$ret .= '<br><a id="advanced-link" onClick="sroToggleAdvancedSearch();">Advanced Search</a>';
			$ret .= '<div id="advanced-search" style="display:none">';
				$ret .= '<label for="limit">Limit to:</label>&nbsp;';
				$ret .= '<select id="limit" name="limit">';
					$ret .= '<option value="">(none)</option>';
					$ret .= '<option value="author">Author</option>';
					$ret .= '<option value="journal">Journal</option>';
				$ret .= '</select>';
				$ret .= '&nbsp;&nbsp;&nbsp;';
				$ret .= '<label for="date">Limit by year:</label>&nbsp;';
				$ret .= '<input id="date" name="date" maxlength="4" size="4">';
				$ret .= '&nbsp;&nbsp;&nbsp;';
				$ret .= '<label for="date">Museum/Department:</label>&nbsp;';
				$ret .= '<select name="department" id="department">';
					$ret .= '<option value="" selected="selected">All</option>';
					$opts = _sro_get_departments();
					foreach ($opts as $o) {
						$ret .= '<option value="'.$o['id'].'">'.$o['name'].'</option>';
					}
				$ret .= '</select>';
			$ret .= '</div>';
		}
		$ret .= '</form>';
		return $ret;
	}

	function search_submit() {

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

		print $this->get_form('advanced', $_GET['q']);

		if (isset($_GET['action']) && $_GET['action'] === 'sro_search_results') {

			// Do the search at SRO in JSON
			$results = null;
			$perpage = 20;
			$page = 1;
			$debug = false;
			if (isset($_GET['debug']) && $_GET['debug'] == 1) {
				$debug = true;
			}
			if (isset($_GET['perpage']) && $_GET['perpage']) {
				$perpage = $_GET['perpage'];
			}
			if (isset($_GET['pg']) && $_GET['pg']) {
				$page = $_GET['pg'];
			}

			if (isset($_GET['q']) && $_GET['q']) {

				$options = get_option(
					'sro_options',
					array('server_url' => 'http://research.si.edu/search/', 'query_extra' => '')
				);
				
				$results = $this->_perform_query(
					$options['server_url'], 
					array(
						'search_term' => $_GET['q'],
						'submit' => 'Export data',
						'date' => '',
						'format' => 'JSON', 
						'unit' => 'All',
						'count' => $perpage,
						'pagenum' => $page,
					)
				);
			}

			// Print the output, includes all the components to make a full poage.

			if ($results) {
				print '<div id="sro">';
				print "<h2>Search Results</h2>";				

				// Calculate the pages and records and stuff for pagination
				$total_recs = $results->count;
				$total_pages = floor($total_recs / $perpage);
				if ($total_recs % $perpage != 0) {
					$total_pages++;
				}				
				$min_this_page = (($page-1) * $perpage)+1;
				$max_this_page = min(array($page * $perpage, $total_recs));
				$remaining_records = $total_recs - $max_this_page;
				print '<div id="summary">Showing '.$min_this_page."-".$max_this_page.' of about '.$total_recs.' results.</div>';

				if ($results->count > $perpage) {
					$pagination =  PaginationLinks::create(
						$page, $total_pages, 2, 
						'<a class="page" href="?action=sro_search_results&q='.urlencode($_GET['q']).'&pg=%d&perpage='.$perpage.'">%d</a>',
						'<span class="current">%d</span>'
					);
					print '<div id="pagination">'.$pagination.'</div>';
				}

				print $this->_format_results($results, $perpage, true);
				
				print '<div id="pagination">'.$pagination.'</div>';
				print '</div>';
			}
		}
	}
	
	function _format_results($res, $perpage, $extras = false) {
		$ret = '';
		$c = 1;
		$ret .= '<div id="results">';
		foreach ($res->records as $r) {
			$ret .= $this->_format_entry($r->reference, $extras);
			$c++;
			if ($c > $perpage) {
				break;
			}
		}
		$ret .= '</div>';
		return $ret;
	}	
	
	function _perform_query($url, $args) {
		if (!preg_match('/\/$/', $url)) {
			$url .= '/';
		}		
		if (isset($args['full_query'])) {
			$query = $args['full_query'];
		} else {
			$query = [];
			foreach ($args as $name => $val) {
				$query[] = $name.'='.urlencode($val);
			}
			$query = implode('&', $query);
		}
		$results = file_get_contents($url.'?'.$query);
		
		$results = json_decode($results);
		return $results;		
	}

	function _format_entry($rec, $include_extras = false) {
		$coins = array();
		$coins[] = 'url_ver=Z39.88-2004';
		$coins[] = 'ctx_ver=Z39.88-2004';
		$coins[] = 'rfr_id=info%3Asid%2Fzotero.org%3A2';
		if (isset($rec->authors)) {
			foreach ($rec->authors as $a) {
				$coins[] = 'rft.au='.urlencode($a->name);
			}
		}
		$coins[] = 'rft.date='.urlencode($rec->date);

		// Normalization
		if (isset($rec->title)) {
			$rec->title = preg_replace('/<[^>].*>/', '', $rec->title);
		}
		if ($include_extras) {
			if ($rec->pubtype == 'article') {
				// COinS DATA FOR ZOTERO IMPORT
				if (isset($rec->doi)) {
					$coins[] = 'rft_id=info%3Adoi%2F'.urlencode($rec->doi);
				}
				$coins[] = 'rft_val_fmt=info%3Aofi%2Ffmt%3Akev%3Amtx%3Ajournal';
				$coins[] = 'rft.genre=article';
				$coins[] = 'rft.atitle='.urlencode($rec->title);
				$coins[] = 'rft.jtitle='.urlencode($rec->journal);
				if (isset($rec->volume)) {
					$coins[] = 'rft.volume='.urlencode($rec->volume);
				}
				if (isset($rec->issue)) {
					$coins[] = 'rft.issue='.urlencode($rec->issue);
				}
				$coins[] = 'rft.stitle='.urlencode($rec->journal);
				$coins[] = 'rft.pages='.urlencode($rec->pages);
				if (isset($rec->pages)) {
					if (strpos($rec->pages, '-')) {
						$p = explode('-', $rec->pages);
						$coins[] = 'rft.spage='.$p[0];
						$coins[] = 'rft.epage='.$p[1];
					} else {
						$coins[] = 'rft.spage='.$rec->pages;
					}
				}
				if (isset($rec->issn)) {
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
				$ret .= '<div vocab="http://schema.org/" typeof="ScholarlyArticle">';
					$ret .= '<span property="name">'.$rec->title.'</span>';
					foreach ($rec->authors as $a) {
						$ret .= '<span property ="author">'.$a->name.'</span>';
					}
					$ret .= '<span property="datePublished">'.$rec->date.'</span>';
					if (isset($rec->doi)) {
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
					if (isset($rec->doi)) {
						$ret .= '<div class="show_metric" class="altmetric-embed" data-badge-type="donut" data-badge-popover="left" data-hide-no-mentions="true" data-doi="'.$rec->doi.'"></div>';
					}
				}
				// #a1#
				$ret .= $rec->author_display;
				if (!preg_match('/\.$/', $rec->author_display)) {
					$ret .= '.';
				}
				// #yr#
				$ret .= ' <span class="date_highlight" class="date_display">'.$rec->date.'</span>.';

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
				$ret .= ' <span class="journal_bold" class="journal_display"><em>'.$rec->journal.'</em></span>,';

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
				if (isset($rec->doi)) {
					$ret .= ' <a href="http://dx.doi.org/'.$rec->doi.'">doi:'.$rec->doi.'</a>';
				}
			$ret .= '</div>';
			
		// ------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------
		} elseif ($rec->pubtype == 'ejournal_article') {
			if ($include_extras) {
				if (isset($rec->doi)) {
					$ret .= '<div class="show_metric" class="altmetric-embed" data-badge-type="donut" data-badge-popover="left" data-hide-no-mentions="true" data-doi="'.$rec->doi.'"></div>';
				}
			}
			$ret .= '<div class="result fa-file-pdf" title="E-Journal Article">';
				$ret .= $rec->author_display;
				if (!preg_match('/\.$/', $rec->author_display)) {
					$ret .= '.';
				}
				$ret .= ' <span class="date_highlight" class="date_display">'.$rec->date.'</span>.';
				if (preg_match('/http/', $rec->link)) {
					$ret .= '<a href="'.$rec->link.'">'.$rec->title.'</a>';
				} else {
					$ret .= $rec->title;
				}
				if (!preg_match('/[.?]$/', $rec->title)) {
					$ret .= '.';
				}
				$ret .= ' <span class="journal_bold" class="journal_display"><em>'.$rec->journal.'</em></span>,';
				if ($rec->issue > 0 && $rec->volume > 0) {
					$ret .= ' '.$rec->volume.'('.$rec->issue.')';
				} elseif ($rec->issue > 0 && $rec->volume == 0) {
					$ret .= ' '.$rec->issue;
				} elseif ($rec->issue == 0 && $rec->volume > 0) {
					$ret .= ' '.$rec->volume;
				}
				$ret .= ' '.$rec->pages;
				if (isset($rec->doi)) {
					$ret .= ' <a href="http://dx.doi.org/'.$rec->doi.'">doi:'.$rec->doi.'</a>';
				}
			$ret .= '</div>';

		// ------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------
		} elseif ($rec->pubtype == 'chapter') {

			$ret .= '<div class="result fa-bookmark" title="Chapter">';
				$ret .= '<div class="schema-dot-org">';
					$ret .= '<div vocab="http://schema.org/" typeof="Chapter">';
						$ret .= '<span itemprop="name" itemtype="http://schema.org/thing">'.$rec->title.'</span>';
						foreach ($rec->authors as $a) {
							$ret .= '<span property ="author">'.$a->name.'</span>';
						}
						$ret .= '<span itemprop="pageStart" itemtype="http://schema.org/Chapter">'.$rec->start_page.'</span>';
						$ret .= '<span itemprop="pageEnd" itemtype="http://schema.org/Chapter">'.$rec->end_page.'</span>';
						$ret .= '<span itemprop="ISBN" itemtype="http://schema.org/Book">'.$rec->issn_isbn.'</span>';
						$ret .= '<span itemprop="isPartOf"itemtype="http://schema.org/CreativeWork">';
						if (isset($rec->editors)) {
								$ret .= '<span itemscope itemtype="http://schema.org/Person">';
									$ret .= '<span itemprop="editor" itemtype="http://schema.org/Person">';
										foreach ($rec->editors as $a) {
											$ret .= '<span property ="editor">'.$a->name.'</span>';
										}
									$ret .= '</span>';
							 $ret .= '</span>';
						}
						$ret .= '<span itemprop="name" itemtype="http://schema.org/thing">'.$rec->book_title.'</span>';
						$ret .= '<span property="datePublished">'.$rec->year.'</span>';
						$ret .= '<span itemprop="location" itemtype= "http://schema.org/event">'.$rec->publisher_place.'</span>';
						$ret .= '<span itemprop="publisher">'.$rec->publisher.'</span>';
					$ret .= '</div>';
				$ret .= '</div>';
				# BOOK CHAPTER DISPLAY

				if ($include_extras) {
					if (isset($rec->doi)) {
						$ret .= '<div class="show_metric" class="altmetric-embed" data-badge-type="donut" data-badge-popover="left" data-hide-no-mentions="true" data-doi="'.$rec->doi.'"></div>';
					}
				}
				$ret .= $rec->author_display;
				if (!preg_match('/\.$/', $rec->author_display)) {
					$ret .= '.';
				}
				$ret .= ' <span class="date_highlight" class="date_display">'.$rec->date.'</span>.';
				if (preg_match('/http/', $rec->link)) {
					$ret .= ' <a href="'.$rec->link.'">'.$rec->title.'</a>';
				} else {
					$ret .= ' '.$rec->title;
				}
				if (!preg_match('/[.?]$/', $rec->title)) {
					$ret .= '.';
				}
				$ret .= ' In: ';
				if (isset($ret->editor_display)) {
					$ret .= $rec->editor_display.',';
				}
				$ret .= ' <i>'.$rec->book_title.'.</i>';
				if (isset($rec->publisher_place)) {
					$ret .= ' '.$rec->publisher_place.':';
				}
				if (isset($rec->publisher)) {
					$ret .= ' '.$rec->publisher;
				}
				if (isset($rec->series)) {
					$ret .= ', '.$rec->series;
				}
				if (isset($rec->pages)) {
					$ret .= ' pp. '.$rec->pages;
				}
				if (isset($rec->doi)) {
					$ret .= ' <a href="http://dx.doi.org/'.$rec->doi.'">doi:'.$rec->doi.'</a>';
				}
			$ret .= '</div>';

		// ------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------
		} elseif ($rec->pubtype == 'book') {
			$ret .= '<div class="schema-dot-org">';
				$ret .= '<div vocab="http://schema.org/" typeof="Book">';
					$ret .= '<span property="name">'.$rec->title.'</span>';
					foreach ($rec->authors as $a) {
						$ret .= '<span property ="author">'.$a->name.'</span>';
					}
					$ret .= '<span property="datePublished">'.$rec->date.'</span>';
					if (isset($rec->doi)) {
						$ret .= 'DOI: <a property="sameAs" href="http://dx.doi.org/'.$rec->doi.'">info:'.$rec->doi.'</a>';
					}
					$ret .= '<span itemprop="location" itemtype= "http://schema.org/event">'.$rec->publisher_place.'</span>';
					$ret .= '<span itemprop="publisher">'.$rec->publisher.'</span>';
					$ret .= '<span itemprop="numberOfPages" itemtype="http://schema.org/Book">'.$rec->pages.'</span>';
					$ret .= '<span itemprop="ISBN" itemtype="http://schema.org/Book">'.$rec->issn_isbn.'</span>';
					$ret .= '</span>';
				$ret .= '</div>';
			$ret .= '</div>';

			# BOOK DISPLAY

			$ret .= '<div class="result fa-book" title="Book">';
				$ret .= $rec->author_display;
				if (!preg_match('/\.$/', $rec->author_display)) {
					$ret .= '.';
				}
				$ret .= ' <span class="date_highlight" class="date_display">'.$rec->date.'</span>.';
				if (preg_match('/http/', $rec->link)) {
					$ret .= ' <a href="'.$rec->link.'">'.$rec->title.'</a>';
				} else {
					$ret .= ' '.$rec->title;
				}
				if (!preg_match('/[.?]$/', $rec->title)) {
					$ret .= '.';
				}
				if (isset($ret->editor_display)) {
					$ret .= ' '.$rec->editor_display;
				}
				if (isset($rec->publisher_place)) {
					$ret .= ' '.$rec->publisher_place.':';
				}
				if (isset($rec->publisher)) {
					$ret .= ' '.$rec->publisher.'.';
				}
				if (isset($rec->book_title)) {
					$ret .= ' '.$rec->book_title;
					if (isset($rec->book_title)) {
						$ret .= ' ('.$rec->volume.')';
					}
				}
				if (isset($rec->book_title)) {
					$ret .= ' '.$rec->pages.' pages.';
				}
				if (isset($rec->doi)) {
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
				if (isset($ret->editor_display)) {
					$ret .= $rec->editor_display.',';
					if (!preg_match('/[.?]$/', $rec->editor_display)) {
						$ret .= '.';
					}
				}
				$ret .= ' <span class="date_highlight" class="date_display">'.$rec->date.'</span>.';
				if (isset($rec->publisher_place)) {
					$ret .= ' '.$rec->publisher_place.':';
				}
				if (isset($rec->publisher)) {
					$ret .= ' '.$rec->publisher.'.';
				}
				if (isset($rec->book_title)) {
					$ret .= ' '.$rec->book_title;
					if (isset($rec->book_title)) {
						$ret .= ' ('.$rec->volume.')';
					}
				}
				if (isset($rec->book_title)) {
					$ret .= ' '.$rec->pages.' pages.';
				}
				if (isset($rec->doi)) {
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
				$ret .= ' <span class="date_highlight" class="date_display">'.$rec->date.'</span>.';
				if (preg_match('/http/', $rec->link)) {
					$ret .= ' <a href="'.$rec->link.'">'.$rec->title.'</a>';
				} else {
					$ret .= ' '.$rec->title;
				}
				if (!preg_match('/[.?]$/', $rec->title)) {
					$ret .= '.';
				}
				if (isset($rec->publisher_place)) {
					$ret .= ' '.$rec->publisher_place.':';
				}
				if (isset($rec->publisher)) {
					$ret .= ' '.$rec->publisher.'.';
				}
				if (isset($rec->book_title)) {
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
				$ret .= ' <span class="date_highlight" class="date_display">'.$rec->date.'</span>.';
				if (preg_match('/http/', $rec->link)) {
					$ret .= ' <a href="'.$rec->link.'">'.$rec->title.'</a>';
				} else {
					$ret .= ' '.$rec->title;
				}
				if (!preg_match('/[.?]$/', $rec->title)) {
					$ret .= '.';
				}
				if (isset($rec->publisher)) {
					$ret .= ' '.$rec->publisher.'.';
				}
				if (isset($rec->link)) {
					$ret .= ' (<a href="'.$rec->link.'">'.$rec->link.'</a>).';
				}
				if (isset($rec->date)) {
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
				$ret .= ' <span class="date_highlight" class="date_display">'.$rec->date.'</span>.';
				if (preg_match('/http/', $rec->link)) {
					$ret .= ' <a href="'.$rec->link.'">'.$rec->title.'</a>';
				} else {
					$ret .= ' '.$rec->title;
				}
				if (!preg_match('/[.?]$/', $rec->title)) {
					$ret .= '.';
				}
				$ret .= ' <span class="journal_bold" class="journal_display"><em>'.$rec->journal.'</em></span>.';
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
				if (isset($rec->publisher_place)) {
					$ret .= ' '.$rec->publisher_place.':';
				}
				if (isset($rec->publisher)) {
					$ret .= ' '.$rec->publisher;
				}
				$ret .= ' <span class="date_highlight" class="date_display">'.$rec->date.'</span>.';
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
				if (isset($rec->publisher_place)) {
					$ret .= ' '.$rec->publisher_place.':';
				}
				if (isset($rec->publisher)) {
					$ret .= ' '.$rec->publisher;
				}
				$ret .= ' <span class="date_highlight" class="date_display">'.$rec->date.'</span>.';
			$ret .= '</div>';

		// ------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------
		} elseif ($rec->pubtype == 'report') {
			$ret .= '<div class="result fa-list-alt" title="Report">';
				$ret .= $rec->author_display;
				if (!preg_match('/\.$/', $rec->author_display)) {
					$ret .= '.';
				}
				$ret .= ' <span class="date_highlight" class="date_display">'.$rec->date.'</span>.';
				$ret .= '"'.$rec->title.'".';
				$ret .= ' <em>'.$rec->journal.'</em>.';
				if (isset($ret->editor_display)) {
					$ret .= ' ed.'.$rec->editor_display.'.';
				}
				if (isset($ret->volume)) {
					$ret .= ' '.$rec->volume.'.';
				}
				if (isset($rec->publisher_place)) {
					$ret .= ' '.$rec->publisher_place.':';
				}
				if (isset($rec->publisher)) {
					$ret .= ' '.$rec->publisher.',';
				}
				if (isset($rec->pages)) {
					$ret .= ' '.$rec->pages.'.';
				}
				if (isset($rec->link)) {
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
				if (isset($rec->issue)) {
					$ret .= ' '.$rec->issue.',';
				}
				$ret .= ' <span class="date_highlight" class="date_display">'.$rec->date.'</span>.';
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
				if (isset($rec->acknowledgement)) {
					$ret .= ' '.$rec->acknowledgement.':';
				}
				if (isset($rec->publisher_place)) {
					$ret .= ' '.$rec->publisher_place.':';
				}
				if (isset($rec->publisher)) {
					$ret .= ' '.$rec->publisher.'.';
				}
				$ret .= ' <span class="date_highlight" class="date_display">'.$rec->date.'</span>.';
			$ret .= '</div>';

		// ------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------
		} elseif ($rec->pubtype == 'monograph') {
			$ret .= '<div class="result fa-book" title="Monograph">';

				if (isset($rec->author_display)) {
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
				if (isset($rec->issue)) {
					$ret .= ' '.$rec->issue.'.';
				}
				if (isset($rec->publisher_place)) {
					$ret .= ' '.$rec->publisher_place.':';
				}
				if (isset($rec->publisher)) {
					$ret .= ' '.$rec->publisher.'.';
				}
				$ret .= ' <span class="date_highlight" class="date_display">'.$rec->date.'</span>,';
				if (isset($rec->book_title)) {
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
				if (isset($rec->book_title)) {
					$ret .= ' '.$rec->book_title.'.';
				}
				if (isset($rec->volume)) {
					$ret .= ' '.$rec->volume.',';
				}
				if (isset($rec->issue)) {
					$ret .= ' '.$rec->issue.'.';
				}
				if (isset($rec->publisher_place)) {
					$ret .= ' '.$rec->publisher_place.':';
				}
				if (isset($rec->publisher)) {
					$ret .= ' '.$rec->publisher.',';
				}
				$ret .= ' <span class="date_highlight" class="date_display">'.$rec->date.'</span>.';
			$ret .= '</div>';

		// ------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------
		} elseif ($rec->pubtype == 'artwork') {
			$ret .= '<div class="result fa-image" title="Artwork">';
				$ret .= $rec->author_display;
				if (!preg_match('/\.$/', $rec->author_display)) {
					$ret .= '.';
				}
				$ret .= ' <span class="date_highlight" class="date_display">'.$rec->date.'</span>.';
			$ret .= '</div>';

		// ------------------------------------------------------------------------------
		// ------------------------------------------------------------------------------
		} elseif ($rec->pubtype == 'abstract') {
			$ret .= '<div class="result fa-align-justify" title="Abstract">';
				$ret .= $rec->author_display;
				$ret .= ' <span class="date_highlight" class="date_display">'.$rec->date.'</span>.';
				$ret .= '[Abstract:] "'.$rec->title.'".';
				if (isset($rec->book_title)) {
					$ret .= ' <em>'.$rec->book_title.'</em>,';
				}
				if (isset($rec->book_title)) {
					$ret .= ' :'.$rec->pages.'.';
				}
				if (isset($rec->link)) {
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
				$ret .= ' <span class="date_highlight" class="date_display">'.$rec->date.'</span>.';
				$ret .= ' "'.$rec->title.'".';
				if (isset($rec->journal)) {
					$ret = ' <em>'.$rec->journal.'</em>.';
				} elseif (isset($rec->book_title)) {
					$ret = ' '.$rec->book_title.'.';
				}
				if (isset($rec->link)) {
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
				$ret .= ' <span class="date_highlight" class="date_display">'.$rec->date.'</span>.';
				$ret .= '[Presentation:] "'.$rec->title.'".';
				if (isset($rec->journal)) {
					$ret = ' <em>'.$rec->journal.'</em>.';
				} elseif (isset($rec->book_title)) {
					$ret = ' '.$rec->book_title.'.';
				}
				if (isset($rec->link)) {
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
				$ret .= ' <span class="date_highlight" class="date_display">'.$rec->date.'</span>.';
				if (preg_match('/http/', $rec->link)) {
					$ret .= '<a href="'.$rec->link.'">'.$rec->title.'</a>.';
				} else {
					$ret .= $rec->title.'.';
				}
				if (!preg_match('/[.?]$/', $rec->title)) {
					$ret .= '.';
				}
				if (isset($rec->publisher_place)) {
					$ret .= ' '.$rec->publisher_place.':';
				}
				if (isset($rec->publisher)) {
					$ret .= ' '.$rec->publisher.'.';
				}
				if (isset($rec->doi)) {
					$ret .= ' <a href="http://dx.doi.org/'.$rec->doi.'">doi:'.$rec->doi.'</a>';
				}
			$ret .= '</div>';

//    ALTHOUGH THIS IS REFERENCE TYPE: JOURNAL ARTICLE, THIS SEGMENT HOPEFULLY IDENTIFIES IT AS A BOOK REVIEW.
// 		REMOVED PER DISCUSSION W/SCP 2016-11-8
//    Dashes added by JMR 2017/08/03
// 		 } elseif ((rt eq 'Journal Article') and (k1 contains 'Book review')) {
// 			#-a1-#
// 				if (REfind("\.$","#-a1-#")) {  } else { . }<span class="date_highlight" class="date_display">#-yr-#</span>.
// 				if (REfind ("[R|r]eview","#-t1-#")) { } else { [Review]:}
// 				if (ul contains 'http') {<a href="#ul#">#-t1-#</a> } else { #-t1-#}.
//        <span class="journal_bold" class="journal_display"><i>#jf#</i></span>,
// 				if (is_no ge '0' and vo ge '0') {#vo#(#is_no#) } elseif (vo eq ' ' and is_no ge '0') { #is_no# } elseif (vo ge '0' and is_no eq ' ') { #vo#}: #sp#
// 				if (op ge '0' and op neq sp) {-#op#. } else { }
// 				if (doi contains '10.') {<a href="http://dx.doi.org/#doi#"> doi:#doi#</a>}<br />
// 			$ret .= '</div>';


		}
		return $ret;
	}
}

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

function sro_insert_search_results_page_new_blog($blog_id, $user_id, $domain, $path, $site_id, $meta) {
	//replace with your base plugin path E.g. dirname/filename.php
	if ( is_plugin_active_for_network( 'silibraries-sro/silibraries-sro.php' ) ) {
		switch_to_blog($blog_id);
		_sro_insert_page();
		restore_current_blog();
	} 
}

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

function _sro_delete_page() {
	$post = get_page_by_path('publications', OBJECT, 'page');
	wp_delete_post($post->ID);	
}

function _sro_get_departments() {
	return array(
		array('id' => '690000', name => 'Anacostia Community Museum'), 
		array('id' => '480000', name => 'Archives of American Art'), 
		array('id' => '710000', name => 'Asian Pacific American Center'), 
		array('id' => '510000', name => 'Center for Folklife and Cultural Heritage'), 
		array('id' => '580000', name => 'Cooper-Hewitt National Design Museum'), 
		array('id' => '540000', name => 'Freer-Sackler Galleries'), 
		array('id' => '560000', name => 'Hirshhorn Museum and Sculpture Garden'), 
		array('id' => '640000', name => 'Museum Conservation Institute'), 
		array('id' => '380000', name => 'National Air and Space Museum'), 
		array('id' => '382010', name => 'NASM-Aeronautics'), 
		array('id' => '382020', name => 'NASM-Space History'), 
		array('id' => '382050', name => 'NASM-CEPS'), 
		array('id' => '680000', name => 'National Museum of African American History and Culture'), 
		array('id' => '570000', name => 'National Museum of African Art'), 
		array('id' => '550000', name => 'National Museum of American History'), 
		array('id' => '330000', name => 'NMNH'), 
		array('id' => '331040', name => 'NMNH Encyclopedia of Life'), 
		array('id' => '332010', name => 'NH-Mineral Science'), 
		array('id' => '332020', name => 'NH-Anthropology'), 
		array('id' => '332031', name => 'NH-Invertebrate Zoology'), 
		array('id' => '332032', name => 'NH-Vertebrate Zoology'), 
		array('id' => '332033', name => 'NH-Botany'), 
		array('id' => '332034', name => 'NH-Entomology'), 
		array('id' => '332040', name => 'NH-Paleobiology'), 
		array('id' => '332050', name => 'NH-Smithsonian Marine Station'), 
		array('id' => '500000', name => 'National Museum of the American Indian'), 
		array('id' => '520000', name => 'National Portrait Gallery'), 
		array('id' => '301000', name => 'National Postal Museum'), 
		array('id' => '350000', name => 'National Zoological Park'), 
		array('id' => '770000', name => 'Office of Policy and Analysis'), 
		array('id' => '250001', name => 'Office of the Under Secretary for History, Art &amp; Culture'), 
		array('id' => '110000', name => 'Secretary\'s Cabinet'), 
		array('id' => '100000', name => 'SI-Other'), 
		array('id' => '530000', name => 'Smithsonian American Art Museum'), 
		array('id' => '404000', name => 'Smithsonian Astrophysical Observatory'), 
		array('id' => '390000', name => 'Smithsonian Environmental Research Center'), 
		array('id' => '733400', name => 'Smithsonian Gardens'), 
		array('id' => '170000', name => 'Smithsonian Institution Archives'), 
		array('id' => '360000', name => 'Smithsonian Latino Center'), 
		array('id' => '630000', name => 'Smithsonian Institution Libraries'), 
		array('id' => '340000', name => 'Smithsonian Tropical Research Institute'), 
		array('id' => '250000', name => 'Arts and Humanities'), 
		array('id' => '590000', name => 'Science'), 
		array('id' => '941000', name => 'Smithsonian Institution Scholarly Press'), 
		array('id' => '960000', name => 'DUSCIS')
	);
}

wp_register_style('silibraries-sro', plugins_url('/css/style.css', __FILE__));
wp_enqueue_style('silibraries-sro');

// Register our widget with an anonymous function.
add_action( 'widgets_init', function() { register_widget('SROSearchWidget');});
add_action( 'widgets_init', function() { register_widget('SROFixedSearchWidget');});

// Create our object and make magic happen.
$wpSROSearch = new SROSearch();
register_activation_hook( __FILE__, 'sro_insert_search_results_page' );
register_deactivation_hook( __FILE__, 'sro_remove_search_results_page' );
add_action('wpmu_new_blog', 'sro_insert_search_results_page_new_blog', 10, 6 );
