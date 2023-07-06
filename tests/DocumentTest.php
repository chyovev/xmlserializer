<?php

namespace ChYovev\Tests;

use ChYovev\XMLSerializer\Document;
use PHPUnit\Framework\TestCase;

final class DocumentTest extends TestCase
{

    ///////////////////////////////////////////////////////////////////////////
    /**
     * By default, Document indentation is set to true,
     * having four spaces as an indentation string.
     * The setIndent() method can be used to either
     * disable indentation or to change the default
     * string (which in effect turns indentation back on).
     */
    public function testIndentationSettings(): void {
        $document = new Document();
        
        // default values
        $this->assertSame($document->useIndent(), true);
        $this->assertSame($document->getIndentString(), '    ');
        
        $document->setIndent(false);
        $this->assertSame($document->useIndent(), false);

        $document->setIndent("\t");
        $this->assertSame($document->useIndent(), true);
        $this->assertSame($document->getIndentString(), "\t");
    }

    ///////////////////////////////////////////////////////////////////////////
    /**
     * The standAlone setting signifies that the XML document
     * should depend on an external (DTD) markup declaration.
     * By default this setting is not serialized (value is null),
     * but in case it gets set to true/false, the serialized
     * value should be a 'yes' or 'no'.
     */
    public function testStandAloneSettings(): void {
        $document = new Document();

        $this->assertNull($document->getStandAloneString());
        
        $document->setStandAlone(true);
        $this->assertSame($document->getStandAloneString(), 'yes');
        
        $document->setStandAlone(false);
        $this->assertSame($document->getStandAloneString(), 'no');
        
        // reset back to null
        $document->setStandAlone(null);
        $this->assertNull($document->getStandAloneString());
    }

}