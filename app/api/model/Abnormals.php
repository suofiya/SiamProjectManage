<?php
namespace app\api\model;


use think\Model;

class Abnormals extends Model
{
    protected $autoWriteTimestamp = 'datetime';
    protected $pk = 'ab_id';

    public static function get_list($page = 1, $limit = 10, $project_id = NULL)
    {
        if ($project_id !== NULL){
            $where[] = ['project_id', '=', $project_id];
        }else{
            $where = [];
        }
        $list  = (new static)->where($where)->page($page, $limit)->field('ab_id, project_id, ab_class, ab_date, ab_message, create_time')->select();
        $count = (new static)->where($where)->count("ab_id");

        return [
            'list'  => $list,
            'count' => $count
        ];
    }

    public static function getDetailById($id)
    {
        $abnormal = (new static)->find($id);
        return $abnormal;
    }

    public static function getStaticByDate($date, $project_id = NULL)
    {
        if ($project_id !== NULL){
            $where[] = ['project_id', '=', $project_id];
        }else{
            $where = [];
        }

        $dateArray  = explode('-', $date);
        $thisMonth7 = strtotime("{$dateArray[0]}-{$dateArray[1]}-07");

        $temString = "{$dateArray[0]}-{$dateArray[1]}-";

        if (strtotime($date) <= $thisMonth7){// 如果date 小于7号，则返回1~6号
            $queryDate = [
                $temString."01",
                $temString."02",
                $temString."03",
                $temString."04",
                $temString."05",
                $temString."06",
                $temString."07"
            ];
            $where[] = ['ab_date', 'in', $queryDate];
        }else{// date 大于7号，则取前后3天 总共7天
            $midDay = $dateArray[2];
            $queryDate = [
                $temString.($midDay - 6),
                $temString.($midDay - 5),
                $temString.($midDay - 4),
                $temString.($midDay - 3),
                $temString.($midDay - 2),
                $temString.($midDay - 1),
                $temString.$midDay
            ];
            $where[] = ['ab_date', 'in', $queryDate ];
        }

        $data = Abnormals::where($where)->field("ab_date, count(ab_id) as count")->group("ab_date")->select();

        $return = [];
        foreach ($queryDate as $query){
            $return[] = $query;
        }
        foreach ($data as $one){
            if ( ($index = array_search($one->ab_date, $return))  !== -1 ){
                $return[$index] = $one;
            }
        }
        foreach ($return as $ke => $re){
            if(is_string($re)){
                $return[$ke] = [
                    "ab_date" => $re,
                    "count" => 0
                ];
            }
        }
        return $return;
    }
}