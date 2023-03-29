<?php

namespace ChYovev\XMLSerializer;

use ChYovev\XMLSerializer\Traits\Elementable;

class Document
{

    use Elementable;

    ///////////////////////////////////////////////////////////////////////////
    /**
     * Generate an XML from current Document object and its
     * Elements and subelements using a Writer object.
     * 
     * @return string
     */
    public function generateXML(): string {
        $writer = new Writer($this);

        return $writer->generateXML();
    }

}