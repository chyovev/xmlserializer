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
    /**
     * A shortcut method to initiate, populate and add an
     * Element object to the elements array property.
     * There are several options for the $value property
     * which will generate different output during XML
     * serialization:
     *     – not providing a value would result in an emtpy tag:
     * 
     *       $document->add('book'):
     * 
     *           <book/>
     * 
     *     – a string would is the standard option:
     * 
     *       $document->add('language', 'Bulgarian'):
     * 
     *           <language>Bulgarian</language>
     * 
     *     – a callback method which accepts the Element
     *       object being generated as a property – this
     *       offers maximum flexibility as it allows for
     *       the Element to be further manipulated,
     *       including adding sub-elements to it
     *       (nested callbacks are also supported):
     * 
     *       $document->add('book', function(Element $element) {
     *            $element->add('chapter', 'The boy who lived');
     *       })
     * 
     *           <book>
     *               <chapter>The boy who lived</title>
     *           </book>
     * 
     * @param  string $tagName
     * @param  null|string|callback $value
     * @return static
     */
    public function add(string $tagName, mixed $value = null): static {
        $element = new Element($tagName);

        $element->value($value);

        $this->addElement($element);

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