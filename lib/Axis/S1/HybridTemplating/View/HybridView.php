<?php
/**
 * Date: 30.11.12
 * Time: 4:04
 * Author: Ivan Voskoboynyk
 */

namespace Axis\S1\HybridTemplating\View;

/**
 * @property \sfContext $context
 * @property \sfEventDispatcher $dispatcher
 * @property \sfViewParameterHolder $attributeHolder
 */
class HybridView extends \sfView
{
  // use empty extension to enable multiple rendering engines
  protected $extension = '';

  /**
   * @var \Axis\S1\HybridTemplating\Loader\BasicFilesystemLoader
   */
  protected $loader;

  /**
   * @var \Axis\S1\HybridTemplating\Engine\TemplatingEngine
   */
  protected $engine;

  /**
   * @var string
   */
  protected $templateFile;

  /**
   * @param \sfContext $context
   * @param string $moduleName
   * @param string $actionName
   * @param string $viewName
   * @return bool
   */
  public function initialize($context, $moduleName, $actionName, $viewName)
  {
    $this->loader = $context->get('hybrid_templating.loader');
    $this->engine = $context->get('hybrid_templating.engine');

    return parent::initialize($context, $moduleName, $actionName, $viewName);
  }

  /**
   * Executes any presentation logic and set template attributes.
   */
  function execute()
  {

  }

  /**
   * Configures template.
   * @refactored
   */
  function configure()
  {
    // store our current view
    $this->context->set('view_instance', $this);

    // require our configuration
    require($this->context->getConfigCache()->checkConfig('modules/'.$this->moduleName.'/config/view.yml'));

    // set template directory
    if (!$this->directory)
    {
      $this->setDirectory($this->loader->getModuleTemplateDir($this->moduleName, $this->getTemplate()));
    }
  }

  /**
   * Loads core and standard helpers to be use in the template.
   * @refactored
   */
  protected function loadCoreAndStandardHelpers()
  {
    static $coreHelpersLoaded = 0;

    if ($coreHelpersLoaded)
    {
      return;
    }

    $coreHelpersLoaded = 1;

    $helpers = array_unique(array_merge(array('Helper', 'Url', 'Asset', 'Tag', 'Escaping'), \sfConfig::get('sf_standard_helpers')));
    $this->context->getConfiguration()->loadHelpers($helpers);
  }


  /**
   * Retrieves the template engine associated with this view.
   *
   * @return \Axis\S1\HybridTemplating\Engine\TemplatingEngine A template engine instance
   */
  function getEngine()
  {
    return $this->engine;
  }

  /**
   * Renders the presentation.
   *
   * @return string A string representing the rendered presentation
   */
  function render()
  {
    $content = null;
    if (\sfConfig::get('sf_cache'))
    {
      /** @var $viewCache \sfViewCacheManager */
      $viewCache = $this->context->getViewCacheManager();
      $uri = $viewCache->getCurrentCacheKey();

      if (null !== $uri)
      {
        list($content, $decoratorTemplate) = $viewCache->getActionCache($uri);
        if (null !== $content)
        {
          $this->setDecoratorTemplate($decoratorTemplate);
        }
      }
    }

    // render template if no cache
    if (null === $content)
    {
      // execute pre-render check
      $this->preRenderCheck();

      $this->attributeHolder->set('sf_type', 'action');

      // render template file
      $templateFile = $this->loader->getTemplatePath($this->getDirectory().'/'.$this->getTemplate());
      $content = $this->renderFile($templateFile);

      /** @var $viewCache \sfViewCacheManager */
      /** @var $uri string */
      if (\sfConfig::get('sf_cache') && null !== $uri)
      {
        $content = $viewCache->setActionCache($uri, $content, $this->isDecorator() ? $this->getDecoratorDirectory().'/'.$this->getDecoratorTemplate() : false);
      }
    }

    // now render decorator template, if one exists
    if ($this->isDecorator())
    {
      $content = $this->decorate($content);
    }

    return $content;
  }

  protected function renderFile($_sfFile)
  {
    if (\sfConfig::get('sf_logging_enabled'))
    {
      $this->dispatcher->notify(new \sfEvent($this, 'application.log', array(sprintf('Render "%s"', $_sfFile))));
    }

    $this->loadCoreAndStandardHelpers();

    // EXTR_REFS can't be used (see #3595 and #3151)
    $vars = $this->attributeHolder->toArray();
    return $this->getEngine()->render($_sfFile, $vars);
  }

