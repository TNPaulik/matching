<?php

require 'connect.php';

/**
 *
 */
class migration
{
    /**
     * the db connection
     * @var object
     */
    protected object $db;

    /**
     * our tables
     * @var array
     */
    protected array $tablesOur = [];

    /**
     * their tables
     * @var array
     */
    protected array $tablesTheir = [];

    /**
     * our db name
     * @var string
     */
    protected string $dbOur = 'our';

    /**
     * their db name
     * @var string
     */
    protected string $dbTheir = 'db_guh';

    /**
     * The key is the our part, the value is the their part of the matches.json, seperated in an array by the {...} things
     * i.e. "{t1.name} ist member von {t2.name}" wird zu ["{t1.name}", " ist member von ", "{t2.name}"]
     * @var object
     */
    protected object $subfields;

    /**
     * the relations between their tables
     * loaded from matches/karat/info/rels.json
     * created with the UI using the "+ Rel" button
     * @var object
     */
    protected object $rels;

    /**
     * created dynamically from the custom functions for joins and group_concats
     * @var array
     */
    protected array $relsCustom = [];

    /**
     * the matchings between our tables that should to be imported and their matching main tables
     * loaded from matches/karat/tablematchings.json
     * created with the UI using the "+ Tablematching" button
     * @var array|string[]
     */
    protected array $tableMatchings;

    /**
     * @var string|mixed
     */
    protected string $name;

    /**
     * @var string
     */
    protected string $jsonDesc;

    /**
     * @var object
     */
    protected object $desc;

    /**
     * @var object
     */
    protected object $data;

    /**
     * @var int
     */
    protected int $_OFFSET_ = 100000;

    /**
     * @var string
     */
    protected string $sqlc = '';

    /**
     * @var array|string[]
     */
    protected array $functions = [
        'delim', // delim({name of col with the values to put together}, {col to match it to}) ,=default seperator cause of , colliding with everything
        'phone', // phone({phonenumber})
        'count' // count({name of col with the values to count together})
    ];

    /**
     * @var array|false
     */
    protected array|false $matchesNames;

    /**
     * @param string $name
     */
    public function __construct(string $name = 'karat')
    {
        $this->name = $name;
        $this->data = new stdClass();
        $this->rels = json_decode(file_get_contents(__DIR__ . '/matches/karat/info/rels.json'));
        $this->tableMatchings = (array) json_decode(file_get_contents(__DIR__ . '/matches/karat/info/tms.json'));
        //$this->migration($name);
    }

    /**
     * makes the sqls and executes them
     * @return void
     */
    public function migration(): void
    {
        $this->matchesNames = scandir('matches/' . $this->name . '/');
        foreach ($this->matchesNames AS $key => $val) {
            if (!preg_match('/\.json$/', $val)) {
                unset($this->matchesNames[$key]);
            } else {
                $name = preg_replace('/\.json$/', '', $val);
                $this->jsonDesc = file_get_contents(__DIR__ . '/matches/'. $this->name . '/' . $name . '.json');
                $this->data = (object)json_decode($this->jsonDesc);
                $this->desc = (object)$this->data->matches;
                if (class_exists('db'))
                    $this->db = new db();
                $this->desc = (object)$this->data->matches;
                $this->splitt();
                $this->getTables();
                $this->getSql($name);
            }
        }
        echo $this->sqlc;
		$res = $GLOBALS['cons'][0]->query($this->sqlc);
        $this->afterWorks();
    }

    /**
     * @param string $str
     * @return string|null
     */
    protected function getTableName(string $str): string|null
    {
        $parts = preg_split('/\./', $str);
        return $parts[0];
    }

    /**
     * @param string $str
     * @return string|null
     */
    protected function getFieldName(string $str): string|null
    {
        $parts = preg_split('/\./', $str);
        return isset($parts[1]) ? $parts[1] : $str;
    }

    /**
     * @param string $sql
     * @param string $table
     * @return void
     */
    protected function addToRelsCustom(string $sql, string $table): void
    {
        if (isset($this->relsCustom[$table]) && !is_array($this->relsCustom[$table])) {
            $this->relsCustom[$table] = [];
        }
        $this->relsCustom[$table][] = trim($sql);
    }

    /**
     * delim({name of col with the values to put together}, {col to group by and match it to}) ,=default seperator cause of , colliding with everything
     * @param array $params
     * @param string $our
     * @return string
     */
    protected function customDelim(array $params, string $our): string
    {
        if (!isset($params[0]) || !isset($params[1])) {
            return "ERROR";
        }
        $ourTable = $this->getTableName($our);
        $ourField = $this->getFieldName($our);
        $jointable = $this->getTableName($params[0]);
        $joincol = $this->getFieldName($params[0]);
        $matchtable = $this->getTableName($params[1]);
        $matchcol = $this->getFieldName($params[1]);
        $mainTable = $this->tableMatchings[$ourTable];
        $sql = "\n\nLEFT JOIN ";
        $sql .= "(SELECT `$matchcol`, GROUP_CONCAT(`$joincol` SEPARATOR ',') `$ourField` FROM `$jointable` GROUP BY `$matchcol`) ";
        $sql .= "`$jointable` ON `$mainTable`.`ID` = `$matchtable`.`$matchcol`";
        $this->addToRelsCustom($sql, $ourTable);
        return $ourField;
    }

