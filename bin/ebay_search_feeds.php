<?php
/**
 * File to update ebay search results database for each product.
 * Run once an hour..?
 *
 * Uses Ebay API
 * DEVID: 	a2e6dc2f-2f46-47a9-8fbc-b95851abac0a
 * AppID: 	UMPCMedi-4981-4d8e-b4b9-40045544c05a
 * CertID:	d49c1c1b-5b04-4d71-bfdd-3f44c82fc772
 *
 * Site IDS:
 * 0 - US
 * 3 - UK
 *
 * Categories:
 * 177 -Computers & Networking:PC Laptops & Netbooks
 */
die;
define('CRONSCRIPT',true);
error_reporting(E_ALL);
$startTime = microtime(1);

//Settings
$siteIds = array('0', '3');
$apiUrl = 'http://open.api.ebay.com/shopping?';
$apiSettings = array (
	'appid' => 'UMPCMedi-4981-4d8e-b4b9-40045544c05a',
	'version' => 643,
	//'siteid' => 0,
	'callname' => 'FindItems',
	'responseEncoding' => 'XML',
	'MaxEntries' => 10,
	'HideDuplicateItems' => true
);

//Includes
require('lib/init.php');
require('lib/xml2array.php');
$total = 0;
//Start
$query = mysql_query("SELECT `id`, `url_slug` FROM `products`") or die(mysql_error());
while($product = mysql_fetch_assoc($query)) {
	$apiSettings['QueryKeywords'] = str_replace('_', ' ', $product['url_slug']);
	$itemsArray = array();
	foreach($siteIds as $siteId) {
		$apiSettings['siteid'] = $siteId;
		$httpQuery = $apiUrl.http_build_query($apiSettings);
		$xmlArray = xml2array(file_get_contents($httpQuery));
		$items = $xmlArray['FindItemsResponse']['Item'];
		if(isset($xmlArray['FindItemsResponse']['Errors']) || empty($items)) {//somethings gone wrong
			/*errorme('Ebay Search Results CRON Failed','The Ebay updater script has failed to get the feeds.'."\n".
				'The offending feed is: '.$apiUrl.http_build_query($apiSettings)."\n\n".
				'Error Message: '.$xmlArray['FindItemsResponse']['Errors']['LongMessage']."\n\n".
				'Items array empty:'.empty($items)
			
			);*/
		} else {
			foreach($items as $item) { //need to loop through to insert site ID :/
				$item['SiteId'] = $siteId;
				$itemsArray[] = $item;
			}
		}
	}
	
	$insertQueries = array();
	foreach($itemsArray as  $item) {
		$insertQueries[] = "
			INSERT INTO `ebay_search_results`
			VALUES(
				".mysql_real_escape_string($item['ItemID']).",
				".$product['id'].",
				".$item['PrimaryCategoryID'].",
				".$item['SiteId'].",
				'".mysql_real_escape_string($item['Title'])."',
				'".mysql_real_escape_string($item['ViewItemURLForNaturalSearch'])."',
				'".mysql_real_escape_string($item['ConvertedCurrentPrice'])."',
				'".mysql_real_escape_string($item['ConvertedCurrentPrice_attr']['currencyID'])."',
				'".mysql_real_escape_string(@$item['ShippingCostSummary']['ShippingServiceCost'])."',
				'".mysql_real_escape_string(@$item['GalleryURL'])."'
			)";
	}
	//Delete Old and Add New.
	if(empty($insertQueries)) {
		echo 'Found nothing for the '.$product['url_slug'];
		continue;
	}
	mysql_query("DELETE FROM `ebay_search_results` WHERE `product_id` = ".$product['id']);
	foreach($insertQueries as $insertQuery) {
		mysql_query($insertQuery) or errorme('mysql error in ebay_search_feeds',$insertQuery."\n\n".mysql_error());
	}

	$numItems = count($insertQueries);
	$total += $numItems;
	echo ' - Found '.$numItems.' items for '.$product['url_slug']."\n";
}
mysql_query('OPTIMIZE TABLE `ebay_search_results`');
mysql_close();
$totalTime = microtime(1) - $startTime;
echo 'Script Added '.$total.' records to the database in '.round($totalTime,2).' seconds. See you in an hour!';