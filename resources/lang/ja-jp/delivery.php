<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/4/5
 * Time: 11:17
 * 交收相关错误提示
 */
return [
    'success'   => '成功した操作',
    'fails'     => '操作が失敗しました',
    'issue_not_exist'    => 'データは存在しません。',
    'empty_list'    => 'データリストは空です',
    'data_error'    => 'データエラー',

    'issue' => [
        'required'  => '期間は空にすることはできません',
        'numeric'   => '唯一のデジタルタイプの期間'
    ],
    'start_date'    => [
        'required'  => '開始時間は、空にすることはできません',
    ],
    'end_date'    => [
        'required'  => '終了時刻は、空にすることはできません',
        'le_start'  => '終了時刻は、開始時刻に等しいより小さくすることはできません',
        'has_been'  => '時間は段落に占領されています',
    ],

    'issue_exist'   => '分割払い名はすでに存在しています',
];