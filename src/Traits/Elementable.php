<?php

namespace ChYovev\XMLSerializer\Traits;

use ChYovev\XMLSerializer\Element;

/**
 * Since both Document & Element classes can have
 * (sub)elements, they both share this common trait
 * which allows them to manage these (sub)elements. 
 */

trait Elementable
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
     * Elements on the other hand can alternatively have
     * a regular string value, but in case subelements
     * are present, said value will be ignored. 
     * 
     * @var Element[]
     */
    protected array $elements = [];


    ///////////////////////////////////////////////////////////////////////////
    public function addElement(Element $element): self {
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