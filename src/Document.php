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
     * By default all XML documents have a prolog
     * (i.e. an opening tag <?xml ?>).
     * Using the skipProlog() method one can choose
     * to exclude the prolog from the generated XML.
     * 
     * @var bool
     */
    protected bool $useProlog = true;

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
     * @var array  
     */
    protected array $namespaces = [];


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
    public function getNamespaces(): array {
        return $this->namespaces;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * @param string[] $namespaces
     */
    public function setNamespaces(array $namespaces): static {
        $this->namespaces = [];

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