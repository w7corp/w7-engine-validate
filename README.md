<div align="center">
    <h1>微擎表单验证</h1>
    <img src="https://icon.itwmw.com/badge/dynamic/json?label=&query=star&url=https%3A%2F%2Fwww.itwmw.com%2Ftools%2Fgitee.php%3Fname%3Dwe7coreteam%2Fw7-engine-validate&logo=gitee&logoColor=fff&labelColor=c72e34&suffix=%20Stars&color=383d48" alt="Stars"/>
    <img src="https://icon.itwmw.com/badge/dynamic/json?label=&query=fork&url=https%3A%2F%2Fwww.itwmw.com%2Ftools%2Fgitee.php%3Fname%3Dwe7coreteam%2Fw7-engine-validate&logo=gitee&logoColor=fff&labelColor=c72e34&suffix=%20Forks&color=383d48" alt="Forks"/>
    <div>
        <img src="https://icon.itwmw.com/badge/License-Apache--2.0-blue" alt="License" />
        <img src="https://icon.itwmw.com/badge/PHP-%5E7.2.5%7C%5E8.0%7C%5E8.1-blue?logo=php&logoColor=violet" alt="PHP Version Support" />
        <img src="https://app.fossa.com/api/projects/custom%2B27665%2Fgitee.com%2Fwe7coreteam%2Fw7-engine-validate.svg?type=shield" alt="FOSSA Status"/>
        <img src="https://icon.itwmw.com/badge/Test%20Coverage-100%25-brightgreen" alt="Tests" />
        <img src="https://icon.itwmw.com/packagist/dt/w7/engine-validate?style=social&logo=packagist" alt="Download" />
    </div>
</div>

## 介绍
一个让你的表单验证更为方便，快捷，安全的扩展，满足你的一切验证需求。

