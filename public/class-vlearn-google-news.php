<?php
/**
 * Vlearn Google News.
 *
 * @package   Vlearn Google News
 * @author    Your Name <graeme@lostgrad.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2014 Your Name or Company Name
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `class-plugin-name-admin.php`
 *
 * @TODO: Rename this class to a proper name for your plugin.
 *
 * @package Vlearn_Google_News
 * @author  Your Name <email@example.com>
 */
class Vlearn_Google_News {


	protected $limit = "5";
	protected $region = "uk";
	protected $query = "";
	protected $topic = "b";
	protected $images = "on";
	protected $length = "200";
	protected $sort = "r";
	protected $layout = "horizontal";
	
	
	
	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	public function __construct() {

		//add_action( '@TODO', array( $this, 'action_method_name' ) );
		add_shortcode( 'show_google_news', array($this,'vlearn_init_google_news') );

	}



	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}


/**
	 * NOTE:  Actions are points in the execution of a page or process
	 *        lifecycle that WordPress fires.
	 *
	 *        Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */
	public function vlearn_init_google_news($params) {
                          
            if(array_key_exists('layout', $params)){
                $this->layout=$params['layout'];
            }
            if(array_key_exists('limit', $params)){
                $this->limit=$params['limit'];
            }
                
                
		//process the incoming values and assign defaults if they are undefined
		$params=shortcode_atts(array(
				"region" => $this->region,
				"query" => $this->query,
				"topic" => $this->topic,
				"images" => $this->images,
				"length" => $this->length,
				"sort" => $this->sort		
		), $params);

              
		return $output = $this->build_query_string($params);
	}

	/*
	 * Encapsulated functions
	 */

	/**
	 * build_query_string
	 * 
	 * @param params: the parameters sent in by the user
	 * @return $output: a valid query string that will be used to receive the RSS Feed
	 */

	private function build_query_string($params){
		//remove any single quotes from the query param, as they may affect the query
		$params['query'] = str_replace("'", "", $params['query']);

		//replace spaces with +'s
		$params['query'] = str_replace(" ", "+", $params['query']);

		$query_string = 'http://news.google.com/news?q='.$params['query']
											//.'&topic='.$params['topic'] 
											.'&ned='.$params['region']
											.'&scoring='.$params['sort']
											.'&oq='.$params['query'];
                
               // echo '<h4>'.$query_string.'</h4>';
		//call the build_feed function to parse the feed and return the results to us
		$output = $this->generate_rss_output($params, $query_string);
                
             return  $this->print_table($output);

	}

	/**
	 * generate_rss_output
	 *
	 * @param $params: the parameters sent in by the user
	 * @param $query_string: the string to be processed by google
	 * 
	 * @return $output: a html table summarizing the feeds
	 */
	 private function generate_rss_output($params, $query_string){

	 	$feed = simplexml_load_file($query_string . '&output=rss');

	 	//output array to hold each table row
	 	$output=array();

	 	foreach($feed->channel->item as $feed){
		//var_dump($feed);
	 	$title= $feed->title;
	 	$link=$feed->link;
	 	//get the image using google's description table
	 	$desc= $feed->description;
	 	$image= $this->get_image_from_rss_description($desc);

	 	//now produce the output, and return as $output
	 	ob_start();
	 	include('views/google_news_layout/'.$this->layout.'_layout.php');
	 	$output[] = ob_get_contents();
	 	ob_end_clean();
	 	}
	 	return $output;
}

	/**
	 * get_image_from_rss_description
	 *
	 * @param $rss_desc - Google's Description table in it's RSS feed
	 *
	 * @return $image
	 */
	private function get_image_from_rss_description($rss_desc){

		$dom = new DOMDocument();
		//disable warnings - as it gives warnings due to google's custom tags
		libxml_use_internal_errors(true);
		$dom->loadHTML( $rss_desc );
		//clear the warnings
		libxml_clear_errors();
		//find the images
		$xml = simplexml_import_dom($dom);
		$images = $xml -> xpath('//img/@src');

		//if there is an image, return it 		
		if($images){
			if($images[0]){
				return '<img src="'.$images[0].'"/>';
			}
		}
                else return '<img src="'.plugin_dir_url(__FILE__).'/assets/google_news_icon_2.jpg"/>';


	}
        
        /**
	 * print_table
	 *
	 * @param $output - the output data generated from templates
	 *
	 * @return $output_string
	 */
        private function print_table($output){
              
                //limit posts to our post limit
                $count=0;
                $output_string='';
                $output_string.= '<div class="vlearn-google-news vlearn-google-news-'.$this->layout.'">';
                $output_string.= '<h2>Related News</h2>';
                //determine layout of table
                $output_string.='<table class="vlearn-table vlearn-table-'.$this->layout.'">';

                //print the internal table data
		foreach($output as $out){
                    
                    if(++$count<$this->limit){	
                        $output_string.= $out;
                    }
			}
                $output_string.='</table></div>';
                return $output_string;
                
        }


}
