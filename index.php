<?php

error_reporting(E_ALL);

require 'connect.php';

/**
 * class for the 1:n matching of databases
 */
class sourceMatcher {

    /**
     * the opened connections
     * @var array
     */
    protected array $cons;

    /**
     * the databases
     * @var array
     */
    protected array $dbs;

    /**
     * the relations between the tables in the database
     * @var mixed
     */
    public mixed $rels;

    /**
     * matchings from ourTable to theirTable
     * @var mixed
     */
    public mixed $tablematchings;

    /**
     * min not empty values for table not marked as empty
     * @var int
     */
    protected int $minCountTableNotEmpty = 3;

    /**
     * min not empty values for field of table not marked as empty
     * @var int
     */
    protected int $minCountFieldNotEmpty = 3;

    /**
     * the tables array
     * @var array
     */
    protected array $tables = [];

    /**
     * the info gathered and cached from the databases
     * @var array
     */
    protected array $dbinfo = [];

    /**
     * name of current matches json
     * @var string
     */
    protected string $matchesName;

    /**
     * the available matches jsons
     * @var array
     */
    protected array $matchesNames = [];

    /**
     * @var string|false
     */
    private string|false $jsonInfo;

    /**
     * @var array
     */
    private array $info;

    /**
     *
     */
    public function __construct() {
        $this->cons = $GLOBALS['cons'];
        $this->dbs = $GLOBALS['dbs'];
        $this->jsonInfo = file_get_contents(__DIR__ . '/matches/karat/info/dbanalysis.json');
        $this->info = json_decode($this->jsonInfo);
        $this->readMatchesNames();
        $this->matchesName = $_GET['matches'] ?? reset($this->matchesNames);
        $this->rels = file_get_contents(__DIR__ . '/matches/karat/info/rels.json');
        $this->tablematchings = file_get_contents(__DIR__ . '/matches/karat/info/tms.json');
    }

    /**
     * @return array
     */
    public function getMatchesNames(): array
    {
        return $this->matchesNames;
    }

    /**
     * gets the json files of the folder
     * @return void
     */
    protected function readMatchesNames(): void {
        $this->matchesNames = scandir(__DIR__ . '/matches/karat');
        foreach ($this->matchesNames AS $key => $val) {
            if (!preg_match('/\.json$/', $val)) {
                unset($this->matchesNames[$key]);
            } else {
                $this->matchesNames[$key] = preg_replace('/\.json$/', '', $val);
            }
        }
    }

    /**
     * gets tables of database
     * @param int $dbIndex
     * @return void
     */
    public function getTables(int $dbIndex = 1): void {
        $this->cons[$dbIndex]->query("USE ".$this->dbs[$dbIndex]);
        $res = $this->cons[$dbIndex]->query("SHOW TABLES");
        $data = $res->fetch_all();
        $this->tables[$dbIndex] = [];
        foreach($data AS $table) {
            if (!empty($table[0])) {
                $this->tables[$dbIndex][] = $table[0];
            }
        }
    }

