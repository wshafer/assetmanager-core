<?php

namespace AssetManager\Core\Resolver;

use AssetManager\Core\Exception\InvalidArgumentException;
use Zend\Stdlib\ArrayUtils;

trait CollectionTrait
{
    /**
     * @var array the collections
     */
    protected $collections = array();

    /**
     * Set (overwrite) collections
     *
     * Collections should be arrays or Traversable objects with name => path pairs
     *
     * @param  array|\Traversable                  $collections
     * @throws InvalidArgumentException
     */
    public function setCollections($collections)
    {
        if (!is_array($collections) && !$collections instanceof \Traversable) {
            throw new InvalidArgumentException(sprintf(
                '%s: expects an array or Traversable, received "%s"',
                __METHOD__,
                (is_object($collections) ? get_class($collections) : gettype($collections))
            ));
        }

        if ($collections instanceof \Traversable) {
            $collections = ArrayUtils::iteratorToArray($collections);
        }

        $this->collections = $collections;
    }

    /**
     * Retrieve the collections
     *
     * @return array
     */
    public function getCollections()
    {
        return $this->collections;
    }
}
