<?php

namespace ChYovev\XMLSerializer;

class Element
{

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

    /**
     * Each Element may have subelements, which can
     * also have subelements of their own, and so on.
     * Subelements cannot coexist with a value property
     * at the same time: if subelements are added, the
     * value property will be ignored.
     * Field is optional.
     * 
     * @var Element[]
     */
    protected array $elements = [];


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

    ///////////////////////////////////////////////////////////////////////////
    public function addElement(Element $element): static {
        $this->elements[] = $element;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * @return Element[] 
     */
    public function getElements(): array {
        return $this->elements;
    }

}