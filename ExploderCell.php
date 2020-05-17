<?php

class ExploderCell extends ExploderObject implements JsonSerializable {
	private $contenido;
	private $mapa;
	private $vecinos;

	public function __construct(ExploderMap $mapa = null) {
		parent::__construct();
		$this->vecinos = new SplObjectStorage();
		$this->contenido = 0;
		$this->mapa = $mapa;
	}

	public function setNeighbour(self $vecino) {
		$this->vecinos->attach($vecino);
	}

	public function setMap(ExploderMap $mapa) {
		$this->mapa = $mapa;
	}

	public function getBall() {
		$this->contenido++;
		if($this->contenido == count($this->vecinos))
			$this->mapa->queue($this);
	}

	public function explode() {
		foreach($this->vecinos as $vecino_actual) {
			$this->contenido--;
			$vecino_actual->getBall();
		}
	}

	public function jsonSerialize() {
		return array(
			'id' => $this->getId(),
			'contenido' => $this->contenido,
		);
	}
}

?>
