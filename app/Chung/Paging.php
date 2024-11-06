<?php
namespace App\Chung;

use Log;
use DB;


/**
 * 페이징 처리하기 위한 클래스
 */
class Paging
{
    private
    $page,
    $total_page,
    $first_page,
    $last_page,
    $prev_page,
    $next_page,
    $total_block,
    $next_block,
    $next_block_page,
    $prev_block,
    $prev_block_page,
    $limit_idx,
    $page_set, 
    $total_cnt,
    $add_infos;
    
    /**
     * 페이징 처리
     *
     * @param DB $queryBuilder          쿼리빌더
     * @param integer $now_page         현재페이지
     * @param integer $num_per_page     보여줄 데이터 수
     * @param integer $num_per_block    보여줄 페이지 수
     * @param integer $cookieName       리스트별 보여줄 갯수 쿠키로 저장
     * @param integer $count       Group BY 사용 후 count 출력 변경
     */
    public function __construct($queryBuilder, $now_page = 1, $num_per_page=10, $num_per_block=10, $cookieName='', $group_cnt='', $sum_column=[], $pageCnt='')
    {
        // 초기값
        if( !$num_per_page || $num_per_page==0 || !is_numeric($num_per_page) )
        {
            $num_per_page = 10;
        }
        // 최대갯수 설정
        else if($num_per_page>1000)
        {
            $num_per_page = 1000;  
        }

        $this->page_set = $num_per_page;    // 한페이지 줄수
        $block_set = $num_per_block;        // 한페이지 블럭수

        if( is_array($sum_column) && sizeof($sum_column)>0 )
        {
            $ainfo = "";
            $queryBuilder2 = clone $queryBuilder;
            $SELECT_RAW = "COUNT(*) AS CNT";
            foreach( $sum_column as $i => $aggs )
            {
                $SELECT_RAW.= ", ".$aggs[0]." AS SUM".$i;
            }
            $pgrst = (Array) $queryBuilder2->SELECT(DB::raw($SELECT_RAW))->reorder()->FIRST();
            $total = $pgrst['cnt'];
            foreach( $sum_column as $i => $aggs )
            {
                if( $aggs[2]=="원" )
                {
                    $sum_val = number_format($pgrst['sum'.$i]);
                }
                else
                {
                    $sum_val = $pgrst['sum'.$i];
                }
                if( $aggs[1]!="" )
                {
                    $ainfo.= " / ".$aggs[1]." : ".$sum_val.$aggs[2];
                }
                else
                {
                    $ainfo.= " / ".$sum_val.$aggs[2];
                }
            }
        }
        else
        {
            $total = $queryBuilder->count();    // 전체글수
            $ainfo = "";
        }
        if(!empty($pageCnt))
        {
            $total = $pageCnt;
        }
        if(!empty($group_cnt))
        {
            $total = $group_cnt;
        }
        

        $this->total_cnt = $total;
        $this->add_infos = $ainfo;

        // 한페이지 갯수 설정
        if(!empty($cookieName))
        {
            // 30일 지정
            setcookie($cookieName, $num_per_page, time()+(60*60*24*30));
        }
        if($total>0)
        {
            $this->total_page = ceil($total / $this->page_set);             // 총페이지수(올림함수)
        }
        $this->total_block = ceil($this->total_page / $block_set);          // 총블럭수(올림함수)
    
        $this->page = $now_page ? $now_page : 1;                            //파라미터로 현재 페이지정보를 받아옴
        $block = ceil($this->page/$block_set);                              // 현재블럭(올림함수)
        $this->limit_idx = ($this->page - 1) * $this->page_set;             // limit시작위치

        $queryBuilder->limit($this->page_set)->offset($this->limit_idx);    // 페이징 쿼리
    
        $this->first_page = (($block - 1) * $block_set) + 1;                // 첫번째 페이지번호
        $this->last_page = min ($this->total_page, $block * $block_set);    // 마지막 페이지번호

        $this->prev_page = $this->page - 1; // 이전페이지
        $this->next_page = $this->page + 1; // 다음페이지
    
        $this->prev_block = $block - 1; // 이전블럭
        $this->next_block = $block + 1; // 다음블럭
    
        // 전 블럭 마지막 페이지
        $this->prev_block_page = $this->prev_block * $block_set; // 이전블럭 페이지번호    
    
        // 전 블럭 첫 페이지
        // $this->prev_block_page = $this->prev_block * $block_set - ($block_set - 1);
    
        $this->next_block_page = $this->next_block * $block_set - ($block_set - 1); // 다음블럭 페이지번호
    }
    

