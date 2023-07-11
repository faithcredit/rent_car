<?php

/**
 * Custom exceptions
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\U
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Dup installer custom exception
 */
class DupxException extends Exception
{
    /**
     *
     * @var string // formatted html string
     */
    protected $longMsg = '';
    protected $faqLink = false;

    /**
     *
     * @param string    $shortMsg
     * @param string    $longMsg
     * @param string    $faqLinkUrl
     * @param string    $faqLinkLabel
     * @param int       $code
     * @param Exception $previous
     */
    public function __construct($shortMsg, $longMsg = '', $faqLinkUrl = '', $faqLinkLabel = '', $code = 0, Exception $previous = null)
    {
        parent::__construct($shortMsg, $code, $previous);
        $this->longMsg = (string) $longMsg;
        if (strlen($faqLinkUrl) > 0) {
            $this->faqLink = array(
                'url' => $faqLinkUrl,
                'label' => $faqLinkLabel
            );
        }
    }

    public function getLongMsg()
    {
        return $this->longMsg;
    }

    public function haveFaqLink()
    {
        return $this->faqLink !== false;
    }

    public function getFaqLinkUrl()
    {
        if ($this->haveFaqLink()) {
            return $this->faqLink['url'];
        } else {
            return '';
        }
    }

    public function getFaqLinkLabel()
    {
        if ($this->haveFaqLink()) {
            return $this->faqLink['label'];
        } else {
            return '';
        }
    }

    // custom string representation of object
    public function __toString()
    {
        $result = __CLASS__ . ": [{$this->code}]: {$this->message}";
        if ($this->haveFaqLink()) {
            $result .= "\n\tSee FAQ " . $this->faqLink['label'] . ': ' . $this->faqLink['url'];
        }
        if (!empty($this->longMsg)) {
            $result .= "\n\t" . strip_tags($this->longMsg);
        }
        $result .= "\n";
        return $result;
    }
}
