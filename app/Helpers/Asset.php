<?php 

namespace App\Helpers;

use Storage;
use Intervention\Image\ImageManager as Image;

class Asset {
    public static function upload_image($file, $folder, $encode = false, $new_type = NULL, $width = NULL, $height = NULL, $extension = NULL) {
        $filename = $folder.'_'.substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 20);
        $image_folder = \App\Models\ImageFolder::where('name', $folder)->first();
        if($image_folder&&count($image_folder->image_sizes)>0){
          $size_extension = $image_folder->extension;
          if($extension){
            $size_extension = $extension;
          }
          $new_filename = public_path('tmp/'.$filename.'.'.$size_extension);
          $image_sizes = $image_folder->image_sizes()->get(['code','type','width','height'])->toArray();
          array_push($image_sizes, ['code'=>'mini','type'=>'fit','width'=>150,'height'=>150]);
          $image_quality = config('solunes.image_quality');
          foreach($image_sizes as $size){
            if($size['code']=='custom'&&$new_type){
                $size['type'] = $new_type;
            }
            if($size['code']=='custom'&&$width){
                $size['width'] = $width;
            }
            if($size['code']=='custom'&&$height){
                $size['height'] = $height;
            }
            $type = $size['type'];
            if($encode===true){
                if(config('app.system')=='linux'){
                    $encoded_file = utf8_decode(utf8_encode($file));
                } else {
                    $encoded_file = utf8_decode($file);
                }
            } else {
                $encoded_file = $file;
            }
            $imageManager = new Image(['driver' => 'imagick']);
            if($type=='original'){
                $img = $imageManager->make($encoded_file)->encode($size_extension)->save($new_filename, $image_quality);
            } else {
                try {
                    $img = $imageManager->make($encoded_file)->$type($size['width'], $size['height'], function ($constraint) {
                        $constraint->aspectRatio();
                        //$constraint->upsize();
                    })->encode($size_extension)->save($new_filename, $image_quality);
                } catch (\Intervention\Image\Exception\NotReadableException $e) {
                    return false;
                }
            }
            $handle = fopen($new_filename, 'r+');
            \Storage::put($folder.'/'.$size['code'].'/'.$filename.'.'.$size_extension, $handle);
            fclose($handle);
            unlink($new_filename);
          }
          return $filename.'.'.$size_extension;
        } else {
            return false;
        }
    }    

    public static function get_image_path($folder, $code, $file) {
        $path = $folder.'/'.$code.'/'.$file;
        if(config('filesystems.cloud')=='cloudfront'){
            $final_path = config('filesystems.disks.cloudfront.url').'/'.$path;
        } else {
            $final_path = \Storage::url($path);
        }
        return $final_path;
    }

    public static function upload_file($file, $folder, $encode = false) {
        if(is_object($file)){
            $file_info = pathinfo($file->getClientOriginalName());
            $filename = time().'_'.\Illuminate\Support\Str::slug($file_info['filename']).'.'.$file->getClientOriginalExtension();
            $file->move('tmp', $filename);
        } else {
            $file_info = pathinfo($file);
            if($encode===true){
                if(config('app.system')=='linux'){
                    $file = utf8_decode(utf8_encode($file_info['dirname'].'/'.$file_info['basename']));
                } else {
                    $file = utf8_decode($file_info['dirname'].'/'.$file_info['basename']);
                }
            } else {
                $file = $file_info['dirname'].'/'.$file_info['basename'];
            }
            $filename = time().'_'.\Illuminate\Support\Str::slug($file_info['filename']).'.'.$file_info['extension'];
            copy($file, 'tmp/'.$filename);
        }
        $handle = fopen('tmp/'.$filename, 'r+');
        Storage::put($folder.'/'.$filename, $handle);
        fclose($handle);
        unlink('tmp/'.$filename);
        return $filename;
    }

    public static function get_file($folder, $file) {
        $path = $folder.'/'.$file;
        if(config('filesystems.cloud')=='cloudfront'){
            $final_path = config('filesystems.disks.cloudfront.url').'/'.$path;
        } else {
            $final_path = Storage::url($path);
        }
        return $final_path;
    }    
}