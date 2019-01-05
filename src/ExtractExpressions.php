<?php

namespace rOpenDev\ExtractExpression;

// curl: automatic set referrer
// pomme de terre ne marche pas

class ExtractExpressions
{
    public $onlyInSentence = false;
    public $expressionMaxWords = 5;
    public $keepTrail = 3;

    protected $text;

    protected $expressions = [];

    protected $wordNumber = 0;

    protected $trail = [];

    public function __construct(string $text)
    {
        $this->addText($text);
    }

    protected function addText(string $text)
    {
        $text = CleanText::stripHtmlTags($text);
        $text = CleanText::fixEncoding($text);

        $text = CleanText::removeDate($text);

        if ($this->onlyInSentence) {
            $text = CleanText::keepOnlySentence($text);
        }

        $this->text = $text;

        return $this;
    }

    public function getWordNumber()
    {
        return $this->wordNumber;
    }

    protected function incrementWordNumber($value)
    {
        $this->wordNumber = $this->getWordNumber() + $value;
    }

    public function getExpressionsByDensity()
    {
        $expressions = $this->expressions;

        foreach ($expressions as $k => $v) {
            $expressions[$k] = round(($v / $this->getWordNumber()) * 10000) / 100;
        }

        return $expressions;
    }

    public function extract()
    {
        if ($this->onlyInSentence) {
            $sentences = [];
            foreach (explode(chr(10), $this->text) as $paragraph) {
                $sentences = array_merge($sentences, CleanText::getSentences($paragraph));
            }
        } else {
            $sentences = explode(chr(10), trim($this->text));
        }

        foreach ($sentences as $sentence) {
            $sentence = CleanText::removePunctuation($sentence);

            $words = explode(' ', trim(strtolower($sentence)));

            foreach ($words as $key => $word) {
                for ($wordNumber = 1; $wordNumber < $this->expressionMaxWords; ++$wordNumber) {
                    $expression = '';
                    for ($i = 0; $i < $wordNumber; ++$i) {
                        if (isset($words[$key + $i])) {
                            $expression .= ($i > 0 ? ' ' : '').$words[$key + $i];
                        }
                    }

                    $expression = $this->cleanExpr($expression, $wordNumber);

                    if (
                        empty($expression)
                        || ((substr_count($expression, ' ') + 1) != $wordNumber) // We avoid sur-pondération
                        || !preg_match('/[a-z]/', $expression) // We avoid number or symbol only result
                    ) {
                        if (1 === $wordNumber) {
                            $this->incrementWordNumber(-1);
                        }
                    } else {
                        $plus = 1 + substr_count(CleanText::removeStopWords($expression), ' ');
                        $this->expressions[$expression] = isset($this->expressions[$expression]) ? $this->expressions[$expression] + $plus : $plus;
                        if ($this->keepTrail > 0 && $this->expressions[$expression] > $this->keepTrail) {
                            $this->trail[$expression][] = $sentence;
                        }
                    }
                }
                $this->incrementWordNumber(1);
            }
        }

        arsort($this->expressions);

        return $this->expressions;
    }

    protected function cleanExpr($expression, $wordNumber)
    {
        if ($wordNumber <= 2) {
            $expression = trim(CleanText::removeStopWords(' '.$expression.' '));
        } else {
            $expression = CleanText::removeStopWordsAtExtremity($expression);
            $expression = CleanText::removeStopWordsAtExtremity($expression);
            if (false === strpos($expression, ' ')) {
                $expression = trim(CleanText::removeStopWords(' '.$expression.' '));
            }
        }

        // Last Clean
        $expression = trim(preg_replace('/\s+/', ' ', $expression));
        if ('' == htmlentities($expression)) { //Avoid �
            $expression = '';
        }

        return $expression;
    }

    public function getExpressions(?int $number = null)
    {
        if (null === $this->expressions) {
            $this->extract();
        }

        return !$number ? $this->expressions : array_slice($this->getExpressions(), 0, $number);
    }

    /**
     * @return array containing sentence where we can find expresion
     */
    public function getTrail(string $expression)
    {
        if (null === $this->expressions) {
            $this->extract();
        }

        if (isset($this->trail[$expression])) {
            return $this->trail[$expression];
        }

        return [];
    }

    public function getTrails()
    {
        return $this->trail;
    }
}
