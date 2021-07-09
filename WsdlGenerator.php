<?php /** @noinspection PhpUnused */

/**
 * @link https://github.com/uhi67/yii2-services
 * @license https://github.com/uhi67/yii2-services/blob/master/LICENSE.md
 */

namespace uhi67\services;

use DOMDocument;
use DOMElement;
use DOMNode;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use yii\base\Component;

/**
 * WsdlGenerator generates the WSDL (1.0) for a given service class.
 *
 * The WSDL generation is based on the doc comments found in the service class file.
 * In particular, it recognizes the '@soap' tag in the comment and extracts
 * API method and type definitions.
 *
 * In a service class, a remote invokable method must be a public method with a doc
 * comment block containing the '@soap' tag. In the doc comment, the type and name
 * of every input parameter and the type of the return value should be declared using
 * the standard phpdoc format.
 *
 * WsdlGenerator recognizes the following primitive types (case-sensitive) in
 * the parameter and return type declarations:
 * <ul>
 * <li>str/string: maps to xsd:string;</li>
 * <li>int/integer: maps to xsd:int;</li>
 * <li>float/double: maps to xsd:float;</li>
 * <li>bool/boolean: maps to xsd:boolean;</li>
 * <li>date: maps to xsd:date;</li>
 * <li>time: maps to xsd:time;</li>
 * <li>datetime: maps to xsd:dateTime;</li>
 * <li>array: maps to soap-enc:Array;</li>
 * <li>object: maps to xsd:struct;</li>
 * <li>mixed: maps to xsd:anyType.</li>
 * </ul>
 *
 * If a type is not a primitive type, it is considered as a class type, and
 * WsdlGenerator will look for its property declarations. Only public properties
 * are considered, and they each must be associated with a doc comment block containg
 * the '@soap' tag. The doc comment block should declare the type of the property.
 *
 * WsdlGenerator recognizes the array type with the following format:
 * <pre>
 * typeName[]: maps to tns:typeNameArray
 * </pre>
 *
 * The following is an example declaring a remote invokable method:
 * <pre>
 * / **
 *   * A foo method.
 *   * @param string name of something
 *   * @param string value of something
 *   * @return string[] some array
 *   * @soap
 *   * /
 * public function foo($name,$value) {...}
 * </pre>
 *
 * And the following is an example declaring a class with remote accessible properties:
 * <pre>
 * class Foo {
 *     / **
 *       * @var string name of foo {nillable=1, minOccurs=0, maxOccurs=2}
 *       * @soap
 *       * /
 *     public $name;
 *     / **
 *       * @var Member[] members of foo
 *       * @soap
 *       * /
 *     public $members;
 * }
 * </pre>
 * In the above, the 'members' property is an array of 'Member' objects. Since 'Member' is not
 * a primitive type, WsdlGenerator will look further to find the definition of 'Member'.
 *
 * Optionally, extra attributes (nillable, minOccurs, maxOccurs) can be defined for each
 * property by enclosing definitions into curly brackets and separated by comma like so:
 *
 * {[attribute1 = value1][, attribute2 = value2], ...}
 *
 * where the attribute can be one of following:
 * <ul>
 * <li>nillable = [0|1|true|false]</li>
 * <li>minOccurs = n; where n>=0</li>
 * <li>maxOccurs = n; where [n>=0|unbounded]</li>
 * </ul>
 *
 * Additionally, each complex data type can have assigned a soap indicator flag declaring special usage for such a data type.
 * A soap indicator must be declared in the doc comment block with the '@soap-indicator' tag.
 * Following soap indicators are currently supported:
 * <ul>
 * <li>all - (default) allows any sorting order of child nodes</li>
 * <li>sequence - all child nodes in WSDL XML file will be expected in predefined order</li>
 * <li>choice - supplied can be either of the child elements</li>
 * </ul>
 * The Group indicators can be also injected via custom soap definitions as XML node into WSDL structure.
 *
 * In the following example, class Foo will create a XML node &lt;xsd:Foo&gt;&lt;xsd:sequence&gt; ... &lt;/xsd:sequence&gt;&lt;/xsd:Foo&gt; with children attributes expected in pre-defined order.
 * <pre>
 * / *
 *   * @soap-indicator sequence
 *   * /
 * class Foo {
 *     ...
 * }
 * </pre>
 * For more on soap indicators, see See {@link http://www.w3schools.com/schema/schema_complex_indicators.asp}.
 *
 * Since the variability of WSDL definitions is virtually unlimited, a special doc comment tag '@soap-wsdl' can be used in order to inject any custom XML string into generated WSDL file.
 * If such a block of the code is found in class's comment block, then it will be used instead of parsing and generating standard attributes within the class.
 * This gives virtually unlimited flexibility in defining data structures of any complexity.
 * Following is an example of defining custom piece of WSDL XML node:
 * <pre>
 * / *
 *   * @soap-wsdl <xsd:sequence>
 *   * @soap-wsdl     <xsd:element minOccurs="1" maxOccurs="1" nillable="false" name="name" type="xsd:string"/>
 *   * @soap-wsdl     <xsd:choice minOccurs="1" maxOccurs="1" nillable="false">
 *   * @soap-wsdl         <xsd:element minOccurs="1" maxOccurs="1" nillable="false" name="age" type="xsd:integer"/>
 *   * @soap-wsdl         <xsd:element minOccurs="1" maxOccurs="1" nillable="false" name="date_of_birth" type="xsd:date"/>
 *   * @soap-wsdl     </xsd:choice>
 *   * @soap-wsdl </xsd:sequence>
 *   * /
 * class User {
 *     / **
 *       * @var string User name {minOccurs=1, maxOccurs=1}
 *       * @soap
 *       * /
 *     public $name;
 *     / **
 *       * @var integer User age {nillable=0, minOccurs=1, maxOccurs=1}
 *       * @example 35
 *       * @soap
 *       * /
 *     public $age;
 *     / **
 *       * @var date User's birthday {nillable=0, minOccurs=1, maxOccurs=1}
 *       * @example 1980-05-27
 *       * @soap
 *       * /
 *     public $date_of_birth;
 * }
 * </pre>
 * In the example above, WSDL generator would inject under XML node &lt;xsd:User&gt; the code block defined by @soap-wsdl lines.
 *
 * By inserting into SOAP URL link the parameter "?doc", WSDL generator will output human-friendly overview of all operations and complex data types rather than XML WSDL file.
 * Each complex type is described in a separate HTML table and recognizes also the '@example' PHPDoc tag. See {@link xslt/wsdl.xslt}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.services
 * @since 1.0
 * @noinspection PhpUndefinedClassInspection
 */
