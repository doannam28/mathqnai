<?php

namespace App\Helpers;

use App\Models\Menu;
use App\Models\Page;
use App\Models\Taxonomy;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
/**
 * Created by PhpStorm.
 * User: MrNam
 * Date: 21/04/2023
 * Time: 10:13
 */

class Utility {
    public static function listtree($listdata,$parent = 0,&$tree,$sp=''){
        if($parent == 0){
            $sp = '';
        }else{
            $sp =$sp.' - ';
        }
        foreach($listdata as $k => $v){
            if($v['parent'] == $parent){
                $v['nameold'] = $v['name'];
                $v['name'] = $sp.$v['name'];
                $tree[] =$v;
                unset($listdata[$k]);
                Utility::listtree($listdata,$v['id'],$tree,$sp);
            }
        }
        return $tree;
    }
    public static function getMenus(){
        $menus = Menu::where('status', 1)
            ->where(function ($q) {
                $q->where('parent_id', 0)
                    ->orWhereNull('parent_id');
            })
            ->orderBy('order', 'ASC')
            ->get();
        foreach ($menus as $row){
            $row->subs = Menu::where('parent_id',$row->id)->where('status',1)->orderBy('order','ASC')->get();
        }
        return $menus;
    }

    public static function getPages()
    {
        return Page::where('status', 1)->where('menu', 1)
            ->orderBy('order', 'ASC')
            ->get();
    }

    public static function setting()
    {
        return \App\Models\Settings::where('id',1)->first();
    }

    public static function thumb($url,$width, $height)
    {
        $url_path = public_path('uploads');
        if(empty($url) || !file_exists($url_path.'/'.$url)){
            return 'images/no-image.jpg';
        }
        $paththumb = '/thumbs/'.$width.'/'.$height.'/'.$url;
        if(file_exists($url_path.'/'.$url) && !file_exists($url_path.$paththumb)){
            $bien = $width.'/'.$height.'/'.$url;
            $bien1 = explode('/',$bien);
            $url1 = $url_path.'/thumbs';
            for($i=0;$i<count($bien1)-1;$i++){
                $url1 = $url1.'/'.$bien1[$i];
                if(!file_exists($url1)){
                    mkdir($url1);
                    chmod($url1, 0777);
                }
            }
            try {
                if (!file_exists($url_path . $paththumb)) {
                    Image::make($url_path. '/' . $url)->fit($width, $height, function ($constraint) {
                        $constraint->upsize();
                    })->save($url_path . $paththumb);
                }
            }catch(\Exception $e){
                return $e->getMessage();
            }

            return $paththumb;
        }else{
            return $paththumb;
        }
    }

    public static function slug($name, $table ='news', $id = null ){
        switch($table){
            case 'taxonomy':
                $slug = Str::slug($name);
                $obj = new Taxonomy();
                $i = 0;
                while(1){
                    if($i == 0){
                        $check = $obj->where('slug',$slug)->count();
                    }else{
                        $check = $obj->where('slug',$slug.'-'.$i)->count();
                    }
                    if($check == 0 && $i!=0){
                        return $slug.'-'.$i;
                    }elseif($check == 0 && $i == 0){
                        return $slug;
                    }
                    $i++;
                }
                break;
            case 'taxonomyitem':
                $slug = Str::slug($name);
                $obj = new \App\Models\TaxonomyItem();
                $i = 0;
                while(1){
                    if($i == 0){
                        $check = $obj->where('slug',$slug)->count();
                    }else{
                        $check = $obj->where('slug',$slug.'-'.$i)->count();
                    }
                    if($check == 0 && $i!=0){
                        return $slug.'-'.$i;
                    }elseif($check == 0 && $i == 0){
                        return $slug;
                    }
                    $i++;
                }
                break;
            case 'post':
                $slug = Str::slug($name);
                $obj = new \App\Models\Post();
                $i = 0;
                while(1){
                    if($i == 0){
                        $check = $obj->where('slug',$slug)->where('id','<>',$id)->count();
                    }else{
                        $check = $obj->where('slug',$slug.'-'.$i)->where('id','<>',$id)->count();
                    }
                    if($check == 0 && $i!=0){
                        return $slug.'-'.$i;
                    }elseif($check == 0 && $i == 0){
                        return $slug;
                    }
                    $i++;
                }
                break;
            case 'page':
                $slug = Str::slug($name);
                $obj = new \App\Models\Page();
                $i = 0;
                while(1){
                    if($i == 0){
                        $check = $obj->where('slug',$slug)->where('id','<>',$id)->count();
                    }else{
                        $check = $obj->where('slug',$slug.'-'.$i)->where('id','<>',$id)->count();
                    }
                    if($check == 0 && $i!=0){
                        return $slug.'-'.$i;
                    }elseif($check == 0 && $i == 0){
                        return $slug;
                    }
                    $i++;
                }
                break;
            case 'tag':
                $slug = Str::slug($name);
                $obj = new \App\Models\Tag();
                $i = 0;
                while(1){
                    if($i == 0){
                        $check = $obj->where('slug',$slug)->where('id','<>',$id)->count();
                    }else{
                        $check = $obj->where('slug',$slug.'-'.$i)->where('id','<>',$id)->count();
                    }
                    if($check == 0 && $i!=0){
                        return $slug.'-'.$i;
                    }elseif($check == 0 && $i == 0){
                        return $slug;
                    }
                    $i++;
                }
                break;
            case 'category':
                $slug = Str::slug($name);
                $obj = new \App\Models\Category();
                $i = 0;
                while(1){
                    if($i == 0){
                        $check = $obj->where('slug',$slug)->count();
                    }else{
                        $check = $obj->where('slug',$slug.'-'.$i)->count();
                    }
                    if($check == 0 && $i!=0){
                        return $slug.'-'.$i;
                    }elseif($check == 0 && $i == 0){
                        return $slug;
                    }
                    $i++;
                }
                break;
            default:
                $slug = Str::slug($name);
                break;
        }
        return $slug;
    }
    public static function cut_string($str,$len,$more){
        if ($str=="" || $str==NULL) return $str;
        if (is_array($str)) return $str;
        $str = trim($str);
        if (strlen($str) <= $len) return $str;
        $str = substr($str,0,$len);
        if ($str != "") {
            if (!substr_count($str," ")) {
                if ($more) $str .= " ...";
                return $str;
            }
            while(strlen($str) && ($str[strlen($str)-1] != " ")) {
                $str = substr($str,0,-1);
            }
            $str = substr($str,0,-1);
            if ($more) $str .= " ...";
        }
        return $str;
    }

}
