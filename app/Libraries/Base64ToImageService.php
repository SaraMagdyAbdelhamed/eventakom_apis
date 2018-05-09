<?php
/**
 * Created by PhpStorm.
 * User: Ash
 * Date: 8/29/2017
 * Time: 11:19 AM
 */
namespace App\Libraries;


class Base64ToImageService
{
    // protected $upload_path;
      // protected $base_path="C:/xampp\htdocs\secure_bridge\public/";
//    protected $base_path = "/home/evilcell/public_html/secure_bridge/public/";
         protected $base_path = "/home/avocatoapp/public_html/secure_bridge/public/";

    private function __construct(){}

    public static function _instantiate()
    {
        static $instance = null;

        if($instance === null)
        {
            $instance = new Base64ToImageService();
        }

        return $instance;
    }

    public function setUploadPath($upload_path)
    {
        if(isset($upload_path) && is_string($upload_path) && strlen($upload_path))
        {
            $this->upload_path = (substr($upload_path, -1) != '/')?$upload_path.'/':$upload_path;
        }

        return $this;
    }

    public function setBasePath($base_path)
    {
        if(isset($base_path) && is_string($base_path) && strlen($base_path))
        {
            $this->base_path = (substr($base_path, -1) != '/')?$base_path.'/':$base_path;
        }

        return $this;
    }

    public function getUploadPath()
    {
        return $this->upload_path;
    }

    public function getBasePath()
    {
        return $this->base_path;
    }

    public static function convert($base64_string ,$upload_path)
    {
        $result = false;

        if(isset($base64_string) && is_string($base64_string) && strlen($base64_string))
        {
            $get_folder=explode('/', $upload_path);
            if (!file_exists((new self)->base_path.$get_folder[0])) {
                    mkdir((new self)->base_path.$get_folder[0], 0777, true);
                }
            $output_file = (new self)->generateImage($upload_path, $base64_string);
            $file_stream = fopen((new self)->base_path.$output_file, 'wb');

            $data = explode(',', $base64_string)[1];

            if(base64_decode($data, true))
            {
                try
                {
                    fwrite($file_stream, base64_decode($data));
                    fclose($file_stream);
//                    $result = explode($this->upload_path, $output_file)[1];
                    $result = $output_file;
                }
                catch(\Exception $e)
                {
                    $result = false;
                }
            }
        }

        return $result;
    }

    private function generateImage($upload_path, $base64_string)
    {
        $image_random_name = $this->getRandomString().'.'.$this->getBase64ImageExtension($base64_string);
        if(file_exists($this->base_path.$upload_path.$image_random_name))
        {
            $this->generateImage($upload_path, $base64_string);
        }

        return $upload_path.$image_random_name;
    }

    private function getBase64ImageExtension($base64_string)
    {
        $base64_data = explode(',', $base64_string)[0];
        $extension = explode(';', explode('/', $base64_data)[1])[0];

        return $extension;
    }

    private function getRandomString($length = 10)
    {
        // $characters = 'abc012def345ghi678jkl91011mno1213pqr141516stw171819vyz';
        // $random_string = '';
        // for($i = 0; $i < $length; $i++)
        // {
        //     $random_string .= $characters[rand(0, strlen($characters)-1)];
        // }
        // return $random_string;
        $str = "";
    $characters = array_merge(range('A','Z'), range('a','z'), range('0','9'));
    $max = count($characters) - 1;
    for ($i = 0; $i < $length; $i++) {
        $rand = mt_rand(0, $max);
        $str .= $characters[$rand];
    }
    return $str;
    }
}