<?php

// Our namespace…
namespace Nucleus;

/**
 * The class, which implements an Iterator so we can loop over each record in
 * a `foreach` loop.
 */
class Result implements \Iterator {
	/**
	 * The index of the iterator as we loop over the records.
	 */
	private $index = 0;

	/**
	 * Stores each record this result set is responsible for. It is stored in a
	 * nested fashion like this:
	 * 
	 *     $records = array(
	 *         [t0] = array(                         // The table name
	 *             [id] = array(                     // The related table key
	 *                 [null] = array(               // The related table id
	 *                     [12] = Nucleus_record     // The related object
	 *                     [9] = Nucleus_record      // The key maps to the...
	 *                     [2] = Nucleus_record,     // ..object's primary key
	 *                 )
	 *             )
	 *         ), 
	 *         [t1] = array(                         // Stored by the table id
	 *             [post_id] = array(
	 *                 [3] = array(
	 *                     [12] = Nucleus_record,
	 *                     [9] = Nucleus_record,
	 *                     [2] = Nucleus_record,
	 *                 )
	 *             )
	 *         ),
	 *         [t2] = array(
	 *             [post_id] = array(
	 *                 [3] = array(
	 *                     [1] Nucleus_record,
	 *                     [2] Nucleus_record,
	 *                     [3] Nucleus_record,
	 *                 )
	 *             )
	 *         )
	 *     )
	 */
	private $records = array();

	/**
	 * The query that generated this result set. This is useful because it
	 * defines how JOINs were added and what identifiers table names were
	 * mapped to.
	 */
	private $query;

	/**
	 * Each relation has one, and only one, primary table. It is this table
	 * that we iterate over and this table that drives all the relations. In
	 * the sample query `SELECT * FROM posts LEFT JOIN post_data…` the primary
	 * table would be the `posts` table.
	 */
	private $table = 't0';

	/**
	 * The key defines how related tables are joined onto the primary table.
	 * In the $records array this is the second level key. It defaults to `id`
	 * as a way to index the primary table.
	 */
	private $key = 'id';

	/**
	 * The ID is the way related tables are joined onto the primary table. By
	 * default we set this to null.
	 */
	private $id = 'null';

	/**
	 * Accepts the rows to parse and the query that generated this result.
	 */
	public function __construct($query=FALSE, $rows=array()) {
		$this->query = $query;
		$this->table = $query->primary_table();
		foreach ($rows as $row) {
			$records_in_row = $this->parse_row_to_records($row);
			foreach ($records_in_row as $record) {
				$this->add_record($record);
			}
		}
	}

	/**
	 * When a result set is cloned, possibly to reference related entries we
	 * want to make sure certain properties are reset.
	 */
	public function __clone() {
		$defaults = (object)get_class_vars('Nucleus\Result');
		$this->index = $defaults->index;
	}

	/**
	 * Parses a raw database result and turns it into actual records. The raw
	 * database result would be an array with column names mapping to one or
	 * more tables. This method takes each column, determines which table or
	 * record it references and adds it to the appropriate record.
	 * This is called from the __constructor and probably won't need to be
	 * called publically on the object.
	 */
	private function parse_row_to_records($row) {

		// We'll store each record contained in this row here
		$records = array();

		/** Loop through each column in the row to determine which table
		 * it belongs to.
		 */
		foreach ($row as $key => $value) {

			/** Explode the column name to determine the table name. So, a
			 * column name of `posts.title` will be from the table `posts` and
			 * have a column name of `title`.
			 */
			list($table_identifier, $column) = explode('.', $key);
			
			/** If this is the first column from the determined table create
			 * a new database record to hold it.
			 */
			if (!@$records[$table_identifier]) {
				$records[$table_identifier] = new Record(
					$this,
					Model::for_identifier($table_identifier)
				);
			}

			/** Finally, add the column and its value to the appropriate
			 * database record
			 */
			$records[$table_identifier]->set_data($column, $value);
		}

		/**
		 * Loop through each record and remove records that don't have a PK
		 * since they're probably just artifacts of the join. This happens when
		 * you SELECT * FROM posts LEFT JOIN comments. If a post has no
		 * comments the comment table columns will still be returned but
		 * instead of having any data in them they will all be null.
		 */
		foreach ($records as &$record) {
			if (!$record->id()) {
				$record = null;
			}
		}

		// Return the array of non-null records contained in this row
		return array_filter($records);
	}

	/**
	 * Adds a record to the result set. If the record contains any foreign keys
	 * we'll add it to the result set mutliple times for each foreign key.
	 */
	public function add_record($record) {

		// Setup the defaults
		$table_identifier = $record->table_identifier();
		$key = $this->key;
		$id = $this->id;

		/**
		 * Check if this record is the primary record or joined on via a
		 * relationship.
		 */
		if ($config = $this->query->join_for($record->model())) {
			$key = $config->foreign_key;
			$id = $record->{$key};
		}

		// Store the record
		$this->records[$table_identifier][$key][$id][$record->id()] = $record;
	}

	/**
	 * Access to the query that generated this result
	 */
	public function query() {
		return $this->query;
	}

	/**
	 * Returns the currently focused collection.
	 */
	public function records() {
		return @$this->records
			[$this->table->identifier()]
			[$this->key]
			[$this->id];
	}

	/**
	 * Returns the record in question. You can ask for a record by
	 * index (0,1,2,3…) which will return the actual Nucleus_record or you
	 * can ask for a string "title" or "body" to return the value of the 
	 * column in the first record.
	 */
	public function record($key) {
		$collection = $this->records();
		if (!$collection) {
			return false;
		}

		$keys = array_keys($collection);

		if (is_string($key)) {
			return @$collection[$keys[0]]->{$key};
		}

		else {
			return @$collection[$keys[$key]];
		}
	}

	/**
	 * If a relation exists at the defined key it is returned, otherwise
	 * FALSE is returned.
	 */
	public function related($record, $name) {
		if (!($config = $this->query->join_for($record->model(), $name))) {
			return FALSE;
		}

		$table = $config->foreign_table;
		$key = $config->foreign_key;
		$id = $record->{$config->primary_key};

		if (isset($this->records[$table->identifier()][$key][$id])) {
			$result = clone $this;
			$result->table = $config->foreign_table;
			$result->key = $key;
			$result->id = $id;

			if (get_class($config) == 'Nucleus\JoinOne') {
				return $result->record(0);
			}

			else {
				return $result;
			}
		}

		return FALSE;
	}

	/**
	 * Returns the number of rows in this result set.
	 */
	public function size() {
		return count($this->records());
	}

	/**
	 * ## Iterators
	 * 
	 * The required methods to iterate over an object. The trickery going on
	 * here involves our $records array being indexed by primary key and not
	 * incrementing numerically. So, for each of these we have to get our
	 * array of keys and apply our index to the array of keys instead of just
	 * using `$this->records` actual keys.
	 */
	public function key() {
		return $this->index;
	}

	public function current() {
		return $this->record($this->index);
	}

	function next() {
		return $this->record(++$this->index)?:FALSE;
	}

	function valid() {
		return $this->record($this->index)?TRUE:FALSE;
	}

	function rewind() {
		return $this->record($this->index = 0);
	}
}
