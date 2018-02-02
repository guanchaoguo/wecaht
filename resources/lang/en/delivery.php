<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/4/5
 * Time: 11:17
 * 交收相关错误提示
 */
return [
    'success'   => 'Success',
    'fails'     => 'Operation failed',
    'menu_not_exist'    => 'The menu does not exist',
    'empty_list'    => ' Database list is empty',
    'data_error'    => 'Error in data',

    'issue' => [
        'required'  => 'Issue  can\'t be empty',
        'numeric'   => 'Issue only for numeric types'
    ],
    'start_date'    => [
        'required'  => 'The start time cannot be empty',
    ],
    'end_date'    => [
        'required'  => 'The end time cannot be empty',
        'le_start'  => 'End time can not be less than or equal to start time',
        'has_been'  => 'In the period of time has been occupied',
    ],

    'issue_exist'   => 'Issue is already',
];