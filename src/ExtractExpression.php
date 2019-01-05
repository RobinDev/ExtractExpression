<?php

namespace rOpenDev\ExtractExpression;

// curl: automatic set referrer
// pomme de terre ne marche pas

class ExtractExpression
{
    public $onlyInSentence = false;
    public $expressionMaxWords = 5;

    /**
     * @var int Minimum value for wich one we keep a trail. Set to 0 to disabled keeping trail
     **/
    public $keepTrail = 3;

    protected $words;

    /**
     * @var array contain the source texts
     **/
    protected $text = [];
    /**
     * @var array textKey => [expression => number]
     **/
    protected $expressions = [];

    protected $mergedExpressions;

    /**
     * @var array containing the number of word analyzed for a specific source text
     **/
    protected $wordNumber = [];

    public function __construct()
    {
    }

    public function addContent(string $text)
    {
        $text = new ExtractExpressions($text);
        $text->onlyInSentence = $this->onlyInSentence;
        $text->expressionMaxWords = $this->expressionMaxWords;
        $text->keepTrail = $this->keepTrail;
        $text->extract();

        $this->text[] = $text;

        return $this;
    }

    public function exec()
    {
        $mergedExpressions = [];

        foreach ($this->text as $text) {
            $expressions = $text->getExpressionsByDensity();
            foreach ($expressions as $expression => $density) {
                $mergedExpressions[$expression] = (isset($mergedExpressions[$expression]) ? $mergedExpressions[$expression] : 0) + $density;
            }
        }

        arsort($mergedExpressions);

        $this->mergedExpressions = $mergedExpressions;
    }

    public function getExpressions(?int $number = null)
    {
        if (null === $this->mergedExpressions) {
            $this->exec();
        }

        return !$number ? $this->mergedExpressions : array_slice($this->getExpressions(), 0, $number);
    }

    public function getWordNumber()
    {
        if (null === $this->mergedExpressions) {
            $this->exec();
        }

        $wn = 0;
        foreach ($this->text as $text) {
            $wn = $wn + $text->getWordNumber();
        }

        return $wn;
    }

    /**
     * @return array containing sentence where we can find expresion
     */
    public function getTrail(string $expression)
    {
        if (null === $this->mergedExpressions) {
            $this->exec();
        }
        $trail = [];

        foreach ($this->text as $text) {
            $trail = array_merge($trail, $text->getTrail($expression));
        }

        return $trail;
    }

    public function getTrails()
    {
        $trail = [];

        foreach ($this->text as $text) {
            $trail = array_merge($trail, $text->getTrails());
        }

        return $trail;
    }
}
