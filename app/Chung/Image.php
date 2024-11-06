<?php
namespace App\Chung;

use App\Models\User;
use DB;
use DBD;
use Auth;
use Log;
use Storage;
use Sum;
use Cache;
use ZipArchive;
use File;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Events\SendMessage;

class Image
{	
    public static function download($imagePath, $fileName, $extension) {
		if(isset($imagePath)&&isset($fileName)&&isset($extension))
		{
			log::debug($imagePath);
			$imagePath = substr($imagePath, 0,7)=="public/" ? $imagePath : "public/".$imagePath;
			
			return Storage::download($imagePath, $fileName.".".$extension);

		} else {
			return false;
		}
    }
	
	// $folderDiv(폴더 구분)
	public static function upload($imagePath, $folderDiv) {
		if(isset($imagePath)){
			return $imagePath->store($folderDiv."/".date("Ymd"), 'public');
		} else {
			return false;
		}
	}
	
	public static function preview($imagePath, $fileName, $extension) {
		log::debug("imagePath : ".$imagePath.",  fileName : ".$fileName.",  extension : ".$extension);

		if(isset($imagePath) && isset($fileName) && isset($extension)) {

			if(substr_count(Storage::url("public/".$imagePath, $fileName).".".$extension, $extension) == 1)
			{
				// return Storage::url("public/".$imagePath, $fileName).".".$extension;

				return "file/".$imagePath;
			}
			else
			{
				// return Storage::url("public/".$imagePath, $fileName);

				return "file/".$imagePath;
			}
		} else {
			return false;
		}
	}

	public static function delete($imagePath) {
		if(isset($imagePath)) {

			$imagePath = substr($imagePath, 0,7)=="public/" ? substr($imagePath,7) : $imagePath;
			return Storage::disk('public')->delete($imagePath);
		} else {
			return false;
		}
	}

	/**
	*	압축파일 만들어 주는 함수
	*	$zipPath	:	압축파일경로
	*	$zipName	:	압축파일이름
	*	$arrayImagePath	:	압축될 파일경로 배열
	*
	*	return 압축된 파일 경로 || false
	*/
	public static function zipMake($zipPath, $zipName, $arrayImagePath)
	{
		if( empty($zipPath) || empty($zipName) || empty($arrayImagePath) || !is_array($arrayImagePath) )
		{
			return false;
		}

		$zipName = $zipName.".zip";

		$save_path = Storage::path($zipPath);
		$save_file_path = $zipPath."/".$zipName;

		$folder_exists = File::Exists($save_path);

		if( !$folder_exists )
		{
			File::makeDirectory($save_path, $mode = 0777, true);
		}

		//	ZipArchive()
		$zip = new ZipArchive();
		if( $zip->open(Storage::path($save_file_path), ZipArchive::CREATE) === TRUE )
		{
	        // 파일 추가
			foreach($arrayImagePath as $file)
			{
				if ( !$zip->addFile(Storage::path("public/".$file['file_path']), $file['filename']) ) 
				{
					return false;
				}
			}
			$zip->close();
		}
		else
		{
			return false;
		}

		return $save_file_path;
	}



}