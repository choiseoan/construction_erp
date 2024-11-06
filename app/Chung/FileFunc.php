<?php
namespace App\Chung;

use App\Models\User;
use DB;
use DBD;
use Auth;
use Log;
use Storage;
use Func;
use FastExcel;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use App\Chung\ExcelCustomExport;
use App\Chung\ExcelCustomImport;
use App\Chung\ExcelCustomSheets;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Style\Border;
/*
    파일업로드 함수
	static 함수호출시 디스크 위치에 저장 
*/
class FileFunc
{	
	private static $winflex = 'up_winflex'; 
	private static $sftp    = 'up_sftp'; 
	private static $local   = 'up_loc'; 

	/*
	*	파일저장 디스크 설정
	*   url 주소따라 판단
	*/
	public static function setDisk($path=null)
	{
		/**
		 * 로컬 -> 로컬	: 개발계로 SFTP 전송 (loc_winflex)
		 * 로컬 -> SFTP : 개발계로 SFTP 전송
		 * 개발 -> 로컬 : 본인서버 UP_LOCAL_DIV 경로에 업로드
		 * 개발 -> SFTP : 개발계로 SFTP 전송					(소스 통일을 위함)
		 * 운영 -> 로컬 : 본인서버 UP_LOCAL_DIV 경로에 업로드
		 * 운영 -> SFTP : 운영WEB에서 운영WAS로 SFTP 전송
		*/

		// 플랫폼
		if($path=="sftp")
		{
			$disk = static::$sftp;
		}
		else
		{
			// 개발계 & 로컬
			if(Func::isDev())
			{
				// 로컬
				if(Func::isLocDev())
				{
					$disk = static::$winflex;
				}
				// 개발계
				else
				{
					$disk = static::$local;
				}
			}
			// 운영계
			else
			{
				$disk = static::$local;
			}
		}

		return $disk;
	}

	/*
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
		$zip = new ZipArchive;
		if( $zip->open(Storage::path($save_file_path), ZipArchive::CREATE) === TRUE )
		{
	        // 파일 추가
			foreach($arrayImagePath as $files => $file)
			{ 
				//log::debug(Storage::disk('up_winflex')->url($file['filename']));
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

	 /**
     * 디스크에 파일 업로드
     *
     * @param Illuminate\Http\UploadedFile $file	파일
	 * @param string $folderDiv	 저장폴더명(경로 사이에만 / 입력)
	 * @param array $custom 	 값 변경 필요시 [ key => value ] 로 입력
     * @return array 
     */
    public static function upload($file, $folderDiv, Array $custom=null) 
    {
		$disk = self::setDisk();

		//저장경로 최상위 폴더명 필요시 폴더이름 추가
        if(!isset($file) || !preg_match('/^(\b(customer|user|excel|hn|board|posting_img|platform)\b)/', $folderDiv))
        {
			Log::debug("폴더경로 잘못됨!");
            return false;
        }

		$_IMG['disk'] 			 = $disk;
        $_IMG['origin_filename'] = $file->getClientOriginalName();
        $_IMG['extension']		 = $file->getClientOriginalExtension();
		$_IMG['file_dir']		 = $folderDiv.'/'.date("Ymd");
        $_IMG['filename']		 = date('YmdHis').uniqid().'.'.$_IMG['extension'];
        $_IMG['file_path']		 = $folderDiv.'/'.date("Ymd").'/'.$_IMG['filename'];
        // $_IMG['file_path']	 = $folderDiv.'/'.date("Ym"."/".date("md")).'/'.$_IMG['filename'];
		
		//변경필요시 [ key => value ] 로 바인딩 
		if(is_array($custom) && !empty($custom))
		{
			foreach($custom as $k => $v)
			{
				if(isset($_IMG[$k]))
				{
					$_IMG[$k] = $v;
				}
			}
		}

        //이미지 파일은 exif 반영해야함 
        if(Func::checkImg( $_IMG['extension'])){    
            $file = Image::make($file->getRealPath());
            $file->orientate();

			if(isset($custom['resize'])){
				// $origin_w = $file->width();
				// $origin_h = $file->height();
				$width = $custom['resize']['width']  ?? null;
				$height = $custom['resize']['height']  ?? null;
				$file->resize($width, $height, function ($constraint) { $constraint->aspectRatio(); });
			}

            $file->stream(); 
            Storage::disk($disk)->put($_IMG['file_path'], $file, 'public');
        }else{
            Storage::disk($disk)->put($_IMG['file_path'], file_get_contents($file), 'public');
        }
		
        return $_IMG;
    }

	 /**
     * 파일 미리보기관련 정보 
     *
     * @param string $path		  파일경로
	 * @param string $filename	  파일명
	 * @param string $table  	  파일다운로드용 경로
	 * @param string $disk_choice 디스크선택
     * @return Array 
     */
    public static function preview($path, $filename, $table=null, $disk_choice=null)
    {
		$disk = self::setDisk($disk_choice);

        if(Storage::disk($disk)->exists($path))
        {
            $ext = pathinfo($path, PATHINFO_EXTENSION);
			$RES['filename'] = $filename;
			$RES['extension'] = $ext;
			$RES['table'] = $table;
			
			if(Func::checkImg($RES['extension']))
			{	
                // $RES['filedata']= Image::make(Storage::disk($disk)->get($path))->orientate()->encode('data-url')->encoded;
                $RES['filedata'] = base64_encode(Image::make(Storage::disk($disk)->get($path))->orientate());
				$RES['extension'] = 'img';
			}
			
			return $RES;
        }

        return false;
    }
	
	public static function previewWithKey($path,$filename,$table,$key)
	{
		$RES = self::preview($path,$filename,$table); 
		
		if(!$RES)
		{
			$RES = array_merge($RES,['no' => $key]);
			return $RES;
		}
		
		return false;
	}
	
