<?php
/**
* ThreeSixxty - 360°-view for products
* 
* Upload a series of single images of a product in the OXID eShop backend view of the article.
* At the first frontend loading of the detailed product view the plugin will render and 
* display a 360°-view of your uploaded files. You can set the final width of the 360°-view
* in this file: 
* <your shop root directory>/modules/article_reel_frontend/views/blocks/details_productmain_zoom.tpl
* Please Note: You have to activate two modules (article_reel => '360°-Ansicht-Frontenteinbindung',
* article_reel_frontend => 'Artikel 360°-Ansicht') for errorless usage!
*
* @link      	...
* @author		Martin Popp <post@popp-media.de>
* @version		1.0 24/04/2013
*/

/**
 * Metadata version
 */
$sMetadataVersion = '1.0';

/**
 * Module information
 */
$aModule = array(
    'id'           => 'article_reel_frontend',
    'title'        => '360°-Ansicht-Frontenteinbindung',
    'description'  => 'Erweiterung des Artikelbildes um eine 360°-Ansicht im Frontent',
    'thumbnail'    => 'three-sixxty-icon.gif',
    'version'      => '1.0',
    'author'       => 'Martin Popp',
    'extend'       => array(
    ),
    'extend'       => array(
        'oxarticle' => 'article_reel/article_reel',
        'article_main' => 'article_reel/article_main_reel'
    ),
    'blocks' => array(
    	array('template' => 'page/details/inc/productmain.tpl', 'block' => 'details_productmain_zoom', 'file' => '/views/blocks/details_productmain_zoom.tpl')  	
    ),
);

