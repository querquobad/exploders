<?php

class ExploderPlayer extends ExploderObject {
	private $nombre;
	private $score;

	public function __construct($nombre) {
		parent::__construct();
		$this->nombre = $nombre;
	}

	public function getNombre() {
		return $this->nombre;
	}

	public function score(int $cambio) {
		if(($this->score + $cambio) < 0)
			throw new OutOfBoundsException('El jugador '.$this->id.' tiene '.$this->score.' bolas, no puede perder '.$cambio);
		$this->score += $cambio;
	}

	public function __isset($nombre) {
		if($nombre == 'score' || $nombre == 'id')
			return true;
		return false;
	}

	public function __get($nombre) {
		if($nombre == 'score')
			return $this->$nombre;
		elseif($nombre == 'id')
			return $this->getId();
		return null;
	}
}

?>
