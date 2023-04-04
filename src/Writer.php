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

    /**
     * The namespace helper is used to figure out
     * whether and which namespace and prefix to use
     * for an element which is marked to belong to a
     * certain namespace.
     * Gets set in constructor.
     * 
     * @var NamespaceHelper
     */
    protected NamespaceHelper $namespaceHelper;


    ///////////////////////////////////////////////////////////////////////////
    public function __construct(Document $document) {
        $this->document = $document;

        $this->setWriter();
        $this->setNamespaceHelper();
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function setWriter(): void {
        $this->writer = new XMLWriter();
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function setNamespaceHelper(): void {
        $rootNamespaces = $this->document->getNamespaces();
        
        $this->namespaceHelper = new NamespaceHelper($rootNamespaces);
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

        // if an XML prolog should be generated (default = true),
        // use the XML version and encoding from the Document object
        if ($this->document->useProlog()) {
            $this->writer->startDocument(
                $this->document->getXmlVersion(),
                $this->document->getEncoding(),
                $this->document->getStandAloneString()
            );
        }

        // what indentation to use (and whether),
        // default is 4 white spaces
        $this->writer->setIndent($this->document->useIndent());
        $this->writer->setIndentString($this->document->getIndentString());

        $elements = $this->document->getElements();
        $this->serializeElements($elements);

        if ($this->document->useProlog()) {
            $this->writer->endDocument();
        }

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
        $this->serializePreComment($element);

        // figure out which namespace and prefix to use
        // for the element being serialized
        $this->namespaceHelper->route($element);
        $tagPrefix    = $this->namespaceHelper->getPrefix();
        $tagNamespace = $this->namespaceHelper->getNamespace();

        // always use startElementNs to open a tag,
        // even if the element has no namespace:
        // null values are not serialized anyway
        $tagName = $element->getTagName();
        $this->writer->startElementNs($tagPrefix, $tagName, $tagNamespace);

        $this->serializeComment($element);
        $this->serializeElementAttributes($element);
        $this->serializeElementContents($element);

        $this->writer->endElement();
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Serialize an Element's pre-comment (if any) after proper sanitation.
     * In comparison to the regular comment, the pre-comment is written
     * before the opening tag of the element.
     * 
     * @param  Element $element
     * @return void 
     */
    protected function serializePreComment(Element $element): void {
        $preComment = $element->getPreComment();

        if ( ! is_null($preComment)) {
            $this->sanitizeComment($preComment);

            $this->writer->writeComment($preComment);
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Serialize an Element's comment (if any) after proper sanitation.
     * 
     * @param  Element $element
     * @return void 
     */
    protected function serializeComment(Element $element): void {
        $comment = $element->getComment();

        if ( ! is_null($comment)) {
            $this->sanitizeComment($comment);

            $this->writer->writeComment($comment);
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Comments are not allowed to contain two or more
     * consecutive dashes as this would make the XML
     * document invalid. Consecutive dashes are escaped
     * by putting a backslash inbetween.
     * 
     * @param  string &$comment – passed by reference
     * @return void
     */
    private function sanitizeComment(string &$comment): void {
        while (preg_match('/-{2,}/', $comment)) {
            $comment = str_replace('--', '-\-', $comment);
        }

        // trailing dashes should also be escaped since they'd
        // form consecutive dashes with the comment's closing
        // tag (which is -->) 
        $comment = preg_replace('/-$/', '-\\', $comment);
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Cycle through an Element's attributes and serialize them one by one.
     * 
     * @param  Element $element
     * @return void
     */
    protected function serializeElementAttributes(Element $element): void {
        // root elements should have the Document
        // namespaces serialized as attributes
        if ($element->isRoot()) {
            $this->serializeRootNamespaces();
        }

        foreach ($element->getAttributes() as $attribute => $value) {
            $this->serializeElementAttribute($attribute, $value);
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * All root document namespaces should be serialized
     * as attributes of the root elements.
     */
    protected function serializeRootNamespaces(): void {
        $namespaces = $this->document->getNamespaces();

        foreach ($namespaces as $uri => $prefix) {
            $attribute = $this->getAttributeForNamespace($prefix);

            $this->serializeElementAttribute($attribute, $uri);
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * The prefixes of the root namespace attributes should be
     * preceeded by a prefix of their own – xlmns.
     * Default namespaces applying to all elements in a document
     * have no prefixes, but the xlmns part should remain.
     * 
     * @param string $prefix
     */
    protected function getAttributeForNamespace(string $prefix = null): string {
        $attribute = "xmlns";

        if ($prefix !== '') {
            $attribute .= ":{$prefix}";
        }

        return $attribute;
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