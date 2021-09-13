<?php declare(strict_types=1);

namespace XmlViewer\Form;

use Laminas\Form\Fieldset;
use Omeka\Form\Element\ArrayTextarea;

class SettingsFieldset extends Fieldset
{
    protected $label = 'XML Viewer'; // @translate

    public function init(): void
    {
        $this
            ->add([
                'name' => 'xmlviewer_renderings',
                'type' => ArrayTextarea::class,
                'options' => [
                    'label' => 'Render by xml media-type', // @translate
                    'info' => 'Set the rendering or the stylesheet (xsl or css) to use by media-type, one by line. Other xml types are rendered as a download link. In all cases, they should be allowed. You may set the precise media-types with module Bulk Edit too.', // @translate
                    'documentation' => 'https://gitlab.com/Daniel-KM/Omeka-S-module-XmlViewer#usage',
                    'as_key_value' => true,
                ],
                'attributes' => [
                    'id' => 'xmlviewer_renderings',
                    'rows' => 5,
                    'placeholder' => 'text/xml = text
application/xml = text
',
                ],
            ]);
    }
}
