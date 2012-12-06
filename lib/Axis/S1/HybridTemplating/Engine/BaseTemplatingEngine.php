<?php
/**
 * Date: 06.12.12
 * Time: 1:06
 * Author: Ivan Voskoboynyk
 */

namespace Axis\S1\HybridTemplating\Engine;

abstract class BaseTemplatingEngine implements TemplatingEngine
{
  /**
   * @var string
   */
  protected $extension;

  /**
   * @param string $extension
   * @throws \InvalidArgumentException If no template extension is defined
   */
  public function __construct($extension = null)
  {
    if ($extension !== null)
    {
      $this->extension = $extension;
    }
    if ($this->extension === null)
    {
      throw new \InvalidArgumentException('Template extension is not defined.');
    }
  }

  /**
   * @return string
   */
  public function getExtension()
  {
    return $this->extension;
  }

  /**
   * @return bool
   */
  public function isEscapingNeeded()
  {
    return true;
  }

  /**
   * @param string $template
   * @param array $vars
   * @return string
   */
  abstract public function render($template, $vars = array());
}
