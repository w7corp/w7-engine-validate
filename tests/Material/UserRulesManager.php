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

use W7\Validate\RuleManager;
use W7\Validate\Support\Concerns\SceneInterface;

class UserRulesManager extends RuleManager
{
    protected $rule = [
        'user'    => 'required|email',
        'pass'    => 'required|lengthBetween:6,16',
        'name'    => 'required|chs|lengthBetween:2,4',
        'remark'  => 'required|alpha_dash',
        'captcha' => 'required|length:4|checkCaptcha',
    ];

    protected $scene = [
        'login'   => ['user', 'pass'],
        'captcha' => ['captcha']
    ];

    protected $customAttributes = [
        'user'    => '用户名',
        'pass'    => '密码',
        'name'    => '昵称',
        'remark'  => '备注',
        'captcha' => '验证码',
    ];

    protected $message = [
        'captcha.checkCaptcha' => '验证码错误',
        'user.email'           => '用户名必须为邮箱',
        'pass.lengthBetween'   => '密码长度错误'
    ];

    protected function sceneRegister(SceneInterface $scene)
    {
        return $scene->only(['user', 'pass', 'name', 'remark'])
            ->remove('remark', 'required|alpha_dash')
            ->append('remark', 'chs');
    }

    protected function sceneRegisterNeedCaptcha(SceneInterface $scene)
    {
        return $this->sceneRegister($scene)->appendCheckField('captcha');
    }

    public function ruleCheckCaptcha($att, $value): bool
    {
        return true;
    }
}
