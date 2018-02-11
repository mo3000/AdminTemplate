<?php

namespace App\Utils\CloudStorage;


use Illuminate\Support\Facades\Storage;

class CloudWrapper {
	public static function get(string $path) : string
	{

	}

	public static function put(string $from, string $to) : string
	{

	}

	public static function delete(string $path)
	{

	}

	public static function editorContentPut(string $content)
	{
		$preg = '/<img\ssrc=\"data\:image\/(jpeg|png|jpg|gif)\;base64\,(.*?)\".*?>/i';
		preg_match_all($preg, $content, $base64Contents);
		if (count($base64Contents[2]) == 0) {
			return $content;
		}
		//创建资源存储目录
		$tmpdirname = 'tmp-'.date('m');
		Storage::makeDirectory($tmpdirname);
		preg_match_all('/<img\ssrc=\"(.*?)\".*?>/i', $content, $imageNames);
		$result = $content;
		//循环匹配结果,上传并替换base64格式图片
		foreach ($base64Contents[2] as $key => $img) {
			$tmpfilename = self::randomName();
			$path = "$tmpdirname/$tmpfilename";
			Storage::put($path, base64_decode($img));
			$url = self::put($path, $path);
			$newimg = '<img src="'.$url.'">';
			$result = str_replace($base64Contents[0][$key], $newimg, $result);
		}
		return $result;
	}

	public static function randomName() : string
	{
		return bin2hex(openssl_random_pseudo_bytes(24));
	}

	public static function editorContentDelete(string $content)
	{
		preg_match_all('/<img\ssrc=\"(.*?)\".*?>/i', $content, $deletes);
		foreach ($deletes as $delete) {
			self::delete($delete);
		}
	}
}