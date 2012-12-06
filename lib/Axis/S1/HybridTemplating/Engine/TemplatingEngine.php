<?php
/**
 * Date: 06.12.12
 * Time: 1:06
 * Author: Ivan Voskoboynyk
 */

namespace Axis\S1\HybridTemplating\Engine;

interface TemplatingEngine
{
  /**
   * @return string
   */
  public function getExtension();

  /**
   * @return bool
   */
  public function isEscapingNeeded();

  /**
   * @param string $template
   * @param array $vars
   * @return string
   */
  public function render($template, $vars = array());
}
