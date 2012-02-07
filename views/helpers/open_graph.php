<?php
/**
 * OpenGraph CakePHP Helper class with Facebook methods.
 * Works with CakePHP 1.3, possibly 1.2
 * @author Matthew Dunham
 *
 *		<meta property="og:site_name" content="Storbital" />
		<meta property="og:title" content="<?=$product['Product']['name']?>"/>
	    <meta property="og:type" content="product"/>
	    <meta property="og:url" content="http://buyersguide.umpcmedia.com<?=$html->url();?>"/>
	    <meta property="og:image" content="http://buyersguide.umpcmedia.com/<?=$product['Product']['image_dir'].'/'.$product['Product']['image'];?>"/>
		<meta property="fb:app_id" content="180948576449" />
		<meta property="fb:admins" content="505549054"/>
 */

class OpenGraphHelper extends AppHelper {
	
	public $helpers = array('Html');
	
	//Array of facebook admin IDs
	private $_openGraphProperties = array(
		'og:site_name' => 'Storbital',
		'fb:app_id' => '180948576449',
		'fb:admins' => '505549054'
	);
	
	/**
	 * Outputs basic meta data.
	 */
	public function meta($propeties = array()) {
		
		$metaTags = array();
		foreach($properties as $property => $content) {
			$metaTags[] = $this->metaTag($property, $content);
		}
		return implode("\r\n", $metaTags);
	}
	
	/*
	 * Returns a meta tag.
	 */
	public function metaTag($property, $content) {
		return '<meta property="'.$property.'" content="'.$content.'" />';
	}
	
	/*
	 * Returns HTML Tag with OpenGraph and optionally the facebook schemas attached.
	 * eg <html xmlns:og="http://opengraphprotocol.org/schema/"
     		 xmlns:fb="http://www.facebook.com/2008/fbml">

	 */
	public function html($facebook = true) {
		if($facebook) {
			$facebook = ' xmlns:fb="http://www.facebook.com/2008/fbml"';
		}
		return '<html xmlns="http://www.w3.org/1999/xhtml" xmlns:og="http://opengraphprotocol.org/schema/"'.$facebook.'>'."\n";
	}
}