  /**
   * Loop through all template slots and fill them in with the results of presentation data.
   *
   * @param  string $content  A chunk of decorator content
   *
   * @throws \sfRenderException
   * @return string A decorated template
   */
  protected function decorate($content)
  {
    if (\sfConfig::get('sf_logging_enabled'))
    {
      $this->dispatcher->notify(new \sfEvent($this, 'application.log', array(sprintf('Decorate content with "%s/%s"', $this->getDecoratorDirectory(), $this->getDecoratorTemplate()))));
    }

    // set the decorator content as an attribute
    $attributeHolder = $this->attributeHolder;

    $this->attributeHolder = $this->initializeAttributeHolder(array('sf_content' => new \sfOutputEscaperSafe($content)));
    $this->attributeHolder->set('sf_type', 'layout');

    // check to see if the decorator template exists
    if (!$this->loader->templateExists($this->getDecoratorDirectory().'/'.$this->getDecoratorTemplate()))
    {
      throw new \sfRenderException(sprintf('The decorator template "%s" does not exist or is unreadable in "%s".', $this->decoratorTemplate, $this->decoratorDirectory));
    }

    // render the decorator template and return the result
    $templateFile = $this->loader->getTemplatePath($this->getDecoratorDirectory().'/'.$this->getDecoratorTemplate());
    $ret = $this->renderFile($templateFile);

    $this->attributeHolder = $attributeHolder;

    return $ret;
  }

  /**
   * Sets the template for this view.
   *
   * If the template path is relative, it will be based on the currently
   * executing module's template sub-directory.
   *
   * @param string $template  An absolute or relative filesystem path to a template
   */
  public function setTemplate($template)
  {
    if (\sfToolkit::isPathAbsolute($template))
    {
      $this->directory = dirname($template);
      $this->template  = basename($template);
    }
    else
    {
      $this->directory = $this->loader->getModuleTemplateDir($this->moduleName, $template);
      $this->template = $template;
    }
    $this->templateFile = $this->loader->getTemplatePath($this->directory . '/' . $this->template);
  }

  /**
   * Sets the decorator template for this view.
   *
   * If the template path is relative, it will be based on the currently
   * executing module's template sub-directory.
   *
   * @param string $template  An absolute or relative filesystem path to a template
   */
  public function setDecoratorTemplate($template)
  {
    if (false === $template)
    {
      $this->setDecorator(false);

      return;
    }
    else if (null === $template)
    {
      return;
    }

    if (\sfToolkit::isPathAbsolute($template))
    {
      $this->decoratorDirectory = dirname($template);
      $this->decoratorTemplate  = basename($template);
    }
    else
    {
      $this->decoratorDirectory = $this->loader->getDecoratorDir($template);
      $this->decoratorTemplate = $template;
    }

    // set decorator status
    $this->decorator = true;
  }

  /**
   * Executes a basic pre-render check to verify all required variables exist
   * and that the template is readable.
   *
   * @throws \sfRenderException If the pre-render check fails
   */
  protected function preRenderCheck()
  {
    if (null === $this->template)
    {
      // a template has not been set
      throw new \sfRenderException('A template has not been set.');
    }

    if (!$this->loader->templateExists($this->directory.'/'.$this->template))
    {
      // 404?
      /** @var $response \sfWebResponse */
      $response = $this->context->getResponse();
      if ('404' == $response->getStatusCode())
      {
        // use default exception templates
        /** @var $request \sfWebRequest */
        $request = $this->context->getRequest();
        $this->template = \sfException::getTemplatePathForError($request->getRequestFormat(), false);
        $this->directory = dirname($this->template);
        $this->template = basename($this->template);
        $this->setAttribute('code', '404');
        $this->setAttribute('text', 'Not Found');
      }
      else
      {
        throw new \sfRenderException(sprintf('The template "%s" does not exist or is unreadable in "%s".', $this->template, $this->directory));
      }
    }
  }

  /**
   * @param array $attributes
   * @return \sfViewParameterHolder
   */
  protected function initializeAttributeHolder($attributes = array())
  {
    $attributeHolder = parent::initializeAttributeHolder($attributes);
    $attributeHolder->setEscaping($this->getEngine()->isEscapingNeeded());
    return $attributeHolder;
  }
}
