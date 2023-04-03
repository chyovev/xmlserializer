<?php

namespace ChYovev\XMLSerializer;

use ChYovev\XMLSerializer\Traits\Elementable;

class Document
{

    use Elementable;

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