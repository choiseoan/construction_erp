<?php

namespace Illuminate\Support\Facades;

use Func;
use Cache;
use Log;
use Str;

/**
 * @method static \Illuminate\Database\Connection connection(string|null $name = null)
 * @method static void registerDoctrineType(string $class, string $name, string $type)
 * @method static void purge(string|null $name = null)
 * @method static void disconnect(string|null $name = null)
 * @method static \Illuminate\Database\Connection reconnect(string|null $name = null)
 * @method static mixed usingConnection(string $name, callable $callback)
 * @method static string getDefaultConnection()
 * @method static void setDefaultConnection(string $name)
 * @method static string[] supportedDrivers()
 * @method static string[] availableDrivers()
 * @method static void extend(string $name, callable $resolver)
 * @method static void forgetExtension(string $name)
 * @method static array getConnections()
 * @method static void setReconnector(callable $reconnector)
 * @method static \Illuminate\Database\DatabaseManager setApplication(\Illuminate\Contracts\Foundation\Application $app)
 * @method static void macro(string $name, object|callable $macro)
 * @method static void mixin(object $mixin, bool $replace = true)
 * @method static bool hasMacro(string $name)
 * @method static void flushMacros()
 * @method static mixed macroCall(string $method, array $parameters)
 * @method static void useDefaultQueryGrammar()
 * @method static void useDefaultSchemaGrammar()
 * @method static void useDefaultPostProcessor()
 * @method static \Illuminate\Database\Schema\Builder getSchemaBuilder()
 * @method static \Illuminate\Database\Query\Builder table(\Closure|\Illuminate\Database\Query\Builder|string $table, string|null $as = null)
 * @method static \Illuminate\Database\Query\Builder query()
 * @method static mixed selectOne(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method static mixed scalar(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method static array selectFromWriteConnection(string $query, array $bindings = [])
 * @method static array select(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method static \Generator cursor(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method static bool insert(string $query, array $bindings = [])
 * @method static int update(string $query, array $bindings = [])
 * @method static int delete(string $query, array $bindings = [])
 * @method static bool statement(string $query, array $bindings = [])
 * @method static int affectingStatement(string $query, array $bindings = [])
 * @method static bool unprepared(string $query)
 * @method static array pretend(\Closure $callback)
 * @method static void bindValues(\PDOStatement $statement, array $bindings)
 * @method static array prepareBindings(array $bindings)
 * @method static void logQuery(string $query, array $bindings, float|null $time = null)
 * @method static void whenQueryingForLongerThan(\DateTimeInterface|\Carbon\CarbonInterval|float|int $threshold, callable $handler)
 * @method static void allowQueryDurationHandlersToRunAgain()
 * @method static float totalQueryDuration()
 * @method static void resetTotalQueryDuration()
 * @method static void reconnectIfMissingConnection()
 * @method static \Illuminate\Database\Connection beforeExecuting(\Closure $callback)
 * @method static void listen(\Closure $callback)
 * @method static \Illuminate\Contracts\Database\Query\Expression raw(mixed $value)
 * @method static bool hasModifiedRecords()
 * @method static void recordsHaveBeenModified(bool $value = true)
 * @method static \Illuminate\Database\Connection setRecordModificationState(bool $value)
 * @method static void forgetRecordModificationState()
 * @method static \Illuminate\Database\Connection useWriteConnectionWhenReading(bool $value = true)
 * @method static bool isDoctrineAvailable()
 * @method static bool usingNativeSchemaOperations()
 * @method static \Doctrine\DBAL\Schema\Column getDoctrineColumn(string $table, string $column)
 * @method static \Doctrine\DBAL\Schema\AbstractSchemaManager getDoctrineSchemaManager()
 * @method static \Doctrine\DBAL\Connection getDoctrineConnection()
 * @method static \PDO getPdo()
 * @method static \PDO|\Closure|null getRawPdo()
 * @method static \PDO getReadPdo()
 * @method static \PDO|\Closure|null getRawReadPdo()
 * @method static \Illuminate\Database\Connection setPdo(\PDO|\Closure|null $pdo)
 * @method static \Illuminate\Database\Connection setReadPdo(\PDO|\Closure|null $pdo)
 * @method static string|null getName()
 * @method static string|null getNameWithReadWriteType()
 * @method static mixed getConfig(string|null $option = null)
 * @method static string getDriverName()
 * @method static \Illuminate\Database\Query\Grammars\Grammar getQueryGrammar()
 * @method static \Illuminate\Database\Connection setQueryGrammar(\Illuminate\Database\Query\Grammars\Grammar $grammar)
 * @method static \Illuminate\Database\Schema\Grammars\Grammar getSchemaGrammar()
 * @method static \Illuminate\Database\Connection setSchemaGrammar(\Illuminate\Database\Schema\Grammars\Grammar $grammar)
 * @method static \Illuminate\Database\Query\Processors\Processor getPostProcessor()
 * @method static \Illuminate\Database\Connection setPostProcessor(\Illuminate\Database\Query\Processors\Processor $processor)
 * @method static \Illuminate\Contracts\Events\Dispatcher getEventDispatcher()
 * @method static \Illuminate\Database\Connection setEventDispatcher(\Illuminate\Contracts\Events\Dispatcher $events)
 * @method static void unsetEventDispatcher()
 * @method static \Illuminate\Database\Connection setTransactionManager(\Illuminate\Database\DatabaseTransactionsManager $manager)
 * @method static void unsetTransactionManager()
 * @method static bool pretending()
 * @method static array getQueryLog()
 * @method static void flushQueryLog()
 * @method static void enableQueryLog()
 * @method static void disableQueryLog()
 * @method static bool logging()
 * @method static string getDatabaseName()
 * @method static \Illuminate\Database\Connection setDatabaseName(string $database)
 * @method static \Illuminate\Database\Connection setReadWriteType(string|null $readWriteType)
 * @method static string getTablePrefix()
 * @method static \Illuminate\Database\Connection setTablePrefix(string $prefix)
 * @method static \Illuminate\Database\Grammar withTablePrefix(\Illuminate\Database\Grammar $grammar)
 * @method static void resolverFor(string $driver, \Closure $callback)
 * @method static mixed getResolver(string $driver)
 * @method static mixed transaction(\Closure $callback, int $attempts = 1)
 * @method static void beginTransaction()
 * @method static void commit()
 * @method static void rollBack(int|null $toLevel = null)
 * @method static int transactionLevel()
 * @method static void afterCommit(callable $callback)
 *
 * @see \Illuminate\Database\DatabaseManager
 */
