<?php

namespace ChYovev\XMLSerializer;

/**
 * The NamespaceHelper class is used to figure out
 * which namespace and prefix to use for an Element
 * during XML serialization:
 *     – if the namespace URI of the Element being
 *       subject to serialization is pre-declared
 *       as a root namespace (which in turn is
 *       serialized as a root element's attribute),
 *       only the namespace's prefix will be used
 *       in front of the element's tag name;
 *     – if the Element namespace is not pre-declared
 *       as a root namespace (i.e. it's custom), both
 *       the prefix and the namespace (as an attribute)
 *       will be serialized for that element;
 *     – if the Element namespace is not pre-declared
 *       and no prefix is passed for that namespace,
 *       a prefix will be automatically generated
 *       on the fly; all consecutive elements belonging
 *       to that new namespace will have the same prefix
 */

class NamespaceHelper
{

    /**
     * All namespaces pre-declared in the Document object.
     * Elements belonging to any of the root namespaces
     * use only the namespace's prefix for the tag name.
     * 
     * @var array
     */
    protected array $rootNamespaces = [];

    /**
     * All namespaces used in the XML document:
     * both root and custom namespaces.
     * If an Element belongs to a custom (non-root)
     * namespace, said namespace gets appended to
     * this list during XML serialization.
     * 
     * @var array
     */
    protected array $allNamespaces = [];

    /**
     * What prefix to use for an Element
     * object subject to serialization.
     * Gets populated during routing and
     * can be retrieved by a getter method.
     * 
     * @see self :: route()
     * @var string
     */
    protected ?string $prefix = null;

    /**
     * What namespace URI to use for an
     * Element object subject to serialization.
     * Gets populated during routing and
     * can be retrieved by a getter method.
     * 
     * @see self :: route()
     * @see self :: declareNewNamespace()
     * @var string
     */
    protected ?string $namespace = null;

    /**
     * Namespaces which will be used across the XML
     * document should normally be pre-declared in
     * the Document object as root namespaces.
     * Elements can still belong to a non-root
     * namespace, but a prefix should also be passed
     * for the respective namespace.
     * If no prefix is passed, one will be generated
     * automatically on the fly using this counter.
     * 
     * @var int
     */
    protected int $namespaceCounter = 0;


    ///////////////////////////////////////////////////////////////////////////
    public function __construct(array $rootNamespaces) {
        $this->rootNamespaces = $rootNamespaces;
        $this->allNamespaces  = $rootNamespaces;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Figure out which prefix and namespace to use for an Element
     * which is marked to belong to a certain namespace.
     * 
     * @param  Element
     * @return void
     */
    public function route(Element $element): void {
        $this->resetPrefixAndNamespace();

        // if the element is not marked as belonging
        // to a namespace, no routing should take place
        if ( ! $element->hasNamespace()) {
            return;
        }

        $elementUri = $element->getNamespaceUri();

        if ($this->isNamespaceDeclared($elementUri)) {
            $this->useExistingNamespace($elementUri);
        }
        else {
            $this->declareNewNamespace($element);
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Mark both the prefix and namespace as null.
     * During serialization, an element with no namespace
     * and prefix will behave exaclty like a regular element.
     */
    protected function resetPrefixAndNamespace(): void {
        $this->prefix    = null;
        $this->namespace = null;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Check if a namespace already exists in the $allNamespaces
     * parameter. If it does not, it will be added once an
     * Element with a custom namespace gets processed.
     * 
     * @see self :: declareNewNamespace()
     * @param  string $namespace – namespace of an element
     * @return bool
     */
    protected function isNamespaceDeclared(string $namespace): bool {
        return array_key_exists($namespace, $this->allNamespaces);
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * If an element's namespace is already declared
     * (no matter if it's a root/custom namespace),
     * use its respective prefix.
     * 
     * @param string $namespace – namespace of an element
     */
    protected function useExistingNamespace(string $namespace): void {
        $prefix = $this->allNamespaces[$namespace];

        $this->setPrefix($prefix);
        $this->setNamespace($namespace);
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * An empty prefix (for default namespaces) should return a null
     * value, otherwise an empty prefix will be added, such as <:title>.
     * 
     * @param string $prefix – prefix of a namespace
     */
    protected function setPrefix(string $prefix): void {
        $this->prefix = ($prefix === '') ? null : $prefix;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getPrefix(): ?string {
        return $this->prefix;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * If the namespace is declared as a root namespace,
     * don't serialize it – simply use its prefix for the tag name.
     * 
     * @param string $namespace – namesoace of an element
     */
    protected function setNamespace(string $namespace): void {
        $this->namespace = $this->isRootNamespace($namespace) ? null : $namespace;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getNamespace(): ?string {
        return $this->namespace;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Check if an element namespace is pre-declared as a root namespace.
     * 
     * @param  string $namespace – namespace of an element
     * @return bool
     */
    protected function isRootNamespace(string $namespace): bool {
        return array_key_exists($namespace, $this->rootNamespaces);
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Add a custom namespace which is not pre-declared as root.
     * If the namespace does not have a prefix, such will be
     * automatically generated.
     * 
     * @param  Element $element – extract namespace and prefix from element
     * @return void
     */
    protected function declareNewNamespace(Element $element): void {
        $namespace = $element->getNamespaceUri();
        $prefix    = $element->getNamespacePrefix() ?: $this->generatePrefix();

        $this->addNamespace($namespace, $prefix);

        $this->setPrefix($prefix);
        $this->setNamespace($namespace);
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Generate a prefix for a non-root custom namespace which
     * has no prefix specified. Such a prefix should start with
     * the abbreviation ns (short for namespace) and be followed
     * by a counter which signifies how many custom prefix-less
     * namespaces were generated for the XML document: the first
     * one will be ns1, the second ns2, an so on.
     */
    protected function generatePrefix(): string {
        $this->namespaceCounter++;

        return "ns{$this->namespaceCounter}";
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Add a custom non-root namespace to the $allNamespaces array.
     * Future elements belonging to the same namespace will use the
     * prefix specified here.
     */
    protected function addNamespace(string $uri, string $prefix): void {
        $this->allNamespaces[$uri] = $prefix;
    }

}