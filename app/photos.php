<?php


class photos extends \db
{

    private $id;
    private $villa_id;
    private $main = 0;
    private $name;
    private $ext;
    private $title_id;
    private $order = 1000;
    private $correct;
    private $source_id = 0;
    private $uploadMode = 'local';

    private $exts = ['jpg','jpeg','png'];
    private $types = [2,3,18];
    private $sizes = ['original','resized',1920,1366,1170,960,848,750,555,370];
    //private $dir = '../photos';
    //private $dir = APP_PATH_PHOTOS;
    private $absdir = __DIR__ . "/../photos";
    // private $webdir = APP_PATH_PHOTOS;

    public static function build(string $photo){

        $query = 'select * from vn_villas_photos where villa_photo_id = '.$photo.' or photo_name like "'.$photo.'"';
        $that = new static;
        $that->exec($query);

        if( $row = $that->getAssoc() ){
            $that->id = $row['villa_photo_id'];
            $that->villa_id = $row['villa_id'];
            $that->main = $row['photo_main'];
            $that->name = $row['photo_name'];
            $that->ext = $row['photo_ext'];
            $that->title_id = $row['photo_title_id'];
            $that->order = $row['photo_order'];
            $that->correct = $row['correct'];
        }

        return $that;

    }

    public function setId($id){
        $this->id = $id;
    }

    public function setVillaId($villa_id){
        $this->villa_id = $villa_id;
    }

    public function setMain($main){
        $this->main = $main;
    }

    public function setName($name){
        $this->name = $name;
    }

    public function setExt($ext){
        $this->ext = $ext;
    }

    public function setTitleId($title_id){
        $this->title_id = $title_id;
    }

    public function setOrder($order){
        $this->order = $order;
    }

    public function setCorrect($correct){
        $this->correct = $correct;
    }

    public function setSourceId($source_id){
        $this->source_id = $source_id;
    }

    public function setUploadMode($mode){
        if( in_array($mode,['local','remote']) ){
            $this->uploadMode = $mode;
        }
    }

    public function getDir($type='abs'){
        $dir = $type == 'abs' ? $this->absdir.'/' : $this->webdir;
        return $dir.$this->villa_id;
    }

    public function getFile($size=''){
        $size = ( $size == '' || $size == 960 ) ? '' : '.'.$size;
        return $this->name.$size.'.'.$this->ext;
    }

    public function getPath($size,$type='abs'){
        return $this->getDir($type).'/'.$this->getFile($size);
    }

    public function upload($tmp){

        $result = false;
        $isUploaded = true;

        if( $this->uploadMode == 'local' ){
            $isUploaded = is_uploaded_file($tmp);
        }

        if( $isUploaded ){

            list($width,$height,$type) = getimagesize($tmp);

            if( in_array($type,$this->types) ){

                $dir = $this->getDir();
                $file = $this->getFile('original');

                //var_dump($dir);exit();

                if( !is_dir($dir) ){
                    if( mkdir($dir,0775) ) chmod($dir,0775);
                }

                if( is_dir($dir) ){
                    $photo_path = $dir.'/'.$file;
                    if( $this->uploadMode == 'local' ) $result = move_uploaded_file($tmp, $photo_path);
                    else $result = file_put_contents($photo_path, file_get_contents($tmp));
                }

            }

        }

        return $result;

    }

    public function add(){

        $path = $this->getPath('original');

        list($imageWidth,$imageHeight) = getimagesize($path);
        $divisor = gmp_intval(gmp_gcd($imageWidth,$imageHeight));
        $imageRatioX = $imageWidth/$divisor;
        $imageRatioY = $imageHeight/$divisor;
        $imageRatio = $imageRatioX.':'.$imageRatioY;

        $photo_correct = ( $imageWidth >= 1920 and $imageRatio == '16:9' ) ? 1 : 0;

        $query = 'insert into vn_villas_photos(villa_id,photo_name,photo_ext,photo_title_id,photo_main,photo_order,photo_correct,photo_source_id)
                  values('.$this->villa_id.',"'.$this->name.'","'.$this->ext.'",0,'.$this->main.','.$this->order.','.$photo_correct.',"'.$this->source_id.'")';


        $this->exec($query);

        return $this->affectedRows() > 0;

    }

    public function isGoodRatio($ratio){
        return $ratio == '16:9';
    }

