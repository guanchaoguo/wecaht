<?php
return array (
  'title' => 
  array (
    'required' => '模板标题不能为空',
    'unique' => '模板标题已存在',
  ),
  'code' => 
  array (
    'required' => '风格代码不能为空',
    'unique' => '风格代码已存在',
  ),
  'not_exist' => '模板不存在',
);