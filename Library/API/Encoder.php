<?php

namespace Fatchip\Afterbuy;

use Symfony\Component\Serializer\Encoder\XmlEncoder;

class Encoder extends XmlEncoder
{
    public function encode($data, $format, array $context = array())
    {
        if (!count($context)) {
            $context = ['xml_root_node_name' => 'Request'];
        }
        return parent::encode($data, $format, $context);
    }

    public function supportsDecoding($format)
    {
        return 'response/xml' === $format;
    }

    public function supportsEncoding($format)
    {
        return 'request/xml' === $format;
    }
}