class WsdlGenerator extends Component
{
    const STYLE_RPC = 'rpc';
    const STYLE_DOCUMENT = 'document';
    const USE_ENCODED = 'encoded';
    const USE_LITERAL = 'literal';
    /**
     * @var string the namespace to be used in the generated WSDL.
     * If not set, it defaults to the name of the class that WSDL is generated upon.
     */
    public $namespace;
    /**
     * @var string the name of the generated WSDL.
     * If not set, it defaults to "urn:{$className}wsdl".
     */
    public $serviceName;
    /**
     * @var array
     * soap:body operation style options
     */
    public $operationBodyStyle = [
        'use' => self::USE_ENCODED,
        'encodingStyle' => 'http://schemas.xmlsoap.org/soap/encoding/',
    ];
    /**
     * @var array
     * soap:operation style
     */
    public $bindingStyle = self::STYLE_RPC;
    /**
     * @var string
     * soap:operation transport
     */
    public $bindingTransport = 'http://schemas.xmlsoap.org/soap/http';

    protected static $typeMap = [
        'string' => 'xsd:string',
        'str' => 'xsd:string',
        'int' => 'xsd:int',
        'integer' => 'xsd:integer',
        'float' => 'xsd:float',
        'double' => 'xsd:float',
        'bool' => 'xsd:boolean',
        'boolean' => 'xsd:boolean',
        'date' => 'xsd:date',
        'time' => 'xsd:time',
        'datetime' => 'xsd:dateTime',
        'array' => 'soap-enc:Array',
        'object' => 'xsd:struct',
        'mixed' => 'xsd:anyType',
    ];

    /**
     * @var array List of recognized SOAP operations that will become remotely available.
     * All methods with declared @soap parameter will be included here in the format operation1 => description1, operation2 => description2, ..
     */
    protected $operations;

    /**
     * @var array List of complex types used by operations.
     * If an SOAP operation defines complex input or output type, all objects are included here containing all sub-parameters.
     * For instance, if an SOAP operation "createUser" requires complex input object "User", then the object "User" will be included here with declared subparameters such as "firstname", "lastname", etc..
     */
    protected $types;

    /**
     * @var array
     */
    protected $elements;

    /**
     * @var array Map of request and response types for all operations.
     */
    protected $messages;

