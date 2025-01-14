<?php namespace Anomaly\Streams\Platform\View\Twig;

/**
 * This file is part of the TwigBridge package.
 *
 * @copyright Robert Crowe <hello@vivalacrowe.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\View\Engines\CompilerEngine;
use Twig\Error\Error;
use Twig\Error\LoaderError;
use ErrorException;


use Twig\Loader\LoaderInterface;
//use TwigBridge\Twig\Loader;
use Anomaly\Streams\Platform\View\Twig\OriginalLoader;

/**
 * View engine for Twig files.
 */
class Engine extends CompilerEngine
{
    /**
     * Data that is passed to all templates.
     *
     * @var array
     */
    protected $globalData = [];

    /**
     * Used to find the file that has failed.
     *
     * @var \TwigBridge\Twig\Loader
     */
    protected $loader = [];

    /**
     * Create a new Twig view engine instance.
     *
     * @param \TwigBridge\Engine\Compiler        $compiler
     * @param \TwigBridge\Twig\Loader            $loader
     * @param array                              $globalData
     */
    public function __construct(Compiler $compiler, OriginalLoader $loader, array $globalData = [])
    {
        parent::__construct($compiler);

        $this->loader     = $loader;
        $this->globalData = $globalData;
    }

    /**
     * Get the global data.
     *
     * @return array
     */
    public function getGlobalData()
    {
        return $this->globalData;
    }

    /**
     * Set global data sent to the view.
     *
     * @param array $globalData Global data.
     *
     * @return void
     */
    public function setGlobalData(array $globalData)
    {
        $this->globalData = $globalData;
    }

    /**
     * Get the evaluated contents of the view.
     *
     * @param string $path Full file path to Twig template.
     * @param array  $data
     *
     * @throws Error|\ErrorException When unable to load the requested path.
     *
     * @return string
     */
    public function get($path, array $data = [])
    {
        $data = array_merge($this->globalData, $data);

        try {
            $content = $this->compiler->load($path)->render($data);
        } catch (Error $ex) {
            $this->handleTwigError($ex);
        }

        return $content;
    }

    /**
     * Handle a TwigError exception.
     *
     * @param Error $ex
     *
     * @throws Error|\ErrorException
     */
    protected function handleTwigError(Error $ex)
    {
        $context = $ex->getSourceContext();

        if (null === $context) {
            throw $ex;
        }

        $templateFile = $context->getPath();
        $templateLine = $ex->getTemplateLine();

        if ($templateFile && file_exists($templateFile)) {
            $file = $templateFile;
        } elseif ($templateFile) {
            // Attempt to locate full path to file
            try {
                if ($this->loader instanceof Loader) {
                    //Outside of unit test, we should be able to load the file
                    $file = $this->loader->findTemplate($templateFile);
                }
            } catch (LoaderError $exception) {
                // Unable to load template
            }
        }

        if (isset($file)) {
            $ex = new ErrorException($ex->getMessage(), 0, 1, $file, $templateLine, $ex);
        }

        throw $ex;
    }
}
