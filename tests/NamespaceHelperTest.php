<?php

namespace ChYovev\Tests;

use ChYovev\XMLSerializer\Document;
use ChYovev\XMLSerializer\Element;
use ChYovev\XMLSerializer\NamespaceHelper;
use PHPUnit\Framework\TestCase;

final class NamespaceHelperTest extends TestCase
{

    private NamespaceHelper $nsHelper;

    ///////////////////////////////////////////////////////////////////////////
    /**
     * The setUp() method is automatically being called before each test method.
     */
    protected function setUp(): void {
        $this->setNamespaceHelper();
    }

    ///////////////////////////////////////////////////////////////////////////
    private function setNamespaceHelper(): void {
        $rootNamespaces = $this->getRootNamespaces();

        $this->nsHelper = new NamespaceHelper($rootNamespaces);
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * All namespaces pre-declared in the Document object
     * are considered to be root namespaces.
     */
    private function getRootNamespaces(): array {
        $document = new Document();

        // the default namespace of a Document has no prefix
        $document->setNamespace('https://example.com');

        // add additional root namespace
        $document->addNamespace('https://somesite.com', 'ex');

        return $document->getNamespaces();
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * If an Element is not assigned to any namespace, its namespace
     * attribute and prefix will be non-existent (i.e. null) during
     * serialization.
     */
    public function testRoutingOfElementWithNoNamespace(): void {
        $element = $this->createElement();

        $this->nsHelper->route($element);

        $prefix    = $this->nsHelper->getPrefix();
        $namespace = $this->nsHelper->getNamespace();

        $this->assertNull($prefix);
        $this->assertNull($namespace);
    }

    ///////////////////////////////////////////////////////////////////////////
    private function createElement(string $namespace = null, string $prefix = null): Element {
        $element = new Element();

        if ($namespace) {
            $element->setNamespaceUri($namespace);
        }

        if ($prefix) {
            $element->setNamespacePrefix($prefix);
        }

        return $element;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * If a Document has a default namespace, all Elements
     * are considered to belong to this namespace unless specified
     * otherwise in the Element's namespace properties.
     * Explicitly specifying the Document's default namespace
     * as an Element's namespace will have no effect on the
     * Element's serialization – no prefix, no namespace attribute,
     * same as if no namespace were specified.
     */
    public function testRoutingOfElementWithDefaultNamespace(): void {
        $element = $this->createElement('https://example.com');

        $this->nsHelper->route($element);

        $prefix    = $this->nsHelper->getPrefix();
        $namespace = $this->nsHelper->getNamespace();

        $this->assertNull($prefix);
        $this->assertNull($namespace);
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * If an Element is assigned to a root namespace,
     * it will not have the URI as an attribute, but will
     * have its prefix before the tag name.
     */
    public function testRoutingOfElementWithRootNamespace(): void {
        $element = $this->createElement('https://somesite.com');

        $this->nsHelper->route($element);

        $prefix    = $this->nsHelper->getPrefix();
        $namespace = $this->nsHelper->getNamespace();

        $this->assertSame($prefix, 'ex');
        $this->assertNull($namespace);
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * If an Element is assigned to a custom (i.e. non-root) namespace
     * and it has a prefix, said prefix will be used on the tag,
     * and the namespace will be serialized as an attribute.
     */
    public function testRoutingOfElementWithCustomNamespaceWithPrefix(): void {
        $element = $this->createElement('https://test.com', 'test');

        $this->nsHelper->route($element);

        $prefix    = $this->nsHelper->getPrefix();
        $namespace = $this->nsHelper->getNamespace();

        $this->assertSame($prefix,    'test');
        $this->assertSame($namespace, 'https://test.com');
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * If an Element is assigned to a custom (i.e. non-root) namespace
     * and it does not have a prefix, a prefix will be generated for it
     * and the namespace will be serialized as an attribute.
     */
    public function testRoutingOfElementWithCustomNamespaceWithoutPrefix(): void {
        $element = $this->createElement('https://test.com');

        $this->nsHelper->route($element);

        $prefix    = $this->nsHelper->getPrefix();
        $namespace = $this->nsHelper->getNamespace();

        $this->assertSame($prefix,    'ns1');
        $this->assertSame($namespace, 'https://test.com');
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * If two or more Elements which are assigned to the same custom namespace
     * are being routed, they will have the same prefix.
     */
    public function testRoutingOfMultipleElementsWithSameCustomNamespace(): void {
        $element1 = $this->createElement('https://test.com');
        $element2 = $this->createElement('https://test.com');

        $this->nsHelper->route($element1);
        $prefix1  = $this->nsHelper->getPrefix();

        $this->nsHelper->route($element2);
        $prefix2  = $this->nsHelper->getPrefix();

        $this->assertSame($prefix1, $prefix2);
        $this->assertSame($prefix1, 'ns1');
        $this->assertSame($prefix2, 'ns1');
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * If two or more Elements assigned to the same custom namespace
     * are being routed, and the second one is trying to use another prefix,
     * said prefix will be ignored and the first one will be used instead.
     */
    public function testRoutingOfMultipleElementsWithSameCustomNamespacesButDifferentPrefixes(): void {
        $element1 = $this->createElement('https://test.com', 'test1');
        $element2 = $this->createElement('https://test.com', 'test2');

        $this->nsHelper->route($element1);
        $prefix1  = $this->nsHelper->getPrefix();

        $this->nsHelper->route($element2);
        $prefix2  = $this->nsHelper->getPrefix();

        $this->assertSame($prefix1,    $prefix2);
        $this->assertSame($prefix1,    'test1');
        $this->assertNotSame($prefix2, 'test2');
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * If two or more Elements assigned to different custom namespaces
     * without prefixes are being routed, each newly introduced namespace
     * will have a prefix with a number specifying the amount of
     * custom prefix-less namespaces being used.
     */
    public function testRoutingOfMultipleElementsWithDifferentCustomNamespacesBothWithoutPrefixes(): void {
        $element1 = $this->createElement('https://test1.com');
        $element2 = $this->createElement('https://test2.com');

        $this->nsHelper->route($element1);
        $prefix1 = $this->nsHelper->getPrefix();
        
        $this->nsHelper->route($element2);
        $prefix2 = $this->nsHelper->getPrefix();

        $this->assertSame($prefix1, 'ns1');
        $this->assertSame($prefix2, 'ns2');
    }

}