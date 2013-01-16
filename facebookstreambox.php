<?php
/**
 * 
* @copyright Copyright (C) 2012 Stephane Martin. All rights reserved.
* @license GNU/GPL
*
* Version 1.0

*/

defined( '_JEXEC' ) or die();

jimport( 'joomla.event.plugin' );

jimport( 'fb-sdk.src.facebook'); 


class plgContentfacebookstreambox extends JPlugin 
{
	public $facebook;
	public $fan_id;
	public $app_id;
	public $secret_key;

	function plgContentfacebookstreambox( &$subject, $params ) 
	{
		parent::__construct( $subject, $params );
		
		$pluginParams = $this->params;
	
		$this->fan_id=$pluginParams->get('fan_id','');
		$this->app_id=$pluginParams->get('app_id','');
		$this->secret_key=$pluginParams->get('secret_key','');
		
		$this->facebook = new Facebook(array(
		'appId'  => $this->app_id,
		'secret' => $this->secret_key,
		'cookie' => true, // enable optional cookie support
		));
 	}

	function onContentPrepare( $context, &$row, &$params, $limitstart=0 )
	{
		//global $mainframe;

		//$plugin	=& JPluginHelper::getPlugin('content', 'facebookstreambox');
		//$pluginParams = $this->params;
	
		/*$fan_id=$pluginParams->get('fan_id','');
		$app_id=$pluginParams->get('app_id','');
		$secret_key=$pluginParams->get('secret_key','');
	
		$uri =& JURI::getInstance();
		$curl = $uri->toString();
	
		$config =& JFactory::getConfig();
	
		$lang=&JFactory::getLanguage();
		$lang_tag=$lang->getTag();
		$lang_tag=str_replace("-","_",$lang_tag);
				
		$facebook = new Facebook(array(
		'appId'  => $app_id,
		'secret' => $secret_key,
		'cookie' => true, // enable optional cookie support
		));*/
			
		$apiResult = $this->facebook->api("/".$this->fan_id, "get");		
		$pageLink = $apiResult['link'];
			
		$url="<div class=\"my_facebook_link\"><a href=\"".$apiResult['link']."\" target=\"_blank\"><img style= \"float:left; margin-right:15px;\" src=\"https://graph.facebook.com/".$this->fan_id."/picture?height=80\"><div class=\"my_facebook_link_title\">Suivez-nous sur Facebook</div><div class=\"my_facebook_link_name\">".$apiResult['name']."</div></a></div>";
		
		$fql    =   "SELECT post_id, actor_id, target_id , created_time, message, likes, attachment FROM stream WHERE source_id = '".$this->fan_id."'  ORDER BY created_time DESC LIMIT 20";
		$param  =   array(

		 'method'    => 'fql.query',
		 'query'     => $fql,
		 'callback'  => ''

		);
		$fqlResult   =   $this->facebook->api($param);
		$url .="<div class=\"my_facebook_wall\">";
		$index=1;
		foreach( $fqlResult as $keys => $values ){		
			if ((strlen($values['message'])>0) and (sizeof($values['attachment'])>0) and ($index<=6)) {
			
					
					/*	$url .= $values['attachment']['href'];
					$url.=$values['attachment']['media'][0]['src'].'<br/>';
						foreach($values['attachment']['media'] as $i => $media)
					{

					$url.=$i.$media.'<br/>';


					}*/
					
					$photosrc = $values['attachment']['media'][0]['src'];
					$photosrc = substr($photosrc, 0, strlen($photosrc)-5)."n.jpg";
	
					$url .= '<div class="my_facebook_post"><div class="my_facebook_postwrapper"><a href="'.$pageLink.'" target="_blank"><div class="my_facebook_post_image" style="background:url('.$photosrc.') center center no-repeat;"></div><div class="my_facebook_post_text"><div class="my_facebook_post_textwrapper">'.substr($values['message'], 0, 999).'<br/>('.$values['likes']['count'].' personnes aiment)</div></div></a></div></div>';
					
					$index++;

			}			
		}
			
		$url .= "</div>";
	
		/*$url .="<script type=\"text/javascript\" src=\"http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php/fr_FR\"></script>
<script type=\"text/javascript\">FB.init(\"373297839412766\");</script>
<fb:fan profile_id=\"130255687016868\" stream=\"0\" connections=\"5\" width=\"300px\" height=\"120px\" header=\"0\" logobar=\"0\"   css=\"http://casanova.lagrangeweb.fr/templates/casanova/css/facebook_11.css\"></fb:fan>";*/

		$row->text = preg_replace('/{(fstreambox)\s*(.*?)}/i', $url, $row->text, 1);

	}
}
?>