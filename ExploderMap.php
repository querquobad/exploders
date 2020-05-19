<?php

class ExploderMap extends ExploderObject implements JsonSerializable {
	private $cuartos = array();
	private $exploders;
	private $cuenta = array();

	public function __construct($ancho,$alto) {
		parent::__construct();
		$this->exploders = new SplQueue();
		foreach(array('crear','conectar') as $accion) {
			for($h = 0;$h < $alto; $h++) {
				for($w = 0;$w < $ancho; $w++) {
					if($accion == 'crear') {
						$this->cuartos[$h][$w] = new ExploderCell($this);
					} else {
						if(isset($this->cuartos[$h+1][$w]))
							$tmp[] = $this->cuartos[$h+1][$w];
						if(isset($this->cuartos[$h-1][$w]))
							$tmp[] = $this->cuartos[$h-1][$w];
						if(isset($this->cuartos[$h][$w-1]))
							$tmp[] = $this->cuartos[$h][$w-1];
						if(isset($this->cuartos[$h][$w+1]))
							$tmp[] = $this->cuartos[$h][$w+1];
						foreach($tmp as $actual) {
							$this->cuartos[$h][$w]->setNeighbour($actual);
						}
						unset($tmp);
					}
				}
			}
		}
	}

	public function jsonSerialize() {
		return array(
			'id' => $this->getId(),
			'cuartos' => $this->cuartos
		);
	}

	public function pushBall($id, int $jugador) {
		if(!isset($this->cuenta[$jugador]))
			$this->cuenta[$jugador] = 0;
		$cuarto = $this->recursive_room_search($id);
		$owner = $cuarto->getJugador();
		if(!is_null($owner) && $jugador != $owner)
			throw new CellNotOwnedException('Esa celda pertenece al jugador '.$owner);
		$this->cuenta[$jugador]++;
		$cuarto->getBall($jugador);
		echo $id."\n";
		while($count = $this->exploders->count()) {
			for($i=0;$i<$count;$i++) {
				$cuarto = $this->exploders->dequeue();
				echo "\t".$cuarto->getId()."\n";
				$change = $cuarto->explode();
				foreach($change as $k => $v)
					$this->cuenta[$k] += $v;
			}
		}
	}

	private function recursive_room_search($id) {
		foreach($this->cuartos as $v => $nivel) {
			foreach($nivel as $h => $cuarto) {
				if($cuarto->getId() == $id)
					return $cuarto;
			}
		}
		throw new OutOfBoundsException('El cuarto no existe');
	}

	public function queue(ExploderCell $cell) {
		$this->exploders->enqueue($cell);
	}
}

?>
