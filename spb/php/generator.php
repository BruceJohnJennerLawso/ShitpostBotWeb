<?php
class ImageGenerator{
	
	private $srcPath;
	private $t6ePath;
	
	function __construct($srcPath, $t6ePath){
		$this->srcPath = $srcPath;
		$this->t6ePath = $t6ePath;
	}
	
	function getRandomTemplate(){
		$templates = glob($this->t6ePath.'/*.{jpg,png}', GLOB_BRACE);
		$index = mt_rand(0, count($templates) - 1);
		return pathinfo($templates[$index], PATHINFO_BASENAME);
	}

	static function imagexy($img) {
		return array(imagesx($img), imagesy($img));
	}
	
	static function getending($file) {
		return strtolower(array_slice(explode('.', $file), -1)[0]);
	}
	
	static function openimage($file) {
		if(self::getending($file) == 'png'){
			return imagecreatefrompng($file);
		}
		return imagecreatefromjpeg($file);
	}
	
	function generate($pos, $templateImg, $overlayImg=null) {
		$data = json_decode($pos);
		$imgcount = count($data);
		$images = glob($this->srcPath."*.{jpg,png}", GLOB_BRACE);
		shuffle($images);
		$imgs = array_slice($images, 0, $imgcount);
	
		$fullPath = $this->t6ePath.$templateImg;
		$img = self::openimage($fullPath);
		list($sx, $sy) = self::imagexy($img);
	
		for ($i = 0; $i < $imgcount; $i++){
			$imgpos = $data[$i];
			$im2 = self::openimage($imgs[$i]);
			$poscount = count($imgpos);
			list($sx2,$sy2) = self::imagexy($im2);
			for ($p = 0; $p < $poscount; $p++){
				$rotim2 = imagerotate($im2, 360-$imgpos[$p][4], imageColorAllocateAlpha($im2, 0, 0, 0, 127));
				$sizeX = imagesx($rotim2);
				$sizeY = imagesy($rotim2);
				//colour fill
				if(count($imgpos[$p]) > 5){
					list($x1, $y1, $x2, $y2) = $imgpos[$p];
					list($r, $g, $b) = self::convertToRGB($imgpos[$p][5]);
					$colour = imagecolorallocate($img, $r, $g, $b);
					imagefilledrectangle($img, $x1 * $sx, $y1 * $sy, $x2 * $sx, $y2 * $sy, $colour);
				}
				
				//source image
				list($x1, $y1, $x2, $y2) = self::getBestFit($imgpos[$p], $sizeX, $sizeX, $sx, $sy);
				imagecopyresampled($img, $rotim2, $x1, $y1, 0, 0, $x2-$x1, $y2-$y1, $sizeX, $sizeX);
			}
			imagedestroy($im2);
		}
		
		if(!is_null($overlayImg)){
			$overlayPath = $this->t6ePath.$overlayImg;
			if(file_exists($overlayPath)){
				$overlay = self::openimage($overlayPath);
				list($overlaysx, $overlaysy) = self::imagexy($overlay);
				imagecopyresampled($img, $overlay, 0, 0, 0, 0, $sx, $sy, $overlaysx, $overlaysy);
				imagedestroy($overlay);
			}
		}
		return $img;
	}
	
	//args: pos array (x1, y1, x2, y2), image width, image height, total width, total height
	//image is the source image, total is the template
	static function getBestFit($pos, $iw, $ih, $tw, $th){
		$x = $pos[0] * $tw;
		$y = $pos[1] * $th;
		$w = $pos[2] * $tw - $x;
		$h = $pos[3] * $th - $y;
		
		// Calculate resize ratios for resizing 
		$ratioW = $w / $iw; 
		$ratioH = $h / $ih;
		
		// smaller ratio will ensure that the image fits in the view
		$ratio = min($ratioW, $ratioH);
	
		$nw = $iw * $ratio;
		$nh = $ih * $ratio;
		
		if($w > $h){
			$x += ($w - $nw) / 2;
		} else{
			$y += ($h - $nh) / 2;
		}
		
		return array(round($x), round($y), round($x + $nw), round($y + $nh));
	}
	
	static function convertToRGB($hex) {
		if(strlen($hex) == 3) {
			$r = hexdec(substr($hex,0,1).substr($hex,0,1));
			$g = hexdec(substr($hex,1,1).substr($hex,1,1));
			$b = hexdec(substr($hex,2,1).substr($hex,2,1));
		} else {
			$r = hexdec(substr($hex,0,2));
			$g = hexdec(substr($hex,2,2));
			$b = hexdec(substr($hex,4,2));
		}
		
		return array($r, $g, $b);
	}
}

?>