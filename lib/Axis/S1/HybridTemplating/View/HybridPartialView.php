<?php
/**
 * Date: 30.11.12
 * Time: 4:04
 * Author: Ivan Voskoboynyk
 */

namespace Axis\S1\HybridTemplating\View;

/**
 * @property \sfViewCacheManager viewCache
 */
class HybridPartialView extends HybridView
{
  protected
    $viewCache   = null,
    $checkCache  = false,
    $cacheKey    = null,
    $partialVars = array();

  /**
   * Constructor.
   *
   * @see sfView
   */
  public function initialize($context, $moduleName, $actionName, $viewName)
  {
    $ret = parent::initialize($context, $moduleName, $actionName, $viewName);

    $this->viewCache = $this->context->getViewCacheManager();

    if (\sfConfig::get('sf_cache'))
    {
      $this->checkCache = $this->viewCache->isActionCacheable($moduleName, $actionName);
    }

    return $ret;
  }

  /**
   * @param array $partialVars
   */
  public function setPartialVars(array $partialVars)
  {
    $this->partialVars = $partialVars;
    $this->getAttributeHolder()->add($partialVars);
  }

  /**
   * Configures template for this view.
   */
  public function configure()
  {
    parent::configure();

    $this->setDecorator(false);
    $this->setTemplate($this->actionName);
    if ('global' == $this->moduleName)
    {
      $this->setDirectory($this->loader->getDecoratorDir($this->getTemplate()));
    }
    else
    {
      $this->setDirectory($this->loader->getModuleTemplateDir($this->moduleName, $this->getTemplate()));
    }
  }

  /**
   * Renders the presentation.
   *
   * @throws \Exception
   * @return string Current template content
   */
  public function render()
  {
    if (\sfConfig::get('sf_debug') && \sfConfig::get('sf_logging_enabled'))
    {
      $timer = \sfTimerManager::getTimer(sprintf('Partial "%s/%s"', $this->moduleName, $this->actionName));
    }

    if ($retval = $this->getCache())
    {
      return $retval;
    }

    if ($this->checkCache)
    {
      /** @var $mainResponse \sfWebResponse */
      $mainResponse = $this->context->getResponse();

      $responseClass = get_class($mainResponse);
      /** @var $response \sfWebResponse */
      $response = new $responseClass($this->context->getEventDispatcher(), $mainResponse->getOptions());

      // the inner response has access to different properties, depending on whether it is marked as contextual in cache.yml
      if ($this->viewCache->isContextual($this->viewCache->getPartialUri($this->moduleName, $this->actionName, $this->cacheKey)))
      {
        $response->copyProperties($mainResponse);
      }
      else
      {
        $response->setContentType($mainResponse->getContentType());
      }

      $this->context->setResponse($response);
    }

    /** @var $mainResponse \sfWebResponse */
    /** @var $response \sfWebResponse */

    try
    {
      // execute pre-render check
      $this->preRenderCheck();

      $this->getAttributeHolder()->set('sf_type', 'partial');

      // render template
      $templateFile = $this->loader->getTemplatePath($this->getDirectory().'/'.$this->getTemplate());
      $retval = $this->renderFile($templateFile);
    }
    catch (\Exception $e)
    {
      if ($this->checkCache)
      {
        $this->context->setResponse($mainResponse);
        $mainResponse->merge($response);
      }

      throw $e;
    }

    if ($this->checkCache)
    {
      $retval = $this->viewCache->setPartialCache($this->moduleName, $this->actionName, $this->cacheKey, $retval);
      $this->context->setResponse($mainResponse);
      $mainResponse->merge($response);
    }

    if (\sfConfig::get('sf_debug') && \sfConfig::get('sf_logging_enabled'))
    {
      /** @var $timer \sfTimer */
      $timer->addTime();
    }

    return $retval;
  }

  public function getCache()
  {
    if (!$this->checkCache)
    {
      return null;
    }

    $this->cacheKey = $this->viewCache->checkCacheKey($this->partialVars);
    if ($retval = $this->viewCache->getPartialCache($this->moduleName, $this->actionName, $this->cacheKey))
    {
      return $retval;
    }

    return null;
  }
}
