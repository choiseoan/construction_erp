<?php
namespace App\Chung;

use Func;
use Log;
use DB;
use Cache;
use stdClass;
use Illuminate\Support\Collection;

class Decrypter
{
    /**
     * 전체 암호화 대상 컬럼
     *
     * @var array
     */
    public $arrayEncCol;

    public function __construct()
	{
        $this->arrayEncCol = self::getArrayEncCol();
    }

    // 테이블별로 해당되는 컬럼을 묶어서 배열화
    public static function getArrayEncCol()
    {
        $arrayEncCol = Array();
        $sch = "public";
        $conn = "pgsql";

        $columns = Cache::remember('DB_columns_'.$sch, 86400, function() use ($sch, $conn)
        {
            $columns = DB::connection($conn)->select("
                select 
                    table_name, column_name, data_type, character_maximum_length 
                from 
                    information_schema.columns 
                where 
                    character_maximum_length = 999
                    and table_schema = '".$sch."'
                order by table_name
            ");
            return $columns;
        });
        
        foreach($columns as $object)
        {
            $arrayEncCol[$object->table_name][] = $object->column_name;
        }
        return $arrayEncCol;
    }

   /**
 	* DB 결과 row 단위로 복호화 해주는 함수.
 	*
 	* @param  object  $arrayObj        row값 객체
    * @param  array  $arrayTable      복호화할 테이블 배열(1개일때는 string으로 넘겨도 됨)
 	* @return object
 	*/
    public static function row($arrayObj, $arrayTable)
    {
        $obj = new Decrypter();
        $arrayAllCol = $obj->arrayEncCol;

        if(!is_array($arrayTable))
            $arrayTable = array($arrayTable);

        // 값 체크를 위한 배열변환(object일때 값이 없으면 에러가 난다.)
        $arrayVal = (array) $arrayObj;
        $arrAll = [];

    	foreach($arrayTable as $table)
    	{
    		if(!isset($arrayAllCol[strtolower($table)])) continue;
			$arrayCol = $arrayAllCol[strtolower($table)];

    		// 암호화 대상 컬럼이 있는지 확인.
    		if(is_array($arrayCol))
    		{
    			foreach($arrayCol as $key)
    			{   
    				if(isset($arrayVal[$key]) && $arrayVal[$key])
    				{
                        // 해당컬럼이 이미 복호화 완료된 배열에 존재하면 복호화 하지 않는다. 2021-12-08 helen
						if(in_array($key, $arrAll)) continue;

                        if(gettype($arrayObj)=="array")
                        {
                            $arrayObj[$key] = Func::decrypt($arrayObj[$key], 'ENC_KEY_SOL');
                            
                        }
                        else
                        {
                            $arrayObj->$key = Func::decrypt($arrayObj->$key, 'ENC_KEY_SOL');                        }
                        $arrAll[] = $key;
    				}
    			}
    		}
    	}
    	return $arrayObj;
    }

    /**
 	* 쿼리빌더 GET(COLLECT)로  들어올때 복호화 하는 함수
 	*
 	* @param  object  $collect       get으로 가져온 collection
    * @param  array   $arrayTable      복호화할 테이블 배열(1개일때는 string으로 넘겨도 됨)
    * @param  string  $ToArr         배열화할껀지 여부
 	* @return object
 	*/
     public static function get($collect,$arrayTable,$ToArr='')
     {
        $obj = new Decrypter();
        $tmp_arr = [];

        for($i=0; $i<count($collect); $i++)
        {
            $tmp_arr[$i] = $obj->all($collect[$i],$arrayTable);
        }
        $tmp_collect = collect($tmp_arr);

        if($ToArr=='Y')
        {
            $tmp_collect = $tmp_collect->toArray();
        }
        
        return $tmp_collect;
     }

    /**
 	* 암호화 컬럼 여부 함수 - 20220412 (DataList.php getListQuery() 리스트 정렬, 검색에서 암호화 대상인지 확인하는 용도로 사용 중)
 	*
 	* @param  string  $col          컬럼명       
    * @param  string  $table        테이블명
 	* @return bool
 	*/
    public static function isEncCol($col, $table='')
    {
        if(Cache::has('DB_columns_public'))
        {
            $columns = Cache::get('DB_columns_public');
            foreach($columns as $object)
            {
                $arrayEncCol[$object->table_name][] = $object->column_name;
            }
        }
        else
        {
            $arrayEncCol = self::getArrayEncCol();
        }

        if(strpos($col,".")) // 관계있으면 제거 (암호화 컬럼에서 매칭이 안된다.)
        {
            $col = substr(strstr($col, '.'), 1);
        }

        // 지정 테이블이 있다면 해당 테이블 내 암호화 컬럼이 있는지
        if($table && isset($arrayEncCol[$table]))
        {
            if(in_array($col, $arrayEncCol[$table]))
            {
                return true;
            }
        }
        else
        {
            foreach($arrayEncCol as $EncCol)
            {
                if(in_array($col, $EncCol))
                {
                    return true;
                }
            }
        }

        return false;
    }

    /**
 	* DB 결과 전체를 복호화 해주는 함수.
 	*
 	* @param  object  $arrayObj        row값 객체
    * @param  array  $arrayTable      복호화할 테이블 배열(1개일때는 string으로 넘겨도 됨)
 	* @return object
 	*/
     public static function all($dataObj, $arrayTable)
     {
        $obj = new Decrypter();
        $arrayAllCol = $obj->arrayEncCol;
 
        if(!is_array($arrayTable))
        {
            $arrayTable = array($arrayTable);
        }

        if(!empty($dataObj))
        {
            foreach($arrayTable as $tblName)
            {
                $tblName = strtolower($tblName);
                if(array_key_exists($tblName, $arrayAllCol))
                {
                    foreach($arrayAllCol[$tblName] as $columnName) 
                    {
                        $array_all_col[] = $columnName;
                    }
                }
            }
            
            if(isset($array_all_col))
            {
                foreach($dataObj as $key => $val)
                {
                    // get() 으로 넘어온 데이터는 $dataObj[] = (object) 의 형태이다.
                    if(is_numeric($key) && is_object($val))
                    {
                        foreach($val as $key2 => $val2)
                        {
                            // 암호화 대상 컬럼이면 복호화 반영
                            if( in_array(strtolower($key2), $array_all_col)) $val->$key2 = Func::decrypt($val2, 'ENC_KEY_SOL');
                        }

                        $dataObj[$key] = $val;
                    }
                    // first() 으로 넘어온 데이터는 (object) 의 형태이다.
                    else
                    {
                        // 암호화 대상 컬럼이면 복호화 반영
                        if( in_array(strtolower($key), $array_all_col)) $dataObj->$key = Func::decrypt($val, 'ENC_KEY_SOL');
                    }
                }
            }
        }
        
        return $dataObj;
    }
    
}
