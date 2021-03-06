<?php

/**
 * WeEngine System
 *
 * (c) We7Team 2021 <https://www.w7.cc>
 *
 * This is not a free software
 * Using it under the license terms
 * visited https://www.w7.cc for more details
 */

namespace W7\Tests\Material;

use W7\Validate\Validate;

class ArticleValidate extends Validate
{
    protected array $rule = [
        'id'      => 'required|numeric',
        'content' => 'required|between:1,2000',
        'title'   => 'required|between:4,50|alpha',
        'type'    => 'required|numeric',
    ];

    protected array $message = [
        'id.required'            => '缺少参数：文章Id',
        'id.numeric'             => '参数错误：文章Id',
        'content.required'       => '文章内容必须填写',
        'content.digits_between' => '文章长度为1~2000个字符',
        'title.required'         => '文章标题必须填写',
        'title.digits_between'   => '文章标题格式错误',
        'title.alpha'            => '文章标题长度为4~50个字符',
        'type.required'          => '文章分类必须填写',
        'type.numeric'           => '文章分类错误',
    ];
    
    protected array $scene = [
        'add'  => ['content', 'title'],
        'save' => ['use' => 'edit'],
        'del'  => ['id'],
    ];
    
    public function sceneEdit()
    {
        return $this->only(['id', 'content', 'title'])
            ->append('id', 'max:10000')
            ->remove('content', 'between')
            ->remove('title', null)
            ->append('title', 'required|between:4,50|alpha');
    }
    
    public function sceneDynamic()
    {
        return $this->only(['title', 'content'])
            ->remove('content', 'between');
    }
}
