<?php
namespace Library;
class qrcodes{
    private $errorCorrectionLevel ; //容错级别
    private $matrixPointSize;//图片大小
    private $margin; //边距
    public function __construct() {
         require_once 'phpqrcode/qrlib.php';
         $this->errorCorrectionLevel = QR_ECLEVEL_H;
         $this->matrixPointSize = 10;
         $this->margin = 4;
    }
    
    /**
     * 
     * @param type $url  要生成二维码地址
     * @param type $img_url  二维码保存地址
     * @return type
     */
    public function generateQrcodeByUrl($url,$img_url='',$size = 10,$logo='',$level=QR_ECLEVEL_H){
        $this->matrixPointSize = $size;
        $this->errorCorrectionLevel = $level;
        $png_path = !empty($img_url) ? $img_url : UPLOAD_PATH.date('Ymd').'/';
        mkdir($png_path,0777,true);
        $png_name = date('YmdHis').'_'.  mt_rand(1000, 9999).'.png';
        $img_url  = $png_path.$png_name;
        \QRcode::png($url, $img_url,$this->errorCorrectionLevel,$this->matrixPointSize,$this->margin);   
        if(!empty($logo)){
            //加入logo
            $img_url = $this->generateLogoQrcode($img_url, $logo);
        }
        return $img_url;
    }
    
    
    private function generateLogoQrcode($QR,$logo){
        $ori_qr = $QR;
        $QR = imagecreatefromstring(file_get_contents($QR));  
        $logo = imagecreatefromstring(file_get_contents($logo));  
        if (imageistruecolor($logo)) imagetruecolortopalette($logo, false, 65535); // 新加这个。
        $QR_width = imagesx($QR);//二维码图片宽度   
        $QR_height = imagesy($QR);//二维码图片高度   
        $logo_width = imagesx($logo);//logo图片宽度   
        $logo_height = imagesy($logo);//logo图片高度   
        $logo_qr_width = $QR_width / 5;   
        $scale = $logo_width/$logo_qr_width;   
        $logo_qr_height = $logo_height/$scale;   
        $from_width = ($QR_width - $logo_qr_width) / 2;   
        //重新组合图片并调整大小   
        imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,$logo_qr_height, $logo_width, $logo_height); 
        imagepng($QR, $ori_qr); 
        return $ori_qr;
    }
}

?>
