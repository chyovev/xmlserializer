<?php

namespace ChYovev\XMLSerializer\Interfaces;

use ChYovev\XMLSerializer\Element;

/**
 * Adding sub-elements to an Element can be achieved
 * either by passing a callback method to the add()
 * method, or by providing an XmlSerializable object.
 * The two methods can be combined: a sub-element can
 * reference an XmlSerializable object, and such an
 * object can also add nested Element objects.
 * Objects implementing the XmlSerializable interface
 * should have the xmlSerialize() method declared.
 * 
 * @see \ChYovev\XMLSerializer\Element :: parseValue()
 */

interface XmlSerializable
{

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Add sub-elements to a parent Element object.
     * Despite the name of the method being xmlSerialize,
     * at this stage data is only being prepared for
     * serialization. The actual serialization takes place
     * once the method Document's generateXML() is called. 
     * 
     * NB! It's still possible to manipulate the immediate
     *     parent which is being passed as a parameter to
     *     the method, but one would have to rely on the
     *     traditional setters instead of the shortcut ones
     *     which are used for adding new sub-elements, i.e.:
     * 
     *         – addAttribute()  instead of attribute()
     *         – setNamespace()  instead of namespace()
     *         – setPreComment() instead of preComment()
     *           etc.
     * 
     * @param  Element $parent – the parent element which the object belongs to
     * @return void
     */
    public function xmlSerialize(Element $parent): void;
    
}