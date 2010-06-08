<?php

/**
 * This is used in order to connect to a MySQL database.
 *
 * @author	 Hans Lellelid <hans@xmpl.org> (Propel)
 * @author	 Jon S. Stevens <jon@clearink.com> (Torque)
 * @author	 Brett McLaughlin <bmclaugh@algx.net> (Torque)
 * @author	 Daniel Rall <dlr@finemaltcoding.com> (Torque)
 * @version	$Revision: 989 $
 * @package	propel.adapter
 */
class DBMySQL extends DABLPDO {

	private $_transaction_count = 0;
	private $_rollback_connection = false;

	/**
	 * This method is used to ignore case.
	 *
	 * @param	  in The string to transform to upper case.
	 * @return	 The upper case string.
	 */
	function toUpperCase($in){
		return "UPPER(" . $in . ")";
	}

	/**
	 * This method is used to ignore case.
	 *
	 * @param	  in The string whose case to ignore.
	 * @return	 The string in a case that can be ignored.
	 */
	function ignoreCase($in){
		return "UPPER(" . $in . ")";
	}

	/**
	 * Returns SQL which concatenates the second string to the first.
	 *
	 * @param	  string String to concatenate.
	 * @param	  string String to append.
	 * @return	 string
	 */
	function concatString($s1, $s2){
		return "CONCAT($s1, $s2)";
	}

	/**
	 * Returns SQL which extracts a substring.
	 *
	 * @param	  string String to extract from.
	 * @param	  int Offset to start from.
	 * @param	  int Number of characters to extract.
	 * @return	 string
	 */
	function subString($s, $pos, $len){
		return "SUBSTRING($s, $pos, $len)";
	}

	/**
	 * Returns SQL which calculates the length (in chars) of a string.
	 *
	 * @param	  string String to calculate length of.
	 * @return	 string
	 */
	function strLength($s){
		return "CHAR_LENGTH($s)";
	}


	/**
	 * Locks the specified table.
	 *
	 * @param	  string $table The name of the table to lock.
	 * @throws	 PDOException No Statement could be created or
	 * executed.
	 */
	function lockTable($table){
		$this->exec("LOCK TABLE " . $table . " WRITE");
	}

	/**
	 * Unlocks the specified table.
	 *
	 * @param	  string $table The name of the table to unlock.
	 * @throws	 PDOException No Statement could be created or
	 * executed.
	 */
	function unlockTable($table){
		$statement = $this->exec("UNLOCK TABLES");
	}

	/**
	 * @see		DABLPDO::quoteIdentifier()
	 */
	function quoteIdentifier($text){
		return '`' . $text . '`';
	}

	/**
	 * @see		DABLPDO::useQuoteIdentifier()
	 */
	function useQuoteIdentifier(){
		return true;
	}

	/**
	 * @see		DABLPDO::applyLimit()
	 */
	function applyLimit(&$sql, $offset, $limit){
		if ( $limit > 0 ) {
			$sql .= " LIMIT " . ($offset > 0 ? $offset . ", " : "") . $limit;
		} else if ( $offset > 0 ) {
			$sql .= " LIMIT " . $offset . ", 18446744073709551615";
		}
	}

	/**
	 * @see		DABLPDO::random()
	 */
	function random($seed = null){
		return 'rand('.((int) $seed).')';
	}

	/**
	 * Begin a (possibly nested) transaction.
	 *
	 * @author Aaron Fellin <aaron@manifestwebdesign.com>
	 * @see PDO::beginTransaction()
	 */
	function beginTransaction() {
		if ($this->_transaction_count<=0) {
			$this->_rollback_connection = false;
			$this->_transaction_count = 0;
			parent::beginTransaction();
		}
		++$this->_transaction_count;
	}

	/**
	 * Commit a (possibly nested) transaction.
	 * FIXME: Make this throw an Exception of a DABL class
	 *
	 * @author Aaron Fellin <aaron@manifestwebdesign.com>
	 * @see PDO::commit()
	 * @throws Exception
	 */
	function commit() {
		--$this->_transaction_count;
		if ($this->_rollback_connection) throw new Exception('DABL: Attempting to commit a rolled back connection');
		if ($this->_transaction_count==0) {
			return parent::commit();
		} elseif ($this->_transaction_count < 0) {
			throw new Exception('DABL: Attempting to commit outside of a transaction');
		}
	}

	/**
	 * Rollback, and prevent all further commits in this transaction.
	 * FIXME: Make this throw an Exception of a DABL class
	 *
	 * @author Aaron Fellin <aaron@manifestwebdesign.com>
	 * @see PDO::rollback()
	 * @throws Exception
	 */
	function rollback() {
		--$this->_transaction_count;
		$this->_rollback_connection = true;
		if ($this->_transaction_count==0) {
			return parent::rollback();
		} elseif ($this->_transaction_count < 0) {
			throw new Exception('DABL: Attempting to rollback outside of a transaction');
		}
	}

	/**
	 * Utility function for writing test cases.
	 *
	 * @author Aaron Fellin <aaron@manifestwebdesign.com>
	 * @return int
	 */
	function getTransactionCount() {
		return $this->_transaction_count;
	}

	/**
	 * Utility function for writing test cases.
	 *
	 * @author Aaron Fellin <aaron@manifestwebdesign.com>
	 * @return bool
	 */
	function getRollbackImminent() {
		return $this->_rollback_connection;
	}

	/**
	 * @return Database
	 */
	function getDatabaseSchema(){
		
		Module::import('ROOT:libraries:propel');
		Module::import('ROOT:libraries:propel:database');
		Module::import('ROOT:libraries:propel:database:model');
		Module::import('ROOT:libraries:propel:database:reverse');
		Module::import('ROOT:libraries:propel:database:reverse:mysql');
		Module::import('ROOT:libraries:propel:database:tranform');
		Module::import('ROOT:libraries:propel:platform');

		$parser = new MysqlSchemaParser();
		$parser->setConnection($this);
		$database = new Database($this->getDBName());
		$database->setPlatform(new MysqlPlatform());
		$parser->parse($database);
		return $database;
	}

}