    /**
     * Generates the WSDL for the given class.
     * @param string $className class name
     * @param string $serviceUrl Web service URL
     * @param string $encoding encoding of the WSDL. Defaults to 'UTF-8'.
     * @return string the generated WSDL
     * @throws ReflectionException
     */
    public function generateWsdl($className, $serviceUrl, $encoding = 'UTF-8')
    {
        $this->operations = [];
        $this->types = [];
        $this->elements = [];
        $this->messages = [];

        $reflection = new ReflectionClass($className);

        if ($this->serviceName === null) {
            $this->serviceName = $reflection->getShortName();
        }
        if ($this->namespace === null) {
            $this->namespace = 'urn:' . str_replace('\\', '/', $className) . 'wsdl';
        }
        foreach ($reflection->getMethods() as $method) {
            if ($method->isPublic()) {
                $this->processMethod($method);
            }
        }

        $xslt = isset($_GET['doc']) ? '?xslt' : null;
        $opName = $_GET['o'] ?? null;
        return $this->buildDOM($serviceUrl, $encoding, $xslt, $opName)->saveXML();
    }

    /**
     * @param ReflectionMethod $method method
     * @throws ReflectionException
     */
    protected function processMethod($method)
    {
        $comment = $method->getDocComment();
        if (strpos($comment, '@soap') === false) {
            return;
        }
        $comment = strtr($comment, ["\r\n" => "\n", "\r" => "\n"]); // make line endings consistent: win -> unix, mac -> unix

        $methodName = $method->getName();
        $comment = preg_replace('/^\s*\**(\s*?$|\s*)/m', '', $comment);
        $params = $method->getParameters();
        $message = [];
        $headers = [];
        $n = preg_match_all('/^@param\s+([\w.\\\]+(\[\s*])?)\s*?(.*)$/im', $comment, $matches);
        if ($n > count($params)) {
            $n = count($params);
        }
        if ($this->bindingStyle == self::STYLE_RPC) {
            for ($i = 0; $i < $n; ++$i) {
                $message[$params[$i]->getName()] = [
                    'type' => $this->processType($matches[1][$i]),
                    'doc' => trim($matches[3][$i]),
                ];
            }
        } else {
            $this->elements[$methodName] = [];
            for ($i = 0; $i < $n; ++$i) {
                $this->elements[$methodName][$params[$i]->getName()] = [
                    'type' => $this->processType($matches[1][$i]),
                    'nillable' => $params[$i]->isOptional(),
                ];
            }
            $message['parameters'] = ['element' => 'tns:' . $methodName];
        }

        $this->messages[$methodName . 'In'] = $message;

        $n = preg_match_all('/^@header\s+([\w.\\\]+(\[\s*])?)\s*?(.*)$/im', $comment, $matches);
        for ($i = 0; $i < $n; ++$i) {
            $name = $matches[1][$i];
            $type = $this->processType($matches[1][$i]);
            $doc = trim($matches[3][$i]);
            if ($this->bindingStyle == self::STYLE_RPC) {
                $headers[$name] = [$type, $doc];
            } else {
                $this->elements[$name][$name] = ['type' => $type];
                $headers[$name] = ['element' => $type];
            }
        }

	    $doc = "";
	    $n = preg_match_all('/^@example\s+(.*)$/m', $comment, $matches);
	    for ($i = 0; $i < $n; ++$i) {
		    $doc .= $matches[1][$i].PHP_EOL;
	    }

        if ($headers !== []) {
            $this->messages[$methodName . 'Headers'] = $headers;
            $headerKeys = array_keys($headers);
            $firstHeaderKey = reset($headerKeys);
            $firstHeader = $headers[$firstHeaderKey];
        } else {
            $firstHeader = null;
            $firstHeaderKey = null;
        }

        if ($this->bindingStyle == self::STYLE_RPC) {
            if (preg_match('/^@return\s+([\w.\\\]+(\[\s*])?)\s*?(.*)$/im', $comment, $matches)) {
                $return = [
                    'type' => $this->processType($matches[1]),
                    'doc' => trim($matches[2]),
                ];
            } else {
                $return = null;
            }
            $this->messages[$methodName . 'Out'] = ['return' => $return];
        } else {
            if (preg_match('/^@return\s+([\w.\\\]+(\[\s*])?)\s*?(.*)$/im', $comment, $matches)) {
                $this->elements[$methodName . 'Response'][$methodName . 'Result'] = [
                    'type' => $this->processType($matches[1]),
                ];
            }
            $this->messages[$methodName . 'Out'] = ['parameters' => ['element' => 'tns:' . $methodName . 'Response']];
        }

        // If no @example, Method decription is the bare beginning of the comment
        if(!$doc &&preg_match('/^\/\*+\s*([^@]*?)\n@/s', $comment, $matches)) {
            $doc = trim($matches[1]);
            if($doc) $doc.=PHP_EOL;
        }

        $this->operations[$methodName] = [
            'doc' => $doc,
            'headers' => $firstHeader===null ? null : ['input' => [$methodName . 'Headers', $firstHeaderKey]],
        ];
    }

