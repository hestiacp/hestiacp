Rakit Validation - PHP Standalone Validation Library
======================================================

[![Build Status](https://img.shields.io/travis/rakit/validation.svg?style=flat-square)](https://travis-ci.org/rakit/validation)
[![Coverage Status](https://coveralls.io/repos/github/rakit/validation/badge.svg?branch=setup_coveralls)](https://coveralls.io/github/rakit/validation)
[![License](http://img.shields.io/:license-mit-blue.svg?style=flat-square)](http://doge.mit-license.org)


PHP Standalone library for validating data. Inspired by `Illuminate\Validation` Laravel.

## Features

* API like Laravel validation.
* Array validation.
* `$_FILES` validation with multiple file support.
* Custom attribute aliases.
* Custom validation messages.
* Custom rule.

## Requirements

* PHP 7.0 or higher
* Composer for installation

## Quick Start

#### Installation

```
composer require "rakit/validation"
```

#### Usage

There are two ways to validating data with this library. Using `make` to make validation object,
then validate it using `validate`. Or just use `validate`.
Examples:

Using `make`:

```php
<?php

require('vendor/autoload.php');

use Rakit\Validation\Validator;

$validator = new Validator;

// make it
$validation = $validator->make($_POST + $_FILES, [
    'name'                  => 'required',
    'email'                 => 'required|email',
    'password'              => 'required|min:6',
    'confirm_password'      => 'required|same:password',
    'avatar'                => 'required|uploaded_file:0,500K,png,jpeg',
    'skills'                => 'array',
    'skills.*.id'           => 'required|numeric',
    'skills.*.percentage'   => 'required|numeric'
]);

// then validate
$validation->validate();

if ($validation->fails()) {
    // handling errors
    $errors = $validation->errors();
    echo "<pre>";
    print_r($errors->firstOfAll());
    echo "</pre>";
    exit;
} else {
    // validation passes
    echo "Success!";
}

```

or just `validate` it:

```php
<?php

require('vendor/autoload.php');

use Rakit\Validation\Validator;

$validator = new Validator;

$validation = $validator->validate($_POST + $_FILES, [
    'name'                  => 'required',
    'email'                 => 'required|email',
    'password'              => 'required|min:6',
    'confirm_password'      => 'required|same:password',
    'avatar'                => 'required|uploaded_file:0,500K,png,jpeg',
    'skills'                => 'array',
    'skills.*.id'           => 'required|numeric',
    'skills.*.percentage'   => 'required|numeric'
]);

if ($validation->fails()) {
	// handling errors
	$errors = $validation->errors();
	echo "<pre>";
	print_r($errors->firstOfAll());
	echo "</pre>";
	exit;
} else {
	// validation passes
	echo "Success!";
}

```

In this case, 2 examples above will output the same results.

But with `make` you can setup something like custom invalid message, custom attribute alias, etc before validation running.

### Attribute Alias

By default we will transform your attribute into more readable text. For example `confirm_password` will be displayed as `Confirm password`.
But you can set it anything you want with `setAlias` or `setAliases` method.

Example:

```php
$validator = new Validator;

// To set attribute alias, you should use `make` instead `validate`.
$validation->make([
	'province_id' => $_POST['province_id'],
	'district_id' => $_POST['district_id']
], [
	'province_id' => 'required|numeric',
	'district_id' => 'required|numeric'
]);

// now you can set aliases using this way:
$validation->setAlias('province_id', 'Province');
$validation->setAlias('district_id', 'District');

// or this way:
$validation->setAliases([
	'province_id' => 'Province',
	'district_id' => 'District'
]);

// then validate it
$validation->validate();

```

Now if `province_id` value is empty, error message would be 'Province is required'.

## Custom Validation Message

Before register/set custom messages, here are some variables you can use in your custom messages:

* `:attribute`: will replaced into attribute alias.
* `:value`: will replaced into stringify value of attribute. For array and object will replaced to json.

And also there are several message variables depends on their rules.

Here are some ways to register/set your custom message(s):

#### Custom Messages for Validator

With this way, anytime you make validation using `make` or `validate` it will set your custom messages for it.
It is useful for localization.

To do this, you can set custom messages as first argument constructor like this:

```php
$validator = new Validator([
	'required' => ':attribute harus diisi',
	'email' => ':email tidak valid',
	// etc
]);

// then validation belows will use those custom messages
$validation_a = $validator->validate($dataset_a, $rules_for_a);
$validation_b = $validator->validate($dataset_b, $rules_for_b);

```

Or using `setMessages` method like this:

```php
$validator = new Validator;
$validator->setMessages([
	'required' => ':attribute harus diisi',
	'email' => ':email tidak valid',
	// etc
]);

// now validation belows will use those custom messages
$validation_a = $validator->validate($dataset_a, $rules_for_dataset_a);
$validation_b = $validator->validate($dataset_b, $rules_for_dataset_b);

```

#### Custom Messages for Validation

Sometimes you may want to set custom messages for specific validation.
To do this you can set your custom messages as 3rd argument of `$validator->make` or `$validator->validate` like this:

```php
$validator = new Validator;

$validation_a = $validator->validate($dataset_a, $rules_for_dataset_a, [
	'required' => ':attribute harus diisi',
	'email' => ':email tidak valid',
	// etc
]);

```

Or you can use `$validation->setMessages` like this:

```php
$validator = new Validator;

$validation_a = $validator->make($dataset_a, $rules_for_dataset_a);
$validation_a->setMessages([
	'required' => ':attribute harus diisi',
	'email' => ':email tidak valid',
	// etc
]);

...

$validation_a->validate();
```

#### Custom Message for Specific Attribute Rule

Sometimes you may want to set custom message for specific rule attribute.
To do this you can use `:` as message separator or using chaining methods.

Examples:

```php
$validator = new Validator;

$validation_a = $validator->make($dataset_a, [
	'age' => 'required|min:18'
]);

$validation_a->setMessages([
	'age:min' => '18+ only',
]);

$validation_a->validate();
```

Or using chaining methods:

```php
$validator = new Validator;

$validation_a = $validator->make($dataset_a, [
	'photo' => [
		'required',
		$validator('uploaded_file')->fileTypes('jpeg|png')->message('Photo must be jpeg/png image')
	]
]);

$validation_a->validate();
```

## Translation

Translation is different with custom messages.
Translation may needed when you use custom message for rule `in`, `not_in`, `mimes`, and `uploaded_file`.

For example if you use rule `in:1,2,3` we will set invalid message like "The Attribute only allows '1', '2', or '3'"
where part "'1', '2', or '3'" is comes from ":allowed_values" tag.
So if you have custom Indonesian message ":attribute hanya memperbolehkan :allowed_values",
we will set invalid message like "Attribute hanya memperbolehkan '1', '2', or '3'" which is the "or" word is not part of Indonesian language.

So, to solve this problem, we can use translation like this:

```php
// Set translation for words 'and' and 'or'.
$validator->setTranslations([
    'and' => 'dan',
    'or' => 'atau'
]);

// Set custom message for 'in' rule
$validator->setMessage('in', ":attribute hanya memperbolehkan :allowed_values");

// Validate
$validation = $validator->validate($inputs, [
    'nomor' => 'in:1,2,3'
]);

$message = $validation->errors()->first('nomor'); // "Nomor hanya memperbolehkan '1', '2', atau '3'"
```

> Actually, our built-in rules only use words 'and' and 'or' that you may need to translates.

## Working with Error Message

Errors messages are collected in `Rakit\Validation\ErrorBag` object that you can get it using `errors()` method.

```php
$validation = $validator->validate($inputs, $rules);

$errors = $validation->errors(); // << ErrorBag
```

Now you can use methods below to retrieves errors messages:

#### `all(string $format = ':message')`

Get all messages as flatten array.

Examples:

```php
$messages = $errors->all();
// [
//     'Email is not valid email',
//     'Password minimum 6 character',
//     'Password must contains capital letters'
// ]

$messages = $errors->all('<li>:message</li>');
// [
//     '<li>Email is not valid email</li>',
//     '<li>Password minimum 6 character</li>',
//     '<li>Password must contains capital letters</li>'
// ]
```

#### `firstOfAll(string $format = ':message', bool $dotNotation = false)`

Get only first message from all existing keys.

Examples:

```php
$messages = $errors->firstOfAll();
// [
//     'email' => Email is not valid email',
//     'password' => 'Password minimum 6 character',
// ]

$messages = $errors->firstOfAll('<li>:message</li>');
// [
//     'email' => '<li>Email is not valid email</li>',
//     'password' => '<li>Password minimum 6 character</li>',
// ]
```

Argument `$dotNotation` is for array validation.
If it is `false` it will return original array structure, if it `true` it will return flatten array with dot notation keys.

For example:

```php
$messages = $errors->firstOfAll(':message', false);
// [
//     'contacts' => [
//          1 => [
//              'email' => 'Email is not valid email',
//              'phone' => 'Phone is not valid phone number'
//          ],
//     ],
// ]

$messages = $errors->firstOfAll(':message', true);
// [
//     'contacts.1.email' => 'Email is not valid email',
//     'contacts.1.phone' => 'Email is not valid phone number',
// ]
```

#### `first(string $key)`

Get first message from given key. It will return `string` if key has any error message, or `null` if key has no errors.

For example:

```php
if ($emailError = $errors->first('email')) {
    echo $emailError;
}
```

#### `toArray()`

Get all messages grouped by it's keys.

For example:

```php
$messages = $errors->toArray();
// [
//     'email' => [
//         'Email is not valid email'
//     ],
//     'password' => [
//         'Password minimum 6 character',
//         'Password must contains capital letters'
//     ]
// ]
```

#### `count()`

Get count messages.

#### `has(string $key)`

Check if given key has an error. It returns `bool` if a key has an error, and otherwise.


## Getting Validated, Valid, and Invalid Data

For example you have validation like this:

```php
$validation = $validator->validate([
    'title' => 'Lorem Ipsum',
    'body' => 'Lorem ipsum dolor sit amet ...',
    'published' => null,
    'something' => '-invalid-'
], [
    'title' => 'required',
    'body' => 'required',
    'published' => 'default:1|required|in:0,1',
    'something' => 'required|numeric'
]);
```

You can get validated data, valid data, or invalid data using methods in example below:

```php
$validatedData = $validation->getValidatedData();
// [
//     'title' => 'Lorem Ipsum',
//     'body' => 'Lorem ipsum dolor sit amet ...',
//     'published' => '1' // notice this
//     'something' => '-invalid-'
// ]

$validData = $validation->getValidData();
// [
//     'title' => 'Lorem Ipsum',
//     'body' => 'Lorem ipsum dolor sit amet ...',
//     'published' => '1'
// ]

$invalidData = $validation->getInvalidData();
// [
//     'something' => '-invalid-'
// ]
```

## Available Rules

> Click to show details.

<details><summary><strong>required</strong></summary>

The field under this validation must be present and not 'empty'.

Here are some examples:

| Value         | Valid |
| ------------- | ----- |
| `'something'` | true  |
| `'0'`         | true  |
| `0`           | true  |
| `[0]`         | true  |
| `[null]`      | true  |
| null          | false |
| []            | false |
| ''            | false |

For uploaded file, `$_FILES['key']['error']` must not `UPLOAD_ERR_NO_FILE`.

</details>

<details><summary><strong>required_if</strong>:another_field,value_1,value_2,...</summary>

The field under this rule must be present and not empty if the anotherfield field is equal to any value.

For example `required_if:something,1,yes,on` will be required if `something` value is one of `1`, `'1'`, `'yes'`, or `'on'`.

</details>

<details><summary><strong>required_unless</strong>:another_field,value_1,value_2,...</summary>

The field under validation must be present and not empty unless the anotherfield field is equal to any value.

</details>

<details><summary><strong>required_with</strong>:field_1,field_2,...</summary>

The field under validation must be present and not empty only if any of the other specified fields are present.

</details>

<details><summary><strong>required_without</strong>:field_1,field_2,...</summary>

The field under validation must be present and not empty only when any of the other specified fields are not present.

</details>

<details><summary><strong>required_with_all</strong>:field_1,field_2,...</summary>

The field under validation must be present and not empty only if all of the other specified fields are present.

</details>

<details><summary><strong>required_without_all</strong>:field_1,field_2,...</summary>

The field under validation must be present and not empty only when all of the other specified fields are not present.

</details>

<details><summary><strong>uploaded_file</strong>:min_size,max_size,extension_a,extension_b,...</summary>

This rule will validate data from `$_FILES`.
Field under this rule must be follows rules below to be valid:

* `$_FILES['key']['error']` must be `UPLOAD_ERR_OK` or `UPLOAD_ERR_NO_FILE`. For `UPLOAD_ERR_NO_FILE` you can validate it with `required` rule.
* If min size is given, uploaded file size **MUST NOT** be lower than min size.
* If max size is given, uploaded file size **MUST NOT** be higher than max size.
* If file types is given, mime type must be one of those given types.

Here are some example definitions and explanations:

* `uploaded_file`: uploaded file is optional. When it is not empty, it must be `ERR_UPLOAD_OK`.
* `required|uploaded_file`: uploaded file is required, and it must be `ERR_UPLOAD_OK`.
* `uploaded_file:0,1M`: uploaded file size must be between 0 - 1 MB, but uploaded file is optional.
* `required|uploaded_file:0,1M,png,jpeg`: uploaded file size must be between 0 - 1MB and mime types must be `image/jpeg` or `image/png`.

Optionally, if you want to have separate error message between size and type validation.
You can use `mimes` rule to validate file types, and `min`, `max`, or `between` to validate it's size.

For multiple file upload, PHP will give you undesirable array `$_FILES` structure ([here](http://php.net/manual/en/features.file-upload.multiple.php#53240) is the topic). So we make `uploaded_file` rule to automatically resolve your `$_FILES` value to be well-organized array structure. That means, you cannot only use `min`, `max`, `between`, or `mimes` rules to validate multiple file upload. You should put `uploaded_file` just to resolve it's value and make sure that value is correct uploaded file value.

For example if you have input files like this:

```html
<input type="file" name="photos[]"/>
<input type="file" name="photos[]"/>
<input type="file" name="photos[]"/>
```

You can  simply validate it like this:

```php
$validation = $validator->validate($_FILES, [
    'photos.*' => 'uploaded_file:0,2M,jpeg,png'
]);

// or

$validation = $validator->validate($_FILES, [
    'photos.*' => 'uploaded_file|max:2M|mimes:jpeg,png'
]);
```

Or if you have input files like this:

```html
<input type="file" name="images[profile]"/>
<input type="file" name="images[cover]"/>
```

You can validate it like this:

```php
$validation = $validator->validate($_FILES, [
    'images.*' => 'uploaded_file|max:2M|mimes:jpeg,png',
]);

// or

$validation = $validator->validate($_FILES, [
    'images.profile' => 'uploaded_file|max:2M|mimes:jpeg,png',
    'images.cover' => 'uploaded_file|max:5M|mimes:jpeg,png',
]);
```

Now when you use `getValidData()` or `getInvalidData()` you will get well array structure just like single file upload.

</details>

<details><summary><strong>mimes</strong>:extension_a,extension_b,...</summary>

The `$_FILES` item under validation must have a MIME type corresponding to one of the listed extensions.

</details>

<details><summary><strong>default/defaults</strong></summary>

This is special rule that doesn't validate anything.
It just set default value to your attribute if that attribute is empty or not present.

For example if you have validation like this

```php
$validation = $validator->validate([
    'enabled' => null
], [
    'enabled' => 'default:1|required|in:0,1'
    'published' => 'default:0|required|in:0,1'
]);

$validation->passes(); // true

// Get the valid/default data
$valid_data = $validation->getValidData();

$enabled = $valid_data['enabled'];
$published = $valid_data['published'];
```

Validation passes because we sets default value for `enabled` and `published` to `1` and `0` which is valid. Then we can get the valid/default data.

</details>

<details><summary><strong>email</strong></summary>

The field under this validation must be valid email address.

</details>

<details><summary><strong>uppercase</strong></summary>

The field under this validation must be valid uppercase.

</details>

<details><summary><strong>lowercase</strong></summary>

The field under this validation must be valid lowercase.

</details>

<details><summary><strong>json</strong></summary>

The field under this validation must be valid JSON string.

</details>

<details><summary><strong>alpha</strong></summary>

The field under this rule must be entirely alphabetic characters.

</details>

<details><summary><strong>numeric</strong></summary>

The field under this rule must be numeric.

</details>

<details><summary><strong>alpha_num</strong></summary>

The field under this rule must be entirely alpha-numeric characters.

</details>

<details><summary><strong>alpha_dash</strong></summary>

The field under this rule may have alpha-numeric characters, as well as dashes and underscores.

</details>

<details><summary><strong>alpha_spaces</strong></summary>

The field under this rule may have alpha characters, as well as spaces.

</details>

<details><summary><strong>in</strong>:value_1,value_2,...</summary>

The field under this rule must be included in the given list of values.

This rule is using `in_array` to check the value.
By default `in_array` disable strict checking.
So it doesn't check data type.
If you want enable strict checking, you can invoke validator like this:

```php
$validation = $validator->validate($data, [
    'enabled' => [
        'required',
        $validator('in', [true, 1])->strict()
    ]
]);
```

Then 'enabled' value should be boolean `true`, or int `1`.

</details>

<details><summary><strong>not_in</strong>:value_1,value_2,...</summary>

The field under this rule must not be included in the given list of values.

This rule also using `in_array`. You can enable strict checking by invoking validator and call `strict()` like example in rule `in` above.

</details>

<details><summary><strong>min</strong>:number</summary>

The field under this rule must have a size greater or equal than the given number.

For string value, size corresponds to the number of characters. For integer or float value, size corresponds to its numerical value. For an array, size corresponds to the count of the array. If your value is numeric string, you can put `numeric` rule to treat its size by numeric value instead of number of characters.

You can also validate uploaded file using this rule to validate minimum size of uploaded file.
For example:

```php
$validation = $validator->validate([
    'photo' => $_FILES['photo']
], [
    'photo' => 'required|min:1M'
]);
```

</details>

<details><summary><strong>max</strong>:number</summary>

The field under this rule must have a size lower or equal than the given number.
Value size calculated in same way like `min` rule.

You can also validate uploaded file using this rule to validate maximum size of uploaded file.
For example:

```php
$validation = $validator->validate([
    'photo' => $_FILES['photo']
], [
    'photo' => 'required|max:2M'
]);
```

</details>

<details><summary><strong>between</strong>:min,max</summary>

The field under this rule must have a size between min and max params.
Value size calculated in same way like `min` and `max` rule.

You can also validate uploaded file using this rule to validate size of uploaded file.
For example:

```php
$validation = $validator->validate([
    'photo' => $_FILES['photo']
], [
    'photo' => 'required|between:1M,2M'
]);
```

</details>

<details><summary><strong>digits</strong>:value</summary>

The field under validation must be numeric and must have an exact length of `value`.

</details>

<details><summary><strong>digits_between</strong>:min,max</summary>

The field under validation must have a length between the given `min` and `max`.

</details>

<details><summary><strong>url</strong></summary>

The field under this rule must be valid url format.
By default it check common URL scheme format like `any_scheme://...`.
But you can specify URL schemes if you want.

For example:

```php
$validation = $validator->validate($inputs, [
    'random_url' => 'url',          // value can be `any_scheme://...`
    'https_url' => 'url:http',      // value must be started with `https://`
    'http_url' => 'url:http,https', // value must be started with `http://` or `https://`
    'ftp_url' => 'url:ftp',         // value must be started with `ftp://`
    'custom_url' => 'url:custom',   // value must be started with `custom://`
    'mailto_url' => 'url:mailto',   // value must conatin valid mailto URL scheme like `mailto:a@mail.com,b@mail.com`
    'jdbc_url' => 'url:jdbc',       // value must contain valid jdbc URL scheme like `jdbc:mysql://localhost/dbname`
]);
```

> For common URL scheme and mailto, we combine `FILTER_VALIDATE_URL` to validate URL format and `preg_match` to validate it's scheme.
  Except for JDBC URL, currently it just check a valid JDBC scheme.

</details>

<details><summary><strong>integer</strong></summary>
The field under t rule must be integer.

</details>

<details><summary><strong>boolean</strong></summary>

The field under this rule must be boolean. Accepted input are `true`, `false`, `1`, `0`, `"1"`, and `"0"`.

</details>

<details><summary><strong>ip</strong></summary>

The field under this rule must be valid ipv4 or ipv6.

</details>

<details><summary><strong>ipv4</strong></summary>

The field under this rule must be valid ipv4.

</details>

<details><summary><strong>ipv6</strong></summary>

The field under this rule must be valid ipv6.

</details>

<details><summary><strong>extension</strong>:extension_a,extension_b,...</summary>

The field under this rule must end with an extension corresponding to one of those listed.

This is useful for validating a file type for a given a path or url. The `mimes` rule should be used for validating uploads.

</details>

<details><summary><strong>array</strong></summary>

The field under this rule must be array.

</details>

<details><summary><strong>same</strong>:another_field</summary>

The field value under this rule must be same with `another_field` value.

</details>

<details><summary><strong>regex</strong>:/your-regex/</summary>

The field under this rule must be match with given regex.

</details>

<details><summary><strong>date</strong>:format</summary>

The field under this rule must be valid date format. Parameter `format` is optional, default format is `Y-m-d`.

</details>

<details><summary><strong>accepted</strong></summary>

The field under this rule must be one of `'on'`, `'yes'`, `'1'`, `'true'`, or `true`.

</details>

<details><summary><strong>present</strong></summary>

The field under this rule must be exists, whatever the value is.

</details>

<details><summary><strong>different</strong>:another_field</summary>

Opposite of `same`. The field value under this rule must be different with `another_field` value.

</details>

<details><summary><strong>after</strong>:tomorrow</summary>

Anything that can be parsed by `strtotime` can be passed as a parameter to this rule. Valid examples include :
- after:next week
- after:2016-12-31
- after:2016
- after:2016-12-31 09:56:02

</details>

<details><summary><strong>before</strong>:yesterday</summary>

This also works the same way as the [after rule](#after). Pass anything that can be parsed by `strtotime`

</details>

<details><summary><strong>callback</strong></summary>

You can use this rule to define your own validation rule.
This rule can't be registered using string pipe.
To use this rule, you should put Closure inside array of rules.

For example:

```php
$validation = $validator->validate($_POST, [
    'even_number' => [
        'required',
        function ($value) {
            // false = invalid
            return (is_numeric($value) AND $value % 2 === 0);
        }
    ]
]);
```

You can set invalid message by returning a string.
For example, example above would be:

```php
$validation = $validator->validate($_POST, [
    'even_number' => [
        'required',
        function ($value) {
            if (!is_numeric($value)) {
                return ":attribute must be numeric.";
            }
            if ($value % 2 !== 0) {
                return ":attribute is not even number.";
            }
            // you can return true or don't return anything if value is valid
        }
    ]
]);
```

> Note: `Rakit\Validation\Rules\Callback` instance is binded into your Closure.
  So you can access rule properties and methods using `$this`.

</details>

<details><summary><strong>nullable</strong></summary>

Field under this rule may be empty.

</details>

## Register/Override Rule

Another way to use custom validation rule is to create a class extending `Rakit\Validation\Rule`.
Then register it using `setValidator` or `addValidator`.

For example, you want to create `unique` validator that check field availability from database.

First, lets create `UniqueRule` class:

```php
<?php

use Rakit\Validation\Rule;

class UniqueRule extends Rule
{
    protected $message = ":attribute :value has been used";

    protected $fillableParams = ['table', 'column', 'except'];

    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function check($value): bool
    {
        // make sure required parameters exists
        $this->requireParameters(['table', 'column']);

        // getting parameters
        $column = $this->parameter('column');
        $table = $this->parameter('table');
        $except = $this->parameter('except');

        if ($except AND $except == $value) {
            return true;
        }

        // do query
        $stmt = $this->pdo->prepare("select count(*) as count from `{$table}` where `{$column}` = :value");
        $stmt->bindParam(':value', $value);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        // true for valid, false for invalid
        return intval($data['count']) === 0;
    }
}

```

Then you need to register `UniqueRule` instance into validator like this:

```php
use Rakit\Validation\Validator;

$validator = new Validator;

$validator->addValidator('unique', new UniqueRule($pdo));
```

Now you can use it like this:

```php
$validation = $validator->validate($_POST, [
    'email' => 'email|unique:users,email,exception@mail.com'
]);
```

In `UniqueRule` above, property `$message` is used for default invalid message. And property `$fillable_params` is used for `fillParameters` method (defined in `Rakit\Validation\Rule` class). By default `fillParameters` will fill parameters listed in `$fillable_params`. For example `unique:users,email,exception@mail.com` in example above, will set:

```php
$params['table'] = 'users';
$params['column'] = 'email';
$params['except'] = 'exception@mail.com';
```

> If you want your custom rule accept parameter list like `in`,`not_in`, or `uploaded_file` rules,
  you just need to override `fillParameters(array $params)` method in your custom rule class.

Note that `unique` rule that we created above also can be used like this:

```php
$validation = $validator->validate($_POST, [
    'email' => [
    	'required', 'email',
    	$validator('unique', 'users', 'email')->message('Custom message')
    ]
]);
```

So you can improve `UniqueRule` class above by adding some methods that returning its own instance like this:

```php
<?php

use Rakit\Validation\Rule;

class UniqueRule extends Rule
{
    ...

    public function table($table)
    {
        $this->params['table'] = $table;
        return $this;
    }

    public function column($column)
    {
        $this->params['column'] = $column;
        return $this;
    }

    public function except($value)
    {
        $this->params['except'] = $value;
        return $this;
    }

    ...
}

```

Then you can use it in more funky way like this:

```php
$validation = $validator->validate($_POST, [
    'email' => [
    	'required', 'email',
    	$validator('unique')->table('users')->column('email')->except('exception@mail.com')->message('Custom message')
    ]
]);
```

#### Implicit Rule

Implicit rule is a rule that if it's invalid, then next rules will be ignored. For example if attribute didn't pass `required*` rules, mostly it's next rules will also be invalids. So to prevent our next rules messages to get collected, we make `required*` rules to be implicit.

To make your custom rule implicit, you can make `$implicit` property value to be `true`. For example:

```php
<?php

use Rakit\Validation\Rule;

class YourCustomRule extends Rule
{

    protected $implicit = true;

}
```

#### Modify Value

In some case, you may want your custom rule to be able to modify it's attribute value like our `default/defaults` rule. So in current and next rules checks, your modified value will be used.

To do this, you should implements `Rakit\Validation\Rules\Interfaces\ModifyValue` and create method `modifyValue($value)` to your custom rule class.

For example:

```php
<?php

use Rakit\Validation\Rule;
use Rakit\Validation\Rules\Interfaces\ModifyValue;

class YourCustomRule extends Rule implements ModifyValue
{
    ...

    public function modifyValue($value)
    {
        // Do something with $value

        return $value;
    }

    ...
}
```

#### Before Validation Hook

You may want to do some preparation before validation running. For example our `uploaded_file` rule will resolves attribute value that come from `$_FILES` (undesirable) array structure to be well-organized array structure, so we can validate multiple file upload just like validating other data.

To do this, you should implements `Rakit\Validation\Rules\Interfaces\BeforeValidate` and create method `beforeValidate()` to your custom rule class.

For example:

```php
<?php

use Rakit\Validation\Rule;
use Rakit\Validation\Rules\Interfaces\BeforeValidate;

class YourCustomRule extends Rule implements BeforeValidate
{
    ...

    public function beforeValidate()
    {
        $attribute = $this->getAttribute(); // Rakit\Validation\Attribute instance
        $validation = $this->validation; // Rakit\Validation\Validation instance

        // Do something with $attribute and $validation
        // For example change attribute value
        $validation->setValue($attribute->getKey(), "your custom value");
    }

    ...
}
```
