<?php

/**
 * 
 */
class Database
{
	
	private $con;
	public function connect(){
		$this->con = new Mysqli("znci.webhop.net", "grocery", "Gr0c3ry^&*()", "grocery");
		return $this->con;
	}
}

?>