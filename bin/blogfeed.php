<?php
/**
 * File to update latest blog post element.
 */
if(!CRONSCRIPT) die;

//Settings
$blogFeed = 'http://www.storbital.com/blog/feed';
$numItems = 1;
$destinationFile = '/sites/storbital.com.local/app/views/elements/latestBlog.phtml';
//$destinationFile = '/sites/storbital.com/app/views/elements/latestBlog.phtml';

//Includes
require('lib/xml2array.php');

//Get Blog Feed
$xmlArray = xml2array(file_get_contents($blogFeed));
$blog = $xmlArray['rss']['channel']['item'][0];

//Setup Output
$output = '<h3 class="blog-link"><a href="'.$blog['link'].'">'.$blog['title'].'</a></h3>
'.$blog['description'];

//Open File for editing.
$fp= fopen($destinationFile, 'w');
fwrite($fp, $output);
fclose($fp);