    /**
     * gets the description of a table and save it in $this->dbinfo[$dbIndex]->$table
     * @param int $dbIndex
     * @param string $table
     * @return object
     */
    protected function getTableDescription(int $dbIndex, string $table): object {
        if (!isset($this->dbinfo[$dbIndex]))
            $this->dbinfo[$dbIndex] = new stdClass();
        if (!isset($this->info[$dbIndex]->$table) || !isset($this->info[$dbIndex]->$table->count)) {
            $q = "SELECT COUNT(*) c FROM " . $this->dbs[$dbIndex] . ".`$table`";
            $res = $this->cons[$dbIndex]->query($q);
            $this->dbinfo[$dbIndex]->$table = (object)[
                'count' => $res->fetch_row()[0]
            ];
            $res = $this->cons[$dbIndex]->query("
                select
                    COLUMN_NAME as 'Field',
                    COLUMN_TYPE as 'Type',
                    IS_NULLABLE as 'Null',
                    COLUMN_KEY as 'Key',
                    COLUMN_DEFAULT as 'Default',
                    EXTRA as 'Extra'
                from
                    INFORMATION_SCHEMA.COLUMNS
                where
                    TABLE_NAME = '$table' and
                    TABLE_SCHEMA = '" . $this->dbs[$dbIndex] . "'            
                order by Field;
            ");
            $ar = $res->fetch_all();
            $this->dbinfo[$dbIndex]->$table->fields = new stdClass();
            foreach ($ar AS $key => $val) {
                $this->dbinfo[$dbIndex]->$table->fields->{$val[0]} = new stdClass();
            }
            $this->dbinfo[$dbIndex]->$table = $this->dbinfo[$dbIndex]->$table;
        } else {
            $this->dbinfo[$dbIndex]->$table = $this->info[$dbIndex]->$table;
        }
        return $this->dbinfo[$dbIndex]->$table;
    }

    /**
     * prints the Dbs in HTML
     * @return void
     */
	public function echoDbs(): void {
        foreach($this->dbs AS $dbIndex => $db) {
            $this->getTables($dbIndex);
            $this->echoTables($dbIndex, $dbIndex == 0 ? 'fieldOur' : 'fieldTheir');
        }
        $this->saveDbinfo();
    }

    /**
     * echos a table
     * @param int $dbIndex
     * @param string $name
     * @param string $fieldName
     * @return void
     */
    protected function echoTable(int $dbIndex, string $name, string $fieldName): void {
        $data = $this->getTableDescription($dbIndex, $name);
        $countstr = " (" . $this->dbinfo[$dbIndex]->$name->count . ")";
        if ($this->dbinfo[$dbIndex]->$name->count < $this->minCountTableNotEmpty && $fieldName == 'fieldTheir') {
            echo "<div class='table tableEmpty' data-db='$dbIndex' data-name='$name'>";
            echo "<div class='tableName'>$name</div>";
        } else {
            echo "<div class='table' data-db='$dbIndex' data-name='$name'>";
            echo "<div class='tableName'>$name" . ($fieldName == 'fieldTheir' ? $countstr : '') . "</div>";
            $this->echoEntrys($data, $fieldName, $name, $dbIndex);
        }
        echo "</div>";
    }

    /**
     * echos all tables
     * @param int $dbIndex
     * @param string $fieldname
     * @return void
     */
    public function echoTables(int $dbIndex, string $fieldname = 'fieldOur'): void {
        echo "<div class='dbWrapper ".($dbIndex==0?'our':'their')."'>";
        echo "<div class='searchWrapper'>";
        echo "<input type='text' class='search' data-name='".$this->dbs[$dbIndex]."' value='search here for database table and fieldnames below'>";
        echo "<input type='button' class='zuklappen' data-name='$fieldname' value='zu' />";
        echo "</div>";
        echo "<div class='db' data-name='".$this->dbs[$dbIndex]."'>";
        foreach ($this->tables[$dbIndex] AS $table) {
            $this->echoTable($dbIndex, $table, $fieldname);
        }
        echo "</div>";
        echo "</div>";
    }

    /**
     * echos the entries of a table
     * @param object $data
     * @param string $fieldName
     * @param string $tablename
     * @param int $dbIndex
     * @return void
     */
    protected function echoEntrys(object &$data, string $fieldName, string $tablename, int $dbIndex): void {
        echo "<div class='group' data-name='$fieldName'>";
        if (!is_object($this->dbinfo[$dbIndex]->$tablename->fields))
            $this->dbinfo[$dbIndex]->$tablename->fields = new stdClass();
        foreach ($data->fields as $entryName => $entry) {
            $fieldNotSet = '';
            // if not cached in dbanalysis.json, analyse the db (takes some time for all the querries)
            if (!isset($this->info[$dbIndex]->$tablename->fields->$entryName)) {
                $q = "SELECT COUNT(*) c FROM " . $this->dbs[$dbIndex] . "." . $tablename . " WHERE `".$entryName."` != ''";
                $res = $this->cons[$dbIndex]->query($q);
                $this->dbinfo[$dbIndex]->$tablename->fields->$entryName = $res->fetch_row()[0];
                $rowcount = $this->dbinfo[$dbIndex]->$tablename->fields->$entryName;
            } else {
                $rowcount = $this->info[$dbIndex]->$tablename->fields->$entryName;
            }
            if($rowcount < $this->minCountFieldNotEmpty) {
                $fieldNotSet = 'fieldEmpty';
            }
            echo "<div class='field $fieldName" . ($fieldName == "fieldTheir" ? " " . $fieldNotSet . " empty" : '') . "' data-name='" . $entryName . "'>" . $entryName .
                ($fieldName == "fieldTheir" ? "<span> ($rowcount)</span>" : "") .
                "<input type='button' class='getRandomValue' data-name='" . $entryName . "' data-tablename='" . $this->dbs[$dbIndex] . "." . $tablename . "' value='Rand' />" .
                "</div>";
        }
        echo "</div>";
    }

    public function echoMatchesNames(): void {
        foreach($this->getMatchesNames() AS $name) {
            echo '<option' . ($name==$this->matchesName ? ' selected' : '') . '>' . $name .'</option>';
        }
    }

    /**
     * saves the db info (back) into the json
     * @return void
     */
    public function saveDbinfo(): void
    {
        $this->info = $this->dbinfo;
        file_put_contents('matches/karat/info/dbanalysis.json', json_encode($this->info, JSON_PRETTY_PRINT));
    }
}

$sm = new sourceMatcher();

?>

<html>
    <head>
        <title>matcher</title>
        <link rel="stylesheet" href="style.css?time=<?= filemtime('style.css') ?>">
        <script src="js/jquery-3.6.3.min.js"></script>
        <script src="js/cookie.js"></script>
        <script src="js/script.js?time=<?= filemtime('js/script.js') ?>"></script>
        <link rel="icon" type="image/png" href="favicon-32x32.png" sizes="32x32">
    </head>
    <body>
        <div class="left block">
            <div class="info">
                <div class="inputGroup">
                    <div class="currentOur">Start Matching</div>
                    <input type="text" class="match" value="by selecting fields" />
                </div>
                <input type="button" class="getRandomValueQuery hidden" value="Rand" title="Gets Random value of Query to the left... 'soon'" />
                <input type="button" class="tojson important" value="Save Matches" title="saves current matches made in the interface to the .json of the selected dropdown value" />
                <input type="button" class="highlight" value="Highlight" title="highlights the fields already matched in green (oder was hald in der css steht)" />
                <input type="button" class="getsql important" value="Save SQLs" title="creates the .sql files of the current folder of .jsons" />
                <input type="button" class="createtablematching" value="+ Tablematching" title="adds a matching four our <-> their table" />
                <input type="button" class="createrel" value="+ Rel" title="adds a relation for matching with joins" />
                <input type="button" class="hideEmpty" value="hideEmpty" title="hides the fields/tables with no (<3 oder so) not empty values" /><br>
                <input type="text" class="mainTable" />
                <input type="text" class="fkPos" value="gets filled with randoms if you click random" />
                <select class="currentMatches important">
                    <?php $sm->echoMatchesNames(); ?>
                </select>
            </div>
            <div class="spacer"></div>
            <?php $sm->echoDbs(); ?>
        </div>
        <div class="right block">
            <span class="important">matches:</span><br>
            <textarea class="result" spellcheck="false"></textarea>
            <span class="important">sqls:</span><br>
            <textarea class="resultSql" spellcheck="false"></textarea>
            <span class="important">tablematchings:</span><br>
            <textarea class="tablematchings" spellcheck="false"><?= $sm->tablematchings ?></textarea>
            <span class="important">rels:</span><br>
            <textarea class="rels" spellcheck="false"><?= $sm->rels ?></textarea>
        </div>
    </body>
</html>