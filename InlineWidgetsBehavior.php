<?php

namespace sadovojav\iwb;

use Yii;
use yii\base\Behavior;

/**
 * Class InlineWidgetsBehavior
 * @package sadovojav\iwb
 */
class InlineWidgetsBehavior extends Behavior
{
    /**
     * @var string marker of block begin
     */
    public $startBlock = '[*';

    /**
     * @var string marker of block end
     */
    public $endBlock = '*]';

    /**
     * @var string of widgets like 'common\components\widgets'
     */
    public $namespace = '';

    /**
     * @var string global classname suffix like 'Widget'
     */
    public $classSuffix = '';

    /**
     * @var int default cache duration
     */
    public $cacheDuration = 0;

    /**
     * @var array of allowed widgets
     */
    public $widgets = [];

    /**
     * @var string widget token
     */
    private $widgetToken;

    public function init()
    {
        $this->initToken();
    }

    /**
     * Content parser
     * Use $this->view->decodeWidgets($model->text) in view
     * @param $text
     * @return mixed
     */
    public function decodeWidgets($text)
    {
        $text = $this->clearAutoParagraphs($text);

        $text = $this->replaceBlocks($text);

        $text = $this->processWidgets($text);

        return $text;
    }

    /**
     * Content cleaner
     * Use $this->view->clearWidgets($model->text) in view
     * @param $text
     * @return mixed
     */
    public function clearWidgets($text)
    {
        $text = $this->clearAutoParagraphs($text);

        $text = $this->replaceBlocks($text);

        $text = $this->removeWidgets($text);

        return $text;
    }

    /**
     * Processing widget
     * @param $text
     * @return mixed
     */
    private function processWidgets($text)
    {
        if (preg_match('|\{' . $this->widgetToken . ':.+?' . $this->widgetToken . '\}|is', $text)) {
            foreach ($this->widgets as $alias) {
                $widget = $this->getClassByAlias($alias);

                while (preg_match('/\{' . $this->widgetToken . ':' . $widget . '(\|([^}]*)?)?' . $this->widgetToken . '\}/is', $text, $p)) {
                    $text = str_replace($p[0], $this->loadWidget($alias, isset($p[2]) ? $p[2] : ''), $text);
                }
            }

            return $text;
        }

        return $text;
    }

    /**
     * Remove widget from the content
     * @param $text
     * @return mixed
     */
    private function removeWidgets($text)
    {
        return preg_replace('|\{' . $this->widgetToken . ':.+?' . $this->widgetToken . '\}|is', '', $text);
    }

    /**
     * Token initialisation
     */
    private function initToken()
    {
        $this->widgetToken = md5(microtime());
    }

    /**
     * Replace blocks
     * @param $text
     * @return mixed
     */
    private function replaceBlocks($text)
    {
        $text = str_replace($this->startBlock, '{' . $this->widgetToken . ':', $text);

        $text = str_replace($this->endBlock, $this->widgetToken . '}', $text);

        return $text;
    }

    /**
     * Clear auto paragraphs
     * @param $output
     * @return mixed
     */
    private function clearAutoParagraphs($output)
    {
        $output = str_replace('<p>' . $this->startBlock, $this->startBlock, $output);

        $output = str_replace($this->endBlock . '</p>', $this->endBlock, $output);

        return $output;
    }

    /**
     * Load widget
     * @param $name
     * @param string $attributes
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    private function loadWidget($name, $attributes = '')
    {
        $attrs = $this->parseAttributes($attributes);
        $cache = $this->extractCacheDurationTime($attrs);
        $index = 'widget_' . $name . '_' . serialize($attrs);

        if ($cache && $cachedHtml = Yii::$app->cache->get($index)) {
            $html = $cachedHtml;
        } else {
            $widgetClass = $this->getFullClassName($name);
            $config['class'] = $widgetClass;
            $config = array_merge($config, $attrs);
            $html = Yii::createObject($config)->run();

            Yii::$app->cache->set($index, $html, $cache);
        }

        return $html;
    }

    /**
     * Parse attributes
     * @param $attributesString
     * @return array
     */
    private function parseAttributes($attributesString)
    {
        $params = explode(';', $attributesString);

        $attrs = [];

        foreach ($params as $param) {
            if ($param) {
                list($attribute, $value) = explode('=', $param);

                if ($value) {
                    $attrs[$attribute] = trim($value);
                }
            }
        }

        ksort($attrs);

        return $attrs;
    }

    /**
     * Extrat cache duration time
     * @param $attrs
     * @return int
     */
    private function extractCacheDurationTime(&$attrs)
    {
        if (isset($attrs['cache'])) {
            $this->cacheDuration = (int)$attrs['cache'];

            unset($attrs['cache']);
        }

        return $this->cacheDuration;
    }

    /**
     * Get full Class name
     * @param $name
     * @return string
     */
    private function getFullClassName($name)
    {
        $widgetClass = $name . $this->classSuffix;

        if ($this->getClassByAlias($widgetClass) == $widgetClass && $this->namespace) {
            $widgetClass = $this->namespace . '\\' . $widgetClass;
        }

        return $widgetClass;
    }

    /**
     * Get Class by alias
     * @param $alias
     * @return mixed
     */
    private function getClassByAlias($alias)
    {
        $paths = explode('\\', $alias);

        return array_pop($paths);
    }
} 
