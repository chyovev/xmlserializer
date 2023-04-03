<?php

namespace ChYovev\XMLSerializer\Traits;

use ChYovev\XMLSerializer\Document;
use ChYovev\XMLSerializer\Element;

/**
 * Since both Document & Element classes can have
 * (sub)elements, they both share this common trait
 * which allows them to manage these (sub)elements. 
 * The construction of a single Element can be executed
 * via method chaining (such as marking its contents
 * as character data via the cdata() method), but
 * the last method from the chain should always be
 * the add() one which specifies the name of the
 * tag-to-be-serialized.
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

    /**
     * Every time an Element gets added to the $elements
     * property using the shortcut method, a temporary
     * instance gets created which allows for it to be
     * manipulated by the chain methods, without using
     * a callback method.
     * 
     * @var Element
     */
    private Element $tempElement;


    ///////////////////////////////////////////////////////////////////////////
    /**
     * A shortcut method to initiate, populate and add an
     * Element object to the elements array property.
     * This method should always be called last during
     * method chaining.
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
        $element = $this->getTempElement();

        $element
            ->setTagName($tagName)
            ->value($value);

        $this->addElement($element);

        // unset the temp element so it can be recreated for the
        // next element being added via one of the chain methods
        unset($this->tempElement);

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * The cdata() method is meant to be used as a chain
     * method which marks an Element as a character data
     * in order for its contents not to be escaped during
     * serialization.
     * 
     * @return static
     */
    public function cdata(): static {
        $element = $this->getTempElement();

        $element->markAsCData();

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * The comment() method is meant to be used as a chain
     * method – it would add a comment string at the top
     * of the Element being serialized.
     * 
     * @param  string $preComment
     * @return static
     */
    public function comment(string $comment): static {
        $element = $this->getTempElement();

        $element->setComment($comment);
        
        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Much like the comment() chain method, the preComment()
     * method is used to add a comment string for an Element,
     * the only difference between the two being the location
     * of the comment: with preComment() the comment is
     * serialized before the opening tag of the element.
     * 
     * @param  string $preComment
     * @return static
     */
    public function preComment(string $preComment): static {
        $element = $this->getTempElement();

        $element->setPreComment($preComment);
        
        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * A chain method to set attributes to an Element.
     * 
     * @param string[] $attributes
     */
    public function attributes(array $attributes): static {
        $element = $this->getTempElement();

        $element->setAttributes($attributes);

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * A chain method to set a single attribute to an Element.
     * 
     * @param  string $attribute – name of the attribute (required)
     * @param  string $value     – value of the attribute (can be an empty string)
     * @return static
     */
    public function attribute(string $attribute, string $value): static {
        $element = $this->getTempElement();

        $element->addAttribute($attribute, $value);

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * A chain method to set a namespace and a prefix for the Element.
     * 
     * @see    \ChYovev\XMLSerializer\Element :: setNamespace()
     * @param  string $uri    – namespace URI, required
     * @param  string $prefix – namespace prefix, optional
     * @return static 
     */
    public function namespace(string $uri, string $prefix = null): static {
        $element = $this->getTempElement();

        $element->setNamespace($uri, $prefix);

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Every time an Element gets prepared using any of the chain
     * methods, a temporary Element object gets created and passed
     * around using this method. If no such object exists, it gets
     * instantiated by the first chain method in line.
     * The last chain method is always the add() one which appends
     * the temporary Element to the set, and then unsets it.
     * 
     * @return Element
     */
    private function getTempElement(): Element {
        if ( ! isset($this->tempElement)) {
            $this->tempElement = new Element();

            // if the parent of the tempElement is a Document,
            // the element is considered a root element
            if ($this instanceof Document) {
                $this->tempElement->markAsRoot();
            }
        }

        return $this->tempElement;
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