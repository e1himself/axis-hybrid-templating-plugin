<?php
/**
 * Date: 06.12.12
 * Time: 4:27
 * Author: Ivan Voskoboynyk
 */
namespace Axis\S1\HybridTemplating\Engine;

use Axis\S1\HybridTemplating\Engine\TemplatingEngine;

class HybridTemplatingEngine extends BaseTemplatingEngine
{
  /**
   * @var string
   */
  protected $extension = '~'; // should not be used

  /**
   * @var array|TemplatingEngine[]
   */
  protected $engines = array();

  /**
   * @param array|TemplatingEngine[] $engines
   */
  public function __construct($engines = array())
  {
    foreach ($engines as $engine)
    {
      $this->engines[$engine->getExtension()] = $engine;
    }
  }

  /**
   * @param string $template
   * @param array $vars
   * @return string
   *
   * @throws \InvalidArgumentException If template format is not supported
   */
  public function render($template, $vars = array())
  {
    $ext = pathinfo($template, PATHINFO_EXTENSION);
    if (!isset($this->engines[$ext]))
    {
      throw new \InvalidArgumentException(sprintf(
        'Template format "%s" of file "%s" is not supported.',
        $ext,
        $template
      ));
    }
    return $this->engines[$ext]->render($template, $vars);
  }
}