    /**
     * @param string $type PHP variable type
     * @return mixed|string
     * @throws ReflectionException
     */
    protected function processType($type)
    {
        if (isset(self::$typeMap[$type])) {
            return self::$typeMap[$type];
        } elseif (isset($this->types[$type])) {
            return is_array($this->types[$type]) ? 'tns:' . $type : $this->types[$type];
        } elseif (($pos = strpos($type, '[]')) !== false) {
            // array of types
            $type = substr($type, 0, $pos);
            if (strpos($type, '\\') !== false) {
                $class = new ReflectionClass($type);
                $shortType = $class->getShortName();
            } else {
                $shortType = $type;
            }
            $this->types[$type . '[]'] = 'tns:' . $shortType . 'Array';
            $this->processType($type);
            return $this->types[$type . '[]'];
        } else {
            // process class / complex type
            $class = new ReflectionClass($type);

            $type = $class->getShortName();

            $comment = $class->getDocComment();
            $comment = strtr($comment, ["\r\n" => "\n", "\r" => "\n"]); // make line endings consistent: win -> unix, mac -> unix
            $comment = preg_replace('/^\s*\**(\s*?$|\s*)/m', '', $comment);

            // extract soap indicator flag, if defined, e.g. @soap-indicator sequence
            // see http://www.w3schools.com/schema/schema_complex_indicators.asp
            if (preg_match('/^@soap-indicator\s+(\w+)\s*?(.*)$/im', $comment, $matches)) {
                $indicator = $matches[1];
                $attributes = $this->getWsdlElementAttributes($matches[2]);
            } else {
                $indicator = 'all';
                $attributes = $this->getWsdlElementAttributes('');
            }

            $custom_wsdl = false;
            if (preg_match_all('/^@soap-wsdl\s+(\S.*)$/im', $comment, $matches) > 0) {
                $custom_wsdl = implode("\n", $matches[1]);
            }
            $this->types[$type] = [
                'indicator' => $indicator,
                'nillable' => $attributes['nillable'],
                'minOccurs' => $attributes['minOccurs'],
                'maxOccurs' => $attributes['maxOccurs'],
                'custom_wsdl' => $custom_wsdl,
                'properties' => []
            ];

            foreach ($class->getProperties() as $property) {
                $comment = $property->getDocComment();
                if ($property->isPublic() && strpos($comment, '@soap') !== false) {
                    if (preg_match('/@var\s+([\w.\\\]+(\[\s*])?)\s*?(.*)$/mi', $comment, $matches)) {
                        $attributes = $this->getWsdlElementAttributes($matches[3]);

                        if (preg_match('/{(.+)}/', $comment, $attr)) {
                            $matches[3] = str_replace($attr[0], '', $matches[3]);
                        }

                        // extract PHPDoc @example
                        $example = '';
                        if (preg_match('/@example[:]?(.+)/mi', $comment, $match)) {
                            $example = trim($match[1]);
                        }

                        // extract PHPDoc documentation
                        if (preg_match('/^\/\*+\s*\*?\s*([^@]*)/', $comment, $mm)) {
                            $doc = trim($mm[1], " \ \t\n\r\0\x0B*");
                        } else {
                            $doc = '';
                        }

                        $this->types[$type]['properties'][$property->getName()] = [
                            $this->processType($matches[1]),
                            trim($matches[3]),
                            $attributes['nillable'],
                            $attributes['minOccurs'],
                            $attributes['maxOccurs'],
                            $example,
                            $doc
                        ]; // name => type, doc, nillable, minOccurs, maxOccurs, example
                    }
                }
            }
            return 'tns:' . $type;
        }
    }

    /**
     * Parse attributes nillable, minOccurs, maxOccurs
     * @param string $comment Extracted PHPDoc comment
     * @return array
     */
    protected function getWsdlElementAttributes($comment)
    {
        $nillable = $minOccurs = $maxOccurs = null;
        if (preg_match('/{(.+)}/', $comment, $attr)) {
            if (preg_match_all('/((\w+)\s*=\s*(\w+))/mi', $attr[1], $attr)) {
                foreach ($attr[2] as $id => $prop) {
                    $prop = strtolower($prop);
                    $val = strtolower($attr[3][$id]);
                    if ($prop == 'nillable') {
                        if ($val == 'false' || $val == 'true') {
                            $nillable = $val;
                        } else {
                            $nillable = $val ? 'true' : 'false';
                        }
                    } elseif ($prop == 'minoccurs') {
                        $minOccurs = intval($val);
                    } elseif ($prop == 'maxoccurs') {
                        $maxOccurs = ($val == 'unbounded') ? 'unbounded' : intval($val);
                    }
                }
            }
        }
        return [
            'nillable' => $nillable,
            'minOccurs' => $minOccurs,
            'maxOccurs' => $maxOccurs
        ];
    }