class DB extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'db';
    }

    /**
     * INSERT OR UPDATE OR UPSERT
     * 
     * @param  $mode    = ['INS','UPD','UST','DEL']
     * @param  $table   = 테이블명
     * @param  $dataRaw = Request OR Array
     * @param  $whereArray = Request OR Array / $mode가 UPD, UST 인 경우, 본 배열이 등록되지 않으면 PK정보를 읽어 조건절을 생성한다.
     * @return string   = ['E','Y','N']
     * 
     * INS = INSERT
     * UPD = UPDATE
     * UST = UPSERT ( INSERT OR UPDATE )
     * DEL = DELETE
     * 
     * E = 오류( 쿼리를 수행하기에 적합하지 않음 )
     * Y = 쿼리성공
     * N = 쿼리실패
     */
    public static function dataProcess($mode, $table, $dataRaw, $whereArray=null, &$no=null)
    {
        // Cache::flush();
        // 스키마 .env에서 가져온다. config:cache된 경우 env()로는 읽어오지 못함. config/app.php 에 등록하고 config로 가져온다.
        $sch = config('app.sche');
        $tab = strtolower($table);
        
        $dataArray = ( is_array($dataRaw) ) ? $dataRaw : $dataRaw->all();
        if( !is_array($dataArray) || ( $mode!='INS' && $mode!='UPD' && $mode!='UST' && $mode!='DEL') )
        {
            return "E";
        }
                
        $dataArray = array_change_key_case($dataArray, CASE_UPPER);
        $dataVals  = [];
        
        // PK COLUMNS
        $pk_cols = Cache::remember('DB_pkcols_'.$sch.'_'.$tab, 86400, function() use ($sch, $tab)
        {
            //$pk_colm = DB::select('SELECT colnames FROM SYSCAT.INDEXES WHERE tabschema=:sch AND tabname=:tab and uniquerule=:unq', Array('sch'=>$sch,'tab'=>$tab,'unq'=>'P'))[0] ?? null;
            $pk_colm = DB::select('SELECT array_to_string(array_agg(distinct CC.COLUMN_NAME), \'+\') AS colnames FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS TC ,INFORMATION_SCHEMA.CONSTRAINT_COLUMN_USAGE CC WHERE TC.TABLE_SCHEMA = :sch AND TC.TABLE_NAME = :tab AND TC.CONSTRAINT_TYPE = :unq AND TC.TABLE_CATALOG = CC.TABLE_CATALOG AND TC.TABLE_SCHEMA = CC.TABLE_SCHEMA AND TC.TABLE_NAME = CC.TABLE_NAME AND TC.CONSTRAINT_NAME = CC.CONSTRAINT_NAME', Array('sch'=>$sch,'tab'=>$tab,'unq'=>'PRIMARY KEY'))[0] ?? null;
            if (isset($pk_colm)) {
                $pk_cols = explode("+", strtoupper($pk_colm->colnames));
            } else {
                $pk_cols = array();
            }
            return $pk_cols;
        });
        // TAB COLUMNS
        $columns = Cache::remember('DB_columns_'.$sch.'_'.$tab, 86400, function() use ($sch, $tab)
        {
            if(Str::substr($tab,0,4)=='nsf_')
            {
                $columns = DB::connection('nsf')->select("SELECT COLUMN_NAME as colname, DATA_TYPE as typename, character_maximum_length as length, is_nullable as nulls FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA= :sch AND TABLE_NAME = :tab ORDER BY ORDINAL_POSITION", Array('sch'=>$sch,'tab'=>$tab));
            }
            else
            {
                $columns = DB::select("SELECT COLUMN_NAME as colname, DATA_TYPE as typename, character_maximum_length as length, is_nullable as nulls FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA= :sch AND TABLE_NAME = :tab ORDER BY ORDINAL_POSITION", Array('sch'=>$sch,'tab'=>$tab));
            }
            return $columns;
        });
        
        foreach( $columns as $col )
        {
            
            $col->colname = strtoupper($col->colname);
            
            if( array_key_exists($col->colname, $dataArray) )
            {
                //금액 , 제거 , 형변환                  
                if($col->typename=='integer' || $col->typename=='bigint' || $col->typename=='smallint')
                {
                    $dataVals[$col->colname] = (int) str_replace(",","",$dataArray[$col->colname]);
                }
                else if($col->typename=='numeric' || $col->typename=='double precision')
                {
                    $dataVals[$col->colname] = (float) $dataArray[$col->colname];
                }
                else
                {
                    $dataVals[$col->colname] = $dataArray[$col->colname];
                }
                // // 암호화 대상 컬럼이면 암호화적용
                // if( in_array(strtolower($col->colname), $array_all_col))
                // {
                //     // 암호화 대상 컬럼 데이터 기준 평문이 나오는 상태를 찾는다.
                //     // view에서 평문으로 오는경우가 있고, 상황에 따라서는 controller에서 DB 데이터를 그대로 가져올 수 도 있을 것으로 판단됨.
                //     // 현재 문자열이 평문인지 확인방법 -> decrypt 실행시 평문은 빈값이 return 된다.
                //     // 그럴일은 없겠지만 여러횟수에 의해 암호화된 데이터도 있을 수 있으니 while로 돌린다. 보통은 기존 데이터가 암호화된(1번) 데이터이거나 평문일 것이므로 한번만 반복문이 탈 것이다.
                //     while(true)
                //     {
                //         if(Func::decrypt($dataArray[$col->colname], 'ENC_KEY_SOL')=="") break;
                //         $dataArray[$col->colname] = Func::decrypt($dataArray[$col->colname], 'ENC_KEY_SOL');  // 암호화 적용하기전 평문으로 만든다.
                //     }

                //     $dataVals[$col->colname] = Func::encrypt($dataArray[$col->colname], 'ENC_KEY_SOL');
                // }

                /* 
                    암호화
                */
                if($col->data_type = "character varying")
                {
                    if($col->length == "999")
                    {
                        $dataVals[$col->colname] = Func::encrypt($dataArray[$col->colname], 'ENC_KEY_SOL');;
                    }
                }
            }

            if( $col->colname=="REG_TIME" )
            {
                if( $mode=="INS" )
                {
                    $dataVals['REG_TIME'] = ( isset($dataArray['REG_TIME']) ) ? $dataArray['REG_TIME'] : (( isset($dataArray['SAVE_TIME']) ) ? $dataArray['SAVE_TIME'] : date("YmdHis")) ;
                }
                else
                {
                    unset($dataVals['REG_TIME']);
                }
            }
            if( $col->colname=="REG_ID" )
            {
                if( $mode=="INS" )
                {
                    $dataVals['REG_ID'] = ( isset($dataArray['REG_ID']) ) ? $dataArray['REG_ID'] : (( isset($dataArray['SAVE_ID']) ) ? $dataArray['SAVE_ID'] : "SYSTEM") ;
                }
                else
                {
                    unset($dataVals['REG_ID']);
                }
            }
        }
        $pk_vals = [];
        for( $i=0; $i<sizeof($pk_cols); $i++ )
        {
            if( $pk_cols[$i] && isset($dataVals[$pk_cols[$i]]) )
            {
                $pk_vals[$pk_cols[$i]] = $dataVals[$pk_cols[$i]];
            }
        }
        try
        {
            if( $mode=="INS" )
            {
                // SEQUENCE AUTO FILL
                if( isset($pk_cols[0]) && $pk_cols[0]=="NO" && !isset($dataVals['NO']) )  // sizeof($pk_vals)==0 && 없어도 될듯. Neo
                {
                    //$seqnm = DB::select('SELECT seqname FROM SYSCAT.SEQUENCES WHERE seqname = ? AND seqschema = ?', Array($tab."_SEQ", $sch))[0] ?? null;
                    $seqnm = DB::select('select c.relname as seqname from pg_class c join pg_namespace n on n.oid = c.relnamespace join pg_user u on u.usesysid = c.relowner where c.relkind = ? and u.usename = current_user and relname = ? ', Array("S",strtolower($tab)."_seq"))[0] ?? null;
                    if(isset($seqnm) && $seqnm->seqname )
                    {
                        //$dataVals['NO'] = DB::raw('NEXT VALUE FOR '.$tab.'_SEQ');
                        $seqno = DB::select("SELECT nextval('".$seqnm->seqname."') AS no")[0];
                        $dataVals['NO'] = $seqno->no;
                        $no = $dataVals['NO'];
                    }
                }
                if($tab=="nsf_batch_master")
                {
                    $rslt = DB::connection('nsf')->table($tab)->insert($dataVals);
                }
                else
                {
                    $rslt = DB::table($tab)->insert($dataVals);
                }
            }
            else if( $mode=="UST" )
            {
                //  JACK - $pk_vals 가 정의되지 않았을 값일 경우, Error
                if( !isset($pk_vals) || count($pk_vals)<1 )
                {
                    return "E";
                }

                $rslt = DB::table($tab)->updateOrInsert($pk_vals,$dataVals);
            }
            else if( $mode=="UPD" )
            {
                if( !$whereArray )
                {
                    //  JACK - $pk_vals 가 정의되지 않았을 경우, Error
                    if( !isset($pk_vals) || count($pk_vals)<1 )
                    {
                        return "E";
                    }

                    $whereArray = $pk_vals;
                }
                $rslt = DB::table($tab)->where($whereArray)->update($dataVals);
            }
            else if( $mode=="DEL" )
            {
                if( !$whereArray )
                {
                    return "E";
                }
                $rslt = DB::table($tab)->where($whereArray)->delete();
            }
            return "Y";
        }
        catch (QueryException $ex)
        {
            log::Debug("EXCEPTION = ".$ex->getMessage());
            return "N";
        }

    }
}
