<?php
/*
Plugin Name: Link2Post
Plugin URI: http://www.strangerstudios.com/link2post/
Description: Automatically parse submitted URLs to create posts.
Version: .1
Author: strangerstudios, dlparker1005, andrewza
Author URI: http://www.strangerstudios.com
Text Domain: link2post
*/

define('L2P_VERSION', '.1');

/*
	Load modules
*/
define('L2P_DIR', dirname(__FILE__));
require_once(L2P_DIR . '/modules/youtube.php');
require_once(L2P_DIR . '/modules/gist.php');
require_once(L2P_DIR . '/modules/codepen.php');
require_once(L2P_DIR . '/modules/jsfiddle.php');

$modules = l2p_get_modules();


foreach($modules as $key => $value){
/*
	$key is name of the file(ie. 'gist.php')
	$value is associative array containing info on module
*/
	if(get_option("l2p_".$value['quick_name']."_cpt_enabled")=="enabled"){
		add_action('init',$value['create_cpt']);
	}
}

function myplugin_activate() {
	$modules = l2p_get_modules();
	foreach($modules as $key => $value){
		if(!get_option("l2p_".$value['quick_name']."_content_enabled")){
			update_option("l2p_".$value['quick_name']."_content_enabled", 'enabled');
			call_user_func($value['create_cpt']);
		}
		elseif(get_option("l2p_".$value['quick_name']."_content_enabled")=='enabled'){
			call_user_func($value['create_cpt']);
		}
		if(!get_option("l2p_".$value['quick_name']."_cpt_enabled")){
			update_option("l2p_".$value['quick_name']."_cpt_enabled", 'disabled');
		}
	}
	flush_rewrite_rules(true);
}
register_activation_hook( __FILE__, 'myplugin_activate' );

function l2p_get_modules(){
	/**
	 * Filter to add Link2Post modules. Modules are used to handle parsing
	 * for URLs from specific sites.
	 *
	 * @since .1
	 *
	 * @param array $modules Array of modules. Each element in array should be [host=>callback_function].	 
	 */
	$modules = apply_filters('l2p_modules', array());
	return $modules;
}

function l2p_enqueue_scripts(){
	if(current_user_can('administrator') ) {
		wp_enqueue_script("l2p_vue", 'https://unpkg.com/vue@2.0.3/dist/vue.js', NULL, NULL);		
		wp_enqueue_script("l2p_js_tools", plugins_url('link2post/js/link2post.js', L2P_DIR), array("jquery", "l2p_vue"), L2P_VERSION);
		wp_localize_script( "l2p_js_tools", "ajax_target",  admin_url( 'admin-ajax.php' ));
	}
}
add_action( 'wp_enqueue_scripts', 'l2p_enqueue_scripts');
add_action( 'admin_enqueue_scripts', 'l2p_enqueue_scripts' );

/*
	Add Admin Page
*/
function l2p_admin_pages() {
	add_submenu_page( 'tools.php', 'Link2Post', 'Link2Post', 'edit_posts', 'link2post_tools', 'l2p_admin_tool_pages_main' );
	add_submenu_page( 'options-general.php', 'Link2Post', 'Link2Post', 'edit_posts', 'link2post_settings', 'l2p_admin_settings_pages_main' );
}
add_action('admin_menu', 'l2p_admin_pages');

function l2p_admin_tool_pages_main() {
	require_once(dirname(__FILE__) . '/adminpages/link2post_tools.php');
}

function l2p_admin_settings_pages_main() {
	require_once(dirname(__FILE__) . '/adminpages/link2post_settings.php');
}

function l2p_admin_bar_menu() {
	global $wp_admin_bar;
	if(!current_user_can('edit_posts'))
		return;
	
	$wp_admin_bar->add_menu( array(
		'id' => 'link2post',
		'parent' => 'new-content',
		'title' => __( 'Link2Post', 'link2post' ),
		'href' => get_admin_url(NULL, '/tools.php?page=link2post_tools') ) );
	if(!l2p_on_tools_page()){
		$wp_admin_bar->add_menu( array(
			'id' => 'l2p_input',
			'title' => '
			<div id="l2p_vue" style="height:30px; ">
				<label id="l2p_showAdminbar" for="l2p_showAdminbar" v-show="!l2p_showAdminbar" style="display:none">Show L2P:</label>
				<input id="l2p_checkbox" type=checkbox name="l2p_showAdminbar" v-show="true" v-model="l2p_showAdminbar" style="display:none">
				<div v-show="l2p_showAdminbar" id="vue_holder" style="display:none">
					<label id="l2p_url_label" for="l2purl" v-show="l2p_status==0"> URL:</label>
					<input id="l2p_url_text" name="l2purl" type=text v-show="l2p_status==0" v-model="l2p_url" style="height:20px"/>
					<span v-html="l2p_span_text" id=l2p_span></span>
					<input type=button value="submit" v-show="l2p_status==0" v-on:click="l2p_submit" style="height:30px"/>
					<input type=button value="update" v-show="l2p_status==1" v-on:click="l2p_update" style="height:30px"/>
					<input type=button value="don\'t update" v-show="l2p_status==1" v-on:click="l2p_reset" style="height:30px"/>
					<input type=button value="convert another" v-show="l2p_status==3" v-on:click="l2p_reset" style="height:30px"/>
					<input type=hidden value="false" id=l2p_on_tools_page />
				</div>
			</div>
			<style>
				#l2p_checkbox{
					height:16px;
					width:16px;
				}
				#l2p_span>a{
					color:#96e2ff;
				}
				#vue_holder, #l2p_url_text {
					display:inline-block;
				}
				#l2p_showAdminbar, #l2p_url_label {
					display:inline-block;
					color: #ffffff;
				}
			</style>
			'
		) );

	}
}
add_action('admin_bar_menu', 'l2p_admin_bar_menu', 1000);