## 目录
- [验证器](https://v.neww7.com/3/Validate.html)
- [验证场景](https://v.neww7.com/3/Scene.html)
- [场景事件](https://v.neww7.com/3/Event.html)
- [规则管理器](https://v.neww7.com/3/RuleManager.html)
- [自定义验证规则](https://v.neww7.com/3/Rule.html)
- [自定义消息](https://v.neww7.com/3/Message.html)
- [默认值](https://v.neww7.com/3/Default.html)
- [过滤器](https://v.neww7.com/3/Filter.html)
- [验证集合](https://v.neww7.com/3/Collection.html)

## 安装
使用composer命令
``` shell
composer require w7/engine-validate
```

完整文档查看[完整文档](https://v.neww7.com)

# 验证器
## 简单验证
支持简单定义一个验证器并进行验证：
```php
try {
    $data = Validate::make([
        'user' => 'required|email',
        'pass' => 'required|lengthBetween:6,16',
    ], [
        'user.required'      => '请输入用户名',
        'user.email'         => '用户名格式错误',
        'pass.required'      => '请输入密码',
        'pass.lengthBetween' => '密码长度为6~16位',
    ])->check($data);
} catch (ValidateException $e) {
    echo $e->getMessage();
}
```
如果验证通过，则返回所有通过验证的值，如未通过，则抛出一个`W7\Validate\Exception\ValidateException`异常

## 验证器定义
为具体的验证场景或者数据表单定义验证器类，我们需要继承`W7\Validate\Validate`类，然后实例化后直接调用验证类的`check`方法即可完成验证，下面是一个例子：

我们定义一个`LoginValidate`验证器类用于登录的验证。
```php
class LoginValidate extends Validate
{
    protected $rule = [
        'user' => 'required|email',
        'pass' => 'required|digits_between:6,16',
    ];
    
    protected $message = [
        'user.required'       => '请输入用户名',
        'user.email'          => '用户名格式错误',
        'pass.required'       => '请输入密码',
        'pass.digits_between' => '密码长度为6~16位',
    ];
}

```

> <div style="padding-top:3px;color:#42b983">类属性定义的错误消息，优先级要高于自定义规则中的默认回复，高于自定义规则方法返回的错误</div>

## 数据验证
``` php
$data = [
    'user' => '123@qq.com',
    'pass' => ''
];
$validate = new LoginValidate();
$validate->check($data);
```
此时会抛出一个`W7\Validate\Exception\ValidateException`异常，message为`请输入密码`
``` php
$data = [
    'user' => '123@qq.com',
    'pass' => '123456'
];
$validate = new LoginValidate();
$data = $validate->check($data);
```
验证成功，并返回通过验证的值，返回的值为数组类型

## 验证数组
验证表单的输入为数组的字段也不难。你可以使用 「点」方法来验证数组中的属性。例如，如果传入的 HTTP 请求中包含`search[keyword]`字段， 可以如下验证：
``` php
protected $rule = [
    'search.order'   => 'numeric|between:1,2',
    'search.keyword' => 'chsAlphaNum',
    'search.recycle' => 'boolean',
];
```
你也可以验证数组中的每个元素。例如，要验证指定数组输入字段中的每一个 id 是唯一的，可以这么做：
``` php
protected $rule = [
    'search.*.id' => 'numeric|unique:account'
];
```
数组规则的错误消息的定义也一样
``` php 
protected $message = [
    'search.order.numeric'       => '排序参数错误',
    'search.order.between'       => '排序参数错误',
    'search.keyword.chsAlphaNum' => '关键词只能包含中文，字母，数字',
    'search.recycle.boolean'     => '参数错误：recycle',
];
```
## 验证器类属性
### $rule
用户定义验证器的验证规则，也可以通过`setRules`方法来进行设置，此方法为叠加，如果参数为`null`则为清空全部规则
```php
// 类中定义
protected $rule = [
    'user' => 'required'
];

// 使用方法定义
$v->setRules([
    'user' => 'required'
]);
```
### $message
用户定义验证器的错误信息，也可以通过`setMessages`方法来进行设置，此方法为叠加，如果参数为`null`则为清空全部错误消息
```php
// 类中定义
protected $message = [
    'user.required' => '账号必须填写'
];

// 使用方法定义
$v->setMessages([
    'user.required' => '账号必须填写'
]);
```
### $scene
定义验证场景的数据，用于指定验证场景对应的验证字段等，详细用法查看[验证场景](https://v.neww7.com/3/Scene.html)一节,同样也可以通过`setScene`方法来进行设置，此方法为叠加，如果参数为`null`则为清空全部验证场景
```php
// 类中定义
protected $scene = [
    'login' => ['user', 'pass']
];

// 使用方法定义
$v->setScene([
    'login' => ['user', 'pass']
]);
```
### $event
定义此验证器下的全局事件，详细用法查看[事件](https://v.neww7.com/3/Event.html)一节
```php
protected $event = [
    CheckSiteStatus::class
];
```
### $customAttributes
定义验证字段的名称,也可以通过`setCustomAttributes`方法来进行设置，此方法为叠加，如果参数为`null`则为清空全部字段名称，
错误消息中的[:attribute](https://v.neww7.com/3/Message.html#attribute)会使用下面的值对应的替换
```php
protected $customAttributes = [
    'user' => '账号',
    'pass' => '密码'
];
```
### $default
定义字段的默认值
```php
protected $default = [
    'name' => '张三'
];
```
关于默认值的详情请查看[默认值](https://v.neww7.com/3/Default.html)一节
### $filter
用于数据验证后处理数据
```php
protected $filter = [
    'name' => 'trim'
];
```
关于过滤器的详情请查看[过滤器](https://v.neww7.com/3/Filter.html)一节
### $bail
是否首次验证失败后停止运行,如果此属性值为`true`,所有规则会自动增加`bail`规则，默认为`true`
```php
protected $bail = true;
```
### $filled
所有验证的字段在存在时不能为空，如果此属性值为`true`,所有规则会自动增加`filled`规则，默认为`true`

当出现以下情况时，不会自动添加`filled`规则
- 验证规则中存在`filled`, `nullable`, `accepted`, `present`,`required`, `required_if`, `required_unless`, `required_with`,`required_with_all`, `required_without`, `required_without_all`规则
- 验证规则存在[extendImplicit](https://v.neww7.com/3/Rule.html#extendimplicit-隐式扩展)定义的规则
- 验证规则实现了[ImplicitRule](https://v.neww7.com/3/Rule.html#implicitrule-隐式规则对象)标记接口
```php
protected bool $filled = true;
```
### $regex
预定义正则表达式验证规则,详情查看[正则表达式规则](https://v.neww7.com/3/Rule.html#%E6%AD%A3%E5%88%99%E8%A1%A8%E8%BE%BE%E5%BC%8F%E8%A7%84%E5%88%99)
```php
protected $regex = [
    'number' => '/^\d+$/'
];
```