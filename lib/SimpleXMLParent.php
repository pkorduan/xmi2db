<?
//http://stackoverflow.com/questions/2174263/access-an-elements-parent-with-phps-simplexml
	class SimpleXMLParent extends SimpleXMLElement
	{
		public function get_parent_node()
		{
			return current($this->xpath('parent::*'));
		}
	}
?>