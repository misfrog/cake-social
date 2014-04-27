<?php

/**
 * Connection Factory Interface
 */
interface ConnectionFactory {
	
	public function createConnection($data);
}