    public function resize($size){

        $width = 1920;
        $height = 1080;

        if( $size == 'resize' ){
            $fromSize = 'original';
            $toSize = 'resized';
        }
        elseif( $size == 1920 ){
            $fromSize = 'resized';
            $toSize = 1920;
        }
        else{
            $fromSize = 1920;
            $toSize = $width = $size;
            $height = ceil(($width/16)*9);
        }

        $pathFrom = $this->getPath($fromSize);
        $pathTo = $this->getPath($toSize);

        list($imageWidth,$imageHeight,$imageType) = getimagesize($pathFrom);

        if( $imageWidth == $width && $imageHeight == $height ){
            rename($pathFrom,$pathTo);
        }
        else{

            $divisor = gmp_intval(gmp_gcd($imageWidth,$imageHeight));
            $imageRatioX = $imageWidth/$divisor;
            $imageRatioY = $imageHeight/$divisor;
            $imageRatio = $imageRatioX.':'.$imageRatioY;

            $positionX = 0;
            $positionY = 0;

            if( $size == 'resize' ){

                $new_width = $width;
                $new_height = ceil(($width/$imageRatioX)*$imageRatioY);

                if( $new_height < 1080 ){
                    $new_width = ceil(($height/$imageRatioY)*$imageRatioX);
                    $new_height = $height;
                }

            }
            elseif( $size == '1920' ){
                if( $imageWidth == 1920 ) $positionY = ceil(($imageHeight-$height)/2);
                else $positionX = ceil(($imageWidth-$width)/2);
                $new_width = $imageWidth = $width;
                $new_height = $imageHeight = $height;
            }
            else{
                $new_width = $width;
                $new_height = $height;
            }

            switch($imageType){
                case 2 : 
                    $img = imagecreatefromjpeg($pathFrom);
                    break;
                case 3 : 
                    $img = imagecreatefrompng($pathFrom);
                    break;
                case 18 : 
                    $img = imagecreatefromwebp($pathFrom);
                    break;
            }

            $imgThumb = imagecreatetruecolor($new_width,$new_height);
            imagecopyresampled($imgThumb,$img,0,0,$positionX,$positionY,$new_width,$new_height,$imageWidth,$imageHeight);

            switch($imageType){
                case 2 : 
                    imagejpeg($imgThumb,$pathTo);
                    break;
                case 3 : 
                    imagepng($imgThumb,$pathTo);
                    break;
                case 18 : 
                    imagewebp($imgThumb,$pathTo);
                    break;
            }

            if( in_array($size,['resize',1920]) && $this->isGoodRatio($imageRatio) ){
                unlink($pathFrom);
            }
            
        }

	}

	public function crop($x,$y){

        $result = false;

        $pathFrom = $this->getPath('resized');
        $pathTo = $this->getPath('1920');

        if( file_exists($pathFrom) ){

            list($imageWidth,$imageHeight,$imageType) = getimagesize($pathFrom);

            switch($imageType){
                case 2 :
                    $img = imagecreatefromjpeg($pathFrom);
                    break;
                case 3 :
                    $img = imagecreatefrompng($pathFrom);
                    break;
                case 18 :
                    $img = imagecreatefromwebp($pathFrom);
                    break;
            }

            $imgThumb = imagecreatetruecolor(1920,1080);

            $new_width = $imageWidth - $x;
            $new_height = $imageHeight - $y;

            if( $new_width > 1920 ) $new_width = 1920;
            if( $new_height > 1080 ) $new_height = 1080;

            imagecopyresampled($imgThumb,$img,0,0,$x,$y,1920,1080,$new_width,$new_height);

            switch($imageType){
                case 2 :
                    $result = imagejpeg($imgThumb,$pathTo);
                    break;
                case 3 :
                    $result = imagepng($imgThumb,$pathTo);
                    break;
                case 18 :
                    $result = imagewebp($imgThumb,$pathTo);
                    break;
            }

            if( $result ){
                $this->resize(1366);
                $this->resize(960);
                $this->resize(750);
            }

        }

        return $result;

    }

    public function validate(){

        $affected = 0;

        $query = 'update vn_villas_photos set photo_correct = 1 where villa_photo_id = '.$this->id;
        $this->exec($query);
        $affected = $this->affectedRows();

        if( $affected > 0 ){
            $this->delete('originals');
        }

        return $affected;

    }

	public function delete($sizes='all'){

        $unlink = 0;

        if( in_array($sizes,['all','originals','main']) ){

            $deleted = 1;

            if( $sizes == 'all' ){
                $sizes = $this->sizes;
                $query = 'delete from vn_villas_photos where villa_photo_id = '.$this->id;
                $this->exec($query);
                $deleted = $this->affectedRows();
            }
            else{
                $sizes = $sizes == 'originals' ? ['original','resized'] : [848,555,370];
            }

            if( $deleted == 1 ){
                foreach( $sizes as $size ){
                    $path = $this->getPath($size);
                    if( file_exists($path) ){
                        if( unlink($path) ) $unlink++;
                    }
                }
            }

        }

        return $unlink;

    }

    public function isMain(){

        $result = false;
        $continue = true;

        $query = 'select villa_photo_id from vn_villas_photos where villa_id = '.$this->villa_id.' and photo_main = 1';
        $this->exec($query);

        if( $row = $this->getAssoc() ){
            $main = photos::build($row['villa_photo_id']);
            $query = 'update vn_villas_photos set photo_main = 0 where villa_photo_id = '.$row['villa_photo_id'];
            $this->exec($query);
            if( $this->affectedRows() ){
               $main->delete('main');
            }
            else{
                $continue = false;
            }
        }

        if( $continue ){

            $query = 'update vn_villas_photos set photo_main = 1, photo_correct = 1 where villa_photo_id = '.$this->id;
            $this->exec($query);

            if( $this->affectedRows() > 0 ){
                $result = true;
                $this->resize(848);
                $this->resize(555);
                $this->resize(370);
                $this->delete('originals');
            }

        }

        return $result;

    }

}
