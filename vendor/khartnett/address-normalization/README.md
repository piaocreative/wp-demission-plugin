**Purpose**

A way to normalize US mailing addresses without the need for an external service. This is a port of the perl module Geo::StreetAddress::US originally written by Schuyler D. Erle.

**Installation**

`$composer require khartnett/address-normalization`

**Usage**

```
<?php
 use Khartnett\Normalization;
 $n = new Normalization();
 $result = $n->parse('204 southeast Smith Street Harrisburg, or 97446');
 /* result:
 [
     "number" => "204",
     "street" => "Smith",
     "street_type" => "St",
     "unit" => "",
     "unit_prefix" => "",
     "suffix" => "",
     "prefix" => "SE",
     "city" => "Harrisburg",
     "state" => "OR",
     "postal_code" => "97446",
     "postal_code_ext" => null,
     "street_type2" => null,
     "prefix2" => null,
     "suffix2" => null,
     "street2" => null,
 ] */
 $string_result = $n->parse('204 southeast Smith Street Harrisburg, or 97446', true);
  /* string_result:
  "204 SE Smith St, Harrisburg, OR 97446"
  */
```