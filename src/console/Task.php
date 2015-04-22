<?php
/*
 * This file is part of Yolk - Gamer Network's PHP Framework.
 *
 * Copyright (c) 2013 Gamer Network Ltd.
 * 
 * Distributed under the MIT License, a copy of which is available in the
 * LICENSE file that was bundled with this package, or online at:
 * https://github.com/gamernetwork/yolk-core
 */

namespace yolk\console;

abstract class Task {

	const LOCK_DIR = '/tmp';
	const PS_EXEC  = 'ps --no-heading -p ';

	abstract public function run( array $args );

	public function __invoke() {

		if( !$this->lock() )
			throw new \RuntimeException(sprintf('An instance of \\%s is already running', get_class($this)));

		$result = $this->run(
			$this->parseArgs()
		);

		$this->unlock();

		return $result;

	}

	/**
	 * parseArgs Command Line Interface (CLI) utility function.
	 * @author   Patrick Fisher <patrick@pwfisher.com>
	 * @see      https://github.com/pwfisher/CommandLine.php
	 */
	protected function parseArgs( $argv = null ) {
		$argv = $argv ? $argv : $_SERVER['argv']; array_shift($argv); $o = [];
		for ($i = 0, $j = count($argv); $i < $j; $i++) { $a = $argv[$i];
			if (substr($a, 0, 2) == '--') { $eq = strpos($a, '=');
				if ($eq !== false) { $o[substr($a, 2, $eq - 2)] = substr($a, $eq + 1); }
				else { $k = substr($a, 2);
					if ($i + 1 < $j && $argv[$i + 1][0] !== '-') { $o[$k] = $argv[$i + 1]; $i++; }
					else if (!isset($o[$k])) { $o[$k] = true; } } }
			else if (substr($a, 0, 1) == '-') {
				if (substr($a, 2, 1) == '=') { $o[substr($a, 1, 1)] = substr($a, 3); }
				else {
					foreach (str_split(substr($a, 1)) as $k) { if (!isset($o[$k])) { $o[$k] = true; } }
					if ($i + 1 < $j && $argv[$i + 1][0] !== '-') { $o[$k] = $argv[$i + 1]; $i++; } } }
			else { $o[] = $a; } }
		return $o;
	}

	/**
	 * Return the name of the lock file used by this task.
	 * @return string
	 */
	protected function getLockFile() {
		return rtrim(static::LOCK_DIR, '/'). '/'. strtolower(str_replace('\\', '-', get_class($this)));
	}

	protected function isLocked() {

		// check if lock file exists
		$locked = file_exists($lock = $this->getLockFile());

		// if it does then check the process is actually still running
		if( $locked ) {
			$pid = file_get_contents($lock);
			$locked = exec(static::PS_EXEC. $pid) != '';
			// no such process so remove the lock file
			if( !$locked )
				$this->unlock();
		}

		return $locked;

	}

	protected function lock() {

		// locked by running process
		if( $this->isLocked() )
			return false;

		// open the lock file only if it doesn't exist
		if( !$fh = @fopen($this->getLockFile(), 'x') )
			return false;

		// clear it and write out the new PID
		ftruncate($fh, 0);
		fwrite($fh, getmypid());
		fclose($fh);

		return true;

	}

	protected function unlock() {

		$lock = $this->getLockFile();

		if( file_exists($lock) ) {

			// PIDs don't match - ooops!
			if( file_get_contents($lock) != getmypid() )
				throw new \RuntimeException('Lock file contains incorrect PID');

			// Delete the lock file
			if( !unlink($lock) )
				throw new \RuntimeException('Unable to remove lock file');

		}

	}

}

// EOF