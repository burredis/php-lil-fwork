<?php

namespace Data;

use core\Data;

class Example extends Data {

	public function findAll() {
		return $this->fecth_all("SELECT * FROM table", []);
	}

}