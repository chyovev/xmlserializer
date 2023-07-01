<?php

namespace ChYovev\XMLSerializer;

use Closure;
use UnexpectedValueException;
use ChYovev\XMLSerializer\Interfaces\XmlSerializable;
use ChYovev\XMLSerializer\Traits\Elementable;

class Element
{

    use Elementable;

    /**
     * Each Element should have a tag name which is
     * used during the XML serialization.
     * Tag names are case-sensitive and cannot
     * contain spaces or special characters.
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
     * using the namespace properties below.
     * Field is optional.
     * 
     * @var array<string, string> – [key => value]
     */
    protected array $attributes = [];

    /**
     * Each element can belong to a certain namespace;
     * usually namespaces are associated with a prefix
     * for the serialized tag name.
     * Fields are optional.
     * 
     * @see self :: setNamespace()
     * @see \ChYovev\XMLSerializer\NamespaceHelper
     * @var string
     */
    protected ?string $namespaceUri    = null;
    protected ?string $namespacePrefix = null;

    /**
     * All elements which are direct ascendents
     * of the Document object are considered root
     * elements.
     * Root elements should have the document
     * namespaces declared as attributes.
     * Normally there's just one root element.
     * The property is set automatically by the
     * getTempElement() trait method.
     * 
     * @see \ChYovev\XMLSerializer\Traits\Elementable :: getTempElement()
     * @see \ChYovev\XMLSerializer\Writer :: serializeRootNamespaces()
     * @var bool
     */
    protected bool $isRoot = false;

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
     * By default Element values and attributes
     * are serialized the way they are passed to
     * the XMLWriter, i.e. untrimmed.
     * Trimming can be turned on for a single
     * Element by the trimValues() method.
     * Alternatively, it can also be turned on
     * globally to apply to all elements by the
     * trimValues() method invoked on the Document
     * object. From then on, a single Element can
     * be excluded from trimming using the Element's 
     * noTrimValues() method.
     * 
     * @see \ChYovev\XMLSerializer\Writer :: shouldTrimValues()
     * @var bool
     */
    protected ?bool $trimValues = null;

    /**
     * By default Element attributes are always
     * serialized, even if they have empty values,
     * unless the noEmptyAttributes() method is
     * called on that element.
     * Alternatively, it can also be turned on
     * globally to apply to all elements via the
     * noEmptyAttributes() method invoked on the
     * Document object. From then on, a single
     * Element can be excluded from this rule
     * by calling the noSkipEmptyAttributes() method.
     * 
     * @see \ChYovev\XMLSerializer\Writer :: shouldSkipAttribute()
     * @var bool
     */
    protected ?bool $skipEmptyAttributes = null;

    /**
     * By default, Elements with no/empty value will
     * still be serialized as empty tags, e.g.: <book />
     * unless $skipTagIfEmpty is set to true.
     * Alternatively, empty tags can be skipped globally
     * by invoking the skipEmptyTags() method on the
     * Document object. From then on, a single Element
     * can be excluded from skipping by calling the
     * allowEmptyTag() method on the Element.
     * 
     * @var bool
     */
    protected ?bool $skipTagIfEmpty = null;

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

