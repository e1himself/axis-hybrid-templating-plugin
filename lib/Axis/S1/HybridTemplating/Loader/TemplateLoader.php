<?php
/**
 * Date: 06.12.12
 * Time: 0:49
 * Author: Ivan Voskoboynyk
 */

namespace Axis\S1\HybridTemplating\Loader;

interface TemplateLoader
{
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
  public function getDecoratorPath($template);

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
  public function getDecoratorDir($template);

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
  public function getModuleTemplateDir($moduleName, $template);

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
  public function getModuleTemplatePath($moduleName, $template);

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
  public function templateExists($template);

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
  public function getTemplatePath($template);
}
