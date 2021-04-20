# 软擎验证扩展

## 说明
此验证基于Laravel的Validator验证器,做了如下改变
 - 可通过类的方式定义一个[验证器](https://v.neww7.com/Validate.html)
 - 增加[验证场景](https://v.neww7.com/Scene.html)
 - 增加[场景事件处理](https://v.neww7.com/Event.html)
 - 修改了[自定义验证规则](https://v.neww7.com/Rule.html)
 - [自定义消息](https://v.neww7.com/Message.html) 增加了对内容的引用
 - 继承集合类增加一个[验证集合](https://v.neww7.com/Collection.html)

等...使您的验证只需要在验证器中全部都可以完成

> 此文档只说明与Laravel的Validator验证器不同的地方，完整Laravel Validator文档可查看：[完整文档](https://learnku.com/docs/laravel/6.x/validation/5144)

## 安装
使用composer命令
``` shell
composer require w7/rangine-validate
```

完整文档查看[完整文档](https://v.neww7.com)