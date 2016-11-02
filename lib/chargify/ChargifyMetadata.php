<?php

class ChargifyMetadata extends ChargifyBase{

    var $name;
    var $value;
    
    public function __construct(SimpleXMLElement $customer_xml_node = null, $test_mode = false)
    {
            $this->connector = new ChargifyConnector($test_mode);
            if ($customer_xml_node) {
            //Load object dynamically and convert SimpleXMLElements into strings
                    foreach($customer_xml_node as $key => $element) { 
                        $this->$key = (string)$element; 
                    }
            }
    }

    public function create($subscription_id) {
            return $this->connector->createMetadata($this, $subscription_id);
    }
    
    protected function getName() {
            return "metadata";
    }
    
}


?>