	public static function getContent($path)
	{
		$disk = self::setDisk();
		if(Storage::disk($disk)->exists($path))
        { 
            return Storage::disk($disk)->get($path);
        }
		return false;
	}
	
	/**
     * 디스크 파일 다운로드  
     *
     * @param string $path  파일경로 
     * @param string $filename  다운로드 파일명 
     * @return
     */
    public static function download($path, $filename) 
	{
		$disk = self::setDisk();
        if(Storage::disk($disk)->exists($path))
        {
            return Storage::disk($disk)->download($path,$filename);
        }
        return redirect('/404');
    }

	 /**
     * 디스크 파일 삭제 - 디스크 모두 확인 
	 * mode에 따라 원격지, 로컬 모두 삭제가능
     *
     * @param string $path  파일경로 
	 * @param string $mode  삭제위치 (sftp일경우 플랫폼에 업로드된 파일이 삭제됨.)
     * @return
     */
    public static function delete($path, $mode=null) 
    {
        $disk = self::setDisk($mode); 

		if(Storage::disk($disk)->exists($path))
		{
			Storage::disk($disk)->delete($path);
		}

		//로컬작업시
		if($disk == static::$sftp && Storage::disk(static::$local)->exists($path))
		{
			Storage::disk($disk)->delete($path);
		}
    }

	private function tmpToLocal($sftp_path,$origin_name)
	{
		$file = Storage::disk(static::$local)->put($origin_name, Storage::disk(static::$sftp)->get($sftp_path));
	}  

	 /**
     * 파일이동 
     *
     * @param string $pathFrom  기존위치
     * @param string $pathTo	옮길위치 ( 없는 경우 동일위치 )
     * @param string $delflag	기존파일삭제여부 - 기본값:삭제
	 * @param string $mode		플랫폼, 로컬 복사 경로 - 기본값:로컬->로컬복사
     * @return string 
     */
		public static function moveToDisk($pathFrom, $pathTo=null, $delflag=null, $mode=null,$originmode=null)
	{	
		$origin_disk = self::setDisk($originmode);
		
		// 플랫폼으로 복사
		if(!empty($mode))
		{
			$new_disk = self::setDisk($mode);
		}
		else if(!empty($originmode))
		{
			$new_disk = self::setDisk();
		}
		// 로컬->로컬 복사
		else
		{
			$new_disk = $origin_disk;
		}
		
		if(!$pathTo) 
		{
			$pathTo = $pathFrom;
		}

		// 옮길 경로에 해당 이미지가 있는지 확인
		if(empty($mode) || (!empty($mode) && $mode!="sftp"))
		{
			if( Storage::disk($new_disk)->exists($pathTo))
			{
				$msg = '['.$new_disk.']['.$pathTo.'] 해당 파일이 이미 존재합니다.';
				
				return 'M';
			}
		}

		// 옮길 파일 존재
		if(Storage::disk($origin_disk)->exists($pathFrom))
		{
			Log::debug("GOGO [".$origin_disk."]->[".$new_disk."]   [".$pathFrom."]->[".$pathTo."]");
			
			// 이미지 옮기기
			$ttt = Storage::disk($new_disk)->put($pathTo, Storage::disk($origin_disk)->get($pathFrom));
			
			// 로컬->로컬 복사이면서 삭제플래그가 있을경우 삭제			
			if(empty($delflag) && empty($mode))
			{
				Storage::disk($origin_disk)->delete($pathFrom);
			} 	
		}
		//개인로컬->개발계 업로드시
		else if(Storage::exists($pathFrom))
		{
			Storage::disk($new_disk)->put($pathTo, Storage::get($pathFrom));
			//로컬파일삭제 
			if(empty($delflag) && empty($mode))
			{
				Storage::delete($pathFrom);
			} 	
		}

		/*
		else if(Storage::disk($disk)->exists($pathFrom))
		{
			if(empty($delflag))
			{
				Storage::disk($disk)->move($pathFrom,$pathTo);
			}
			else
			{
				Storage::disk($disk)->copy($pathFrom,$pathTo);
			}	
		}
		*/
		else
		{
			return '['.$origin_disk.']['.$pathFrom.'] 유효하지 않은 경로입니다.';
		}

		return 'Y';
	}

	 /**
     * 파일명 만들기 
     *
     * @param string $folderDiv 파일경로
     * @param string $extension	확장자명
     * @return array
     */
	public static function makeNewPath($folderDiv,$extension)
	{
        $_IMG['filename'] = date('YmdHis').uniqid().'.'.$extension;
        // $_IMG['file_path'] = $folderDiv.'/'.date("Ym"."/".date("md")).'/'.$_IMG['filename'];
        $_IMG['file_path'] = $folderDiv.'/'.date("Ymd").'/'.$_IMG['filename'];

		return $_IMG;
	}

	/**
     * BASE64 String 디코딩
     *
     * @param Illuminate\Http\UploadedFile $file	파일
	 * @return array 
     */
	public static function uploadFromBase64($file)
	{
		$_IMG['mime_type']		 = $file->getMimeType();
		$_IMG['origin_filename'] = $file->getClientOriginalName();
        $_IMG['extension']		 = $file->getClientOriginalExtension();
		$_IMG['filedir']         = date('Ymd');
		$_IMG['filedata']		 = base64_decode($file->get());

		return $_IMG;
	}

	// $folderDiv(폴더 구분)
	public static function upload($imagePath, $folderDiv) {
		if(isset($imagePath)){
			return $imagePath->store($folderDiv."/".date("Ymd"), 'lumplog');
		} else {
			return false;
		}
	}

}