    /**
     * Import custom XML source node into WSDL document under specified target node
     * @param DOMDocument $dom XML WSDL document being generated
     * @param DOMElement $target XML node, to which will be appended $source node
     * @param DOMNode $source Source XML node to be imported
     */
    protected function injectDom(DOMDocument $dom, DOMElement $target, DOMNode $source)
    {
        if ($source->nodeType != XML_ELEMENT_NODE) {
            return;
        }
        $import = $dom->createElement($source->nodeName);

        foreach ($source->attributes as $attr) {
            $import->setAttribute($attr->name, $attr->value);
        }
        foreach ($source->childNodes as $child) {
            $this->injectDom($dom, $import, $child);
        }
        $target->appendChild($import);
    }

    /**
     * @param string $serviceUrl Web service URL
     * @param string $encoding encoding of the WSDL. Defaults to 'UTF-8'.
     * @param string|null $xslt -- generate processing instruction with xslt name
     * @param string|null $opName --
     * @return DOMDocument
     */
    protected function buildDOM($serviceUrl, $encoding="UTF-8", $xslt=null, $opName=null)
    {
        $pi = $xslt ? '<?xml-stylesheet type="text/xsl" href="'.$xslt.'"?>'."\n" : '';
        $xml = /** @lang */<<<XML
<?xml version="1.0" encoding="$encoding"?>$pi
<definitions name="$this->serviceName" targetNamespace="$this->namespace"
    xmlns="http://schemas.xmlsoap.org/wsdl/"
    xmlns:tns="$this->namespace"
    xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
    xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/"
    xmlns:soap-enc="http://schemas.xmlsoap.org/soap/encoding/">
</definitions>
XML;

        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        if($opName) {
            $dom->documentElement->setAttribute('opName', $opName);
            $dom->documentElement->setAttribute('uri', $serviceUrl);
            $port = parse_url($serviceUrl, PHP_URL_PORT);
            $scheme = parse_url($serviceUrl, PHP_URL_SCHEME);
            $hostPort = $scheme=='http' && $port=='80' || $scheme=='https' && $port=='443' ? '' : ':'.$port;
            $dom->documentElement->setAttribute('host', parse_url($serviceUrl, PHP_URL_HOST).$hostPort);
        }

        $this->addTypes($dom);

        $this->addMessages($dom);
        $this->addPortTypes($dom);
        $this->addBindings($dom);
        $this->addService($dom, $serviceUrl);

        return $dom;
    }

