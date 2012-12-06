<?php
/**
 * Date: 06.12.12
 * Time: 1:06
 * Author: Ivan Voskoboynyk
 */

namespace Axis\S1\HybridTemplating\Engine;

class PhpTemplatingEngine extends BaseTemplatingEngine
{
  protected $extension = 'php';

  /**
   * @return bool
   */
  public function isEscapingNeeded()
  {
    return true;
  }

  public function render($file, $vars = array())
  {
    extract($vars);

    // render
    ob_start();
    ob_implicit_flush(0);

    try
    {
      require($file);
    }
    catch (\Exception $e)
    {
      // need to end output buffering before throwing the exception #7596
      ob_end_clean();
      throw $e;
    }

    return ob_get_clean();
  }
}
