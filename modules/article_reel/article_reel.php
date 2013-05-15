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

class article_reel extends article_reel_parent {
	protected $_sThisTemplate = "article_reel.tpl";

	//public attributes
	public $dir_src; // example "img/originals/"
	public $dir_out; // example "img/stitched/"
	public $dir_cust; // example "testdir/"
	public $cust_size; // in % example: 30% => 0.3
	public $raw_files = array(); //files in temporary upload directory
	public $files = array(); //files for prosessing large sprite
	public $filename; //project name given by user
	public $size = array(); //size of first image of series

	//private attributes
	private $directory;
	private $sprite_size = array();
	private $sprite;
	private $bg_color;
	private $sprite_resized;
	private $shop_root_dir;
	private $shop_root_url;
	private $cust_size_PERCENT;

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
	public function setCustSize ($custSize){
		$this -> cust_size = $custSize;
	}
	public function setRawFiles ($rawFiles){
		$this -> raw_files = $rawFiles;
	}
	public function setFilename ($filename){
		$this -> filename = 'stitched_'.$this -> cust_size.'_'.$filename;
	}
	public function getArticleReel($ArtID, $imageWidth){
		$this -> shop_root_dir = $this->getConfig()->getConfigParam('sShopDir');
		$this -> shop_root_url = $this->getConfig()->getShopURL();
		$oxID = $ArtID;
		$srcpath = "reelimages/originals/";
		$outpath = "reelimages/stitched/";
		$this -> directory = $this -> shop_root_dir."reelimages/originals/".$oxID;

		//check if reel with given size is existing - if not --> stitch!
		$sql = "SELECT projectname, fileName, width, height, singleFiles FROM images WHERE projectName = '".$oxID."' AND width = '".$imageWidth."'";
		$reel_query = mysql_query($sql) or die ("request not successful");
		$reel_array = mysql_fetch_array($reel_query);
		if ($reel_array[0] != NULL){
			$img_src = $this -> shop_root_url.$outpath.$reel_array[0].'/'.$reel_array[1];
			$image_size_array = array("width" => $reel_array[2], "height" => $reel_array[3]);
			$single_files = $reel_array[4];
			//print reel-code
			$this -> printReel($img_src, $image_size_array);
			//get custom ree-script
			$custom_reel_script = $this -> addReelJavascript($single_files, $img_src);
		} else {
			//check if there are any images uploaded for current article
			if(is_dir($this -> directory)){
				//get filename
				$directory = opendir($this -> directory);
				$i = 0;
				$first_file_for_name;
				while($item = readdir($directory)){
					//take only *.jpg files
					if($item != "." && $item != ".." && strtolower(substr($item, strrpos($item, '.') + 1)) == 'jpg'){
						$first_file_for_name = $item;
						$i++;
					}
					if ($i >0) break;
				}
				$this -> setDirSrc ($srcpath); //originals
				$this -> setDirOut ($outpath); //stitched
				$this -> setDirCust ($oxID); //article-ID
				$this -> setCustSize ($imageWidth); //given in template
				$this -> setFilename ($first_file_for_name); // "stitched_<size-width>_<first_file_of_series>"

				//stitch
				$this -> stitch($this -> directory);

				//collect data for db-insert
				$db_dir_cust = $oxID;
				$db_filename = $this -> filename;
				$db_cust_size = $this -> cust_size;
				$db_width = $this -> size[0] * $this -> cust_size_PERCENT;
				$db_height = $this -> size[1] * $this -> cust_size_PERCENT;
				$single_files = count($this -> files);

				//insert data into db
				$this -> insertNewData ($db_dir_cust, $db_filename, $db_cust_size, $db_width, $db_height, $single_files);

				//after stitching print new reel
				$sql = "SELECT projectname, fileName, width, height, singleFiles FROM images WHERE projectName = '".$oxID."' AND width = '".$imageWidth."'";
				$reel_query = mysql_query($sql) or die ("request not successful");
				$reel_array = mysql_fetch_array($reel_query);
				$img_src = $this -> shop_root_url.$outpath.$reel_array[0].'/'.$reel_array[1];
				$image_size_array = array("width" => $reel_array[2], "height" => $reel_array[3]);
				$single_files = $reel_array[4];
				//print reel-code
				$this -> printReel($img_src, $image_size_array);
				//get custom ree-script
				$custom_reel_script = $this -> addReelJavascript($single_files, $img_src);
			} else {
				//echo "Bitte erst Bilder für diesen Artikel hochladen!";
			}
		}
		return $custom_reel_script; //return reel-javascript
	}
	public function printReel($image_src, $image_size){
		echo '<img src="'.$image_src.'" width="'.$image_size["width"].'" height="'.$image_size["height"].'" id="my_image">';
	}
	public function addReelJavascript($single_files, $image_src){
		$script = '$(document).ready(function(){';
		$script .= '		$("#my_image").reel({frames: '.$single_files.', footage: '.$single_files.', image: "'.$image_src.'" });';
		$script .= '	});';
		return $script;
	}
	//stitch single images into one large image
	private function stitch (){
		$directory = opendir($this -> directory);
		while($item = readdir($directory)){
			//take only *.jpg files
			if($item != "." && $item != ".." && strtolower(substr($item, strrpos($item, '.') + 1)) == 'jpg'){
				$this -> files[] = $item;
			}
		}
		$file_count = count($this -> files);
		$this -> size = getimagesize($this -> directory.'/'.$this -> files[0]);
		$this -> sprite_size["width"] = $file_count * $this -> size[0];
		$this -> sprite_size["height"] = $this -> size[1];
		//fullsize image
		$this -> sprite = imagecreatetruecolor($this -> sprite_size["width"], $this -> sprite_size["height"]);
		$this -> bg_color = imagecolorallocate($this -> sprite, 255, 255, 255);
		imagefill($this -> sprite, 0, 0, $this -> bg_color);
		//cust_size in %
		$this -> cust_size_PERCENT = $this -> cust_size / $this -> size[0];
		//resized image
		$this -> sprite_resized = imagecreatetruecolor($this -> sprite_size["width"] * $this -> cust_size_PERCENT, $this -> sprite_size["height"] * $this -> cust_size_PERCENT);
		$this -> bg_color = imagecolorallocate($this -> sprite_resized, 255, 255, 255);
		imagefill($this -> sprite_resized, 0, 0, $this -> bg_color);

		//put single images into one large image
		foreach ($this -> files as $index => $src) {
			$x = $this -> size[0] * (($index) % $file_count);
			$y = floor(($index)/$file_count) * $this -> size[1];
			$img = @imagecreatefromjpeg($this -> directory.'/'.$this -> files[$index]);
			imagecopy($this -> sprite, $img, $x, $y, 0, 0, $this -> size[0], $this -> size[1]);
		}
		imagecopyresampled( $this -> sprite_resized , $this -> sprite , 0 , 0 , 0 , 0 , $this -> sprite_size["width"] * $this -> cust_size_PERCENT , $this -> sprite_size["height"] * $this -> cust_size_PERCENT , $this -> sprite_size["width"] , $this -> sprite_size["height"] );
		imagejpeg($this -> sprite_resized, $this -> dir_out.'/'.$this -> dir_cust.'/'.$this -> filename);
		imagedestroy($this -> sprite);
		imagedestroy($this -> sprite_resized);

		//echo "Stitching successful!";
	}
	//insert new data to database
	private function insertNewData ($db_dir_cust, $db_filename, $db_cust_size, $db_width, $db_height, $single_files){
		$sql = "INSERT INTO images (projectName, fileName, width, height, singleFiles)
		VALUES ('$db_dir_cust', '$db_filename', '$db_width', '$db_height', '$single_files')";
		mysql_query($sql) or die ("request not successful");
	}
}
?>