function l2p_on_tools_page(){
	$on_tools_page = false;
	if(function_exists ( "get_current_screen" )){
		if(get_current_screen()->base ==  "tools_page_link2post_tools"){
			$on_tools_page = true;
		}
	} 
	return $on_tools_page;
}

function l2p_submit() {
	global $current_user, $wpdb;
	$url = $_POST["l2p_url"];
	
	//no URL, bail
	if(empty($url))
		exit;
	$objToReturn = new stdClass();
	$objToReturn->on_tools_page = l2p_on_tools_page();
	
	//check if we've already processed this URL
	$sqlQuery = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'l2p_url' AND meta_value = '" . esc_sql($url) . "' LIMIT 1";
	$old_post_id = $wpdb->get_var($sqlQuery);
	if(empty((int)$old_post_id) || get_post_status((int)$old_post_id)!='publish'){
		$objToReturn->new_post_created = true;
		$objToReturn->new_post_url = l2p_update($url, NULL, true);
		$JSONtoReturn = json_encode($objToReturn);
		echo $JSONtoReturn;
		exit;
	}	
	//echo("is old post");
	$objToReturn->new_post_created = false;
	$objToReturn->old_post_id = $old_post_id;
	$objToReturn->old_post_url = get_permalink($old_post_id);

	$modules = l2p_get_modules();
	
	//check the domain of the URL to see if it matches a module
	$host = parse_url($url, PHP_URL_HOST);
	$found_match = false;
	foreach($modules as $key => $value) {
    	if($host == $value['host'] && get_option("l2p_".$value['quick_name']."_content_enabled")=="enabled"){
    		$found_match = true;
    		//we found one, use the module's parse function now
    		if(empty($value['callback']) || empty($value['can_update']) || $value['can_update']==false){
    			//echo __("Broken callback function.", 'link2post');
    			$objToReturn->can_update = false;
    		}
			else{
				$objToReturn->can_update = true;
			}
    	}
	}
	if($found_match==false){
		$objToReturn->can_update = true;
	}
	$JSONtoReturn = json_encode($objToReturn);
	echo $JSONtoReturn;
	exit;
}
add_action( 'wp_ajax_l2p_submit', 'l2p_submit' );


function l2p_update($url='', $old_post_id=NULL, $return_result=false){
	global $current_user, $wpdb;
	if(empty($url)){
		if(isset($_POST["l2p_url"]))
			$url = $_POST["l2p_url"];
	}
	if($old_post_id==NULL){
		if(isset($_POST["l2p_old_post_id"]))
			$old_post_id = $_POST["l2p_old_post_id"];
	}
	
	if(empty($url))
		return false;
		
	$modules = l2p_get_modules();
		
	//check the domain of the URL to see if it matches a module
	$host = parse_url($url, PHP_URL_HOST);
	foreach($modules as $key => $value) {
    	if(strpos($host, $value['host']) !== false && get_option("l2p_".$value['quick_name']."_content_enabled")=="enabled"){
    		//we found one, use the module's parse function now
    		if(empty($value['callback'])){
				//can't 
    			exit;
    		}
    		elseif($return_result){
				return call_user_func($value['callback'], $url, NULL, $return_result);
    		}
    		elseif(!empty($value['can_update']) && $value['can_update']==true){
				call_user_func($value['callback'], $url, $old_post_id, $return_result);
				exit;
			}
			else{
				exit;
			}
    	}
	}
		
	require_once(dirname(__FILE__).'/lib/selector.php');	
	try{
		$html = wp_remote_retrieve_body(wp_remote_get($url));
		
		//scrape the title
		$title = l2p_SelectorDOM::select_element('title', $html);
		if(!empty($title) && !empty($title['text']))
			$title = sanitize_text_field($title['text']);
		else{
			$title = "No title";
		}
		
		//scrape the description
		$description = l2p_SelectorDOM::select_element('meta[name=description]', $html);
		if(!empty($description) && !empty($description['attributes']) && !empty($description['attributes']['content']))
			$description = sanitize_text_field($description['attributes']['content']);
		else{
			$description = "";
		}
		
		//add link back to the URL to the description:
		$description .= "\n\n" . sprintf(__('Originally posted at %s.', 'link2post'), '<a href="' . esc_url($url) . '">' . $host . '</a>');
		
		if(empty($old_post_id)){
			//create a link post and insert it
			$postarr = array(
				'post_type' => 'post',
				'post_title' => $title,
				'post_content' => $description,
				'post_author' => $current_user->ID,
				'post_status' => 'publish',
				'meta_input' => array(
					'l2p_url' => $url,
				)
			);
		
			$post_id = wp_insert_post($postarr);
			$post_url = get_permalink($post_id);
			if($return_result==true){
				return $post_url;
			}
			$objToReturn->url = $post_url;
			$JSONtoReturn = json_encode($objToReturn);
			echo $JSONtoReturn;
			exit;
		}	
		else{
			//update existing post
			$postarr = array(
				'ID' => $old_post_id,
				'post_type' => 'post',
				'post_title' => $title,
				'post_content' => $description,
				'post_author' => $current_user->ID
			);
			wp_update_post($postarr);
			$post_url = get_permalink($old_post_id);
			$objToReturn->url = $post_url;
			$JSONtoReturn = json_encode($objToReturn);
			echo $JSONtoReturn;
			exit;
		}		
	}catch (Exception $e) {}
}		
add_action( 'wp_ajax_l2p_update', 'l2p_update' );
?>