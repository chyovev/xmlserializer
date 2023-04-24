<?php

namespace ChYovev\XMLSerializer\Traits;

use UnexpectedValueException;
use ChYovev\XMLSerializer\Document;
use ChYovev\XMLSerializer\Element;
use ChYovev\XMLSerializer\Interfaces\XmlSerializable;

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
     * Sub-elements are added to the $elements property
     * with the help of chain methods. Each of these
     * chain methods passes around a $tempElement object
     * (instantiating it beforehand if needed), updating
     * different properties.
     * Finally, the add() method sets the tag name and
     * value (if needed) of the Element, appends it
     * to the $elements array and unsets the $tempElement.
     * 
     * @var Element
     */
    private Element $tempElement;


    ///////////////////////////////////////////////////////////////////////////
    /**
     * A shortcut method to initiate, populate and add an
     * Element object to the elements array property.
     * This method should always be called last during
     * method chaining, hence the void return type.
     * There are several options for the $value property
     * which will generate different output during XML
     * serialization:
     *     – not providing a value would result in an emtpy tag:
     * 
     *       $document->add('book'):
     * 
     *           <book/>
     * 
     *     – a string is the standard option:
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
     *     – an XmlSerializable object which is supposed to
     *       add sub-elements to an element; to achieve this,
     *       the object implements the xmlSerialize() method
     *       which accepts the parent element as a parameter;
     *       from then on one can invoke the same add() method
     *       on the parent element which can accept yet another
     *       XmlSerializable object and so on.
     *       By design XmlSerializable objects are responsible
     *       for populating only their inner elements:
     * 
     *       class Chapter implements XmlSerializable {
     *           public function xmlSerialize(Element $parent): void {
     *               $parent->add('title', 'The boy who lived');
     *           }
     *       }
     * 
     *       $chapter = new Chapter();
     * 
     *       $document->add('chapter', $chapter);
     * 
     * @param  string $tagName
     * @throws UnexpectedValueException – unserializable object passed as value
     * @param  null|string|callback|XmlSerializable $value
     * @return void
     */
    public function add(string $tagName, mixed $value = null): void {
        $element = $this->getTempElement();

        $element
            ->setTagName($tagName)
            ->parseValue($value);

        $this->addElement($element);

        // unset the temp element so it can be recreated for the
        // next element being added via one of the chain methods
        unset($this->tempElement);
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
     * If a Document has been marked for skipping attributes with
     * empty values using the skipEmptyAttributes() method on it,
     * a single Element's attributes can be excluded from skipping
     * by using the allowEmptyAttributes() chain method.
     * 
     * @see \ChYovev\XMLSerializer\Writer :: shouldSkipAttribute()
     * @return static
     */
    public function allowEmptyAttributes(): static {
        $element = $this->getTempElement();

        $element->noSkipEmptyAttributes();

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * A chain method to mark the Element's empty attributes for
     * skipping during serialization.
     * Alternatively, one can use the skipEmptyAttributes() method
     * on the Document object; in this case skipping would apply to
     * all Elements' empty attributes.
     * 
     * @see self :: allowEmptyAttributes()
     * @return static
     */
    public function noEmptyAttributes(): static {
        $element = $this->getTempElement();

        $element->skipEmptyAttributes();

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
     * A chain method to mark the Element's value and attributes
     * for trimming during serialization.
     * Alternatively, one can use the trimValues() method on the
     * Document object; in this case trimming would apply to all
     * Elements.
     * 
     * @see self :: noTrim()
     * @return static
     */
    public function trim(): static {
        $element = $this->getTempElement();

        $element->trimValues();

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * If a Document has been marked for trimming using the trimValues()
     * method on it, a single Element can be excluded from trimming by
     * using the noTrim() chain method.
     * 
     * @see \ChYovev\XMLSerializer\Writer :: shouldTrimValues()
     * @return static
     */
    public function noTrim(): static {
        $element = $this->getTempElement();

        $element->noTrimValues();

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

            // all elements, no matter the sub-level,
            // should be associated with the Document object
            $this->tempElement->setDocument($this->getDocument());

            // if the parent of the tempElement is a Document,
            // the element is considered a root element
            if ($this instanceof Document) {
                $this->tempElement->markAsRoot();
            }
        }

        return $this->tempElement;
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * New elements are meant to be added by the public add() method
     * which calls this protected method.
     */
    protected function addElement(Element $element): static {
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