<?php

namespace ChYovev\XMLSerializer;

use ChYovev\XMLSerializer\Traits\Elementable;

class Document
{

    use Elementable;

    /**
     * The version number of the generated XML document.
     * If no value is provided, 1.0 will be used by default.
     * Used only when $useProlog = true.
     * 
     * @var string
     */
    protected ?string $xmlVersion = null;

    /**
     * The encoding of the generated XML document.
     * Used only when $useProlog = true.
     * Gets set either in the constructor,
     * or via a public setter.
     * 
     * @var string
     */
    protected ?string $encoding = null;

    /**
     * Whether the XML document should depend on
     * an external (DTD) markup declaration.
     * DTD stands for Document type definition.
     * 
     * @var bool
     */
    protected ?bool $standAlone = null;

    /**
     * By default all XML documents have a prolog
     * (i.e. an opening tag <?xml ?>).
     * Using the skipProlog() method one can choose
     * to exclude the prolog from the generated XML.
     * 
     * @var bool
     */
    protected bool $useProlog = true;

    /**
     * Whether to indent nested elements.
     * Default is true.
     * Can be disabled either by using setIndent(false)
     * or a shortcut method: noIndent().
     * 
     * @var bool
     */
    protected bool $useIndent = true;

    /**
     * What type of indentation to use for
     * nested elements when $useIndent = true.
     * Default value is 4 white spaces, but it
     * can be changed to a tab (\t), a dot (.),
     * two dots (..) or whatever.
     * 
     * @var string
     */
    protected string $indentString = '    ';

    /**
     * All namespaces used in the XML document;
     * serialized as attributes in the root element.
     * Elements belonging to a pre-declared namespace
     * will be serialized with the respective namespace
     * prefix, but not with the actual namespace as an
     * attribute (as it's pre-declared in the root element).
     * Structure of the property:
     * 
     *     [URI => prefix]
     * 
     * Field is optional.
     * 
     * @see \ChYovev\XMLSerializer\NamespaceHelper
     * @var array  
     */
    protected array $namespaces = [];

    /**
     * Sometimes it may be useful for a Document
     * to pass a "global variable" to all elements,
     * no matter in which sub-level they reside.
     * Since the Document object itself can be
     * accessed using the getDocument() method,
     * one can use the getGlobalVars() method on it.
     * 
     * @var array
     */
    protected array $globalVars = [];

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
    protected bool $trimValues = false;

    /**
     * By default Element attributes are always
     * serialized, even if they have empty values,
     * unless the skipEmptyAttributes() method is
     * called on that element.
     * Alternatively, it can also be turned on
     * globally to apply to all elements via the
     * skipEmptyAttributes() method invoked on the
     * Document object. From then on, a single
     * Element can be excluded from this rule
     * by calling the noSkipEmptyAttributes() method.
     * 
     * @see \ChYovev\XMLSerializer\Writer :: shouldSkipAttribute()
     * @var bool
     */
    protected bool $skipEmptyAttributes = false;

    /**
     * By default, Elements with no/empty values will
     * still be serialized as empty tags, e.g.: <book />
     * unless $skipEmptyTags is set to true.
     * A single Element can then be excluded from this
     * global rule by calling the allowEmptyTag()
     * method on it. Alternatively, a single Element
     * can be skipped from serialization individually
     * by calling the skipTagIfEmpty() method on it.
     * 
     * @var bool
     */
    protected bool $skipEmptyTags = false;


    ///////////////////////////////////////////////////////////////////////////
    public function __construct(string $encoding = null) {
        if ( ! is_null($encoding)) {
            $this->setEncoding($encoding);
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getXmlVersion(): ?string {
        return $this->xmlVersion;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setXmlVersion(string $xmlVersion): static {
        $this->xmlVersion = $xmlVersion;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getEncoding(): ?string {
        return $this->encoding;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setEncoding(string $encoding): static {
        $this->encoding = $encoding;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * The standalone property used in the XML prolog
     * accepts only a yes/no value. If the property is
     * not set (null value), the standalone attribute
     * will not be generated in the prolog.
     */
    public function getStandAloneString(): ?string {
        if (is_null($this->standAlone)) {
            return null;
        }

        return $this->standAlone ? 'yes' : 'no';
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getStandAlone(): ?bool {
        return $this->standAlone;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function markAsStandAlone(): static {
        return $this->setStandAlone(true);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function markAsNotStandAlone(): static {
        return $this->setStandAlone(false);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setStandAlone(bool $flag): static {
        $this->standAlone = $flag;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function useProlog(): bool {
        return $this->useProlog;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function skipProlog(): static {
        return $this->setUseProlog(false);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setUseProlog(bool $flag): static {
        $this->useProlog = $flag;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function useIndent(): bool {
        return $this->useIndent;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function noIndent(): static {
        return $this->setIndent(false);
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * The setIndent() method can be used to manipulate
     * both the $useIndent and $indentString properties.
     * 
     * Eg.:
     *     setIndent(false) – don't use indentation
     *     setIndent("\t")  – use a tab character for indentation
     * 
     * @param bool|string $value
     */
    public function setIndent(mixed $value): static {
        if (is_bool($value)) {
            $this->useIndent = $value;
        }
        else {
            $this->useIndent = true;

            $this->setIndentString($value);
        }

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getIndentString(): string {
        return $this->indentString;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setIndentString(string $indentString): static {
        $this->indentString = $indentString;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getNamespaces(): array {
        return $this->namespaces;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function addNamespaces(array $namespaces): static {
        foreach ($namespaces as $uri => $prefix) {
            $this->addNamespace($uri, $prefix);
        }

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function addNamespace(string $uri, string $prefix): static {
        $this->namespaces[$uri] = $prefix;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Set a default namespace for the whole Document.
     * Default namespaces have no prefix (hence the empty string).
     */
    public function setNamespace(string $uri): static {
        return $this->addNamespace($uri, '');
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getGlobalVars(): array {
        return $this->globalVars;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setGlobalVars(array $globalVars): static {
        $this->globalVars = $globalVars;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function addGlobalVar(string $key, mixed $value): static {
        $this->globalVars[$key] = $value;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function shouldTrimValues(): bool {
        return $this->trimValues;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function trimValues(): static {
        return $this->setTrimValues(true);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setTrimValues(bool $flag): static {
        $this->trimValues = $flag;
        
        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function shouldSkipEmptyAttributes(): bool {
        return $this->skipEmptyAttributes;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function skipEmptyAttributes(): static {
        return $this->setSkipEmptyAttributes(true);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setSkipEmptyAttributes(bool $flag): static {
        $this->skipEmptyAttributes = $flag;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function shouldSkipEmptyTags(): bool {
        return $this->skipEmptyTags;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function skipEmptyTags(): static {
        return $this->setSkipEmptyTags(true);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setSkipEmptyTags(bool $flag): static {
        $this->skipEmptyTags = $flag;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Pass self in order for the document to be associated
     * with all elements and sub-elements.
     */
    public function getDocument(): static {
        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Generate an XML from current Document object and its
     * Elements and subelements using a Writer object.
     * 
     * @return string
     */
    public function generateXML(): string {
        $writer = new Writer($this);

        return $writer->generateXML();
    }

}