    /**
     * 페이징 설정값 반환
     *
     * @return Array
     */
    public function getPagingConfig() {
        return [
            'page'              => $this->page,
            'total_page'        => $this->total_page,
            'first_page'        => $this->first_page,
            'last_page'         => $this->last_page,
            'prev_page'         => $this->prev_page,
            'next_page'         => $this->next_page,
            'total_block'       => $this->total_block,
            'next_block'        => $this->next_block,
            'next_block_page'   => $this->next_block_page,
            'prev_block'        => $this->prev_block,
            'prev_block_page'   => $this->prev_block_page,
            'limit_idx'         => $this->limit_idx,
            'page_set'          => $this->page_set,
        ];
    }


    /**
     * 페이징 HTML 반환
     *
     * @param Integer $total 쿼리빌더->count() 결과 
     * @param String $list_url ajax url 옵션 주소
     * @param String $from_name form id 값 
     * @return String
     */
    public function getPagingHtml($list_url, $form_name = '')
    {
        // href=\"javascript:getDataList('".$listName."', ".$i.", '".$list_url."', $('".$form_name."').serialize());\"
        // $page_string[] = "<li class='".$style."'><a href=\"javascript:getDataList('".$listName."', ".$i.", '".$list_url."', $('".$form_name."').serialize());\">".$i."</a></li>";
        $total = $this->total_cnt;

        $list_url = '/'.$list_url;

		$listName = $form_name;

        if(!$form_name)
        {
			$form_name = 'form';
        }
        else
        {
			$form_name = '#form_'.$form_name;
        }

        $str = "<ul class='pagination pagination-sm float-right' id='".$listName."' >";
        
        if ($this->prev_block > 0) 
        {
            $str .= "<li class='page-item'><a class='page-link' href=\"javascript:getDataList('".$listName."', ".$this->prev_block_page.", '".$list_url."', $('".$form_name."').serialize());\">&laquo;</a></li> ";
        }
        else
        {
            $str .= '<li class="page-item disabled"><a class="page-link">&laquo;</a></li> ';
            
        }
        
        for ($i=$this->first_page; $i<=$this->last_page; $i++) {
            if ($i != $this->page)
            {
                $str .= "<li class='page-item'><a class='page-link' href=\"javascript:getDataList('".$listName."', ".$i.", '".$list_url."', $('".$form_name."').serialize());\">".$i."</a></li> ";
            }
            else
            {
                $str .= '<li class="page-item active"><a class="page-link">'.$i.'</a></li> ';
            }
        }
        
        if ($this->next_block <= $this->total_block)
        {
            $str .= "<li class='page-item'><a class='page-link' href=\"javascript:getDataList('".$listName."', ".$this->next_block_page.", '".$list_url."', $('".$form_name."').serialize());\">&raquo;</a></li>";
        }
        else
        {
            $str .= '<li class="page-item disabled"><a class="page-link">&raquo;</a></li> ';
        }
        
        $str .= '</ul>';
        $str .= "<span class='float-right pt-1 pr-2'>검색 : ".number_format($total)."건";
        if( $this->add_infos!="" )
        {
            $str .= $this->add_infos;
        }
        $str .= "</span>";
        
        return $str;
    }

    public function getTotalCnt()
    {
        return $this->total_cnt;
    }
}
?>