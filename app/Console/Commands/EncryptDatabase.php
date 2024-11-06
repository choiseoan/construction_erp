<?php

namespace App\Console\Commands;

use App\Models\ExcelImport;
use Illuminate\Console\Command;
use DB;
use Func;
use Log;
use Excel;
use EncVars;

class EncryptDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DB:Encrypt {tableName?} {pk?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'DB 암호화대상 인크립트';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    private $tableName;
    private $pk;

    public function handle()
    {
        $this->tableName = $this->argument('tableName');
        $this->pk = $this->argument('pk');
        $arrPK = explode(",", $this->pk);

        if(!array_key_exists($this->tableName, EncVars::$arrayAllCol))
        {
            echo "암호화대상 테이블 아님 => ".$this->tableName;
            exit;
        }

        $rs = DB::TABLE($this->tableName)->orderBy($this->pk)->get();
        $arrTarget = array();
        $arrWhere = array();
        foreach($rs as $v)
        {
            unset($arrTarget);
            unset($arrWhere);
            foreach(EncVars::$arrayAllCol[$this->tableName] as $column)
            {
                $arrTarget[$column] = $v->$column;
            }

            foreach($arrPK as $column)
            {
                $arrWhere[$column] = $v->$column;
            }

            DB::dataProcess('UPD', $this->tableName, $arrTarget, $arrWhere, $v->no);
        }
    }
}