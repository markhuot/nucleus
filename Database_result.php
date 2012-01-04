<?php

class Database_result implements Iterator {
	/**
	 * Index
	 * The index of the iterator as we loop over the records.
	 */
	public $index = 0;

	/**
	 * Records
	 * Stores each record this result set is responsible for. It is stored in a
	 * nested fashion like this:
	 * $records = array(
	 *     [posts] = array(                           // The related table name
	 *         [id] = array(                          // The related table key
	 *             [null] = Database_result Object    // The related table id
	 *                 [12] = Database_record Object, // The related object
	 *                 [9] = Database_record Object,  // The key maps to the...
	 *                 [2] = Database_record Object,  // ..object's primary key
	 *             )
	 *         )
	 *     ), 
	 *     [t1] = array(                              // Stored by the table id
	 *         [post_id] = array(
	 *             [3] = Database_result Object
	 *                 [12] = Database_record Object,
	 *                 [9] = Database_record Object,
	 *                 [2] = Database_record Object,
	 *             )
	 *         )
	 *     ),
	 *     [t2] = array(
	 *         [post_id] = array(
	 *             [3] = Database_result Object (
	 *                 [1] Database_record Object,
	 *                 [2] Database_record Object,
	 *                 [3] Database_record Object,
	 *             )
	 *         )
	 *     )
	 * )
	 */
	private $records = array();

	/**
	 * Query
	 * The query that generated this result set. This is useful because it
	 * defines how JOINs were added and what identifiers table names were
	 * mapped to.
	 */
	private $query;

	/**
	 * Table Name
	 * Each relation has one, and only one, primary table. It is this table
	 * that we iterate over and this table that drives all the relations. In
	 * the sample query `SELECT * FROM posts LEFT JOIN post_data…` the primary
	 * table would be the `posts` table. This is passed into the constructor
	 * and must be passed with every result.
	 */
	private $table_name;

	/**
	 * Key
	 * The key defines how related tables are joined onto the primary table.
	 * In the $records array this is the second level key. It defaults to `id`
	 * as a way to index the primary table.
	 */
	private $key = 'id';

	/**
	 * Id
	 * The ID is the way related tables are joined onto the primary table. By
	 * default we set this to null.
	 */
	private $id = 'null';

	/**
	 * Construct
	 * Accepts the rows to parse and the query that generated this result.
	 */
	public function __construct($query=FALSE, $rows=array()) {
		$this->query = $query;
		$this->table_name = $this->query->primary_table();
		foreach ($rows as $row) {
			foreach ($this->parse_row_to_records($row) as $record) {
				$this->add_record($record);
			}
		}
	}

	/**
	 * Clone
	 * When a result set is cloned, possibly to reference related entries we
	 * want to make sure certain properties are reset.
	 */
	public function __clone() {
		$defaults = (object)get_class_vars('Database_result');
		$this->index = $defaults->index;
	}

	/**
	 * Parse Row
	 * Parses a raw database result and turns it into actual records. The raw
	 * database result would be an array with column names mapping to one or
	 * more tables. This method takes each column, determines which table or
	 * record it references and adds it to the appropriate record.
	 * This is called from the __constructor and probably won't need to be
	 * called publically on the object.
	 */
	private function parse_row_to_records($record) {

		// We'll store each record contained in this row here
		$records = array();

		// Loop through each column in the row to determine which table
		// it belongs to.
		foreach ($record as $key => $value) {

			// Explode the column name to determine the table name. So, a
			// column name of `posts.title` will be from the table `posts` and
			// have a column name of `title`.
			list($table_identifier, $column) = explode('.', $key);
			$table_name = $this->query->table_name_for($table_identifier);
			
			// If this is the first column from the determined table create
			// a new database record to hold it.
			if (!@$records[$table_identifier]) {
				$records[$table_identifier] = new Database_record($this, $table_name, $table_identifier);
			}

			// Finally, add the column and it's value to the appropriate
			// database record
			$records[$table_identifier]->set_data($column, $value);
		}

		// Loop through each record and remove records that don't have a PK
		// since they're probably just artifacts of the join. This happens when
		// you SELECT * FROM posts LEFT JOIN comments. If a post has no
		// comments the comment table columns will still be returned but
		// instead of having any data in them they will all be null.
		foreach ($records as &$record) {
			if (!$record->id()) {
				$record = null;
			}
		}

		// Return the array of non-null records contained in this row
		return array_filter($records);
	}

	/**
	 * Add Record
	 * Adds a record to the result set. If the record contains any foreign keys
	 * we'll add it to the result set mutliple times for each foreign key.
	 */
	public function add_record($record) {

		// If the record's table name matches the primary table name then we
		// want to add this record to the `$records` collection using the
		// default key/id. We assume that the primary key is unique, which it
		// should be. This allows us to add the same record multiple times
		// without fear. This is a very real possibility when parsing a query
		// with related data. For example a result set of posts and comments
		// may have a single post repeated several times as each comment is
		// returned. In this way we can simply replace the `post` record each
		// time its encountered.
		if ($record->table_name() == $this->table_name) {
			$this->records
				[$record->table_name()]
				[$this->key]
				[$this->id]
				[$record->id()] =
					$record;
		}

		// If the record's table is not the primary table then we'll dump it
		// into the related_records collection. This collection is organized
		// into three depths. First the relation name (commonly the table
		// name) is used, then the foreign key used for the match, and finally
		// the id. This creates an array as defined in the $related_records
		// comment above.
		foreach ($this->query->join_configs() as $join_config) {
			if ($record->table_identifier() == $join_config['as']) {
				$this->records
					[$join_config['as']]
					[$join_config['foreign_key']]
					[$record->{$join_config['foreign_key']}]
					[$record->id()] =
						$record;
			}
		}
	}

	/**
	 * Records
	 * Returns the currently focused collection.
	 */
	public function records() {
		return @$this->records
			[$this->table_name]
			[$this->key]
			[$this->id];
	}

	/**
	 * Record
	 * Returns the record in question. You can ask for a record by
	 * index (0,1,2,3…) which will return the actual Database_record or you
	 * can ask for a string "title" or "body" to return the value of the 
	 * column in the first record.
	 */
	public function record($key) {
		$collection = $this->records();
		$keys = array_keys($collection);

		if (is_string($key)) {
			return @$collection[$keys[0]]->{$key};
		}

		else {
			return @$collection[$keys[$key]];
		}
	}

	/**
	 * Related
	 * If a relation exists at the defined key it is returned, otherwise
	 * FALSE is returned.
	 */
	public function related($name, $record) {
		$key = $record->table_identifier().'.'.$name;
		if (!($config = $this->query->join_config($key))) {
			return FALSE;
		}
		
		$table = $config['table_name'];
		$as = $config['as'];
		$pk = $config['primary_key'];
		$fk = $config['foreign_key'];
		$id = $record->{$pk};

		if ($this->records[$as][$fk][$id]) {
			$result = clone $this;
			$result->table_name = $as;
			$result->key = $fk;
			$result->id = $id;
			return $result;
		}

		return FALSE;
	}

	/**
	 * Size
	 * Returns the number of rows in this result set.
	 */
	public function size() {
		return count($this->records());
	}

	/**
	 * Iterators
	 * The required methods to iterate over an object. The trickery going on
	 * here involves our $records array being indexed by primary key and not
	 * incrementing numerically. So, for each of these we have to get our
	 * array of keys and apply our index to the array of keys instead of just
	 * using $this->records actual keys.
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