    /**
     * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
     */
    protected function addTypes($dom)
    {
        if ($this->types === [] && $this->elements === []) {
            return;
        }
        $types = $dom->createElement('wsdl:types');
        $schema = $dom->createElement('xsd:schema');
        $schema->setAttribute('targetNamespace', $this->namespace);

        foreach ($this->types as $phpType => $xmlType) {
            if (is_string($xmlType) && strrpos($xmlType, 'Array') !== strlen($xmlType) - 5) {
                continue;  // simple type
            }
            $complexType = $dom->createElement('xsd:complexType');
            if (is_string($xmlType)) {
                if (strpos($xmlType, 'tns:') !== false) {
                    $complexType->setAttribute('name', substr($xmlType, 4));
                } else {
                    $complexType->setAttribute('name', $xmlType);
                }
                $arrayType = ($dppos = strpos($xmlType, ':')) !== false ? substr($xmlType, $dppos + 1) : $xmlType; // strip namespace, if any
                $arrayType = substr($arrayType, 0, -5); // strip 'Array' from name
                if ($this->operationBodyStyle['use'] == self::USE_ENCODED) {
                    $complexContent = $dom->createElement('xsd:complexContent');
                    $restriction = $dom->createElement('xsd:restriction');
                    $restriction->setAttribute('base', 'soap-enc:Array');
                    $attribute = $dom->createElement('xsd:attribute');
                    $attribute->setAttribute('ref', 'soap-enc:arrayType');
                    $attribute->setAttribute('arrayType', (isset(self::$typeMap[$arrayType]) ? 'xsd:' : 'tns:') . $arrayType . '[]');

                    $restriction->appendChild($attribute);
                    $complexContent->appendChild($restriction);
                    $complexType->appendChild($complexContent);
                } else {
                    $sequence = $dom->createElement('xsd:sequence');
                    $element = $dom->createElement('xsd:element');
                    $element->setAttribute('name', 'item');
                    $element->setAttribute('type', (self::$typeMap[$arrayType] ?? 'tns:' . $arrayType));
                    $element->setAttribute('minOccurs', '0');
                    $element->setAttribute('maxOccurs', 'unbounded');
                    $sequence->appendChild($element);
                    $complexType->appendChild($sequence);
                }
            } elseif (is_array($xmlType)) {
                $pathInfo = pathinfo(str_replace('\\', '/', $phpType));

                $complexType->setAttribute('name', $pathInfo['basename']);

                //$complexType->setAttribute('name',$phpType);
                if ($xmlType['custom_wsdl'] !== false) {
                    $custom_dom = new DOMDocument();
                    /** @noinspection XmlUnusedNamespaceDeclaration */
                    $custom_dom->loadXML('<root xmlns:xsd="http://www.w3.org/2001/XMLSchema">' . $xmlType['custom_wsdl'] . '</root>');
                    foreach ($custom_dom->documentElement->childNodes as $el) {
                        $this->injectDom($dom, $complexType, $el);
                    }
                } else {
                    $all = $dom->createElement('xsd:' . $xmlType['indicator']);

                    if (!is_null($xmlType['minOccurs'])) {
                        $all->setAttribute('minOccurs', $xmlType['minOccurs']);
                    }
                    if (!is_null($xmlType['maxOccurs'])) {
                        $all->setAttribute('maxOccurs', $xmlType['maxOccurs']);
                    }
                    if (!is_null($xmlType['nillable'])) {
                        $all->setAttribute('nillable', $xmlType['nillable']);
                    }
                    foreach ($xmlType['properties'] as $name => $type) {
                        $element = $dom->createElement('xsd:element');
                        if (!is_null($type[3])) {
                            $element->setAttribute('minOccurs', $type[3]);
                        }
                        if (!is_null($type[4])) {
                            $element->setAttribute('maxOccurs', $type[4]);
                        }
                        if (!is_null($type[2])) {
                            $element->setAttribute('nillable', $type[2]);
                        }
                        $element->setAttribute('name', $name);
                        $element->setAttribute('type', $type[0]);

                        // Documentation
                        if(isset($type[5]) || isset($type[6])) {
                            $annotation = $dom->createElement('xsd:annotation');
                            if(isset($type[6])) {
                                $documentation = $dom->createElement('xsd:documentation');
                                $annotation->appendChild($documentation)->appendChild($dom->createTextNode($type[6]));

                            }
                            if(isset($type[5])) {
                                $appinfo = $dom->createElement('xsd:appinfo');
                                $annotation->appendChild($appinfo)->appendChild($dom->createTextNode($type[5]));
                            }
                            $element->appendChild($annotation);
                        }

                        $all->appendChild($element);
                    }
                    $complexType->appendChild($all);
                }
            }
            $schema->appendChild($complexType);
        }
        foreach ($this->elements as $name => $parameters) {
            $element = $dom->createElement('xsd:element');
            $element->setAttribute('name', $name);
            $complexType = $dom->createElement('xsd:complexType');
            if (!empty($parameters)) {
                $sequence = $dom->createElement('xsd:sequence');
                foreach ($parameters as $paramName => $paramOpts) {
                    $innerElement = $dom->createElement('xsd:element');
                    $innerElement->setAttribute('name', $paramName);
                    $innerElement->setAttribute('type', $paramOpts['type']);
                    if (isset($paramOpts['nillable']) && $paramOpts['nillable']) {
                        $innerElement->setAttribute('nillable', 'true');
                    }
                    $sequence->appendChild($innerElement);
                }
                $complexType->appendChild($sequence);
            }
            $element->appendChild($complexType);
            $schema->appendChild($element);
        }
        $types->appendChild($schema);
        $dom->documentElement->appendChild($types);
    }

    /**
     * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
     */
    protected function addMessages($dom)
    {
        foreach ($this->messages as $name => $message) {
            $element = $dom->createElement('wsdl:message');
            $element->setAttribute('name', $name);
            foreach ($this->messages[$name] as $partName => $part) {
                if (is_array($part)) {
                    $partElement = $dom->createElement('wsdl:part');
                    $partElement->setAttribute('name', $partName);
                    if (isset($part['type'])) {
                        $partElement->setAttribute('type', $part['type']);
                    }
                    if (isset($part['element'])) {
                        $partElement->setAttribute('element', $part['element']);
                    }
                    $element->appendChild($partElement);
                }
            }
            $dom->documentElement->appendChild($element);
        }
    }

    /**
     * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
     */
    protected function addPortTypes($dom)
    {
        $portType = $dom->createElement('wsdl:portType');
        $portType->setAttribute('name', $this->serviceName . 'PortType');
        $dom->documentElement->appendChild($portType);
        foreach ($this->operations as $name => $operation) {
            $portType->appendChild($this->createPortElement($dom, $name, $operation['doc']));
        }
    }

