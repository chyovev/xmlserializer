<?php

namespace ChYovev\XMLSerializer;

use Closure;
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
     * A single Element can have multiple attributes.
     * Attributes are stored as an associative array
     * having the name of the attribute as key.
     * Attribute values are optional, keys are not.
     * Even though namespaces are also serialized as
     * attributes, they should be added separately
     * using the $namespaces property.
     * 
     * @var string[] – [key => value]
     */
    protected array $attributes = [];

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
     * By default special characters (such as HTML tags)
     * in an Element's contents/value are automatically
     * escaped during XML serialization to avoid them
     * from being interpreted as XML markup.
     * In order for an HTML string not to be escaped,
     * it should be marked as "character data" or CData
     * by setting the $isCData property to true.
     * This will wrap the value in a <![CDATA[  ]]> section.
     * 
     * @var bool
     */
    protected bool $isCData = false;

    /**
     * A comment string which is put at the top of the
     * element being serialized.
     * Comments should not contain two consecutive dashes
     * as this would cause XML validation to fail.
     * If set, they will be escaped during serialization.
     * Field is optional.
     * 
     * @var string
     */
    protected ?string $comment = null;

    /**
     * Same as the $comment property, but
     * is serialized above the Element.
     * Field is optional.
     * 
     * @var string
     */
    protected ?string $preComment = null;


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
    public function getAttributes(): array {
        return $this->attributes;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * @param string[] $attributes
     */
    public function setAttributes(array $attributes): static {
        foreach ($attributes as $attribute => $value) {
            $this->addAttribute($attribute, $value);
        }

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function addAttribute(string $attribute, string $value): static {
        $this->attributes[$attribute] = $value;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getValue(): ?string {
        return $this->value;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * The value mehtod is invoked by the add() method of
     * the Elementable trait and is meant to either populate
     * the value property of an Element, or to add sub-elements.
     * For details, see the add() method annotations.
     * 
     * @see    \ChYovev\XMLSerializer\Traits\Elementable :: add()
     * @param  null|string|callback $value
     * @return static
     */
    public function value(mixed $value = null): static {
        if ($this->isCallback($value)) {
            call_user_func($value, $this);
        }
        else {
            $this->setValue($value);
        }

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Check if the parameter passed to the value()
     * method is a callback method.
     * 
     * @return bool
     */
    private function isCallback(mixed $value): bool {
        return is_a($value, Closure::class);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setValue(string $value = null): static {
        $this->value = $value;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function isCData(): bool {
        return $this->isCData;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function markAsCData(): static {
        return $this->setCData(true);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setCData(bool $flag): static {
        $this->isCData = $flag;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getComment(): ?string {
        return $this->comment;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setComment(string $comment): static {
        $this->comment = $comment;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getPreComment(): ?string {
        return $this->preComment;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setPreComment(string $preComment): static {
        $this->preComment = $preComment;

        return $this;
    }

}