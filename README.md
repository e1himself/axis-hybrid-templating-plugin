AxisHybridTemplatingPlugin
==========================

This plugin allows you to use different templating engines with symfony 1.x simultanously with
fallback in order of priority.

For example you can specify PHP template for applicaiton layout decorator and Twig template 
for action view.

Installation
------------

Use [Composer](http://getcomposer.org/). Just add this dependency to your `composer.json`:
```
  "require": {
    "axis/axis-hybrid-templating-plugin": "dev-master"
  }
```

Configuration
-------------

To enable Hybrid Templating in your project you should configure symfony to use Hybrid views 
for layouts and partials using `module.yml` config file:
```
all:
  view_class: \Axis\S1\HybridTemplating\View\Hybrid   # meens HybridView class
  partial_view_class: \Axis\S1\HybridTemplating\View\Hybrid   # means HybridPartialView class
```

That's all.

By default the only enabled templating engine is PHP. You can enable other templating engines
following instructions beyond.

### Templating engines priorities

Sometimes you'll have situations when there is more than one template for a given partial or 
controller view available in your project. So there is a questin what will be loaded first.
To control the templating engines priority you can use `priority` option of Hybrid Templating Loader
in your project's `factories.yml` file.

```
  hybrid_templating.loader:
    parameters:
      priority: [twig, php] # these are extensions of supported templating engines in order of priority
```
or
```
  hybrid_templating.loader:
    parameters:
      priority:
        twig: 100 # highest priority > will be checked first
        php:  1   # lowest priority > will be checked last
```

This configuration leads to unambiguous template loader order controlled by you.
By default templating engines are checked in order they were defined in `factories.yml` file.


### Twig

To use [Twig](http://twig.sensiolabs.org/) templating engine you should include 
`axis/axis-twig-plugin` into your project. Just add dependency to it in your `composer.json`:

```
  "require": {
    "axis/axis-twig-plugin": "dev-master"
  }
```

Next you should configure Twig engine for Hybrid Templating. Add this to your project's `factories.yml`:
```
all:
  hybrid_templating.engine.twig:
    class: \Axis\S1\HybridTemplating\Engine\TwigTemplatingEngine
    parameters:
      twig: context://twig
    tag: hybrid_templating.engine
```
-----
*Note*: This way of configuration is supported by 
[AxisServiceContainerPlugin](https://github.com/e1himself/axis-service-container-plugin)


Usage examples
--------------

Imagine you have an application:

```
apps/
  - hybrid/
    - config/
      - app.yml
      - module.yml
      - routing.yml
      - settings.yml
    - modules/
      - homepage/
        - actions/
          - actions.class.php
        - templates/
          - _partial.twig
          - indexSucces.php
    - templates/
      - layout.twig
```

As you have notices your controller view is using PHP templating while layout and partial are Twig templates. 
This will work tranparently switching between templating engines contexts passing specified variables between 
templates.