    /**
     * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
     * @param string $name method name
     * @param string $doc doc
     * @return DOMElement
     */
    protected function createPortElement($dom, $name, $doc)
    {
        $operation = $dom->createElement('wsdl:operation');
        $operation->setAttribute('name', $name);

        $input = $dom->createElement('wsdl:input');
        $input->setAttribute('message', 'tns:' . $name . 'In');
        $output = $dom->createElement('wsdl:output');
        $output->setAttribute('message', 'tns:' . $name . 'Out');

        // TODO: md->html
        $documentationNode = $operation->appendChild($dom->createElement('wsdl:documentation'));
		$documentationNode->appendChild($dom->createTextNode($doc));

        $operation->appendChild($input);
        $operation->appendChild($output);

        return $operation;
    }

    /**
     * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
     */
    protected function addBindings($dom)
    {
        $binding = $dom->createElement('wsdl:binding');
        $binding->setAttribute('name', $this->serviceName . 'Binding');
        $binding->setAttribute('type', 'tns:' . $this->serviceName . 'PortType');

        $soapBinding = $dom->createElement('soap:binding');
        $soapBinding->setAttribute('style', $this->bindingStyle);
        $soapBinding->setAttribute('transport', $this->bindingTransport);
        $binding->appendChild($soapBinding);

        $dom->documentElement->appendChild($binding);

        foreach ($this->operations as $name => $operation) {
            $binding->appendChild($this->createOperationElement($dom, $name, $operation['headers']));
        }
    }

    /**
     * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
     * @param string $name method name
     * @param array $headers array like array('input'=>array(MESSAGE,PART),'output=>array(MESSAGE,PART))
     * @return DOMElement
     */
    protected function createOperationElement($dom, $name, $headers = null)
    {
        $operation = $dom->createElement('wsdl:operation');
        $operation->setAttribute('name', $name);
        $soapOperation = $dom->createElement('soap:operation');
        $soapOperation->setAttribute('soapAction', $this->namespace . '#' . $name);
        if ($this->bindingStyle == self::STYLE_RPC) {
            $soapOperation->setAttribute('style', self::STYLE_RPC);
        }

        $input = $dom->createElement('wsdl:input');
        $output = $dom->createElement('wsdl:output');

        $soapBody = $dom->createElement('soap:body');
        $operationBodyStyle = $this->operationBodyStyle;
        if ($this->bindingStyle == self::STYLE_RPC && !isset($operationBodyStyle['namespace'])) {
            $operationBodyStyle['namespace'] = $this->namespace;
        }
        foreach ($operationBodyStyle as $attributeName => $attributeValue) {
            $soapBody->setAttribute($attributeName, $attributeValue);
        }
        $input->appendChild($soapBody);
        $output->appendChild(clone $soapBody);
        if (is_array($headers)) {
            if (isset($headers['input']) && is_array($headers['input']) && count($headers['input']) == 2) {
                $soapHeader = $dom->createElement('soap:header');
                foreach ($operationBodyStyle as $attributeName => $attributeValue) {
                    $soapHeader->setAttribute($attributeName, $attributeValue);
                }
                $soapHeader->setAttribute('message', $headers['input'][0]);
                $soapHeader->setAttribute('part', $headers['input'][1]);
                $input->appendChild($soapHeader);
            }
            if (isset($headers['output']) && is_array($headers['output']) && count($headers['output']) == 2) {
                $soapHeader = $dom->createElement('soap:header');
                foreach ($operationBodyStyle as $attributeName => $attributeValue) {
                    $soapHeader->setAttribute($attributeName, $attributeValue);
                }
                $soapHeader->setAttribute('message', $headers['output'][0]);
                $soapHeader->setAttribute('part', $headers['output'][1]);
                $output->appendChild($soapHeader);
            }
        }

        $operation->appendChild($soapOperation);
        $operation->appendChild($input);
        $operation->appendChild($output);

        return $operation;
    }

    /**
     * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
     * @param string $serviceUrl Web service URL
     */
    protected function addService($dom, $serviceUrl)
    {
        $service = $dom->createElement('wsdl:service');
        $service->setAttribute('name', $this->serviceName . 'Service');

        $port = $dom->createElement('wsdl:port');
        $port->setAttribute('name', $this->serviceName . 'Port');
        $port->setAttribute('binding', 'tns:' . $this->serviceName . 'Binding');

        $soapAddress = $dom->createElement('soap:address');
        $soapAddress->setAttribute('location', $serviceUrl);
        $port->appendChild($soapAddress);
        $service->appendChild($port);
        $dom->documentElement->appendChild($service);
    }

