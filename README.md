# 增强表单验证
## 介绍
一个让你的表单验证更为方便，快捷，安全的扩展，满足你的一切验证需求。

## 说明
此验证基于`illuminate/validation`做了如下扩展

- 可通过类的方式定义一个[验证器](https://v.neww7.com/3/Validate.html)
- 增加[验证场景](https://v.neww7.com/3/Scene.html)
- 增加[规则管理器](https://v.neww7.com/3/RuleManager.html)
- 增加数据[默认值](https://v.neww7.com/3/Default.html)
- 增加数据[过滤器](https://v.neww7.com/3/Filter.html)
- 增加[场景事件](https://v.neww7.com/3/Event.html)
- 修改了[自定义验证规则](https://v.neww7.com/3/Rule.html)
- [自定义消息](https://v.neww7.com/3/Message.html) 增加了对内容的引用
- 继承集合类增加一个[验证集合](https://v.neww7.com/3/Collection.html)

> 验证器支持Laravel的内置规则，内置规则文档可查看[规则文档](https://learnku.com/docs/laravel/7.x/validation/5144#c58a91)

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

# 验证场景
## 验证场景
[规则管理器](https://v.neww7.com/3/RuleManager.html)和[验证器](https://v.neww7.com/3/Validate.html)均支持定义场景，验证不同场景的数据，例如：
``` php
class ArticleValidate extends Validate
{
    protected $rule = [
        'id'      => 'required|numeric',
        'content' => 'required|digits_between:1,2000',
        'title'   => 'required|digits_between:4,50|alpha',
    ];

    protected $message = [
        'id.required'            => '缺少参数：文章Id',
        'id.numeric'             => '参数错误：文章Id',
        'content.required'       => '文章内容必须填写',
        'content.digits_between' => '文章长度为1~2000个字符',
        'title.required'         => '文章标题必须填写',
        'title.digits_between'   => '文章标题格式错误',
        'title.alpha'            => '文章标题长度为4~50个字符',
    ];
    
    protected $scene = [
        'add'  => ['content','title'],
        'edit' => ['id','content','title'],
        'del'  => ['id'],
    ];
}
```
然后可以在验证方法中使用验证的场景,使用`scene`方法指定验证场景
``` php
$data = [
    'content' => '内容',
    'title'   => '这是一个标题'
];
$validate = new ArticleValidate();
$data     = $validate->scene('add')->check($data);
```
## 验证场景复用
有时候验证场景中的字段，要求都一样的时候，可以使用`use`关键词使用验证器

> 请注意:[规则管理器](https://v.neww7.com/3/RuleManager.html)不支持验证场景复用
``` php
protected $scene = [
    'edit' => ['id','content','title'],
    'save' => ['use' => 'edit'],
];
```
``` php
$validate = new ArticleValidate();
$validate->scene('save')->check($data);
```
这里是`save`场景，实际上是用的`edit`场景字段
> <div style="padding-top:3px;">如都定义了中间件，则都生效</div>


`use`关键词也支持传入自定义方法
``` php
protected $scene = [
    'save' => ['type','use' = 'selectSaveScene'],
    'saveSetting' => ['id','name']
]


protected function useSelectSaveScene(array $data)
{
    return 'save'.$data['type'];
}
```

>自定义方法的参数
自定义方法会有一个数组类型的参数，传入的参数为第一次验证后的数据，也就是除去`use`字段的其他字段，返回字符串为使用对应的验证场景，返回数组为使用对应的验证字段

## 自定义验证场景
可以单独为某个场景定义方法（方法的命名规范是`scene`+ 首字母大写的场景名），方法提供一个[场景类](https://v.neww7.com/3/Scene.html#场景类的方法),可用于对某些字段的规则重新设置，例如：

- 在[规则管理器](https://v.neww7.com/3/RuleManager.html)中的场景类为：`W7\Validate\Support\RuleManagerScene`
- 在[验证器](https://v.neww7.com/3/Validate.html)中的场景类为：`W7\Validate\Support\ValidateScene`

> 注意
场景名在调用的时候不能将驼峰写法转为下划线


``` php
protected function sceneEdit($scene)
{
   $scene->only(['id','content','title'])
        ->append('id',"max")
        ->remove("content",'between')
        ->remove('title',null)
        ->append('title','required|between|alpha');
}
```
> 说明
`scene`验证场景在调用`check`方法以后会被重置，并不会对下一次`check`生效

## 验证字段为数组
在验证器中，支持对数组下的元素进行定义规则，在验证场景中，同样支持指定验证数组元素
规则的定义如下：
``` php
protected $rule = [
    'search.order'   => 'numeric|between:1,2',
    'search.keyword' => 'chsAlphaNum',
    'search.recycle' => 'boolean',
];
```
验证场景定义：
``` php
protected $scene = [
    'list'    =>  ['search.order','search.keyword','search.recycle']
];
```
## 场景类的方法
[验证器](https://v.neww7.com/3/Validate.html)的场景类方法说明如下：

| 方法名 | 描述 |
| --- | --- |
| [only](https://v.neww7.com/3/Scene.html#only) | 场景中需要验证的字段 |
| [remove](https://v.neww7.com/3/Scene.html#remove) | 移除场景中的字段的部分验证规则 |
| [append](https://v.neww7.com/3/Scene.html#append) | 给场景中的字段追加验证规则 |
| [sometimes](https://v.neww7.com/3/Scene.html#sometimes) | 复杂条件验证|
| [event](https://v.neww7.com/3/Scene.html#event)|指定事件处理类|
| [getData](https://v.neww7.com/3/Scene.html#getdata)|获取当前验证的数据|
| [getValidateData](https://v.neww7.com/3/Scene.html#getvalidatedata)|作用同[getData](https://v.neww7.com/3/Scene.html#getdata)，区别在于，此方法返回一个[验证器集合](https://v.neww7.com/3/Collection.html)类|
| [before](https://v.neww7.com/3/Scene.html#before)|添加一个验证前的需要执行的方法|
| [after](https://v.neww7.com/3/Scene.html#after)|添加一个验证后需要执行的方法|
| [appendCheckField](https://v.neww7.com/3/Scene.html#appendcheckfield)|添加一个需要验证的字段|
| [removeCheckField](https://v.neww7.com/3/Scene.html#removecheckfield)|删除一个需要验证的字段|
| [setEventPriority](https://v.neww7.com/3/Scene.html#seteventpriority)|设置事件和简易事件的优先级|
| [default](https://v.neww7.com/3/Scene.html#default)| 设置或取消一个字段的默认值|
| [filter](https://v.neww7.com/3/Scene.html#filter)|设置或取消一个字段的过滤器|

[规则管理器](https://v.neww7.com/3/RuleManager.html)的场景类方法说明如下：

| 方法名 | 描述 |
| --- | --- |
| [only](https://v.neww7.com/3/Scene.html#only) | 场景中需要验证的字段 |
| [remove](https://v.neww7.com/3/Scene.html#remove) | 移除场景中的字段的部分验证规则 |
| [append](https://v.neww7.com/3/Scene.html#append) | 给场景中的字段追加验证规则 |
| [appendCheckField](https://v.neww7.com/3/Scene.html#appendcheckfield)|添加一个需要验证的字段|
| [removeCheckField](https://v.neww7.com/3/Scene.html#removecheckfield)|删除一个需要验证的字段|

### only
指定场景需要验证的字段
```php
public function only(array $fields): $this
```
- `$fields` 为要验证的字段数组

### remove
移除场景中的字段的部分验证规则
``` php
public function remove(string $field, string $rule = null): $this
```
- `$field` 为要移除规则的字段
- `$rule` 为要移除的规则，多个规则用`|`分割，也可填入规则数组，`$rule`如为`null`，则清空该字段下的所有规则

### append
给场景中的字段需要追加验证规则
``` php
public function append(string $field, string $rule): $this
```
- `$field` 为要追加规则的字段
- `$rule` 为要追加的规则，多个规则用`|`分割，也可填入规则数组

### sometimes
复杂条件验证

有时候你可能需要增加基于更复杂的条件逻辑的验证规则。例如，你可以希望某个指定字段在另一个字段的值超过 100 时才为必填。或者当某个指定字段存在时，另外两个字段才能具有给定的值
``` php
public function sometimes($attribute, $rules, callable $callback): $this
```
- `$attribute` 要追加规则的字段，多个可传数组
- `$rules` 要追加的规则，多个规则用`|`分割，也可填入规则数组
- `$callback` 闭包方法，如果其返回`true`， 则额外的规则就会被加入
> 参数:传入 闭包 的`$data`参数是`W7\Validate\Support\Storage\ValidateCollection`的一个实例，可用来访问你的输入或文件对象。
  

我们可以根据一个参数或多个来处理其他参数的验证条件，更灵活的实现验证：
``` php
$scene->sometimes("name","required",function ($data) {
    return $data->type === 1;
});
```

### event
指定[场景事件](https://v.neww7.com/3/Event.html)处理类，一个场景可以使用多个[场景事件](https://v.neww7.com/3/Event.html)处理类
``` php
public function event(string $handler, ...$params): $this
```
- `$handler` 处理器命名空间，可使用`:class`进行传入
- `$params` 传递给中间件构建方法的参数，不限数量
### getData
此方法用来获取当前验证的数据
```php
public function getData(string $key = '', $default = null)
```
默认获取全部验证的值
### getValidateData
此方法用来获取当前验证的数据,作用同[getData](https://v.neww7.com/3/Scene.html#getdata)，区别在于，此方法返回一个[验证器集合](https://v.neww7.com/3/Collection.html)类
```php
public function getValidateData(): ValidateCollection
```
### before
添加一个验证前的需要执行的方法，方法仅限本类的方法，方法的命名规则为`before`加方法名，方法的定义查看[简单使用](https://v.neww7.com/3/Event.html#简单使用)
```php
public function before(string $callbackName, ...$params): $this
```
- `$callbackName` 本类的方法名,不带前缀
- `$params` 要传递给方法的参数
### after
添加一个验证后需要执行的方法，方法仅限本类的方法，方法的命名规则为`after`加方法名，方法的定义查看[简单使用](https://v.neww7.com/3/Event.html#简单使用)
```php
public function after(string $callbackName, ...$params): $this
```
- `$callbackName` 本类的方法名,不带前缀
- `$params` 要传递给方法的参数
### appendCheckField
添加一个需要验证的字段，当需要根据Sql或者其他各种条件来增加一个需要验证的字段时，你就需要用到`appendCheckField`这个方法
```php
public function appendCheckField(string $field): $this
```
- `$field` 需要添加的字段名称
### removeCheckField
删除一个需要验证的字段，当需要根据Sql或者其他各种条件来删除一个正在验证的字段时，你就需要用到`removeCheckField`这个方法
```php
public function removeCheckField(string $field): $this
```
- `$field` 需要删除的字段名称
### setEventPriority
设置[事件](https://v.neww7.com/3/Event.html)和[简易事件](https://v.neww7.com/3/Event.html#简单使用)的优先级
```php
public function setEventPriority(bool $priority): Validate
```
- 当值为True时，执行顺序为：`事件类beforeValidate`->`简易事件before`->`开始数据验证`->`简易事件after`->`事件类afterValidate`
- 当值为False时，执行顺序为：`简易事件before`->`事件类beforeValidate`->`开始数据验证`->`事件类afterValidate`->`简易事件after`
### default
设置或取消一个字段的[默认值](https://v.neww7.com/3/Default.html)
```php
public function default(string $field, $callback, bool $any = false): $this
```
- `$field` 字段名称
- `$callback` 默认值或者匿名函数等,如果为`null`，则取消该字段的默认值
- `$any` 是否处理任意值，默认只处理空值
### filter
设置或取消一个字段的[过滤器](https://v.neww7.com/3/Filter.html)
```php
public function filter(string $field, $callback): $this
```
- `$field` 字段名称
- `$callback` 全局函数名，匿名函数，过滤器类或其他

# 自定义规则
## 前置处理
使用自定义验证规则之前，必须先定义自定义验证规则的命名空间前缀
``` php
ValidateConfig::instance()->rulesPath('W7\\App\\Model\\Validate\\Rules\\');
```
建议在`Provider`中定义验证相关的设置
## 使用规则对象
验证器内有很多有用的验证规则；同时也支持自定义规则。注册自定义验证规则的方法之一，就是新建一个规则并继承`W7\Validate\Support\Rule\BaseRule`。新的规则存放在你[设置好的目录](https://v.neww7.com/3/Start.html#配置自定义规则类路径)中

一旦创建了规则，我们就可以定义它的行为。 `passes`方法接收属性值和名称，并根据属性值是否符合规则而返回`true`或`false`。 `message`属性为验证失败时使用的验证错误消息，此错误消息可被验证器中定义的`message`覆盖：

``` php
namespace W7\App\Model\Validate\Rules;

class Chs extends BaseRule
{
    /**
     * 默认错误消息
     * @var string
     */
    protected $message = ':attribute的值只能具有中文';
    
    /**
     * 确定验证规则是否通过。
     *
     * @param mixed $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return is_scalar($value) && 1 === preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', (string)$value);
    }
}
```
一旦规则对象被定义好后，你可以通过将规则对象的实例和其他验证规则一起来传递给验证器：
``` php
protected $rule = [
    'title' => 'required|chs',
];
```
> <div style="padding-top:3px">自定义扩展规则首字母可小写,也建议使用小写</div>

### 自定义规则传入参数
自定义规则，和其他规则一样，也支持传入参数，类似于`max:100`,`in:0,1,2`此类，参数将按顺序传入自定义规则类中的构造函数中，如下：
```php {7}
class Length extends BaseRule
{
    protected $message = ':attribute的长度不符合要求';

    protected $size;

    public function __construct(int $size)
    {
        $this->size = $size;
    }

    public function passes($attribute, $value): bool
    {
        return strlen($value) === $this->size;
    }
}

```

### 格式化错误消息
在[上诉代码](https://v.neww7.com/3/Rule.html#自定义规则传入参数)中，错误提示可能并不是那么清晰，将`$size`变量放入错误提示，也许会更好，我们提供了`$messageParam`参数，用于支持格式化错误消息，如下：
```php {10}
class Length extends BaseRule
{
    protected $message = ':attribute的长度需为%d个字节';

    protected $size;

    public function __construct(int $size)
    {
        $this->size         = $size;
        $this->messageParam = [$size];
    }

    public function passes($attribute, $value): bool
    {
        return strlen($value) === $this->size;
    }
}

```
`$messageParam`是一个数组类型，所以传入的值必须为数组，如使用规则为`length:10`,则触发后的消息为：`:attribute的长度需为10个字节`

`$message`字段定义：

| 格式化格式 | 说明 |
| --- | --- |
| %% | 返回一个百分号 %|
| %b | 二进制数|
| %c | ASCII 值对应的字符|
| %d | 包含正负号的十进制数（负数、0、正数）|
| %e | 使用小写的科学计数法（例如 1.2e+2）|
| %E | 使用大写的科学计数法（例如 1.2E+2）|
| %u | 不包含正负号的十进制数（大于等于 0）|
| %f | 浮点数（本地设置）|
| %F | 浮点数（非本地设置）|
| %g | 较短的 %e 和 %f|
| %G | 较短的 %E 和 %f|
| %o | 八进制数|
| %s | 字符串|
| %x | 十六进制数（小写字母）|
| %X | 十六进制数（大写字母）|

附加的格式值。必需放置在 % 和字母之间（例如 %.2f）：

- `\+` （在数字前面加上 + 或 - 来定义数字的正负性。默认情况下，只有负数才做标记，正数不做标记）
- `'` （规定使用什么作为填充，默认是空格。它必须与宽度指定器一起使用。例如：%'x20s（使用 "x" 作为填充））
- `\-` （左调整变量值）
- `[0-9]` （规定变量值的最小宽度）
- `.[0-9]` （规定小数位数或最大字符串长度）

> 注意 如果使用多个上述的格式值，`$messageParam`的参数必须按照上面的顺序进行使用，不能打乱。

关于错误消息的更多支持请查看[自定义错误消息](https://v.neww7.com/3/Message.html)
## 使用扩展
### extend 扩展方法
注册自定义的验证规则的另一种方法是使用`Validate`中的 `extend `方法。让我们在[验证器](https://v.neww7.com/3/Validate.html)中使用这个方法来注册自定义验证规则：
``` php
public function __construct()
{
    self::extend("check_admin",function($attribute, $value, $parameters, $validator){
        return $value === "owner";
    });
}
```
自定义的验证闭包接收四个参数：要被验证的属性名称 `$attribute`、属性的值 `$value`、传入验证规则的参数数组 `$parameters` 、以及 `Validator` 实例。
除了使用闭包，你也可以传入类和方法到`extend`方法中：
``` php
Validate::extend("check_admin","permissionValidate@checkAdmin");
```
>  请注意
传入类和方法需要指定方法为`public`类型，类要传入完整的命名空间，如果你的方法为静态方法，还可以通过数组形式进行传入[完整类名,方法名],如方法名没有传，则默认方法名为`validate`

### 使用类方法
自定义规则也支持直接使用当前验证器类下的方法，规则为`rule` + 规则名，如`ruleCheckLogin`

> 请注意 这里的`checkLogin`和当前验证器下的其他方法注册的规则名不能重复，否则会覆盖

自定义规则方法类方法接收四个参数：要被验证的属性名称 `$attribute`、属性的值 `$value`、传入验证规则的参数数组 `$parameters` 、以及 `Validator` 实例。
```php
class LoginValidate extends Validate
{
    protected $rule = [
        'user' => 'required|alphaNum|checkLogin'
    ];

    protected $message = [
        'user.checkLogin' => '登录失败'
    ];

    public function ruleCheckLogin($attribute, $value, $parameters, $validator): bool
    {
        return 'admin' === $value;
    }
}
```
### replacer 错误信息替换器
如果需要定义错误消息，可以在`extend`的第三个参数中填入，或通过`replacer`方法进行定义
`replacer`方法接受两个参数`ruleName`和一个闭包参数，闭包接受四个参数：错误消息`$message`, 被验证的属性名称`$attribute`, 当前的规则名称`$rule`,传入验证规则的参数数组`$parameters`
```php
Validate::replacer("check_admin",function($message,$attribute,$rule,$parameters){
  return "自定义错误消息"
});
```
除了使用闭包以外，也支持传入类和方法到`replacer`方法中
```php
Validate::replacer("check_admin","permissionValidate@checkAdminMessage");
```
>  请注意
传入类和方法需要指定方法为`public`类型，类要传入完整的命名空间,不支持数组传递，如方法名没有传，则默认方法名为`replace`

可以覆盖默认规则的自定义消息，如果要定义自定义方法的错误消息，一定要先定义错误规则`extend`，再定义错误消息`replacer`


使用方式同使用规则对象
```php
protected $rule = [
    'title' => 'required|chs|check_admin',
];
```
### extendImplicit 隐式扩展
默认情况下，当所要验证的属性不存在或包含一个空字符串时，使用包含自定义扩展的正常的验证规则是不会执行的。例如，`unique` 规则将不会检验空字符串：
```php
$rules = ['name' => 'unique:users,name'];

$input = ['name' => ''];

// 验证通过
```
如果即使属性为空也要验证规则，则一定要暗示属性是必须的。要创建这样一个「隐式」扩展，可以使用 `Validate`中的`extendImplicit()` 方法：
```php
Validate::extendImplicit('foo', function ($attribute, $value, $parameters, $validator) {
    return $value == 'foo';
});
```
>  <div style="padding-top:3px">注意：「隐式」扩展只暗示该属性是必需的。至于它到底是缺失还是空值这取决于你。</div>

### ImplicitRule 隐式规则对象
如果你想要在属性为空时执行规则对象，你应该实现 `Illuminate\Contracts\Validation\ImplicitRule` 接口。这个接口将充当验证器的「标记接口」；因此，它不包含你要实现的任何方法。
### extendDependent 依赖性验证器
如果想定义一个自定义扩展对数组进行验证时，我们会发现`extend`和`extendImplicit`均不会解析`*`，这个时候就需要用到`Validate`中的`extendDependent`方法：
```php
Validate::extendDependent('contains', function ($attribute, $value, $parameters, $validator) {
    // 下面验证器传来的$parameters是['*.provider']，当我们暗示这个自定义规则是依赖性的时候
    // 验证器往往会按照我们要验证的原始属性，用当前的指数替换星号，所以*.provider会被替换成0.provider
    // 现在我们可以使用Arr:get()来获取其他字段的值。
    // 所以这个自定义规则验证了属性值包含了其他给定属性的值。
    return str_contains($value,Arr::get($validator->getData(),$parameters[0]));
});

$v = new Validate($request);
$v->setRules([
    '*.email' => 'contains:*.provider'
])->check([
    [
        'email' => '995645888@qq.com', 'provider' => 'qq.com'
    ]
]);
```
# 规则管理器
## 介绍
对验证的规则进行管理，[验证器](https://v.neww7.com/3/Validate.html)是规则管理器的子类,如果你仅需要对规则进行管理，可直接继承该类。
```php
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

    protected function sceneRegister(RuleManagerScene $scene)
    {
        return $scene->only(['user', 'pass', 'name', 'remark'])
            ->remove('remark', 'required|alpha_dash')
            ->append('remark', 'chs');
    }

    protected function sceneRegisterNeedCaptcha(RuleManagerScene $scene)
    {
        return $this->sceneRegister($scene)->appendCheckField('captcha');
    }

    public function ruleCheckCaptcha($att, $value): bool
    {
        return true;
    }
}
```
>  提示
在规则管理器中，可以使用[自定义规则](https://v.neww7.com/3/Rule.html)一节中提到的方式来扩展规则和错误消息


## 获取规则
可以直接调用类的静态方法`get`来获取规则、错误消息、属性名称
```php
public static function get($fields = null, bool $initial = false): array
```
- `$fields` 要获取的字段名称，如果为null，则获取所有规则
- `$initial` 是否获取原始规则，默认为false，即解析后的规则
```php
UserRulesManager::get('user');
```
>  请注意
如果想应用验证场景的规则，请实例化类后使用`scene`方法指定验证场景后再调用`get`方法

```php
(new UserRulesManager())->scene('register')->get('user');
```
将返回
```php
Array
(
    [0] => Array
        (
            [user] => Array
                (
                    [0] => required
                    [1] => email
                )

        )

    [1] => Array
        (
            [user.email] => 用户名必须为邮箱
        )

    [2] => Array
        (
            [user] => 用户名
        )

)
```
获取指定[验证场景](https://v.neww7.com/3/Scene.html)下所有的规则、错误消息、属性名称，可直接使用`getBySceneName`静态方法：
```php
UserRulesManager::getBySceneName('register');
```
也可以直接调用静态方法
```php
UserRulesManager::register();
```
## 单独获取
如果想单独的获取规则，错误消息，属性名称，你可以实例化规则管理器后使用下列方法：

- `getRules` 获取规则
- `getCustomAttributes` 获取属性名称
- `getMessages` 获取错误消息

>  请注意
`getRules`方法受`scene`方法影响,不同的场景下取出的规则可能不同

# 自定义错误消息
在[验证器](https://v.neww7.com/3/Validate.html)中提到使用`$message`参数来定义验证的错误消息，此节详细介绍错误消息的定义

## message变量
错误消息`$message`变量支持填入
- `:attribute` 表示字段名称
- `{:field}` 表示字段内容

> 说明：上述文本在[自定义规则](https://v.neww7.com/3/Rule.html)中均支持使用

### :attribute
`:attribute` 代表为当前触发错误的`customAttributes`变量中的字段名称
```php
class Test extends Validate
{
    protected $rule = [
        'user' => 'required'
    ];
    
    protected $message = [
        'user.required' => '请填写:attribute'
    ];
    
    protected $customAttributes = [
        'user' => '账号'
    ];
}
```

触发后，提示的消息为`请填写账号`

### {:field}
`{:field}`中间的`field`为当前验证值的字段，如果指定字段不存在，则为空文本,支持获取数组，如`info.name`，代表获取`info`数组中的`name`参数，可无限下层
```php
class Test extends Validate
{
    protected $rule = [
        'name' => 'chs'
    ];
    
    protected $message = [
        'name.chs' => '你填写的名字{:name}不是中国名字'
    ];
}
```
输入数据`['name' => 'Rasmus Lerdorf']`,提示：`你填写的名字Rasmus Lerdorf不是中国名字`

## customAttributes变量
当我们定义了大量的验证字段和规则时，如果一个一个对应的编写错误消息，需要耗费大量的时间成本，这个时候，我们可以使用`$customAttributes`变量定义字段的名称。

当错误触发时，会自动替换默认错误消息中的`:attribute`文本

```php {7-9}
class User extends Validate
{
    protected $rule = [
        'id' => 'required|numeric',
    ];

    protected $customAttributes = [
        'id' => '字段ID',
    ];
}
```
当错误触发时，会提示`字段ID 不可为空`,`字段ID 必须为数字`
customAttributes变量中也支持`{:field}`,如：
```php {2}
protected $customAttributes = [
    'id' => '字段ID:{:id}',
];
```
如果传入`id`为`hello`

此时触发后会提示`字段ID:hello 必须为数字`

# 过滤器
在验证后给输入值应用一个过滤器， 并在验证后把它赋值回原属性变量。

## 说明
过滤器可以为全局函数名，匿名函数，过滤器类或其他，该函数的样式必须是:
```php
function ($value) {
    return $newValue;
}
```
有许多的PHP方法结构和`filter`需要的结构一致。 比如使用类型转换方法 (`intval`， `boolval`, ...) 来确保属性为指定的类型， 你可以简单的设置这些方法名而不是重新定义一个匿名函数
## 使用
### 类属性定义
```php
class UserValidate extends Validate
{
    protected $rule = [
        'id'   => 'required|array',
        'id.*' => 'numeric'
    ];

    protected $filter = [
        'id.*' => 'intval'
    ];
}

$data = UserValidate::make()->check([
    'id' => ['1', 2]
]);
var_dump($data);
```
输出
```
array(1) {
  'id' =>
  array(4) {
    [0] =>
    int(1)
    [1] =>
    int(2)
  }
}
```
### 在验证场景中使用
```php
class UserValidate extends Validate
{
    protected $rule = [
        'id'   => 'required|array',
        'id.*' => 'numeric'
    ];

    protected function sceneToInt(ValidateScene $scene)
    {
        $scene->only(['id', 'id.*'])
            ->filter('id.*', 'intval');
    }
}

$data = UserValidate::make()->scene('toInt')->check([
    'id' => ['1', 2]
]);
var_dump($data);
```
输出
```
array(1) {
  'id' =>
  array(2) {
    [0] =>
    int(1)
    [1] =>
    int(2)
  }
}
```
## 过滤器类
创建一个过滤器类，需要实现`W7\Validate\Support\Concerns\FilterInterface`接口：
```php
class UniqueFilter implements FilterInterface
{
    public function handle($value)
    {
        return array_unique($value);
    }
}

class UserValidate extends Validate
{
    protected $rule = [
        'id'   => 'required|array',
        'id.*' => 'numeric'
    ];

    protected $filter = [
        'id' => UniqueFilter::class
    ];
}

$data = UserValidate::make()->scene('toInt')->check([
    'id' => [1,2,1,2,3,3,4]
]);
var_dump($data);
```
输出
```
array(1) {
  'id' =>
  array(4) {
    [0] =>
    int(1)
    [1] =>
    int(2)
    [4] =>
    int(3)
    [6] =>
    int(4)
  }
}
```
## 类方法
过滤器也支持直接写入类方法的方式，（方法的命名规范是`filter`+ 首字母大写的名称）
```php
class UserValidate extends Validate
{
    protected $rule = [
        'id'   => 'required|array',
        'id.*' => 'numeric'
    ];

    protected $filter = [
        'id' => 'uniqueFilter'
    ];

    public function filterUniqueFilter($value)
    {
        return array_unique($value);
    }
}
```
在验证场景中可以直接使用`[$this,'filterUniqueFilter']`来传递`callable`
# 默认值
为空值分配默认值，在验证之前执行，当值为空数组，空字符以及`null`时判断为空。

>  请注意
默认值不支持数组元素，也就是说：为`id.*`这种设定默认值是不支持的

## 使用
### 类属性定义
使用类的`$default`参数来为字段设定默认值
```php {8-11}
class Setting extends Validate
{
    protected $rule = [
        'site_status'     => 'required|in:0,1',
        'register_status' => 'required|in:0,1'
    ];

    protected $default = [
        'site_status'     => 1,
        'register_status' => 1
    ];
}

$data = Setting::make()->check([]);
print_r($data);
//Array
//(
//    [site_status]     => 1
//    [register_status] => 1
//)
```
### 在验证场景中使用
在类中增加一个`base`验证场景
```php
class Setting extends Validate
{
    protected $rule = [
        'site_status'     => 'required|in:0,1',
        'register_status' => 'required|in:0,1'
    ];

    protected $default = [
        'site_status'     => 1,
        'register_status' => 1
    ];

    protected function sceneBase(ValidateScene $scene)
    {
        $scene->only(['site_status'])
            ->default('site_status', 0);
    }
}
$data = Setting::make()->scene('base')->check([]);
print_r($data);
//Array
//(
//    [site_status] => 1
//)
```
>  说明
一个字段只能拥有一个默认值，验证场景中的默认值生效后，全局定义的默认值就失效。

<b>默认值只对当前需要验证的字段生效</b>

## 多样的默认值
### 闭包
默认值支持闭包，闭包将收到三个值
- `$value` 当前的值
- `$attribute` 当前的字段名
- `$originalData` 当前正在进行验证的全部原始数据

回调方法的样式如下：
```php
function($value, string $attribute, array $originalData) {
    return $value;
}
```
示例：
```php
$scene->defalt('name', function($value) {
    return '张三';
});
```
### 类方法
使用类方法为指定字段传递默认值时，同样会接受到三个值
- `$value` 当前的值
- `$attribute` 当前的字段名
- `$originalData` 当前正在进行验证的全部原始数据

```php
$scene->defalt('name', [$this,'setDefaultName']);

public function setDefaultName($value, string $attribute, array $originalData)
{
    return '张三';
}
```
简易方法:

定义一个默认值方法，（方法的命名规范是`default`+ 首字母大写的名称）
```php
$scene->defalt('name','setDefaultName');

public function defaultSetDefaultName($value, string $attribute, array $originalData)
{
    return '张三';
}
```
### 忽略空条件
当值为空数组，空字符以及`null`时，才会取出对应默认值并赋值到原数据上，
如果你想任何值都获取一次默认值，可以增加`any => true`的条件，如果增加了`any`，你需要将你的默认值放入`value`中，如下：
```php
class Setting extends Validate
{
    protected $rule = [
        'site_status' => 'required|in:0,1',
    ];

    protected $default = [
        'site_status' => ['value' => 1, 'any' => true],
    ];
}
```
这个时候，无论`site_status`的原始参数是什么，都会被重置为`1`，你也可以使用该特性，为参数做提前格式化等操作
### 取消默认值
如果你想在验证场景中，取消[类属性中](https://v.neww7.com/3/Default.html#类属性定义)设定的默认值,可以将值设定为`null`,如下:
```php
class User extends Validate
{
    protected $rule = [
        'name' => ''
    ];

    protected $default = [
        'name' => '张三'
    ];

    protected function sceneTest(ValidateScene $scene)
    {
        $scene->only(['name'])
            ->default('name', null);
    }
}

$data = User::make()->scene('test')->check([]); // 返回空数组
```
### 提供默认值的类
创建一个提供默认值的类，需要实现`W7\Validate\Support\Concerns\DefaultInterface`接口：
```php
class DefaultUserId implements DefaultInterface
{
    public function handle($value, string $attribute, array $originalData)
    {
        return $_SESSION['user_id'];
    }
}
```
使用：
```php
class UserValidate extends Validate
{
    protected $rule = [
        'id'   => 'required',
    ];

    protected $default = [
        'id' => DefaultUserId::class
    ];
}
```
# 验证事件
## 验证事件处理
目前支持的验证事件为
- 验证前事件`beforeValidate`
- 验证后事件`afterValidate`

> <div style="padding-top:3px">事件仅支持在场景中定义使用</div>

验证器中间件需要继承`W7\Validate\Support\Event\ValidateEventAbstract`

事件类拥有三个类属性

- `$sceneName` 当前的场景名称
- `$data` 验证前的值或验证后的值
- `$message` 错误消息，如果事件返回`false`，则验证器将取此值作为错误消息

```php
abstract class ValidateEventAbstract implements ValidateEventInterface
{
    public function beforeValidate(): bool;
    public function afterValidate(): bool;
}
```

场景事件处理类构建函数中可以取到传递过来的值

如场景中使用了`event(CheckPermission::class,1,2,3)`，则构建函数
```php
public function __construct($a,$b,$c)
{
    
}
```
可依次获取到`$a = 1` `$b = 2` `$c = 3`

## 使用方法
### 在验证场景中
可在验证场景中使用`event`关键词

如果不需要给场景事件处理类传值：
``` php
protected $scene = [
    'add'    => ['id','user_id','role','event' => CheckPermission::class],
];
```
如果需要给场景事件处理类传值：
``` php
protected $scene = [
    'add'    => ['id','user_id','role','event' => [
        CheckPermission::class=>[1,2,3,4]
    ]],
];
```

> <div style="padding-top:3px">传值的数量不限制，可在场景事件处理类的构建函数中获取</div>

### 在自定义验证场景中
在自定义验证场景中，可使用`event`方法定义要使用的场景事件处理类，不限数量
```php
/*
* @method $this event(string $handler,...$params)
*/

$scene->event(CheckPermission::class)
```
如果要传值
``` php
$scene->event(CheckPermission::class,1,2,3)
```
使用多个场景事件处理类
```php
$scene->event(CheckPermission::class,1,2,3)->event(CheckName::class)
```
## 全局事件处理器
如果你某个验证器中的所有场景都使用到共同的事件处理器，可以定义一个全局的事件处理器
```php
protected $event = [
    CheckPermission::class => ['owner']
];
```
可定义多个全局事件处理器，如果场景中也定义了事件处理器，则全部生效

## 简单使用
如果你只是想在验证前给某个字段设置一个默认值或在每个字段验证后进行一个总的验证等需求，而此验证业务不需要复用，
可以直接`after`和`before`来进行简单定义。而不需要去定义一个类

在自定义验证场景中，可使用`after`和`before`方法定义要使用的事件处理方法，不限数量
- `before` 在验证之前执行的方法
- `after` 在验证之后执行的方法

方法接受一个验证数据`array $data`参数

与事件类`event`同用时的执行顺序为:`beforeValidate`->`before`->`after`->`afterValidate`

> 如果想更改执行顺序，可以在[自定义验证场景](https://v.neww7.com/3/Scene.html#自定义验证场景)中使用[setEventPriority](https://v.neww7.com/3/Scene.html#seteventpriority)方法

方法仅限本类的方法，方法的命名规则为`after`或`before`加方法名，如：
```php
class LoginValidate extends \W7\Validate\Validate
{
    protected $rule = [
        'name' => 'required|chs',
        'user' => 'required|alpha_dash',
        'pass' => 'required|min:8',
    ];
    
    protected $scene = [
        'register' => ['name', 'user', 'pass', 'before' => 'checkRegisterStatus']
    ];
    
    public function beforeCheckRegisterStatus(array $data)
    {
        return true;
    }
}
```
简单使用也可以为方法传递参数，传参方法同[使用方法](https://v.neww7.com/3/Event.html#使用方法),只不过要将类名换为方法名，第一个参数为当前验证的值或者验证后的值
```php
protected $message = [
    'user.required' => '用户名不可为空'
];

protected $scene = [
    'register' => ['name', 'user', 'pass', 'before' => 'setDefaultName','after'=> [
        'checkUserExist' => [1,2,3,4]
    ]]
];

public function afterCheckUserExist(array $data,$a,$b,$c)
{
    return true;
}
```
同样可依次获取到`$a = 1` `$b = 2` `$c = 3`

> 返回值：
> 如果返回的是字符串，则抛出`W7\Validate\Exception\ValidateException`异常，代表未通过，如果通过，则返回`$next($data);`
> 
> 事件中可以直接返回`message`的`key`值，如`user.required`,验证器会自动查找对应的错误消息。

# 自定义错误消息
在[验证器](https://v.neww7.com/3/Validate.html)中提到使用`$message`参数来定义验证的错误消息，此节详细介绍错误消息的定义

## message变量
错误消息`$message`变量支持填入
- `:attribute` 表示字段名称
- `{:field}` 表示字段内容

> 说明：上述文本在[自定义规则](https://v.neww7.com/3/Rule.html)中均支持使用

### :attribute
`:attribute` 代表为当前触发错误的`customAttributes`变量中的字段名称
```php
class Test extends Validate
{
    protected $rule = [
        'user' => 'required'
    ];
    
    protected $message = [
        'user.required' => '请填写:attribute'
    ];
    
    protected $customAttributes = [
        'user' => '账号'
    ];
}
```

触发后，提示的消息为`请填写账号`

### {:field}
`{:field}`中间的`field`为当前验证值的字段，如果指定字段不存在，则为空文本,支持获取数组，如`info.name`，代表获取`info`数组中的`name`参数，可无限下层
```php
class Test extends Validate
{
    protected $rule = [
        'name' => 'chs'
    ];
    
    protected $message = [
        'name.chs' => '你填写的名字{:name}不是中国名字'
    ];
}
```
输入数据`['name' => 'Rasmus Lerdorf']`,提示：`你填写的名字Rasmus Lerdorf不是中国名字`

## customAttributes变量
当我们定义了大量的验证字段和规则时，如果一个一个对应的编写错误消息，需要耗费大量的时间成本，这个时候，我们可以使用`$customAttributes`变量定义字段的名称。

当错误触发时，会自动替换默认错误消息中的`:attribute`文本

```php {7-9}
class User extends Validate
{
    protected $rule = [
        'id' => 'required|numeric',
    ];

    protected $customAttributes = [
        'id' => '字段ID',
    ];
}
```
当错误触发时，会提示`字段ID 不可为空`,`字段ID 必须为数字`
customAttributes变量中也支持`{:field}`,如：
```php {2}
protected $customAttributes = [
    'id' => '字段ID:{:id}',
];
```
如果传入`id`为`hello`

此时触发后会提示`字段ID:hello 必须为数字`
