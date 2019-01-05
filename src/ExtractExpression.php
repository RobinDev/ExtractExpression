<?php

namespace rOpenDev\ExtractExpression;

// curl: automatic set referrer
// pomme de terre ne marche pas

class ExtractExpression
{
    public $onlyInSentence = false;
    public $expressionMaxWords = 5;

    /** @var int Minimum value for wich one we keep a trail. Set to 0 to disabled keeping trail * */
    public $keepTrail = 3;

    protected $trail = [];

    protected $words;
    /** @var string contain the source text * */
    protected $text = '';
    /** @var array with expression => number * */
    protected $expressions;

    /** @var int containing the number of word analyzed * */
    protected $wordNumber = 0;

    public function __construct()
    {
    }

    public function addContent(string $text)
    {
        $text = CleanText::stripHtmlTags($text);
        $text = CleanText::fixEncoding($text);

        $text = CleanText::removeDate($text);

        if ($this->onlyInSentence) {
            $text = CleanText::keepOnlySentence($text);
        }

        $this->text .= $text."\n\n";

        return $this;
    }

    protected function exec()
    {
        $this->expressions = [];
        $expressions = [];

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
                        //($wordNumber == 1 && in_array($expression, CleanText::$stopWords))
                        empty($expression)
                        || ((substr_count($expression, ' ') + 1) != $wordNumber) // We avoid sur-pondération
                        || preg_match('/^[0-9]+$/', $expression) // We avoid only number result
                    ) {
                        if (1 == $wordNumber) {
                            --$this->wordNumber;
                        }
                    } else {
                        $plus = 1 + substr_count(CleanText::removeStopWords($expression), ' ');
                        $expressions[$expression] = isset($expressions[$expression]) ? $expressions[$expression] + $plus : $plus;
                        if ($this->keepTrail > 0 && $expressions[$expression] > $this->keepTrail) {
                            $this->trail[$expression][] = $sentence;
                        }
                    }
                }
                ++$this->wordNumber;
            }
        }

        arsort($expressions);

        $this->expressions = $expressions;

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
            $this->exec();
        }

        return !$number ? $this->expressions : array_slice($this->getExpressions(), 0, $number);
    }

    /**
     * @return array with density in percent
     */
    public function getExpressionsByDensity(?int $number = null)
    {
        $expressions = $this->getExpressions($number);

        foreach ($expressions as $k => $v) {
            $expressions[$k] = round(($v / $this->wordNumber) * 10000) / 100;
        }

        return $expressions;
    }

    public function getNumberOfWordsInText()
    {
        if (null === $this->expressions) {
            $this->exec();
        }

        return $this->wordNumber;
    }

    /**
     * @return array containing sentence where we can find expresion
     */
    public function getTrail(string $expression)
    {
        if (null === $this->expressions) {
            $this->exec();
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
