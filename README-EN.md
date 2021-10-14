<div align="center">
    <h1>Micro Engine Form Validate</h1>
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

[中文](https://gitee.com/we7coreteam/w7-engine-validate/blob/4.x/README.md) |
English

## Introduction
An extension to make your form validation easier, faster and more secure for all your validation needs.

## Catalog
- [Validator](https://v.neww7.com/en/4/Validate.html)
- [Validate Scene](https://v.neww7.com/en/4/Scene.html)
- [Validate Events](https://v.neww7.com/en/4/Event.html)
- [Rule Manager](https://v.neww7.com/en/4/RuleManager.html)
- [Built-in Rules](https://v.neww7.com/en/4/BuiltRule.html)
- [Custom Rules](https://v.neww7.com/en/4/Rule.html)
- [Custom Error Messages](https://v.neww7.com/en/4/Message.html)
- [Data Default](https://v.neww7.com/en/4/Default.html)
- [Data Filter](https://v.neww7.com/en/4/Filter.html)
- [Validate Collection](https://v.neww7.com/en/4/Collection.html)

## Install
using Composer
``` shell
composer require w7/engine-validate
```

## Simple Validate
Support for simply defining a validator and performing validation:
```php
try {
    $data = Validate::make([
        'user' => 'required|email',
        'pass' => 'required|lengthBetween:6,16',
    ], [
        'user.required'      => 'Please enter your username',
        'user.email'         => 'User name format error',
        'pass.required'      => 'Please enter your password',
        'pass.lengthBetween' => 'Password length is 6~16 bits',
    ])->check($data);
} catch (ValidateException $e) {
    echo $e->getMessage();
}
```
If the validation passes, all values that passed the validation are returned,
if not, a `W7\Validate\Exception\ValidateException` exception is thrown

## Validator Definition
To define the validator class for a specific validation scenario or data form, we need to inherit the `W7\Validate\Validate` class,
Then instantiate and call the `check` method of the validation class directly to complete the validation, an example of which is as follows.

Define a `LoginValidate` validator class for login validation.
```php
class LoginValidate extends Validate
{
    protected $rule = [
        'user' => 'required|email',
        'pass' => 'required|digits_between:6,16',
    ];
    
    protected $message = [
        'user.required'       => 'Please enter your username',
        'user.email'          => 'Incorrect username format',
        'pass.required'       => 'Please enter your password',
        'pass.digits_between' => 'Password length of 6 to 16 digits',
    ];
}

```
:::tip <div style="padding-top:3px;color:#42b983">Error messages defined by class attributes take precedence over the default responses in custom rules and over the errors returned by custom rule methods.</div>
:::

## Data Validate
``` php
$data = [
    'user' => '123@qq.com',
    'pass' => ''
];
$validate = new LoginValidate();
$validate->check($data);
```
This throws a `W7\Validate\Exception\ValidateException` exception with the message `Please enter your password`
``` php
$data = [
    'user' => '123@qq.com',
    'pass' => '123456'
];
$validate = new LoginValidate();
$data = $validate->check($data);
```
Validates successfully and returns the validated value, which is an array type

## Validate Arrays
It is not difficult to validate the form input as an array of fields.
You can use the "dot" method to validate properties in an array.
For example, if the incoming HTTP request contains the `search[keyword]` field,
it can be validated as follows.

``` php
protected $rule = [
    'search.order'   => 'numeric|between:1,2',
    'search.keyword' => 'chsAlphaNum',
    'search.recycle' => 'boolean',
];
```
You can also validate each element in an array. For example, to validate that each id in a given array input field is unique, you can do this.

``` php
protected $rule = [
    'search.*.id' => 'numeric|unique:account'
];
```
The definition of an error message for an array rule is the same

``` php 
protected $message = [
    'search.order.numeric'       => 'order parameter error',
    'search.order.between'       => 'order parameter error',
    'search.keyword.chsAlphaNum' => 'Keywords can only contain Chinese, letters, numbers',
    'search.recycle.boolean'     => 'Parameter error:recycle',
];
```
## Validator Class Attributes
### $rule
The validation rules of the user-defined validator can also be set via the `setRules` method,
This method will superimpose the data.If the parameter is `null` then it is clear all rules.
```php
// Definition in class
protected $rule = [
    'user' => 'required'
];

// Definition of usage methods
$v->setRules([
    'user' => 'required'
]);
```
### $message
User-defined validator error messages can also be set via the `setMessages` method,
This method will superimpose the data,If the parameter is `null` then it is clear all error messages.
```php
// Definition in class
protected $message = [
    'user.required' => 'user is required'
];

// Definition of usage methods
$v->setMessages([
    'user.required' => 'pass is required'
]);
```
### $scene
Define the data of the validation scenario, which is used to specify the validation fields corresponding to the validation scenario, etc.
See the section on [validate scene](https://v.neww7.com/en/4/Scene.html) for detailed usage.

The same can be done with the `setScene` method.
This method will superimpose the data,
If the parameter is `null`, then all validate scene are cleared.
```php
// Definition in class
protected $scene = [
    'login' => ['user', 'pass']
];

// Definition of usage methods
$v->setScene([
    'login' => ['user', 'pass']
]);
```
### $event
Define global events under this validator, see the section [Events](https://v.neww7.com/en/4/Event.html) for detailed usage.
```php
protected $handler = [
    CheckSiteStatus::class
];
```
### $default
Defining the default value of a field
```php
protected $default = [
    'name' => 'Emma'
];
```
For more information about default values, please see the section [Default Values](https://v.neww7.com/en/4/Default.html).
### $filter
For processing data after data validation
```php
protected $filter = [
    'name' => 'trim'
];
```
For more information about filters, please see the [Filter](https://v.neww7.com/en/4/Filter.html) section.
### $customAttributes
Define the name of the validation field, also can be set by `setCustomAttributes` method, this method will superimpose the data,
if the parameter is `null` then it is clear all field names.
the [:attribute](https://v.neww7.com/en/4/Message.htm#attribute) in the error message will be replaced with the value corresponding to the following

```php
protected $customAttributes = [
    'user' => 'Account',
    'pass' => 'Password'
];
```
### $ruleMessage
Error messages for class method rules
```php
 protected $ruleMessage = [
    'The value of :attribute can only have Chinese'
];
```
Click to view [example](https://v.neww7.com/en/4/Rule.htm#using-rule-objects)
### $filled
All validated fields cannot be empty when they exist, if the value of this property is `true`,
all rules will automatically add `filled` rules, the default is `true`

The `filled` rule is not automatically added when:
- Validation rules exist in `filled`, `nullable`, `accepted`, `present`,`required`, `required_if`, `required_unless`, `required_with`,`required_with_all`, `required_without`, `required_without_all` Rule
- Validation rules exist [extendImplicit](https://v.neww7.com/en/4/Rule.htm#extendimplicit) Defined rules
- Validation rules exist [extendImplicitRule](https://v.neww7.com/en/4/Rule.htm#defining-the-implicit-validator) Defined rules
- The validation rules implement the `Itwmw\Validation\Support\Interfaces\ImplicitRule` Marker Interface
```php
protected bool $filled = true;
```
### $regex
Predefined regular expression validation rules, see [Regular Expression Rule](https://v.neww7.com/en/4/Rule.htm#regular-expression-rule) for details
```php
protected $regex = [
	'number' => '/^\d+$/'
];
```