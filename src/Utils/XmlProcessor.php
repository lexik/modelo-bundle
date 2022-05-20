<?php

namespace Choosit\ModeloBundle\Utils;

use Symfony\Component\Serializer\Encoder\XmlEncoder;
use const XML_PI_NODE;

class XmlProcessor
{
    /**
     * @return false|string
     */
    public static function phpToXml(array $data)
    {
        $encoder = new XmlEncoder();

        return $encoder->encode($data, 'xml', [
            'xml_root_node_name' => 'xml',
            'xml_format_output' => true,
            'encoder_ignored_node_types' => [
                XML_PI_NODE,
            ],
        ]);
    }

    /**
     * @return array|mixed|string
     */
    public static function xmlToPhp(string $data)
    {
        $encoder = new XmlEncoder();

        return $encoder->decode(html_entity_decode($data,ENT_XHTML), 'xml');
    }
}