    /**
     * Which document the Element belongs to.
     * The document object, including its
     * global variables, can be accessed at
     * all times in all sub-levels using the
     * getter method.
     * 
     * @see \ChYovev\XMLSerializer\Document :: $globalVars
     * @var Document
     */
    protected Document $document;


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
    public function hasNamespace(): bool {
        return isset($this->namespaceUri);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getNamespaceUri(): ?string {
        return $this->namespaceUri;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setNamespaceUri(string $namespaceUri): static {
        $this->namespaceUri = $namespaceUri;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getNamespacePrefix(): ?string {
        return $this->namespacePrefix;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setNamespacePrefix(string $namespacePrefix): static {
        $this->namespacePrefix = $namespacePrefix;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Depending on whether the namespace is pre-declared as a root
     * namespace, the Element namespace URI and prefix may be
     * serialized differently.
     * 
     * @see \ChYovev\XMLSerializer\NamespaceHelper
     * @param string $uri    – namespace URI, required
     * @param string $prefix – namespace prefix, optional
     */
    public function setNamespace(string $uri, string $prefix = null): static {
        $this->setNamespaceUri($uri);

        if ( ! is_null($prefix)) {
            $this->setNamespacePrefix($prefix);
        }

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function isRoot(): bool {
        return $this->isRoot;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function markAsRoot(): static {
        return $this->setIsRoot(true);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setIsRoot(bool $flag): static {
        $this->isRoot = $flag;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getValue(): ?string {
        return $this->value;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * The parseValue() mehtod is invoked by the add() method of
     * the Elementable trait and is meant to either populate
     * the value property of an Element, or to add sub-elements.
     * For details, see the add() method annotations.
     * 
     * @see    \ChYovev\XMLSerializer\Traits\Elementable :: add()
     * @throws UnexpectedValueException – value is object, but it is not serializable
     * @param  null|string|callback|XmlSerializable $value
     * @return static
     */
    public function parseValue(mixed $value = null): static {
        if ($this->isCallback($value)) {
            call_user_func($value, $this);
        }
        elseif ($this->isSerializable($value)) {
            $value->xmlSerialize($this);
        }
        elseif (is_object($value)) {
            throw new UnexpectedValueException(sprintf("Object %s passed as Element value does not implement the XmlSerializable interface", get_class($value)));
        }
        else {
            $this->setValue($value);
        }

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Check if the parameter passed to the parseValue()
     * method is a callback method.
     * 
     * @param  mixed $value
     * @return bool
     */
    private function isCallback(mixed $value): bool {
        return is_a($value, Closure::class);
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Check whether the parameter passed to the parseValue()
     * method is an XmlSerializable object, i.e. it implements
     * the xmlSerialize() method in order to add sub-elements
     * to an element.
     * 
     * @param  mixed $value
     * @return bool
     */
    private function isSerializable(mixed $value): bool {
        return is_a($value, XmlSerializable::class);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setValue(string $value = null): static {
        $this->value = $value;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function shouldTrimValues(): ?bool {
        return $this->trimValues;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Mark a single Element for value and attribute trimming.
     */
    public function trimValues(): static {
        return $this->setTrimValues(true);
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * To be used only if value trimming is set globally
     * for the whole Document object.
     * 
     * @see \ChYovev\XMLSerializer\Document :: trimValues()
     */
    public function noTrimValues(): static {
        return $this->setTrimValues(false);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setTrimValues(bool $flag): static {
        $this->trimValues = $flag;
        
        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function shouldSkipEmptyAttributes(): ?bool {
        return $this->skipEmptyAttributes;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Mark a single Element for empty attribute skipping.
     * Usually called by the respective chain method declared
     * in the Elementable trait.
     * 
     * @see \ChYovev\XMLSerializer\Traits\Elementable :: skipEmptyAttributes()
     */
    public function noEmptyAttributes(): static {
        return $this->setSkipEmptyAttributes(true);
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * To be used only if empty attribute skipping is set
     * globally for the whole Document object, usually called
     * by the respective chain method declared in the Elementable trait.
     * 
     * @see \ChYovev\XMLSerializer\Traits\Elementable :: noSkipEmptyAttributes()
     * @see \ChYovev\XMLSerializer\Document :: noEmptyAttributes()
     */
    public function allowEmptyAttributes(): static {
        return $this->setSkipEmptyAttributes(false);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setSkipEmptyAttributes(bool $flag): static {
        $this->skipEmptyAttributes = $flag;
        
        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function shouldSkipIfEmpty(): ?bool {
        return $this->skipTagIfEmpty;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function skipTagIfEmpty(): static {
        return $this->setSkipTagIfEmpty(true);
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Exclude an element from empty-tag skipping. To be used
     * only if empty-tag skipping is turned on globally for the
     * whole Document.
     * Current method is usually called by the chain method
     * declared in the Elementable trait.
     * 
     * @see \ChYovev\XMLSerializer\Traits\Elementable :: noSkipIfEmpty()
     * @see \ChYovev\XMLSerializer\Document :: skipEmptyTags()
     */
    public function allowEmptyTag(): static {
        return $this->setSkipTagIfEmpty(false);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setSkipTagIfEmpty(bool $flag): static {
        $this->skipTagIfEmpty = $flag;

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

    ///////////////////////////////////////////////////////////////////////////
    public function getDocument(): Document {
        return $this->document;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setDocument(Document $document) {
        $this->document = $document;

        return $this;
    }

}