	/**
	 * Adds XML subtree from DOMElement or DOMDocument or xml string
	 *
	 * XML document or xml fragment is supported.
	 *
	 * @param DOMElement $element -- node to append content to
	 * @param string|DOMElement $cont - xml content to insert
	 * @param boolean $omitroot - omits root node of xml document
	 *
	 * @return DOMElement|DOMNode - the (first) inserted node.
	 * @throws DomException on invalid XML string
	 */
	function addXmlContent($element, $cont, $omitroot=false) {
		if($cont instanceof DOMDocument) {
			if($omitroot) {
				/** @var DOMElement $first */
				$first = null;
				foreach($cont->documentElement->childNodes as $child) {
					$inserted = $element->appendChild($element->ownerDocument->importNode($child, true));
					if(!$first) $first = $inserted;
				}
				return $first;
			}
			return $element->appendChild($element->ownerDocument->importNode($cont->documentElement, true));
		}
		if($cont instanceof DOMElement or $cont instanceof DOMDocumentFragment) {
			if($omitroot or $cont instanceof DOMDocumentFragment) {
				/** @var DOMElement $first */
				$first = null;
				foreach($cont->childNodes as $child) {
					$inserted = $element->appendChild($element->ownerDocument->importNode($child, true));
					if(!$first) $first = $inserted;
				}
				return $first;
			}
			return $element->appendChild($element->ownerDocument->importNode($cont, true));
		}
		if(!is_string($cont)) $cont = $element->ownerDocument->toString($cont);
		return $this->addHypertextContent($cont, true, !$omitroot);
	}

	/**
	 * Adds XML content from a string
	 * If string is not valid XML, a text node will be created
	 * If string starts with <?xml, a complete XML document is assumed, and root node may be omitted
	 * Otherwise the string is decoded as xml fragment, and root node cannot be omitted.
	 *
	 * @param DOMElement $element -- node to append content to
	 * @param string $cont - string xml content to insert. May be xml document (starting with <?xml) or xml fragment (starting with '<')
	 * @param bool $strict -- throws an DomException on invalid xml string, otherwise inserts as text with (*) mark.
	 * @param bool $root -- include root node of xml (not used if xml fragment is provided)
	 * @param string $namespace -- insert/replace default namespace. Does not affect declared prefixed namespaces. Default is none, leaves declared
	 *
	 * @return DOMElement|DOMNode - (first) inserted node (may be text node)
	 * @throws \DOMException
	 */
	function addHypertextContent($element, $cont, $strict=false, $root=false, $namespace=null) {
		if($cont instanceof DOMNode) {
			return $this->addXmlContent($element, $cont, !$root);
		}
		if(is_null($cont)) return null;
		if(is_numeric($cont)) $cont=(string)$cont;
		if(!is_string($cont)) $cont = $element->ownerDocument->toString($cont);
		$dom2 = new DOMDocument();
		try {
			$contt = trim($cont);
			if(substr($contt,0,6)=='<?xml '  and substr($contt,-1)=='>') {
				$dom2->loadXML($contt);
				if($root) {
					return $element->appendChild($element->ownerDocument->importNode($dom2->documentElement, true));
				}
				else {
					$nodex = $dom2->documentElement->firstChild;
					/** @var DOMElement $first */
					$first = null;
					while($nodex) {
						$inserted = $element->appendChild($element->ownerDocument->importNode($nodex, true));
						if(!$first) $first = $inserted;
						$nodex = $nodex->nextSibling;
					}
					return $first;
				}
			}
			else if(substr($contt,0,1)=='<' and substr($contt,-1)=='>') {
				$xmlns = $namespace ? ' xmlns="'.$namespace.'"' : '';
				$r = @$dom2->loadXML("<?xml version=\"1.0\" encoding=\"UTF-8\" ?><data$xmlns>$contt</data>");
				if(!$r) @$r = $dom2->loadXML("<?xml version=\"1.0\" encoding=\"ISO-8859-2\" ?><data$xmlns>$contt</data>");
				if(!$r) {
					if($strict) throw new DomException('Invalid XML content');
					return $element->appendChild($element->ownerDocument->createTextNode($cont.' (*)'));
				}
				$nodex = $dom2->documentElement->firstChild;
				/** @var DOMElement $first */
				$first = null;
				while($nodex) {
					$inserted = $element->appendChild($element->ownerDocument->importNode($nodex, true));
					if(!$first) $first = $inserted;
					$nodex = $nodex->nextSibling;
				}
				return $first;
			}
			else {
				return $element->appendChild($element->ownerDocument->createTextNode($cont));
			}
		}
		catch(DomException $e) {
			if($strict) throw new DomException('Invalid XML content', null, $e);
			return $element->appendChild($element->ownerDocument->createTextNode($cont.' (**)'));
		}
	}
}
