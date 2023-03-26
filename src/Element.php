<?php

namespace ChYovev\XMLSerializer;

use ChYovev\XMLSerializer\Traits\Elementable;

class Element
{

    use Elementable;

    /**
     * Each Element should have a tag name which is
     * used during the XML serialization.
     * Tag names are case-sensitive and cannot
     * contain spaces.
     * Field is required.
     * 
     * @var string
     */
    protected ?string $tagName = null;

    /**
     * Each Element typically has a value.
     * Elements with no values are also supported.
     * Alternatively, an Element can have a set of
     * subelements – in such a case the value property,
     * even if populated, will be ignored.
     * Field is optional.
     * 
     * @var string
     */
    protected ?string $value = null;


    ///////////////////////////////////////////////////////////////////////////
    /**
     * The tagName property can be populated upon Element object creation.
     */
    public function __construct(string $tagName = null) {
        if ( ! is_null($tagName)) {
            $this->setTagName($tagName);
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getTagName(): ?string {
        return $this->tagName;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setTagName(string $tagName): static {
        $this->tagName = $tagName;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getValue(): ?string {
        return $this->value;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setValue(string $value = null): static {
        $this->value = $value;

        return $this;
    }

}