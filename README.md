# Yii2 inline widget

This is a fork [howardEagle/yii2-inline-widgets-behavior](https://github.com/howardEagle/yii2-inline-widgets-behavior)

## Installation

### Composer

The preferred way to install this extension is through [Composer](http://getcomposer.org/).

Either run ```php composer.phar require sadovojav/yii2-inline-widgets-behavior ""dev-master"```

or add ```"sadovojav/yii2-inline-widgets-behavior": ""dev-master"``` to the require section of your ```composer.json```

## Config

1. Add the runtime widgets in your config file:

```php
'params' => [
     // ...
    'runtimeWidgets' => [
        'sadovojav\gallery\widgets\Gallery'
    ]
]
```

- runtimeWidgets must contain list of widgets

2. Add behavior in your controller:

```php
public function behaviors()
{
    return [
        'InlineWidgetsBehavior' => [
            'class' => sadovojav\iwb\InlineWidgetsBehavior::className(),
            'widgets' => Yii::$app->params['runtimeWidgets'],
        ]
    ];
}
```

- string `namespace` = `` - Default namespace
- string `startBlock` = `[*` - Start inline widget block
- string `endBlock` = `*]` - End inline widget block
- string `classSuffix` = `` - Default widget Class suffix
- string `cacheDuration` = `0` - Default cache duration

3. Add decodeWidget in view:

```php
<?= $this->context->decodeWidgets($model->text); ?>
```

## Using

Add decodeWidget in view:

```php
<?= $this->context->decodeWidgets($model->text); ?>
```

For insert widgets in content you can use string of this format in your text:
~~~
<startBlock><WidgetName>[|<attribute>=<value>[;<attribute>=<value>]]<endBlock>
~~~

##### For example:

```html
<h2>Lorem ipsum</h2>

<h2>Gallery 1</h2>
<p>[*Gallery*]</p>

<h2>Gallery (with attr)</h2>
<p>[*Gallery|template=tpl-1*]</p>

<h2>Gallery (with inner caching)</h2>
<p>[*Gallery|template=tpl-1;cache=300*]</p>
```