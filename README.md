# 增强表单验证
一个让你的表单验证更为方便，快捷，安全的扩展，满足你的一切验证需求。

## 说明
此验证基于Laravel的Validator验证器,可用于Laravel，软擎等依赖于illuminate/validation的项目中，此验证器做了如下扩展：

 - 可通过类的方式定义一个[验证器](https://v.neww7.com/2/Validate.html)
 - 增加[验证场景](https://v.neww7.com/2/Scene.html)
 - 增加[场景事件处理](https://v.neww7.com/2/Event.html)
 - 修改了[自定义验证规则](https://v.neww7.com/2/Rule.html)
 - [自定义消息](https://v.neww7.com/2/Message.html) 增加了对内容的引用
 - 继承集合类增加一个[验证集合](https://v.neww7.com/2/Collection.html)

等...使您的验证只需要在验证器中全部都可以完成

> 验证器支持Laravel的内置规则，内置规则文档可查看[规则文档](https://learnku.com/docs/laravel/6.x/validation/5144#c58a91)

## 安装
使用composer命令
``` shell
composer require w7/engine-validate
```

完整文档查看[完整文档](https://v.neww7.com)