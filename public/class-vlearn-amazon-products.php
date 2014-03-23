<?php
/**
 * Vlearn Amazon Products.
 *
 * @package   Vlearn Amazon Products
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
class Vlearn_Amazon_Products {


	protected $api= "YOUR_API";
	protected $key = "YOUR_KEY";
	protected $query = "ethical business";//default category
	protected $layout='horizontal';
	
	
	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	public function __construct() {

		//add_action( '@TODO', array( $this, 'action_method_name' ) );
		add_shortcode( 'show_amazon_products', array($this,'vlearn_init_amazon_products') );

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
	public function vlearn_init_amazon_products($params) {
		//process the incoming values and assign defaults if they are undefined
		if(array_key_exists('layout', $params)){
			$this->layout=$params['layout'];
		}
	//process the incoming values and assign defaults if they are undefined
		$params=shortcode_atts(array(
				"query" => $this->query,
		), $params);
				
		return $output = $this->build_query_string($params);
	}

	/*
	 * Encapsulated functions
	 */
	private function build_query_string($params){
		
		require_once 'includes/amazon-ecs-lib/AmazonECS.class.php';
		$amazonEcs = new AmazonECS($this->api, $this->key, 'co.uk', 'microbacon-21');
		
		//tell the amazon class that we want an array, not an object
		$amazonEcs->setReturnType(AmazonECS::RETURN_TYPE_ARRAY);
		
    $response = $amazonEcs->category('Books')->responseGroup('Large')->search($params['query']);
    
    return $this->print_results($response);
		
	}
	
	
	private function print_results($response){
				
		//check that there are items in the response
		if (isset($response['Items']['Item']) ) {
			$output='<div class="vlearn-amazon vlearn-amazon-'.$this->layout.'">';
			$output.='<h2>Related Books</h2><hr>';
			$x=0;
			//loop through each item
			foreach ($response['Items']['Item'] as $result) {
				if($x==4)break;
				//check that there is a ASIN code - for some reason, some items are not
				//correctly listed. Im sure there is a reason for it, need to check.
				if (isset($result['ASIN'])) {
		
					//store the ASIN code in case we need it
					$asin = $result['ASIN'];
		
					//check that there is a URL. If not - no need to bother showing
					//this one as we only want linkable items
					if (isset($result['DetailPageURL'])) {
		
						//set up a container for the details - this could be a DIV
						$output.="<div class='amazon-prod amazon-prod-".$this->layout."'>";
		
						//create the URL link
						$output.="<a target='_Blank' href='" . $result['DetailPageURL'] ."'>";
		
						//if there is a small image - show it
						if (isset($result['LargeImage']['URL'] )) {
							$output.="<img class='amazon-img amazon-img-$this->layout' src='". $result['LargeImage']['URL'] ."'>";
						}
		
						// if there is a title - show it
						if (isset($result['ItemAttributes']['Title'])) {
							$output.='<div class="vlearn-book-title">'.$result['ItemAttributes']['Title'] . "</div><br/>";
						}
		
						//close the paragraph
						$output.="</div></a>";
						$x++;
					}
				}
			}
		
		} else {
		
			//display that nothing was found - should no results be found
			$output.= "<p>No Amazon suggestions found</p>";
		
		}
		$output.="<div class='vlearn-clear vlearn-clear-$this->layout'></div>";
		$output.='</div>';
		return $output;
		
		
		
		
	}


}
