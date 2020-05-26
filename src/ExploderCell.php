<?php
namespace ExploderCells;

class ExploderCell extends ExploderObject implements JsonSerializable {
	private $contenido;
	private $mapa;
	private $vecinos;
	private $jugador;

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

	public function getBall(ExploderPlayer $jugador) {
		$retval = array();
		if(!is_null($this->jugador) && $jugador != $this->jugador) {
			$retval[$this->jugador->getId()] = ($this->contenido * -1);
			$retval[$jugador->getId()] = $this->contenido;
		}
		$this->contenido++;
		$this->jugador = $jugador;
		if($this->contenido == $this->vecinos->count())
			$this->mapa->queue($this);
		return $retval;
	}

	public function explode() {
		$retval = array();
		foreach($this->vecinos as $vecino_actual) {
			$this->contenido--;
			$change = $vecino_actual->getBall($this->jugador);
			foreach($change as $k => $v) {
				if(!isset($retval[$k]))
					$retval[$k] = 0;
				$retval[$k] += $v;
			}
		}
		return $retval;
	}

	public function jsonSerialize() {
		return array(
			'id' => $this->getId(),
			'contenido' => $this->contenido,
			'jugador' => $this->jugador
		);
	}

	public function getJugador() {
		return $this->jugador;
	}
}

?>
