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

class article_main_reel extends article_main_reel_parent {
	//public attributes
	public $dir_src; // example "img/originals/"
	public $dir_out; // example "img/stitched/"
	public $dir_cust; // example "testdir/"
	public $raw_files = array(); //files in temporary upload directory
	public $files = array(); //files for prosessing large sprite
	public $filename; //project name given by user

	//private attributes
	private $directory;
	private $shop_root_dir;

	//public functions
	public function setDirSrc ($dirSrc){
		$this -> dir_src = $dirSrc;
	}
	public function setDirOut ($dirOut){
		$this -> dir_out = $dirOut;
	}
	public function setDirCust ($dirCust){
		$this -> dir_cust = $dirCust.'/';
	}
	public function setRawFiles ($rawFiles){
		$this -> raw_files = $rawFiles;
	}
	public function setFilename ($filename){
		$this -> filename = 'stitched_'.intval($this -> cust_size * 100).'_'.$filename;
	}
	public function generateDir ($dirSrc, $dirOut, $dirCust){
		if (!is_dir($this -> shop_root_dir.$dirSrc.$dirCust)) {
			mkdir($this -> shop_root_dir.$dirSrc.$dirCust);
		}
		if (!is_dir($this -> shop_root_dir.$dirOut.$dirCust)) {
			mkdir($this -> shop_root_dir.$dirOut.$dirCust);
		}
		$this -> directory = $this -> shop_root_dir.$dirSrc.$dirCust; //directory with source data
		$this -> dir_out = $this -> shop_root_dir.$dirOut.$dirCust; //directory with output data
	}
	public function upload_reel() {
		//check if table `images` exists; if not --> create table
		$sql = "SELECT * FROM images";
		$result = @mysql_query($sql);
		if (!$result){
			$this -> createTableImages();
		}
		$this -> shop_root_dir = $this->getConfig()->getConfigParam('sShopDir');
		//create directory vor reel-images
		if (!is_dir($this -> shop_root_dir.'/reelimages')) {
			mkdir($this -> shop_root_dir.'/reelimages');
		}
		if (!is_dir($this -> shop_root_dir.'/reelimages/originals')) {
			mkdir($this -> shop_root_dir.'/reelimages/originals');
		}
		if (!is_dir($this -> shop_root_dir.'/reelimages/stitched')) {
			mkdir($this -> shop_root_dir.'/reelimages/stitched');
		}
		// OXID von Formular holen
		$oxID = oxConfig::getParameter('oxid');
		$srcpath = "reelimages/originals/";
		$outpath = "reelimages/stitched/";
		$directory = $this -> shop_root_dir."reelimages/originals/";
		$this -> setDirSrc ($srcpath);
		$this -> setDirOut ($outpath);
		$this -> setDirCust ($oxID);
		//$this -> setCustSize ($_POST["img_size"]);
		$this -> setRawFiles ($_FILES); //files in temporary upload directory
		$this -> setFilename ($_FILES['userfile']['name'][0]); // "stitched_<size>_<first_file_of_series>"

		$this -> generateDir ($this -> dir_src, $this -> dir_out, $this -> dir_cust );
		$this -> moveFiles($this -> directory);
	}

	//get reel array from database
	public function getReelArray (){
		$sql = "SELECT id, projectName, fileName, width, height, singleFiles FROM images GROUP BY projectName";
		$reel_query = mysql_query($sql) or die ("request not successful");
		$i = 0;
		while ($reel_value = mysql_fetch_array($reel_query)){
			$reel_array[$i] = $reel_value['projectName'];
			$i++;
		}
		return $reel_array;
	}
	public function checkReelExistence($ID){
		if(is_dir($this->getConfig()->getConfigParam('sShopDir')."/reelimages/originals/".$ID)){
			return true;
		} else{
			return false;
		}
	}

	//private functions
	//create table `images in shop-database`
	private function createTableImages(){
		$sql =
			"CREATE TABLE [IF NOT EXISTS] `images` (
				  `id` int(5) NOT NULL AUTO_INCREMENT,
				  `projectName` varchar(50) CHARACTER SET utf8 NOT NULL,
				  `fileName` varchar(50) CHARACTER SET utf8 NOT NULL,
				  `width` int(4) NOT NULL,
				  `height` int(4) NOT NULL,
				  `singleFiles` int(4) NOT NULL,
				  PRIMARY KEY (`id`),
				  KEY `projectName` (`projectName`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
		mysql_query($sql) or die ("creating table `images` not successful");
	}
	//move files to shop_root_directory/reelimages/originals/<custom-directory>
	private function moveFiles ($dir){
		if(isset($this -> raw_files['userfile']['name'])){
			foreach($this -> raw_files['userfile']['name'] as $key => $value){
				//move files to target directory
				$uploadfile = $dir.basename($this -> raw_files['userfile']['name'][$key]);
				move_uploaded_file($this -> raw_files['userfile']['tmp_name'][$key], $uploadfile);
			}
		}
	}
}
?>