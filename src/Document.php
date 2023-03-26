<?php

namespace ChYovev\XMLSerializer;

class Document
{

    /**
     * Each Document object can have Element objects
     * which may have subelements, which can also have
     * subelements of their own, and so on.
     * Normally a valid XML document has a single
     * root (parent) element which all other elements
     * belong to, but one can add as many root elements
     * as one wishes – the generated XML document would
     * not be valid, but it's still possible.
     * 
     * @var Element[]
     */
    protected array $elements = [];

    
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