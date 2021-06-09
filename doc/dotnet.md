Calling PH SOAP service from .NET client in WSDL mode
=====================================================

Basically, a function with this signature:

<?php
function SomeFunction($param1, $param2)
{
   return $param1+$param2;
}
?>

Must be rewritten to this, without changing the WSDL:

<?php
function SomeFunction($data)
{
$valueArray = get_object_vars($data);
$param1 = $valueArray["param1"];
$param2 = $valueArray["param2"];
return Array("SomeFunctionResult" => $param1+$param2);
}
?>