    /**
     * phone({phonenumber})
     * @param string $phonenumber
     * @param string $our
     * @return string[]
     */
    protected function customPhone(string $phonenumber, string $our): array
    {
        return (['\'[{"value":"\'', $phonenumber, '\'"}]\'']);
    }

    /**
     * count({name of col with the values to count together})
     * @param string $countCol
     * @param string $our
     * @return string
     */
    protected function customCount(string $countCol, string $our): string
    {
        $ourTable = $this->getTableName($our);
        $ourField = $this->getFieldName($our);
        $jointable = $this->getTableName($countCol);
        $joincol = $this->getFieldName($countCol);
        $mainTable = $this->tableMatchings[$ourTable];
        $sql = "\n\nLEFT JOIN ";
        $sql .= "(SELECT count(*) `$ourField`,  `$joincol` FROM `$jointable` GROUP BY `$joincol`) ";
        $sql .= "`$jointable` ON `$mainTable`.`ID` = `$jointable`.`$joincol`";
        $this->addToRelsCustom($sql, $ourTable);
        return $ourField;
    }

    /**
     * splits up the matches.json values and checks for custom functions and stuff
     * @return void
     */
    protected function splitt(): void
    {
        $this->subfields = new stdClass();
        foreach ($this->desc as $our => $their) {
            $subs = preg_split('/({.*?})/', $their, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            if (!empty($subs))
                $this->subfields->$our = $subs;
        }
        $this->subfieldss = new stdClass();
        // loop through all $this->subfields and make them to strings for sql
        foreach ($this->subfields as $our => $fields) {
            foreach ($fields as $key => $field) {
                if (preg_match("/{.*?}/", $field)) { // check if special field
                    // check for functions
                    foreach ($this->functions as $func) {
                        if (str_starts_with($field, '{' . $func . '(')) {
                            preg_match("/\{(.*)\(/", $field, $matches);
                            $fname = $matches[1];
                            $customfname = "custom" . ucfirst($fname);
                            if (method_exists($this, $customfname)) {
                                preg_match("/\{.*\((.*)\)/", $field, $params);
                                if (preg_match("/,/", $params[1]))
                                    $params = preg_split("/,\s*/", trim($params[1]));
                                else
                                    $params = $params[1];
                                $this->subfieldss->$our[$key] = $this->$customfname($params, $our);
                            }
                            break;
                        }
                    }
                    $matches = [];
                    if (!isset($this->subfieldss->$our[$key])) {
                        // check for CASE ... WHEN ... assigns
                        $isCaseWhen = preg_match('/\[(.*?)\]/', $field, $matches);
                        if ($isCaseWhen) {
                            $assignsa = explode(',', $matches[1]);
                            $assigns = [];
                            foreach ($assignsa as $val) {
                                $assignsb = explode('=', $val);
                                $assigns[$assignsb[0]] = $assignsb[1];
                            }
                            preg_match('/\{(.*?)\[/', $field, $fildnamea);
                            $fielda = $fildnamea[1];
                            $tablea = $this->getTableName($fielda);
                            $cola = $this->getFieldName($fielda);
                            $sql = "CASE ";
                            foreach ($assigns as $keya => $vala) {
                                $sql .= "WHEN `$tablea`.`$cola` = '$keya' THEN '$vala' ";
                            }
                            $sql .= "END";
                            $this->subfields->$our[$key] = $sql;
                        } else {
                            $this->subfields->$our[$key] = preg_replace('/({|})/', '', $field);
                        }
                    }
                } else if (!empty($field)) { // just a normal string
                    $this->subfields->$our[$key] = "'" . mysqli_escape_string($GLOBALS['cons'][0], $field) . "'";
                }
            }
            if (!isset($this->subfieldss->$our)) { // if not is made a new subfieldss take the original subfields
                if (count($this->subfields->$our) == 1) {
                    $this->subfieldss->$our = $this->subfields->$our[0];
                } else if (!empty($this->subfields->$our)) {
                    $this->subfieldss->$our = "concat(" . implode(", ", $this->subfields->$our) . ")";
                }
            } else { // take new subfieldss (the 2nd s is for special) not the original subfields cause magic happened
                if (count($this->subfieldss->$our) == 1) {
                    $this->subfieldss->$our = $this->subfieldss->$our[0];
                } else if (!empty($this->subfieldss->$our)) {
                    $this->subfieldss->$our = "concat(" . implode(", ", $this->subfieldss->$our) . ")";
                }
            }
        }
    }

    /**
     * returns the table and field escaped
     * @param string $str
     * @return string
     */
    protected function getTableFieldEscaped(string $str): string
    {
        $table = $this->getTableName($str);
        $field = $this->getFieldName($str);
        if (in_array(substr($str, 0, 1), ["'"])
            || str_starts_with($str, 'concat')
            || str_starts_with($str, 'CASE WHEN')
            || is_numeric($str)
            || str_contains($str, ' - ')
            || str_contains($str, ' + ')
        ) {
            return $str;
        } else {
            if ($field == '') {
                return "`$table`";
            } else {
                return "`$table`.`$field`";
            }
        }
    }

    /**
     * gets all the sqls of the current matches ($this->desc) after it was splitt()ed and saves it in $this->sqlc and files according to the names
     * @param string $ourname
     * @return void
     */
    protected function getSql(string $ourname): void
    {
        $tablenameOur = $ourname == 'saleoffer' ? 'sale' : $ourname;
        $tablenameTheir = $this->tableMatchings[$tablenameOur];
        $fieldNames = [];
        foreach ($this->desc as $our => $their) {
            if ($this->getTableName($our) == $tablenameOur)
                $fieldNames[$this->getFieldName($our)] = $this->getFieldName($their);
        }
        $sql = "\n\n#$ourname\nINSERT INTO `$this->dbOur`.`$tablenameOur` (`" . implode('`,`', array_keys($fieldNames)) . "`)\n";
        $sql .= "\nSELECT\n";
        $sqls = [];
        foreach ($this->desc as $our => $their) {
            if ($this->getTableName($our) == $tablenameOur || ($this->getTableName($our) == 'sale' && $tablenameOur == 'saleoffer')) {
                if (!is_array($this->subfieldss->$our)) {
                    $this->subfieldss->$our = $this->subfieldss->$our;
                } else if (!empty($this->subfieldss->$our)) {
                    $this->subfieldss->$our = "concat(" . implode(", ", $this->subfieldss->$our) . ")";
                }
                $sqls[] = $this->getTableFieldEscaped($this->subfieldss->$our) . " AS " . $this->getTableFieldEscaped($this->getFieldName($our)) . "";
            }
        }
        $sql .= '' . implode(",\n", $sqls) . '';
        $sql .= "\n";
        $sql .= 'FROM `' . $this->dbTheir . '`.`' . $tablenameTheir . '`';
        $sql .= "\n";
		if (isset($this->rels->$tablenameTheir)) {
			foreach ($this->rels->$tablenameTheir as $jointable => $joinKeys) {
				foreach ($joinKeys as $mainKey => $joinKey) {
					$sql .= "LEFT JOIN `$this->dbTheir`.`$jointable` ON `$tablenameTheir`.`$mainKey` = `$jointable`.`$joinKey`";
					$sql .= "\n";
				}
			}
		}
		if (isset($this->relsCustom[$tablenameOur])) {
			if (is_array($this->relsCustom[$tablenameOur]))
				$sql .= trim(implode("\n", $this->relsCustom[$tablenameOur]));
			else
				$sql .= $this->relsCustom[$tablenameOur];
		}
        $sql .= "\n";
        $sql .= "GROUP BY `$tablenameTheir`.`ID`;\n\n\n";

        $sql = preg_replace('/\_OFFSET\_/', $this->_OFFSET_, $sql);
        $this->sqlc .= $sql;
        //echo $sql;
		if(!file_exists('sqls/' . $this->name))
			mkdir('sqls/' . $this->name);
        file_put_contents('sqls/' . $this->name . '/' . $tablenameOur . '.sql', $sql);
        //$this->db->query($sql);   //uncomment for directly executing the sqls
    }

    /**
     * gets the tables of the matches.json and saves it in $this->tablesOur and $this->tablesTheir
     * @return void
     */
    protected function getTables(): void
    {
        $arrOur = [];
        $arrTheir = [];
        foreach ($this->desc as $nameOur => $nameTheir) {
            $tableOurName = $this->getTableName($nameOur);
            $tableTheirName = $this->getTableName($nameTheir);
            $arrOur[$tableOurName] = true;
            $arrTheir[$tableTheirName] = true;
        }
        $this->tablesOur = array_keys($arrOur);
        $this->tablesTheir = array_keys($arrTheir);
    }

    /**
     * maybe some things cant be done directly in sql, so here is a function if it occurs
     * @return void
     */
    protected function afterWorks(): void
    {
		include(__DIR__ . '/afterworks.php');
    }
}

$i = new migration();
$i->migration();