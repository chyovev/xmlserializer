<?php

namespace ChYovev\XMLSerializer;

use XMLWriter;

/**
 * The Writer class accepts a Document object
 * and serializes its Elements, its Elements'
 * subelements, and so on.
 * To do this, it relies on the underlying PHP
 * XMLWriter extension.
 */

class Writer
{

    /**
     * The Document object which is subject of serialization.
     * Gets set in constructor.
     * 
     * @var Document
     */
    protected Document $document;

    /**
     * XMLWriter object used to initialize the XML document
     * and serialize all Document Element objects.
     * Gets set in constructor.
     * 
     * @var XMLWriter
     */
    protected XMLWriter $writer;


    ///////////////////////////////////////////////////////////////////////////
    public function __construct(Document $document) {
        $this->document = $document;

        $this->setWriter();
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function setWriter(): void {
        $this->writer = new XMLWriter();
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Generate an XML using memory for string output.
     * The Document's Element objects are serialized recursively
     * in order to generate nested XML elements.
     * 
     * @return string
     */
    public function generateXML(): string {
        $this->writer->openMemory();
        $this->writer->startDocument();

        // use 4 spaces for indentation
        $this->writer->setIndent(true);
        $this->writer->setIndentString('    ');

        $elements = $this->document->getElements();
        $this->serializeElements($elements);

        $this->writer->endDocument();

        return $this->writer->outputMemory();
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Cycle through an array of Element objects and
     * serialize each of them individually.
     * 
     * @param  Element[]
     * @return void
     */
    protected function serializeElements(array $elements): void {
        foreach ($elements as $item) {
            $this->serializeElement($item);
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Serialize a single Element's attributes and contents.
     * 
     * @param  Element $element
     * @return void
     */
    protected function serializeElement(Element $element): void {
        $tagName = $element->getTagName();
        $this->writer->startElement($tagName);
            
        $this->serializeElementAttributes($element);
        $this->serializeElementContents($element);

        $this->writer->endElement();
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Cycle through an Element's attributes and serialize them one by one.
     * 
     * @param  Element $element
     * @return void
     */
    protected function serializeElementAttributes(Element $element): void {
        foreach ($element->getAttributes() as $attribute => $value) {
            $this->serializeElementAttribute($attribute, $value);
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Serialize a single Element's attribute.
     * 
     * @param  string $attribute – name of the attribute
     * @param  string $value     – value of the attribute
     * @return void
     */
    protected function serializeElementAttribute(string $attribute, string $value): void {
        $this->writer->startAttribute($attribute);
        $this->writer->text($value);
        $this->writer->endAttribute();
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * An Element's contents contain either a textual value or
     * a set of subelements which are serialized recursively,
     * but it should never have both at the same time.
     * Even if both are set, the subelements have a higher priority.
     * 
     * @param  Element $element
     * @return void 
     */
    protected function serializeElementContents(Element $element): void {
        $subelements = $element->getElements();
        $value       = $element->getValue();

        if ($subelements) {
            $this->serializeElements($subelements);
        }
        // passing null as parameter to text() is deprecated,
        // so if the value is null, simply leave it as is
        elseif ( ! is_null($value)) {
            $element->isCData()
                ? $this->writer->writeCData($value)
                : $this->writer->text($value);
        }
    }
}