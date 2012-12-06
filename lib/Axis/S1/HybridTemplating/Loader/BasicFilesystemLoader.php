<?php
/**
 * Date: 06.12.12
 * Time: 0:48
 * Author: Ivan Voskoboynyk
 */

namespace Axis\S1\HybridTemplating\Loader;

use Axis\S1\HybridTemplating\Engine\TemplatingEngine;

class BasicFilesystemLoader implements TemplateLoader
{
  protected $cache = array();

  /**
   * @var \sfApplicationConfiguration
   */
  protected $applicationConfiguration;

  /**
   * @var array|TemplatingEngine[]
   */
  protected $engines;

  /**
   * @param \sfApplicationConfiguration $application_configuration Current application configuration
   * @param array|TemplatingEngine[] $engines Templating engines
   * @param array $priority Templating engines priorities
   */
  function __construct($application_configuration, $engines = array(), $priority = array())
  {
    $this->applicationConfiguration = $application_configuration;
    $this->setEngines($engines, $priority);
  }

  /**
   * Returns full file path by $moduleName and $template
   *
   * Example:
   * getModuleTemplatePath('homepage', 'indexSuccess')
   *  = /path/to/project/apps/frontend/modules/homepage/templates/indexSuccess.php
   *
   * @param string $moduleName
   * @param string $template
   * @return null|string
   */
  public function getModuleTemplatePath($moduleName, $template)
  {
    if (!isset($this->cache['getModuleTemplatePath'][$moduleName][$template]))
    {
      $this->cache['getModuleTemplatePath'][$moduleName][$template] = null;
      foreach ($this->applicationConfiguration->getTemplateDirs($moduleName) as $dir)
      {
        if ($path = $this->getTemplatePath($dir.'/'.$template))
        {
          $this->cache['getModuleTemplatePath'][$moduleName][$template] = $path;
          break;
        }
      }
    }
    return $this->cache['getModuleTemplatePath'][$moduleName][$template];
  }

  /**
   * Returns full file directory path by $moduleName and $template
   *
   * Example:
   * getModuleTemplateDir('homepage', 'indexSuccess')
   *  = /path/to/project/apps/frontend/modules/homepage/templates
   *
   * @param string $moduleName Module name
   * @param $template
   * @return string
   */
  public function getModuleTemplateDir($moduleName, $template)
  {
    $path = $this->getModuleTemplatePath($moduleName, $template);
    return $path ? dirname($path) : null;
  }

  /**
   * Returns full decorator template path by $template name
   *
   * Example:
   * getDecoratorPath('layout')
   *  = /path/to/project/apps/frontend/templates/layout.php
   *
   * @param string $template
   * @return string|null
   */
  public function getDecoratorPath($template)
  {
    if (!isset($this->cache['getDecoratorPath'][$template]))
    {
      $this->cache['getDecoratorPath'][$template] = null;
      foreach ($this->applicationConfiguration->getDecoratorDirs() as $dir)
      {
        if ($path = $this->getTemplatePath($dir.'/'.$template))
        {
          $this->cache['getDecoratorPath'][$template] = $path;
          break;
        }
      }
    }
    return $this->cache['getDecoratorPath'][$template];
  }

  /**
   * Returns full decorator template directory path by $template name
   *
   * Example:
   * getDecoratorDir('layout')
   *  = /path/to/project/apps/frontend/templates
   *
   * @param string $template
   * @return string|null
   */
  public function getDecoratorDir($template)
  {
    $path = $this->getDecoratorPath($template);
    return $path ? dirname($path) : null;
  }

  /**
   * This method checks if the file "$template.$engine_extension" exists
   * for each templating engine in order of priority
   * and returns first matched path
   *
   * Example:
   * getTemplatePath('/path/to/project/apps/frontend/templates')
   *  = '/path/to/project/apps/frontend/templates.twig'
   *
   * @param string $template Template path without engine extension
   * @return string
   */
  public function getTemplatePath($template)
  {
    if (!isset($this->cache['getTemplatePath'][$template]))
    {
      $this->cache['getTemplatePath'][$template] = null;
      foreach ($this->engines as $engine)
      {
        if (is_readable($template.'.'.$engine->getExtension()))
        {
          $this->cache['getTemplatePath'][$template] = $template.'.'.$engine->getExtension();
          break;
        }
      }
    }
    return $this->cache['getTemplatePath'][$template];
  }

  /**
   * Checks if there is template for at least one templating engine for given $template
   *
   * Example:
   * templateExists('/path/to/project/apps/frontend/templates')
   *  = true
   *
   * @param string $template Template path
   * @return bool
   */
  public function templateExists($template)
  {
    return $this->getTemplatePath($template) !== null;
  }

  /**
   * @param array|TemplatingEngine[] $engines
   * @param array|string[] $priority Engine priorities
   *
   * Priorities array support two formats:
   * - [engine1, engine2, engine3] - order defines priority
   * - {engine1: 100, engine2: 10, engine3: 5} - values define priority (first engine - highest number)
   */
  protected function setEngines($engines, $priority = array())
  {
    $map = array();
    foreach ($engines as $engine)
    {
      $map[$engine->getExtension()] = $engine;
    }

    /**
     * this converts {engine1: 5, engine2: 100, engine3: 10}
     * to [engine2, engine3, engine1]
     */
    if (count($priority) > 0 && !is_numeric(key($priority))) // use values as priorities values
    {
      arsort($priority, SORT_NUMERIC);
      $priority = array_values($priority);
    }

    $ordered = array();
    // keep priority defined order
    foreach ($priority as $engineExtension)
    {
      $ordered[$engineExtension] = $map[$engineExtension];
      unset($map[$engineExtension]);
    }
    // append the rest as is
    $ordered += $map;

    $this->engines = $ordered;
  }
}
