<?php
namespace DuplicatorPro\Guzzle\Http\Message\Header;

defined("ABSPATH") or die("");

use DuplicatorPro\Guzzle\Http\Message\Header;

/**
 * Default header factory implementation
 */
class HeaderFactory implements HeaderFactoryInterface
{
    /** @var array */
    protected $mapping = array(
        'cache-control' => 'DuplicatorPro\Guzzle\Http\Message\Header\CacheControl',
        'link'          => 'DuplicatorPro\Guzzle\Http\Message\Header\Link',
    );

    public function createHeader($header, $value = null)
    {
        $lowercase = strtolower($header);

        return isset($this->mapping[$lowercase])
            ? new $this->mapping[$lowercase]($header, $value)
            : new Header($header, $value);
    }
}
