<?php

namespace App\Services\Inventory;

use ApaiIO\ApaiIO;
use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\Lookup;
use ApaiIO\Operations\Search;
use App\Services\Inventory\Contracts\Inventory;
use App\Services\Inventory\Responses\XmlToArray;

class ApaiInventory implements Inventory
{
    /**
     * The ApaiIO instance.
     *
     * @var \ApaiIO\ApaiIO
     */
    protected $apai;

    /**
     * The ASIN to lookup.
     *
     * @var string
     */
    protected $asin;

    /**
     * The Amazon.com Associate Tag
     *
     * @var string;
     */
    protected $associateTag;

    /**
     * The individual item from the APAI lookup.
     *
     * @var array
     */
    protected $item;

    /**
     * The callback from the APAI lookup.
     *
     * @var array
     */
    protected $lookup;

    /**
     * Creates a new Inventory instance.
     *
     * @param string $asin
     * @return void
     */
    public function __construct($asin = '')
    {
        $this->apai = $this->setApai();
        $this->asin = $asin;
        $this->associateTag = '';
        $this->item = NULL;
        $this->lookup = NULL;
    }

    /**
     * Gets an ASIN from an Amazon.com URL.
     *
     * @param string $url
     * @return $this
     */
    public function getAsinFromUrl($url) {
        preg_match('#\/([A-Za-z0-9]{10})#', $url, $matches);

        $this->asin = $matches[1];
        return $this;
    }

    /**
     * Gets the item from an APAI lookup callback.
     *
     * @return array|null
     */
    public function getItem()
    {
        $this->getLookup();

        $this->item = array_get($this->lookup, 'Items.Item', NULL);
        return $this;
    }

    /**
     * Gets a callback from an APAI lookup request.
     *
     * @return $this
     */
    public function getLookup()
    {
        $lookup = new Lookup();
        $lookup->setItemId($this->asin);
        $lookup->setResponseGroup(['Large']);

        $this->lookup = $this->apai->runOperation($lookup);
        return $this;
    }

    /**
     * Sets an APAI instance.
     *
     * @return object \ApaiIO\ApaiIO
     */
    public function setApai()
    {
        $conf = new GenericConfiguration();
        $xmlToArray = new XmlToArray();

        $conf->setCountry('com')->setAccessKey(env('AMAZON_APAI_KEY'))->setSecretKey(env('AMAZON_APAI_SECRET'))->setAssociateTag($this->associateTag)->setResponseTransformer($xmlToArray);

        return new ApaiIO($conf);
    }

    /**
     * Sets the ASIN to lookup.
     *
     * @return $this
     */
    public function setAsin($asin) {
        $this->asin = $asin;
        return $this;
    }

    /**
     * Sets the Amazon.com Associate Tag from the Request object
     *
     * @return $this
     */
    public function setAssociateTag($tag = '')
    {
        $this->associateTag = get_associate_tag_from_request() ?? $tag ?? env('AMAZON_ASSOCIATE_TAG');
        return $this;
    }
}
