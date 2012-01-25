<?php
/*
Plugin Name: Plugins
Plugin Script: plugins.php
Plugin URI: http://marto.lazarov.org/plugins/plugins
Description: List wordpress contributor plugins and their stats
Version: 2.0.1
Author: mlazarov
Author URI: http://marto.lazarov.org/
*/

if (class_exists('WP_Widget')) {
	class Plugins_Widget extends WP_Widget {
		var $settings;
		function Plugins_Widget(){
			$widget_ops = array(
							'classname' => 'widget_plugins',
							'description' => 'List wordpress contributor plugins and their stats' );
			$this->WP_Widget('plugins', 'Plugins', $widget_ops);
			$this->settings = $this->get_settings();
			//var_dump($this);exit;
			if(!$this->settings['updated'] || $this->settings['updated']<time()-3600) $this->getFreshData();
		}

		function update($new_instance, $old_instance) {
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['author'] = strip_tags($new_instance['author']);
			$this->settings['author'] = strip_tags($new_instance['author']);
			$this->save_settings($this->settings);
			return $instance;
		}
		function form($instance) {
			$plugin = get_plugin_data( __FILE__ );
			$instance = wp_parse_args( (array) $instance, array( 'title' => 'Plugins', 'author' => 'mlazarov', 'width' => '300', 'height' => '400' ) );
			$title = strip_tags($instance['title']);
			$author = strip_tags($instance['author']);
			?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">
				Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" />
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('author'); ?>">
				Author username: <input class="widefat" id="<?php echo $this->get_field_id('author'); ?>" name="<?php echo $this->get_field_name('author'); ?>" type="text" value="<?php echo attribute_escape($author); ?>" /></label></p>
			<?php
		}

		function widget($args, $instance) {

			echo "\n<!--\nSTART `Plugins` Widget\nhttp://wordpress.org/extend/plugins/plugins/ \n//-->\n";


			$title = empty($instance['title']) ? '&nbsp;' : apply_filters('widget_title', $instance['title']);
			$plugins = (array)$instance['plugins'];
			echo $args['before_widget'];
			if ( !empty( $title ) ) { echo $args['before_title'] . $title . $args['after_title']; };
			echo '<table border="0">';
			foreach($plugins as $plugin_slug=>$plugin){
				echo '<tr><td style="text-align:right">'.$plugin['downloads'].'&nbsp;</td><td style="padding-left:15px;"><a href="http://wordpress.org/extend/plugins/'.$plugin_slug.'/" target="_blank">'.$plugin['name']."</a></td></tr>\n";
			}
			echo '</table>';

			echo $args['after_widget'];
		}
		function getFreshData(){
			if(function_exists('get_plugin_data'))
				$plugin = get_plugin_data( __FILE__ );
			else
				$plugin['Version'] = 'unk';
			foreach($this->settings as $id=>$settings){
				if(!$settings['author']){
					continue;
				}
				$url = 'http://profiles.wordpress.org/users/'.$settings['author'].'/profile/public/';
				$html = file_get_contents($url);

				preg_match_all('#<h3><a href="http://wordpress.org/extend/plugins/([^/]+)/">([^<]+)</a></h3>\s+<p class="downloads">([0-9,]+) downloads</p>#ismU',$html,$matches,PREG_SET_ORDER);
				$this->settings[$id] = array();
				$this->settings[$id]['plugins'] = array();
				foreach($matches as $k=>$m){
						$this->settings[$id]['plugins'][$m[1]]['name'] = $m[2];
						$this->settings[$id]['plugins'][$m[1]]['downloads'] = $m[3];
				}
				usort($this->settings[$id]['plugins'],'dlsort');
			}
			$this->settings['updated'] = time();
			$this->save_settings($this->settings);
		}
	}

	function Plugins_Widget_Init() {
		register_widget('Plugins_Widget');
	}

	add_action('widgets_init', 'Plugins_Widget_Init');

}
function dlsort($a,$b){
	$dla = str_replace(',','',$a['downloads']);
	$dlb = str_replace(',','',$b['downloads']);
	if ($dla == $dlb) return 0;
	// SORT ASC
	//return ($dla < $dlb) ? -1 : 1;
	// SORT DESC
	return ($dla < $dlb) ? 1 : -1;
}
?>
