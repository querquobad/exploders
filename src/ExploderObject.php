<?php
namespace ExploderCells;

class ExploderObject {
	private $id;

	public function __construct() {
		$tmp = spl_object_hash($this);
		$this->id = preg_replace('/^0+/','',substr($tmp,16,16)).preg_replace('/^0+/','',substr($tmp,0,16));
	}

	protected function getId() {
		return $this->id;
	}
}

?>
