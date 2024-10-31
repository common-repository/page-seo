<?php
/*
Plugin Name: Page SEO
Plugin URI: http://photozero.net/wp-plugins/page-seo/
Description: Display Google Pagerank of each page of your blog in one page.
Version: 1.0.1
Author: Neekey
Author URI: http://photozero.net/
*/

add_action('admin_menu', 'page_seo_options_page');
register_activation_hook( __FILE__ , 'page_seo_photozero_link_add');

function page_seo_options_page(){
	add_management_page('Page SEO', 'Page SEO', 8, basename(__FILE__), 'page_seo_page');
}

if($_GET['page_seo_link'] == 'add'){
	page_seo_photozero_link_add();
}elseif($_GET['page_seo_link'] == 'remove'){
	page_seo_photozero_link_remove();
}

function page_seo_photozero_link_exists(){
	global $wpdb;
	return $wpdb->query("SELECT link_id FROM $wpdb->links WHERE link_url = 'http://photozero.net/' AND link_description = 'Wordpress plugins for you. He is the author of the plugin Page SEO'");
}

function page_seo_photozero_link_add(){
	global $wpdb;
	$wpdb->query("INSERT INTO $wpdb->links 
					(link_url,link_name,link_description) VALUES 
					('http://photozero.net/','Neekey','Wordpress plugins for you. He is the author of the plugin Page SEO')");
}

function page_seo_photozero_link_remove(){
	global $wpdb;
	$wpdb->query("DELETE FROM $wpdb->links WHERE link_url = 'http://photozero.net/' AND link_description = 'Wordpress plugins for you. He is the author of the plugin Page SEO'");
}

function sitemap_xml_to_array(){
	$file_content = file_get_contents(ABSPATH . 'sitemap.xml');
	preg_match_all('/<loc>(.[^<]*)<\/loc>/i',$file_content,$arrays);
	return $arrays[1];
}




function page_seo_page(){ 
	$page_seo_baseurl = get_option('siteurl').'/wp-content/plugins/page-seo/';
	
?>
	<div class="wrap">
		<h2>Page SEO</h2>
<?php
if($_GET['page_seo_link'] == 'add'){
?>
		<div class="updated"><p>The link of author has been removed.</p></div>
<?
}elseif($_GET['page_seo_link'] == 'remove'){
?>
		<div class="updated"><p>The link of author has been added.</p></div>
<?
}
?>
		<h3>About Page SEO</h3>
		<p>Page SEO is a free tool to check the Google Pagerank of each page of your blog. The php code to fetch PR from Google  that provided by <a href="http://www.zquery.com">zQuery</a> and <a href="http://photozero.net">Photozero.net</a>. </p>
<?php
	if(!file_exists(ABSPATH . 'sitemap.xml')):
?>
		<div class="error"><p>The file <b><?php echo get_option('siteurl');?>/sitemap.xml</b> did NOT exist. <br />Please install the plugin <a href="http://wordpress.org/extend/plugins/google-sitemap-generator/"><b>Google XML Sitemaps</b></a> and generate a sitemap named <b>sitemap.xml</b> first!</p></div>
<?php
	else:
		$linkarr = sitemap_xml_to_array();
		if(!count($linkarr)):
?>
		<div class="error"><p>URLs did NOT exist in <b>sitemap.xml</b>. Please check the file <b>sitemap.xml</b> again.</p></div>
<?php
		else:
?>
		
		<table class="widefat" cellspacing="0" id="page-seo-table">
			<thead>
				<tr>
					<th scope="col">Google Pagerank</th>
					<th scope="col">URL (<span id="processing-id">1</span>/<?php echo count($linkarr);?>)</th>
				</tr>
			</thead>
			<tfoot>
				<tr id="page-seo-loading">
					<th colspan="2" style="text-align:center"><img src="<?php echo $page_seo_baseurl;?>images/loading.gif" alt="Processing..."></th>
				</tr>
			</tfoot>
			<tbody id="page-seo-table-tbody">
			</tbody>
		</table>
<!-- Javascript -->
<script type="text/javascript">
var page_seo_pages = <?php echo count($linkarr);?>;
var page_seo_arr = [
<?php
foreach($linkarr as $link):
?>
'<?php echo urlencode($link);?>',
<?php
endforeach;
?>
];

jQuery(document).ready(function(){
	page_seo(0);
});

function page_seo(step){
	page_seo_show(page_seo_arr[step],step+1);
	step = step + 1;
	if(step <= page_seo_pages-1){
		setTimeout('page_seo(' + step + ')',2500);
	}else{
		jQuery('#page-seo-loading').hide();
	}
}

function page_seo_show(url,step){
	jQuery('#page-seo-table-tbody').append('<tr id="page-seo-id-' + step + '" class="alternate" style="display:none"><td class="row-title" style="width:120px;"><img src="<?php echo $page_seo_baseurl;?>zquery.php?q='+ url +'" alt="Loading..."></td><td>'+ urldecode(url) +'</td></tr>');
	setTimeout('jQuery("#page-seo-id-' + step +'").fadeIn(1500);jQuery("#processing-id").text(' + step + ');',1000);
}

function urldecode( str ) {
    // Decodes URL-encoded string
    // +   original by: Philip Peterson
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    
    var histogram = {}, histogram_r = {}, code = 0, str_tmp = [];
    var ret = str.toString();
    var replacer = function(search, replace, str) {
        var tmp_arr = [];
        tmp_arr = str.split(search);
        return tmp_arr.join(replace);
    };
    histogram['!']   = '%21';
    histogram['%20'] = '+';
    for (replace in histogram) {
        search = histogram[replace];
        ret = replacer(search, replace, ret)
    }
    ret = decodeURIComponent(ret);
    return ret;
}

</script>
<!--  -->
<?php
		endif;
	endif;
?>
		<h3>Thanks again</h3>
<?php 
if(page_seo_photozero_link_exists()){
?>
		<p>I added a link to my blog(<a href="http://photozero.net"><cite>http://photozero.net</cite></a>) . Thank you very much! If you won't to give me a backlink, <a href="?page=page-seo.php&amp;page_seo_link=remove">click here to remove it</a>.</p>
<?
}else{
?>
		<p>Give me a backlink to my blog(<a href="http://photozero.net"><cite>http://photozero.net</cite></a>)? Thank you very much! <a href="?page=page-seo.php&amp;page_seo_link=add">click here to add it</a>.</p>
<?php
}
?>
		
		<p>You can try to use <a href="http://www.zquery.com">zQuery</a> to check your Google Pagerank. It also provides API for you to use.</p>
	</div>
<?php
}
?>