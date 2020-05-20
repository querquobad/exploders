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
		/*
		 * Status 1 = Starting
		 * Status 2 = In progress
		 * Status 3 = Game Over
		 */
		$this->status = 1;
	}

	public function jsonSerialize() {
		return array(
			'id' => $this->getId(),
			'cuartos' => $this->cuartos
		);
	}

	public function pushBall($id, ExploderPlayer $jugador) {
		if($this->status != 2)
			throw new WrongGameStatusException('El juego no ha iniciado');
		if($jugador != $this->players[0])
			throw new WrongExploderPlayerException('El turno le pertenece al jugador '.$this->players[0]->getId());
		$cuarto = $this->recursive_room_search($id);
		$owner = $cuarto->getJugador();
		if(!is_null($owner) && $jugador != $owner)
			throw new CellNotOwnedException('Esa celda pertenece al jugador '.$owner->getId());
		$jugador->score(1);
		$cuarto->getBall($jugador);
		$retval = array();
		while($count = $this->exploders->count()) {
			for($i=0;$i<$count;$i++) {
				$cuarto = $this->exploders->dequeue();
				$retval[] = $cuarto->getId();
				$change = $cuarto->explode();
				foreach($change as $k => $v)
					$this->getPlayer($k)->score($v);
			}
			$this->validaGanador();
			if($this->status == 3)
				break;
		}
		do {
			array_push($this->players,array_shift($this->players));
		} while ($this->players[0]->score === 0);
		return array(
			'status' => $this->status,
			'play' => array(
				'player' => $jugador->getId(),
				'cell' => $id
			),
			'exploders' => $retval,
			'scores' => array_column($this->players,'score','id')
		);
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

	public function newPlayer(ExploderPlayer $player) {
		if($this->status != 1)
			throw new WrongGameStatusException('El juego ya comenzó');
		$this->players[] = $player;
	}

	private function getPlayer($id) {
		foreach($this->players as $player_actual)
			if($player_actual->getId() == $id)
				return $player_actual;
	}

	private function validaGanador() {
		if(array_sum(array_column($this->players,'score')) == $this->players[0]->score)
			$this->status = 3;
	}

	public function startGame() {
		if($this->status == 2)
			throw new WrongGameStatusException('El juego ya comenzó');
		if(count($this->players) < 2)
			throw new RuntimeException('No se puede iniciar con menos de dos jugadores');
		$this->status = 2;
		return array(
			'status' => $this->status,
			'scores' => array_column($this->players,'score','id')
		);
	}
}

?>
