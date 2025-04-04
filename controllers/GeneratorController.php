<?php

/**
 * Generator Controller
 * ຄລາສສຳລັບສ້າງໂຄ້ດ MVC ອັດຕະໂນມັດ
 */
class GeneratorController extends Controller
{
    protected $db;
    protected $appConfig;
    protected $skipFields = ['created_at', 'updated_at', 'deleted_at'];


    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
        $this->appConfig = [
            'author' => getenv('APP_AUTHOR') ?: 'My PHP Framework',
            'version' => getenv('APP_VERSION') ?: '1.0.0'
        ];
    }

    /**
     * ສະແດງໜ້າຫຼັກຂອງ Generator
     */
    public function index()
    {
        // ກວດສອບວ່າມີການເປີດໃຊ້ debug mode ຫຼືບໍ່
        if (getenv('APP_DEBUG') !== 'true') {
            return $this->redirectWithWarning('/', 'Generator ສາມາດໃຊ້ງານໄດ້ສະເພາະໃນໂໝດ debug ເທົ່ານັ້ນ');
        }

        $tables = $this->getDatabaseTables();

        // ລຽງລຳດັບຕາຕະລາງຕາມຕົວອັກສອນ
        sort($tables);

        return $this->view('generator/index', [
            'tables' => $tables,
            'pageTitle' => 'MVC Generator'
        ]);
    }

    /**
     * ສະແດງໜ້າຕັ້ງຄ່າສຳລັບຕາຕະລາງທີ່ເລືອກ
     * 
     * @param string $table ຊື່ຕາຕະລາງ
     * @return mixed
     */
    public function configure($table)
    {
        // ກວດສອບວ່າມີຕາຕະລາງນີ້ແທ້ຫຼືບໍ່
        $tables = $this->getDatabaseTables();
        if (!in_array($table, $tables)) {
            return $this->redirectWithError('generator', "ບໍ່ພົບຕາຕະລາງ: $table");
        }

        $fields = $this->getTableFields($table);
        $relations = $this->getPossibleRelations($table);
        $primaryKey = $this->getPrimaryKey($table);

        // ສ້າງຄ່າຂໍ້ມູນການຕັ້ງຄ່າເລີ່ມຕົ້ນ
        $config = $this->getDefaultConfig($table, $fields, $primaryKey);

        return $this->view('generator/configure', [
            'table' => $table,
            'fields' => $fields,
            'relations' => $relations,
            'primaryKey' => $primaryKey,
            'config' => $config,
            'pageTitle' => "ຕັ້ງຄ່າ Generator - " . ucfirst($table)
        ]);
    }

    /**
     * ສ້າງໄຟລ໌ MVC ຕາມການຕັ້ງຄ່າ
     */
    public function generate()
    {
        if ($this->request->isPost()) {
            $config = $this->request->post();
            $table = $config['table'];

            try {
                // ເລີ່ມ transaction ເພື່ອໃຫ້ແນ່ໃຈວ່າໂຄດຈະເຮັດວຽກສົມບູນພ້ອມກັນ
                $this->db->beginTransaction();

                // ຮັບຂໍ້ມູນຕາຕະລາງ
                $fields = $this->getTableFields($table);
                $relations = $this->getPossibleRelations($table);
                $primaryKey = $this->getPrimaryKey($table);

                // ສ້າງໂຟລເດີຕ່າງໆຖ້າຍັງບໍ່ມີ
                $this->createDirectories($table);

                // ສ້າງ Model
                $modelFile = $this->generateModel($table, $fields, $config, $primaryKey, $relations);

                // ສ້າງ Controller
                $controllerFile = $this->generateController($table, $config, $primaryKey);

                // ສ້າງ Views
                $viewFiles = $this->generateViews($table, $fields, $config, $primaryKey);

                // ສ້າງເສັ້ນທາງ (Route)
                $routeFile = $this->generateRoute($table);

                // ເມື່ອສຳເລັດໝົດແລ້ວ commit transaction
                $this->db->commit();

                // ສົ່ງຂໍ້ມູນກ່ຽວກັບໄຟລ໌ທີ່ສ້າງແລ້ວ
                return $this->view('generator/result', [
                    'table' => $table,
                    'files' => [
                        'model' => $modelFile,
                        'controller' => $controllerFile,
                        'views' => $viewFiles,
                        'route' => $routeFile
                    ],
                    'pageTitle' => 'ສ້າງ MVC ສຳເລັດ'
                ]);
            } catch (Exception $e) {
                // ຖ້າມີຂໍ້ຜິດພາດໃຫ້ຍົກເລີກການສ້າງທັງໝົດ
                $this->db->rollBack();
                return $this->redirectWithError('generator/configure/' . $table, 'ເກີດຂໍ້ຜິດພາດ: ' . $e->getMessage());
            }
        }

        return $this->redirectWithError('generator', 'ຮູບແບບການຮ້ອງຂໍບໍ່ຖືກຕ້ອງ');
    }

    /**
     * ດຶງຂໍ້ມູນຕາຕະລາງທັງໝົດຈາກຖານຂໍ້ມູນ
     * 
     * @return array ລາຍຊື່ຕາຕະລາງທັງໝົດ
     */
    private function getDatabaseTables()
    {
        $sql = "SHOW TABLES";
        $result = $this->db->query($sql);

        $tables = [];
        if (!empty($result)) {
            foreach ($result as $row) {
                $tables[] = reset($row); // ຮັບເອົາຄ່າທຳອິດໃນແຖວ
            }
        }

        return $tables;
    }

    /**
     * ດຶງຂໍ້ມູລ fields ຂອງຕາຕະລາງ
     * 
     * @param string $table ຊື່ຕາຕະລາງ
     * @return array ຂໍ້ມູນ fields ທັງໝົດ
     */
    private function getTableFields($table)
    {
        $sql = "DESCRIBE `$table`";
        $result = $this->db->query($sql);

        $fields = [];
        if (!empty($result)) {
            foreach ($result as $row) {
                $fields[] = [
                    'name' => $row['Field'],
                    'type' => $row['Type'],
                    'null' => $row['Null'],
                    'key' => $row['Key'],
                    'default' => $row['Default'],
                    'extra' => $row['Extra']
                ];
            }
        }

        return $fields;
    }

    /**
     * ຊອກຫາຄວາມສຳພັນທີ່ເປັນໄປໄດ້ຂອງຕາຕະລາງ
     * 
     * @param string $table ຊື່ຕາຕະລາງ
     * @return array ຂໍ້ມູນຄວາມສຳພັນຂອງຕາຕະລາງ
     */
    private function getPossibleRelations($table)
    {
        $sql = "
            SELECT 
                TABLE_NAME,
                COLUMN_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
            FROM
                INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE
                TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = :table
                AND REFERENCED_TABLE_NAME IS NOT NULL
        ";

        $params = [':table' => $table];
        $result = $this->db->query($sql, $params);

        $relations = [];
        if (!empty($result)) {
            foreach ($result as $row) {
                $relations[] = [
                    'table' => $row['TABLE_NAME'],
                    'column' => $row['COLUMN_NAME'],
                    'referenced_table' => $row['REFERENCED_TABLE_NAME'],
                    'referenced_column' => $row['REFERENCED_COLUMN_NAME']
                ];
            }
        }

        return $relations;
    }

    /**
     * ຊອກຫາ primary key ຂອງຕາຕະລາງ
     * 
     * @param string $table ຊື່ຕາຕະລາງ
     * @return string ຊື່ຄອລັມ primary key
     */
    private function getPrimaryKey($table)
    {
        $fields = $this->getTableFields($table);

        foreach ($fields as $field) {
            if ($field['key'] === 'PRI') {
                return $field['name'];
            }
        }

        return 'id'; // ຄ່າເລີ່ມຕົ້ນຖ້າບໍ່ພົບ primary key
    }

    /**
     * ສ້າງການຕັ້ງຄ່າເລີ່ມຕົ້ນສຳລັບ generator
     * 
     * @param string $table ຊື່ຕາຕະລາງ
     * @param array $fields ຂໍ້ມູນ fields ຂອງຕາຕະລາງ
     * @param string $primaryKey ຊື່ຄອລັມ primary key
     * @return array ຂໍ້ມູນການຕັ້ງຄ່າເລີ່ມຕົ້ນ
     */
    private function getDefaultConfig($table, $fields, $primaryKey)
    {
        $config = [
            'validation' => [],
            'include' => [],
            'relations' => []
        ];

        foreach ($fields as $field) {
            // ຂ້າມບາງຄອລັມອັດຕະໂນມັດ
            if (in_array($field['name'], $this->skipFields)) {
                continue;
            }

            // ຕັ້ງຄ່າເລີ່ມຕົ້ນສຳລັບ validation rules
            $rules = [];

            // ບໍ່ຕ້ອງລວມ primary key auto increment
            if ($field['name'] === $primaryKey && strpos($field['extra'], 'auto_increment') !== false) {
                $config['include'][$field['name']] = false;
                continue;
            }

            // ຕັ້ງຄ່າ validation rules ຕາມປະເພດຂໍ້ມູນ
            if ($field['null'] === 'NO') {
                $rules[] = 'required';
            }

            if (strpos($field['type'], 'int') !== false) {
                $rules[] = 'numeric';
            } elseif (strpos($field['type'], 'varchar') !== false) {
                preg_match('/varchar\((\d+)\)/', $field['type'], $matches);
                if (isset($matches[1])) {
                    $rules[] = 'max:' . $matches[1];
                }
            } elseif (strpos($field['type'], 'text') !== false) {
                // ບໍ່ມີການຈຳກັດຄວາມຍາວສຳລັບ text
            } elseif (strpos($field['name'], 'email') !== false) {
                $rules[] = 'email';
            }

            // ຕັ້ງຄ່າ validation rules
            $config['validation'][$field['name']] = implode('|', $rules);

            // ຕັ້ງຄ່າໃຫ້ລວມໄວ້ໃນຟອມ
            $config['include'][$field['name']] = true;
        }

        return $config;
    }

    /**
     * ສ້າງໂຟລເດີຕ່າງໆຖ້າຍັງບໍ່ມີ
     * 
     * @param string $table ຊື່ຕາຕະລາງ
     */
    private function createDirectories($table)
    {
        // ສ້າງໂຟລເດີສຳລັບ views
        $viewPath = VIEWS_PATH . '/' . strtolower($table);
        if (!is_dir($viewPath)) {
            if (!mkdir($viewPath, 0755, true)) {
                throw new Exception("ບໍ່ສາມາດສ້າງໂຟລເດີ: $viewPath");
            }
        }
    }

    /**
     * ສ້າງ Model
     * 
     * @param string $table ຊື່ຕາຕະລາງ
     * @param array $fields ຂໍ້ມູນ fields ຂອງຕາຕະລາງ
     * @param array $config ຂໍ້ມູນການຕັ້ງຄ່າ
     * @param string $primaryKey ຊື່ຄອລັມ primary key
     * @param array $relations ຂໍ້ມູນຄວາມສຳພັນຂອງຕາຕະລາງ
     * @return string ເສັ້ນທາງໄຟລ໌ model ທີ່ສ້າງແລ້ວ
     */
    private function generateModel($table, $fields, $config, $primaryKey, $relations)
    {
        $modelName = $this->getModelName($table);
        $fillable = [];
        $hidden = [];

        // ລວບລວມຄອລັມສຳລັບ fillable ແລະ hidden
        foreach ($fields as $field) {
            // ຂ້າມບາງຄອລັມອັດຕະໂນມັດ
            if (in_array($field['name'], $this->skipFields)) {
                continue;
            }

            // ຂ້າມ primary key
            if ($field['name'] === $primaryKey) {
                continue;
            }

            // ລວມຊື່ຄອລັມເຂົ້າໃນ fillable
            $fillable[] = $field['name'];

            // ເພີ່ມຄອລັມລະຫັດຜ່ານເຂົ້າໃນ hidden
            if (in_array($field['name'], ['password', 'password_hash'])) {
                $hidden[] = $field['name'];
            }
        }

        // ສ້າງໂຄດສຳລັບ Model
        $modelContent = "<?php\n\n";
        $modelContent .= "/**\n";
        $modelContent .= " * " . ucfirst($table) . " Model\n";
        $modelContent .= " * ໂມເດລສຳລັບຈັດການຂໍ້ມູນ " . ucfirst($table) . "\n";
        $modelContent .= " * \n";
        $modelContent .= " * @author {$this->appConfig['author']}\n";
        $modelContent .= " * @version {$this->appConfig['version']}\n";
        $modelContent .= " */\n";
        $modelContent .= "class $modelName extends Model\n{\n";
        $modelContent .= "    // ຊື່ຕາຕະລາງໃນຖານຂໍ້ມູນ\n";
        $modelContent .= "    protected \$table = '$table';\n\n";

        // ເພີ່ມ primary key ຖ້າບໍ່ແມ່ນ 'id'
        if ($primaryKey !== 'id') {
            $modelContent .= "    // ຄອລັມທີ່ເປັນ primary key\n";
            $modelContent .= "    protected \$primaryKey = '$primaryKey';\n\n";
        }

        // ເພີ່ມ fillable
        $modelContent .= "    // ກຳນົດຄອລັມທີ່ສາມາດເພີ່ມຫຼືອັບເດດໄດ້\n";
        $modelContent .= "    protected \$fillable = ['" . implode("', '", $fillable) . "'];\n\n";

        // ເພີ່ມ hidden ຖ້າມີ
        if (!empty($hidden)) {
            $modelContent .= "    // ຄອລັມທີ່ບໍ່ສະແດງໃນຜົນລັບ\n";
            $modelContent .= "    protected \$hidden = ['" . implode("', '", $hidden) . "'];\n\n";
        }

        // ເພີ່ມ validation rules
        $modelContent .= "    // ກົດການກວດສອບຂໍ້ມູນ\n";
        $modelContent .= "    public \$rules = [\n";
        foreach ($fields as $field) {
            if (isset($config['validation'][$field['name']]) && !empty($config['validation'][$field['name']])) {
                $modelContent .= "        '{$field['name']}' => '{$config['validation'][$field['name']]}',\n";
            }
        }
        $modelContent .= "    ];\n\n";

        // ເພີ່ມ constructor
        $modelContent .= "    /**\n";
        $modelContent .= "     * Constructor\n";
        $modelContent .= "     */\n";
        $modelContent .= "    public function __construct()\n";
        $modelContent .= "    {\n";
        $modelContent .= "        parent::__construct();\n";
        $modelContent .= "    }\n\n";

        // ເພີ່ມຟັງຊັນຄົ້ນຫາ
        $searchableFields = [];
        foreach ($fields as $field) {
            if (
                strpos($field['type'], 'varchar') !== false ||
                strpos($field['type'], 'text') !== false ||
                strpos($field['type'], 'char') !== false
            ) {
                $searchableFields[] = $field['name'];
            }
        }

        if (!empty($searchableFields)) {
            $modelContent .= "    /**\n";
            $modelContent .= "     * ຄົ້ນຫາຂໍ້ມູນຕາມຄຳຄົ້ນຫາ\n";
            $modelContent .= "     * \n";
            $modelContent .= "     * @param string \$keyword ຄຳຄົ້ນຫາ\n";
            $modelContent .= "     * @return array ຜົນລັບການຄົ້ນຫາ\n";
            $modelContent .= "     */\n";
            $modelContent .= "    public function search(\$keyword)\n";
            $modelContent .= "    {\n";
            $modelContent .= "        \$sql = \"SELECT * FROM {\$this->table} WHERE \";\n";
            $modelContent .= "        \$conditions = [];\n";
            $modelContent .= "        \$params = [];\n\n";

            foreach ($searchableFields as $i => $field) {
                $modelContent .= "        \$conditions[] = \"{$field} LIKE :p$i\";\n";
                $modelContent .= "        \$params[\":p$i\"] = \"%{\$keyword}%\";\n";
            }

            $modelContent .= "\n        \$sql .= implode(' OR ', \$conditions);\n";
            $modelContent .= "        return \$this->db->query(\$sql, \$params);\n";
            $modelContent .= "    }\n\n";
        }

        // ເພີ່ມຄວາມສຳພັນຖ້າມີ
        if (!empty($relations)) {
            foreach ($relations as $relation) {
                if (
                    isset($config['relations'][$relation['referenced_table']]) &&
                    $config['relations'][$relation['referenced_table']] == 1
                ) {

                    $relatedModel = $this->getModelName($relation['referenced_table']);
                    $relationMethod = $this->getSingularName($relation['referenced_table']);

                    $modelContent .= "    /**\n";
                    $modelContent .= "     * ຄວາມສຳພັນກັບ {$relation['referenced_table']}\n";
                    $modelContent .= "     * \n";
                    $modelContent .= "     * @return array ຂໍ້ມູນ {$relation['referenced_table']} ທີ່ກ່ຽວຂ້ອງ\n";
                    $modelContent .= "     */\n";
                    $modelContent .= "    public function $relationMethod()\n";
                    $modelContent .= "    {\n";
                    $modelContent .= "        \$sql = \"SELECT * FROM {$relation['referenced_table']} WHERE {$relation['referenced_column']} = :id LIMIT 1\";\n";
                    $modelContent .= "        \$result = \$this->db->query(\$sql, [':id' => \$this->{$relation['column']}]);\n";
                    $modelContent .= "        return !empty(\$result) ? \$result[0] : null;\n";
                    $modelContent .= "    }\n\n";
                }
            }
        }

        // ປິດຄລາສ
        $modelContent .= "}\n";

        // ບັນທຶກໄຟລ໌
        $modelPath = MODELS_PATH . "/$modelName.php";
        if (file_put_contents($modelPath, $modelContent) === false) {
            throw new Exception("ບໍ່ສາມາດບັນທຶກໄຟລ໌: $modelPath");
        }

        return $modelPath;
    }


    /**
     * ສ້າງ Views ຕ່າງໆສຳລັບ CRUD
     * 
     * @param string $table ຊື່ຕາຕະລາງ
     * @param array $fields ຂໍ້ມູນ fields ຂອງຕາຕະລາງ
     * @param array $config ຂໍ້ມູນການຕັ້ງຄ່າ
     * @param string $primaryKey ຊື່ຄອລັມ primary key
     * @return array ລາຍການໄຟລ໌ views ທີ່ສ້າງແລ້ວ
     */
    private function generateViews($table, $fields, $config, $primaryKey)
    {
        // ສ້າງໂຟລເດີສຳລັບ views
        $viewPath = VIEWS_PATH . '/' . strtolower($table);
        if (!is_dir($viewPath)) {
            if (!mkdir($viewPath, 0755, true)) {
                throw new Exception("ບໍ່ສາມາດສ້າງໂຟລເດີ: $viewPath");
            }
        }

        $viewFiles = [];

        // ສ້າງ list view (index)
        $viewFiles[] = $this->generateListView($table, $viewPath, $fields, $config, $primaryKey);

        // ສ້າງ create/edit form view
        $viewFiles[] = $this->generateFormView($table, $viewPath, $fields, $config, $primaryKey);

        // ສ້າງ show (detail) view
        $viewFiles[] = $this->generateShowView($table, $viewPath, $fields, $config, $primaryKey);

        return $viewFiles;
    }


    /**
     * ສ້າງ Controller
     * 
     * @param string $table ຊື່ຕາຕະລາງ
     * @param array $config ຂໍ້ມູນການຕັ້ງຄ່າ
     * @param string $primaryKey ຊື່ຄອລັມ primary key
     * @return string ເສັ້ນທາງໄຟລ໌ controller ທີ່ສ້າງແລ້ວ
     */
    private function generateController($table, $config, $primaryKey)
    {
        $controllerName = $this->getControllerName($table);
        $modelName = $this->getModelName($table);
        $modelVar = lcfirst($this->getSingularName($table)) . 'Model';
        $singular = $this->getSingularName($table);

        // ສ້າງໂຄດສຳລັບ Controller
        $controllerContent = "<?php\n\n";
        $controllerContent .= "/**\n";
        $controllerContent .= " * " . ucfirst($table) . " Controller\n";
        $controllerContent .= " * ຄລາສຄວບຄຸມສຳລັບການຈັດການຂໍ້ມູນ " . ucfirst($table) . "\n";
        $controllerContent .= " * \n";
        $controllerContent .= " * @author {$this->appConfig['author']}\n";
        $controllerContent .= " * @version {$this->appConfig['version']}\n";
        $controllerContent .= " */\n";
        $controllerContent .= "class $controllerName extends Controller\n{\n";
        $controllerContent .= "    protected \$$modelVar;\n\n";

        // ເພີ່ມ constructor
        $controllerContent .= "    /**\n";
        $controllerContent .= "     * Constructor\n";
        $controllerContent .= "     */\n";
        $controllerContent .= "    public function __construct()\n";
        $controllerContent .= "    {\n";
        $controllerContent .= "        parent::__construct();\n";
        $controllerContent .= "        \$this->$modelVar = \$this->loadModel('$singular');\n";
        $controllerContent .= "    }\n\n";

        // ເພີ່ມຟັງຊັນສະແດງລາຍການທັງໝົດ (index)
        $controllerContent .= "    /**\n";
        $controllerContent .= "     * ສະແດງລາຍການ " . ucfirst($table) . " ທັງໝົດ\n";
        $controllerContent .= "     */\n";
        $controllerContent .= "    public function index()\n";
        $controllerContent .= "    {\n";
        $controllerContent .= "        // ຮັບພາລາມິເຕີຄົ້ນຫາຖ້າມີ\n";
        $controllerContent .= "        \$search = \$this->request->get('search');\n\n";
        $controllerContent .= "        // ຮັບຂໍ້ມູນຈາກຖານຂໍ້ມູນ\n";
        $controllerContent .= "        if (\$search) {\n";
        $controllerContent .= "            \${$table} = \$this->loadModel('$singular')->search(\$search);\n";
        $controllerContent .= "            \$pageTitle = \"ຄົ້ນຫາ " . ucfirst($table) . ": \$search\";\n";
        $controllerContent .= "        } else {\n";
        $controllerContent .= "            \${$table} = \$this->$modelVar->all();\n";
        $controllerContent .= "            \$pageTitle = \"ລາຍການ " . ucfirst($table) . " ທັງໝົດ\";\n";
        $controllerContent .= "        }\n\n";
        $controllerContent .= "        // ສົ່ງຂໍ້ມູນໄປຍັງ view\n";
        $controllerContent .= "        \$this->view('$table/index', [\n";
        $controllerContent .= "            '$table' => \${$table},\n";
        $controllerContent .= "            'pageTitle' => \$pageTitle,\n";
        $controllerContent .= "            'search' => \$search\n";
        $controllerContent .= "        ]);\n";
        $controllerContent .= "    }\n\n";

        // ເພີ່ມຟັງຊັນສະແດງລາຍລະອຽດ (show)
        $controllerContent .= "    /**\n";
        $controllerContent .= "     * ສະແດງລາຍລະອຽດ " . ucfirst($singular) . "\n";
        $controllerContent .= "     * \n";
        $controllerContent .= "     * @param int \$id ID ຂອງ " . ucfirst($singular) . "\n";
        $controllerContent .= "     */\n";
        $controllerContent .= "    public function show(\$id)\n";
        $controllerContent .= "    {\n";
        $controllerContent .= "        // ຮັບຂໍ້ມູນຕາມ ID\n";
        $controllerContent .= "        \$item = \$this->$modelVar->find(\$id);\n\n";
        $controllerContent .= "        // ຖ້າບໍ່ພົບຂໍ້ມູນ\n";
        $controllerContent .= "        if (!\$item) {\n";
        $controllerContent .= "            \$this->response->setStatusCode(404);\n";
        $controllerContent .= "            return \$this->view('error', [\n";
        $controllerContent .= "                'message' => \"ບໍ່ພົບຂໍ້ມູນ $singular ທີ່ມີ ID: \$id\"\n";
        $controllerContent .= "            ]);\n";
        $controllerContent .= "        }\n\n";
        $controllerContent .= "        // ສົ່ງຂໍ້ມູນໄປຍັງ view\n";
        $controllerContent .= "        \$this->view('$table/show', [\n";
        $controllerContent .= "            'item' => \$item,\n";
        $controllerContent .= "            'pageTitle' => \"ລາຍລະອຽດ " . ucfirst($singular) . "\"\n";
        $controllerContent .= "        ]);\n";
        $controllerContent .= "    }\n\n";

        // ເພີ່ມຟັງຊັນສະແດງຟອມສ້າງຂໍ້ມູນໃໝ່ (create)
        $controllerContent .= "    /**\n";
        $controllerContent .= "     * ສະແດງຟອມສ້າງ " . ucfirst($singular) . " ໃໝ່\n";
        $controllerContent .= "     */\n";
        $controllerContent .= "    public function create()\n";
        $controllerContent .= "    {\n";
        $controllerContent .= "        \$this->view('$table/create', [\n";
        $controllerContent .= "            'pageTitle' => 'ສ້າງ " . ucfirst($singular) . " ໃໝ່'\n";
        $controllerContent .= "        ]);\n";
        $controllerContent .= "    }\n\n";

        // ເພີ່ມຟັງຊັນບັນທຶກຂໍ້ມູນໃໝ່ (store)
        $controllerContent .= "    /**\n";
        $controllerContent .= "     * ບັນທຶກຂໍ້ມູນ " . ucfirst($singular) . " ໃໝ່\n";
        $controllerContent .= "     */\n";
        $controllerContent .= "    public function store()\n";
        $controllerContent .= "    {\n";
        $controllerContent .= "        // ຮັບຂໍ້ມູນຈາກຟອມ\n";
        $controllerContent .= "        \$data = \$this->getFormData();\n\n";
        $controllerContent .= "        // ກວດສອບຄວາມຖືກຕ້ອງຂອງຂໍ້ມູນ\n";
        $controllerContent .= "        \$validation = \$this->validateForm(\$this->$modelVar->rules);\n\n";
        $controllerContent .= "        // ຖ້າຂໍ້ມູນບໍ່ຖືກຕ້ອງ\n";
        $controllerContent .= "        if (!\$validation['isValid']) {\n";
        $controllerContent .= "            return \$this->view('$table/create', [\n";
        $controllerContent .= "                'pageTitle' => 'ສ້າງ " . ucfirst($singular) . " ໃໝ່',\n";
        $controllerContent .= "                'errors' => \$validation['errors'],\n";
        $controllerContent .= "                'formData' => \$validation['data']\n";
        $controllerContent .= "            ]);\n";
        $controllerContent .= "        }\n\n";
        $controllerContent .= "        // ພະຍາຍາມບັນທຶກຂໍ້ມູນ\n";
        $controllerContent .= "        \$result = \$this->$modelVar->create(\$validation['data']);\n\n";
        $controllerContent .= "        // ກວດສອບວ່າບັນທຶກສຳເລັດຫຼືບໍ່\n";
        $controllerContent .= "        if (\$result) {\n";
        $controllerContent .= "            return \$this->redirectWithSuccess('$table', 'ບັນທຶກຂໍ້ມູນສຳເລັດແລ້ວ');\n";
        $controllerContent .= "        } else {\n";
        $controllerContent .= "            return \$this->view('$table/create', [\n";
        $controllerContent .= "                'pageTitle' => 'ສ້າງ " . ucfirst($singular) . " ໃໝ່',\n";
        $controllerContent .= "                'errors' => ['ເກີດຂໍ້ຜິດພາດໃນການບັນທຶກຂໍ້ມູນ'],\n";
        $controllerContent .= "                'formData' => \$validation['data']\n";
        $controllerContent .= "            ]);\n";
        $controllerContent .= "        }\n";
        $controllerContent .= "    }\n\n";

        // ເພີ່ມຟັງຊັນສະແດງຟອມແກ້ໄຂຂໍ້ມູນ (edit)
        $controllerContent .= "    /**\n";
        $controllerContent .= "     * ສະແດງຟອມແກ້ໄຂ " . ucfirst($singular) . "\n";
        $controllerContent .= "     * \n";
        $controllerContent .= "     * @param int \$id ID ຂອງ " . ucfirst($singular) . "\n";
        $controllerContent .= "     */\n";
        $controllerContent .= "    public function edit(\$id)\n";
        $controllerContent .= "    {\n";
        $controllerContent .= "        // ຮັບຂໍ້ມູນຕາມ ID\n";
        $controllerContent .= "        \$item = \$this->$modelVar->find(\$id);\n\n";
        $controllerContent .= "        // ຖ້າບໍ່ພົບຂໍ້ມູນ\n";
        $controllerContent .= "        if (!\$item) {\n";
        $controllerContent .= "            \$this->response->setStatusCode(404);\n";
        $controllerContent .= "            return \$this->view('error', [\n";
        $controllerContent .= "                'message' => \"ບໍ່ພົບຂໍ້ມູນ $singular ທີ່ມີ ID: \$id\"\n";
        $controllerContent .= "            ]);\n";
        $controllerContent .= "        }\n\n";
        $controllerContent .= "        // ສົ່ງຂໍ້ມູນໄປຍັງ view\n";
        $controllerContent .= "        \$this->view('$table/edit', [\n";
        $controllerContent .= "            'item' => \$item,\n";
        $controllerContent .= "            'pageTitle' => \"ແກ້ໄຂ " . ucfirst($singular) . "\"\n";
        $controllerContent .= "        ]);\n";
        $controllerContent .= "    }\n\n";

        // ເພີ່ມຟັງຊັນອັບເດດຂໍ້ມູນ (update)
        $controllerContent .= "    /**\n";
        $controllerContent .= "     * ອັບເດດຂໍ້ມູນ " . ucfirst($singular) . "\n";
        $controllerContent .= "     * \n";
        $controllerContent .= "     * @param int \$id ID ຂອງ " . ucfirst($singular) . "\n";
        $controllerContent .= "     */\n";
        $controllerContent .= "    public function update(\$id)\n";
        $controllerContent .= "    {\n";
        $controllerContent .= "        // ຮັບຂໍ້ມູນຕາມ ID\n";
        $controllerContent .= "        \$item = \$this->$modelVar->find(\$id);\n\n";
        $controllerContent .= "        // ຖ້າບໍ່ພົບຂໍ້ມູນ\n";
        $controllerContent .= "        if (!\$item) {\n";
        $controllerContent .= "            \$this->response->setStatusCode(404);\n";
        $controllerContent .= "            return \$this->view('error', [\n";
        $controllerContent .= "                'message' => \"ບໍ່ພົບຂໍ້ມູນ $singular ທີ່ມີ ID: \$id\"\n";
        $controllerContent .= "            ]);\n";
        $controllerContent .= "        }\n\n";
        $controllerContent .= "        // ຮັບຂໍ້ມູນຈາກຟອມ\n";
        $controllerContent .= "        \$data = \$this->getFormData();\n\n";
        $controllerContent .= "        // ກວດສອບຄວາມຖືກຕ້ອງຂອງຂໍ້ມູນ\n";
        $controllerContent .= "        \$validation = \$this->validateForm(\$this->$modelVar->rules);\n\n";
        $controllerContent .= "        // ຖ້າຂໍ້ມູນບໍ່ຖືກຕ້ອງ\n";
        $controllerContent .= "        if (!\$validation['isValid']) {\n";
        $controllerContent .= "            return \$this->view('$table/edit', [\n";
        $controllerContent .= "                'item' => array_merge(\$item, \$validation['data']),\n";
        $controllerContent .= "                'pageTitle' => \"ແກ້ໄຂ " . ucfirst($singular) . "\",\n";
        $controllerContent .= "                'errors' => \$validation['errors']\n";
        $controllerContent .= "            ]);\n";
        $controllerContent .= "        }\n\n";
        $controllerContent .= "        // ພະຍາຍາມອັບເດດຂໍ້ມູນ\n";
        $controllerContent .= "        \$result = \$this->$modelVar->update(\$id, \$validation['data']);\n\n";
        $controllerContent .= "        // ກວດສອບວ່າອັບເດດສຳເລັດຫຼືບໍ່\n";
        $controllerContent .= "        if (\$result) {\n";
        $controllerContent .= "            return \$this->redirectWithSuccess('$table', 'ອັບເດດຂໍ້ມູນສຳເລັດແລ້ວ');\n";
        $controllerContent .= "        } else {\n";
        $controllerContent .= "            return \$this->view('$table/edit', [\n";
        $controllerContent .= "                'item' => array_merge(\$item, \$validation['data']),\n";
        $controllerContent .= "                'pageTitle' => \"ແກ້ໄຂ " . ucfirst($singular) . "\",\n";
        $controllerContent .= "                'errors' => ['ເກີດຂໍ້ຜິດພາດໃນການອັບເດດຂໍ້ມູນ']\n";
        $controllerContent .= "            ]);\n";
        $controllerContent .= "        }\n";
        $controllerContent .= "    }\n\n";

        // ເພີ່ມຟັງຊັນລຶບຂໍ້ມູນ (delete)
        $controllerContent .= "    /**\n";
        $controllerContent .= "     * ລຶບຂໍ້ມູນ " . ucfirst($singular) . "\n";
        $controllerContent .= "     * \n";
        $controllerContent .= "     * @param int \$id ID ຂອງ " . ucfirst($singular) . "\n";
        $controllerContent .= "     */\n";
        $controllerContent .= "    public function delete(\$id)\n";
        $controllerContent .= "    {\n";
        $controllerContent .= "        // ຮັບຂໍ້ມູນຕາມ ID\n";
        $controllerContent .= "        \$item = \$this->$modelVar->find(\$id);\n\n";
        $controllerContent .= "        // ຖ້າບໍ່ພົບຂໍ້ມູນ\n";
        $controllerContent .= "        if (!\$item) {\n";
        $controllerContent .= "            \$this->response->setStatusCode(404);\n";
        $controllerContent .= "            return \$this->view('error', [\n";
        $controllerContent .= "                'message' => \"ບໍ່ພົບຂໍ້ມູນ $singular ທີ່ມີ ID: \$id\"\n";
        $controllerContent .= "            ]);\n";
        $controllerContent .= "        }\n\n";
        $controllerContent .= "        // ພະຍາຍາມລຶບຂໍ້ມູນ\n";
        $controllerContent .= "        \$result = \$this->$modelVar->delete(\$id);\n\n";
        $controllerContent .= "        // ກວດສອບວ່າລຶບສຳເລັດຫຼືບໍ່\n";
        $controllerContent .= "        if (\$result) {\n";
        $controllerContent .= "            return \$this->redirectWithSuccess('$table', 'ລຶບຂໍ້ມູນສຳເລັດແລ້ວ');\n";
        $controllerContent .= "        } else {\n";
        $controllerContent .= "            return \$this->redirectWithError('$table', 'ເກີດຂໍ້ຜິດພາດໃນການລຶບຂໍ້ມູນ');\n";
        $controllerContent .= "        }\n";
        $controllerContent .= "    }\n";

        // ປິດຄລາສ
        $controllerContent .= "}\n";

        // ບັນທຶກໄຟລ໌
        $controllerPath = CONTROLLERS_PATH . "/$controllerName.php";
        if (file_put_contents($controllerPath, $controllerContent) === false) {
            throw new Exception("ບໍ່ສາມາດບັນທຶກໄຟລ໌: $controllerPath");
        }

        return $controllerPath;
    }

    /**
     * ສ້າງ list view (index)
     * 
     * @param string $table ຊື່ຕາຕະລາງ
     * @param string $viewPath ເສັ້ນທາງໂຟລເດີສຳລັບ views
     * @param array $fields ຂໍ້ມູນ fields ຂອງຕາຕະລາງ
     * @param array $config ຂໍ້ມູນການຕັ້ງຄ່າ
     * @param string $primaryKey ຊື່ຄອລັມ primary key
     * @return string ເສັ້ນທາງໄຟລ໌ view ທີ່ສ້າງແລ້ວ
     */
    private function generateListView($table, $viewPath, $fields, $config, $primaryKey)
    {
        $singular = $this->getSingularName($table);
        $content = "<div class=\"row mb-4\">\n";
        $content .= "    <div class=\"col-md-6\">\n";
        $content .= "        <h1><?= \$pageTitle ?></h1>\n";
        $content .= "    </div>\n";
        $content .= "    <div class=\"col-md-6 text-end\">\n";
        $content .= "        <a href=\"<?= getenv('APP_URL') ?>/$table/create\" class=\"btn btn-primary\">\n";
        $content .= "            <i class=\"bi bi-plus-circle\"></i> ເພີ່ມ" . ucfirst($singular) . "ໃໝ່\n";
        $content .= "        </a>\n";
        $content .= "    </div>\n";
        $content .= "</div>\n\n";

        // ສ້າງຟອມຄົ້ນຫາ
        $content .= "<!-- ຟອມຄົ້ນຫາ -->\n";
        $content .= "<div class=\"row mb-4\">\n";
        $content .= "    <div class=\"col-md-12\">\n";
        $content .= "        <form action=\"<?= getenv('APP_URL') ?>/$table\" method=\"get\" class=\"d-flex\">\n";
        $content .= "            <input type=\"text\" name=\"search\" class=\"form-control me-2\"\n";
        $content .= "                placeholder=\"ຄົ້ນຫາ...\"\n";
        $content .= "                value=\"<?= isset(\$search) ? \$search : '' ?>\">\n";
        $content .= "            <button type=\"submit\" class=\"btn btn-outline-primary\">ຄົ້ນຫາ</button>\n";
        $content .= "            <?php if (isset(\$search) && !empty(\$search)): ?>\n";
        $content .= "                <a href=\"<?= getenv('APP_URL') ?>/$table\" class=\"btn btn-outline-secondary ms-2\">ລ້າງ</a>\n";
        $content .= "            <?php endif; ?>\n";
        $content .= "        </form>\n";
        $content .= "    </div>\n";
        $content .= "</div>\n\n";

        // ສ້າງຕາຕະລາງສະແດງຂໍ້ມູນ
        $content .= "<?php if (empty(\$$table)): ?>\n";
        $content .= "    <div class=\"alert alert-info\">\n";
        $content .= "        <?= isset(\$search) && !empty(\$search) ? 'ບໍ່ພົບຜົນການຄົ້ນຫາ.' : 'ຍັງບໍ່ມີຂໍ້ມູນໃນລະບົບ.' ?>\n";
        $content .= "    </div>\n";
        $content .= "<?php else: ?>\n";
        $content .= "    <!-- ຕາຕະລາງຂໍ້ມູນ -->\n";
        $content .= "    <div class=\"table-responsive\">\n";
        $content .= "        <table class=\"table table-striped table-hover\">\n";
        $content .= "            <thead class=\"table-light\">\n";
        $content .= "                <tr>\n";

        // ສ້າງຫົວຕາຕະລາງ
        foreach ($fields as $field) {
            // ຂ້າມບາງຄອລັມທີ່ບໍ່ຕ້ອງການສະແດງ
            if (in_array($field['name'], $this->skipFields)) {
                continue;
            }

            // ສະແດງສະເພາະຄອລັມທີ່ເລືອກລວມ
            if (isset($config['include'][$field['name']]) && $config['include'][$field['name']] == 1) {
                $content .= "                    <th>" . ucfirst($field['name']) . "</th>\n";
            }
        }

        $content .= "                    <th>ການດຳເນີນການ</th>\n";
        $content .= "                </tr>\n";
        $content .= "            </thead>\n";
        $content .= "            <tbody>\n";
        $content .= "                <?php foreach (\$$table as \$item): ?>\n";
        $content .= "                    <tr>\n";

        // ສ້າງແຖວຂໍ້ມູນ
        foreach ($fields as $field) {
            // ຂ້າມບາງຄອລັມທີ່ບໍ່ຕ້ອງການສະແດງ
            if (in_array($field['name'], $this->skipFields)) {
                continue;
            }

            // ສະແດງສະເພາະຄອລັມທີ່ເລືອກລວມ
            if (isset($config['include'][$field['name']]) && $config['include'][$field['name']] == 1) {
                // ຮູບແບບພິເສດສຳລັບບາງຄອລັມ
                if ($field['name'] == 'status') {
                    $content .= "                        <td>\n";
                    $content .= "                            <span class=\"badge bg-<?= \$item['{$field['name']}'] === 'active' ? 'success' : 'secondary' ?>\">\n";
                    $content .= "                                <?= \$item['{$field['name']}'] === 'active' ? 'ໃຊ້ງານ' : 'ປິດໃຊ້ງານ' ?>\n";
                    $content .= "                            </span>\n";
                    $content .= "                        </td>\n";
                } else if ($field['name'] == 'role') {
                    $content .= "                        <td>\n";
                    $content .= "                            <span class=\"badge bg-<?= \$item['{$field['name']}'] === 'admin' ? 'danger' : 'info' ?>\">\n";
                    $content .= "                                <?= \$item['{$field['name']}'] === 'admin' ? 'ຜູ້ດູແລລະບົບ' : 'ຜູ້ໃຊ້ທົ່ວໄປ' ?>\n";
                    $content .= "                            </span>\n";
                    $content .= "                        </td>\n";
                } else {
                    $content .= "                        <td><?= \$item['{$field['name']}'] ?></td>\n";
                }
            }
        }

        // ສ້າງປຸ່ມການດຳເນີນການ
        $content .= "                        <td>\n";
        $content .= "                            <div class=\"btn-group btn-group-sm\">\n";
        $content .= "                                <a href=\"<?= getenv('APP_URL') ?>/$table/show/<?= \$item['$primaryKey'] ?>\"\n";
        $content .= "                                    class=\"btn btn-info\">ເບິ່ງ</a>\n";
        $content .= "                                <a href=\"<?= getenv('APP_URL') ?>/$table/edit/<?= \$item['$primaryKey'] ?>\"\n";
        $content .= "                                    class=\"btn btn-warning\">ແກ້ໄຂ</a>\n";
        $content .= "                                <a href=\"<?= getenv('APP_URL') ?>/$table/delete/<?= \$item['$primaryKey'] ?>\"\n";
        $content .= "                                    class=\"btn btn-danger\"\n";
        $content .= "                                    onclick=\"return confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການລຶບລາຍການນີ້?')\">\n";
        $content .= "                                    ລຶບ\n";
        $content .= "                                </a>\n";
        $content .= "                            </div>\n";
        $content .= "                        </td>\n";
        $content .= "                    </tr>\n";
        $content .= "                <?php endforeach; ?>\n";
        $content .= "            </tbody>\n";
        $content .= "        </table>\n";
        $content .= "    </div>\n";
        $content .= "<?php endif; ?>";

        // ບັນທຶກໄຟລ໌
        $indexPath = "$viewPath/index.php";
        if (file_put_contents($indexPath, $content) === false) {
            throw new Exception("ບໍ່ສາມາດບັນທຶກໄຟລ໌: $indexPath");
        }

        return $indexPath;
    }

    /**
     * ສ້າງ form view (create/edit)
     * 
     * @param string $table ຊື່ຕາຕະລາງ
     * @param string $viewPath ເສັ້ນທາງໂຟລເດີສຳລັບ views
     * @param array $fields ຂໍ້ມູນ fields ຂອງຕາຕະລາງ
     * @param array $config ຂໍ້ມູນການຕັ້ງຄ່າ
     * @param string $primaryKey ຊື່ຄອລັມ primary key
     * @return array ເສັ້ນທາງໄຟລ໌ views ທີ່ສ້າງແລ້ວ
     */
    private function generateFormView($table, $viewPath, $fields, $config, $primaryKey)
    {
        $singular = $this->getSingularName($table);
        $viewFiles = [];

        // ສ້າງຟອມສຳລັບສ້າງຂໍ້ມູນໃໝ່
        $createContent = "<div class=\"row mb-4\">\n";
        $createContent .= "    <div class=\"col-md-12\">\n";
        $createContent .= "        <h1><?= \$pageTitle ?></h1>\n";
        $createContent .= "        <nav aria-label=\"breadcrumb\">\n";
        $createContent .= "            <ol class=\"breadcrumb\">\n";
        $createContent .= "                <li class=\"breadcrumb-item\"><a href=\"<?= getenv('APP_URL') ?>\">ໜ້າຫຼັກ</a></li>\n";
        $createContent .= "                <li class=\"breadcrumb-item\"><a href=\"<?= getenv('APP_URL') ?>/$table\">$table</a></li>\n";
        $createContent .= "                <li class=\"breadcrumb-item active\">ສ້າງ" . ucfirst($singular) . "ໃໝ່</li>\n";
        $createContent .= "            </ol>\n";
        $createContent .= "        </nav>\n";
        $createContent .= "    </div>\n";
        $createContent .= "</div>\n\n";

        $createContent .= "<div class=\"row\">\n";
        $createContent .= "    <div class=\"col-md-8\">\n";
        $createContent .= "        <div class=\"card\">\n";
        $createContent .= "            <div class=\"card-header bg-primary text-white\">\n";
        $createContent .= "                <h5 class=\"card-title mb-0\">ຟອມສ້າງ" . ucfirst($singular) . "ໃໝ່</h5>\n";
        $createContent .= "            </div>\n";
        $createContent .= "            <div class=\"card-body\">\n";
        $createContent .= "                <?php if (isset(\$errors) && !empty(\$errors)): ?>\n";
        $createContent .= "                    <div class=\"alert alert-danger\">\n";
        $createContent .= "                        <ul class=\"mb-0\">\n";
        $createContent .= "                            <?php foreach (\$errors as \$field => \$fieldErrors): ?>\n";
        $createContent .= "                                <?php foreach (\$fieldErrors as \$error): ?>\n";
        $createContent .= "                                    <li><?= \$error ?></li>\n";
        $createContent .= "                                <?php endforeach; ?>\n";
        $createContent .= "                            <?php endforeach; ?>\n";
        $createContent .= "                        </ul>\n";
        $createContent .= "                    </div>\n";
        $createContent .= "                <?php endif; ?>\n\n";
        $createContent .= "                <form action=\"<?= getenv('APP_URL') ?>/$table/store\" method=\"post\">\n";

        // ສ້າງຟິວຟອມສຳລັບແຕ່ລະຄອລັມ
        foreach ($fields as $field) {
            // ຂ້າມບາງຄອລັມທີ່ບໍ່ຕ້ອງການສະແດງ
            if (in_array($field['name'], $this->skipFields) || $field['name'] == $primaryKey) {
                continue;
            }

            // ສະແດງສະເພາະຄອລັມທີ່ເລືອກລວມ
            if (isset($config['include'][$field['name']]) && $config['include'][$field['name']] == 1) {
                // ກຳນົດປະເພດຂອງ input
                $inputType = 'text';
                if (strpos($field['type'], 'int') !== false) {
                    $inputType = 'number';
                } else if (strpos($field['name'], 'email') !== false) {
                    $inputType = 'email';
                } else if (strpos($field['name'], 'password') !== false) {
                    $inputType = 'password';
                } else if (strpos($field['type'], 'text') !== false) {
                    $inputType = 'textarea';
                } else if (strpos($field['type'], 'date') !== false) {
                    $inputType = 'date';
                } else if ($field['name'] === 'status') {
                    $inputType = 'select-status';
                } else if ($field['name'] === 'role') {
                    $inputType = 'select-role';
                }

                $createContent .= "                    <div class=\"mb-3\">\n";
                $createContent .= "                        <label for=\"{$field['name']}\" class=\"form-label\">" . ucfirst($field['name']) . " ";

                // ເພີ່ມເຄື່ອງໝາຍບັງຄັບຖ້າຈຳເປັນ
                if ($field['null'] === 'NO') {
                    $createContent .= "<span class=\"text-danger\">*</span>";
                }

                $createContent .= "</label>\n";

                // ສ້າງ input ຕາມປະເພດ
                if ($inputType === 'textarea') {
                    $createContent .= "                        <textarea class=\"form-control\" id=\"{$field['name']}\" name=\"{$field['name']}\" ";
                    if ($field['null'] === 'NO') {
                        $createContent .= "required";
                    }
                    $createContent .= "><?= isset(\$formData['{$field['name']}']) ? \$formData['{$field['name']}'] : '' ?></textarea>\n";
                } else if ($inputType === 'select-status') {
                    $createContent .= "                        <select class=\"form-select\" id=\"{$field['name']}\" name=\"{$field['name']}\">\n";
                    $createContent .= "                            <option value=\"active\" <?= isset(\$formData['{$field['name']}']) && \$formData['{$field['name']}'] === 'active' ? 'selected' : '' ?>>ໃຊ້ງານ</option>\n";
                    $createContent .= "                            <option value=\"inactive\" <?= isset(\$formData['{$field['name']}']) && \$formData['{$field['name']}'] === 'inactive' ? 'selected' : '' ?>>ປິດໃຊ້ງານ</option>\n";
                    $createContent .= "                        </select>\n";
                } else if ($inputType === 'select-role') {
                    $createContent .= "                        <select class=\"form-select\" id=\"{$field['name']}\" name=\"{$field['name']}\">\n";
                    $createContent .= "                            <option value=\"user\" <?= isset(\$formData['{$field['name']}']) && \$formData['{$field['name']}'] === 'user' ? 'selected' : '' ?>>ຜູ້ໃຊ້ທົ່ວໄປ</option>\n";
                    $createContent .= "                            <option value=\"admin\" <?= isset(\$formData['{$field['name']}']) && \$formData['{$field['name']}'] === 'admin' ? 'selected' : '' ?>>ຜູ້ດູແລລະບົບ</option>\n";
                    $createContent .= "                        </select>\n";
                } else {
                    $createContent .= "                        <input type=\"$inputType\" class=\"form-control\" id=\"{$field['name']}\" name=\"{$field['name']}\" ";
                    if ($field['null'] === 'NO') {
                        $createContent .= "required ";
                    }
                    $createContent .= "value=\"<?= isset(\$formData['{$field['name']}']) ? \$formData['{$field['name']}'] : '' ?>\">\n";
                }

                $createContent .= "                    </div>\n\n";
            }
        }

        $createContent .= "                    <div class=\"mt-4\">\n";
        $createContent .= "                        <button type=\"submit\" class=\"btn btn-primary\">ບັນທຶກ</button>\n";
        $createContent .= "                        <a href=\"<?= getenv('APP_URL') ?>/$table\" class=\"btn btn-secondary\">ຍົກເລີກ</a>\n";
        $createContent .= "                    </div>\n";
        $createContent .= "                </form>\n";
        $createContent .= "            </div>\n";
        $createContent .= "        </div>\n";
        $createContent .= "    </div>\n\n";

        // ເພີ່ມຄຳອະທິບາຍຊ່ວຍເຫຼືອໃນການປ້ອນຂໍ້ມູນ
        $createContent .= "    <div class=\"col-md-4\">\n";
        $createContent .= "        <div class=\"card\">\n";
        $createContent .= "            <div class=\"card-header bg-info text-white\">\n";
        $createContent .= "                <h5 class=\"card-title mb-0\">ຄຳແນະນຳ</h5>\n";
        $createContent .= "            </div>\n";
        $createContent .= "            <div class=\"card-body\">\n";
        $createContent .= "                <p>ຂໍ້ມູນທີ່ຕ້ອງປ້ອນ:</p>\n";
        $createContent .= "                <ul>\n";

        foreach ($fields as $field) {
            // ຂ້າມບາງຄອລັມທີ່ບໍ່ຕ້ອງການສະແດງ
            if (in_array($field['name'], $this->skipFields) || $field['name'] == $primaryKey) {
                continue;
            }

            // ສະແດງສະເພາະຄອລັມທີ່ເລືອກລວມ
            if (isset($config['include'][$field['name']]) && $config['include'][$field['name']] == 1) {
                $validation = isset($config['validation'][$field['name']]) ? $config['validation'][$field['name']] : '';
                $hint = '';

                if (strpos($validation, 'required') !== false) {
                    $hint .= 'ຈຳເປັນຕ້ອງປ້ອນ';
                }

                if (strpos($validation, 'min:') !== false) {
                    preg_match('/min:(\d+)/', $validation, $matches);
                    if (isset($matches[1])) {
                        $hint .= ($hint ? ', ' : '') . 'ຢ່າງໜ້ອຍ ' . $matches[1] . ' ຕົວອັກສອນ';
                    }
                }

                if (strpos($validation, 'max:') !== false) {
                    preg_match('/max:(\d+)/', $validation, $matches);
                    if (isset($matches[1])) {
                        $hint .= ($hint ? ', ' : '') . 'ບໍ່ເກີນ ' . $matches[1] . ' ຕົວອັກສອນ';
                    }
                }

                if (strpos($validation, 'email') !== false) {
                    $hint .= ($hint ? ', ' : '') . 'ຕ້ອງເປັນຮູບແບບອີເມລທີ່ຖືກຕ້ອງ';
                }

                $createContent .= "                    <li><strong>" . ucfirst($field['name']) . ":</strong> " . ($hint ? $hint : 'ຂໍ້ມູນທົ່ວໄປ') . "</li>\n";
            }
        }

        $createContent .= "                </ul>\n";
        $createContent .= "            </div>\n";
        $createContent .= "        </div>\n";
        $createContent .= "    </div>\n";
        $createContent .= "</div>";

        // ບັນທຶກໄຟລ໌ create
        $createPath = "$viewPath/create.php";
        if (file_put_contents($createPath, $createContent) === false) {
            throw new Exception("ບໍ່ສາມາດບັນທຶກໄຟລ໌: $createPath");
        }
        $viewFiles[] = $createPath;

        // ສ້າງຟອມສຳລັບແກ້ໄຂຂໍ້ມູນ
        $editContent = "<div class=\"row mb-4\">\n";
        $editContent .= "    <div class=\"col-md-12\">\n";
        $editContent .= "        <h1><?= \$pageTitle ?></h1>\n";
        $editContent .= "        <nav aria-label=\"breadcrumb\">\n";
        $editContent .= "            <ol class=\"breadcrumb\">\n";
        $editContent .= "                <li class=\"breadcrumb-item\"><a href=\"<?= getenv('APP_URL') ?>\">ໜ້າຫຼັກ</a></li>\n";
        $editContent .= "                <li class=\"breadcrumb-item\"><a href=\"<?= getenv('APP_URL') ?>/$table\">$table</a></li>\n";
        $editContent .= "                <li class=\"breadcrumb-item active\">ແກ້ໄຂ: <?= \$item['$primaryKey'] ?></li>\n";
        $editContent .= "            </ol>\n";
        $editContent .= "        </nav>\n";
        $editContent .= "    </div>\n";
        $editContent .= "</div>\n\n";

        $editContent .= "<div class=\"row\">\n";
        $editContent .= "    <div class=\"col-md-8\">\n";
        $editContent .= "        <div class=\"card\">\n";
        $editContent .= "            <div class=\"card-header bg-warning\">\n";
        $editContent .= "                <h5 class=\"card-title mb-0\">ແກ້ໄຂຂໍ້ມູນ " . ucfirst($singular) . "</h5>\n";
        $editContent .= "            </div>\n";
        $editContent .= "            <div class=\"card-body\">\n";
        $editContent .= "                <?php if (isset(\$errors) && !empty(\$errors)): ?>\n";
        $editContent .= "                    <div class=\"alert alert-danger\">\n";
        $editContent .= "                        <ul class=\"mb-0\">\n";
        $editContent .= "                            <?php foreach (\$errors as \$field => \$fieldErrors): ?>\n";
        $editContent .= "                                <?php foreach (\$fieldErrors as \$error): ?>\n";
        $editContent .= "                                    <li><?= \$error ?></li>\n";
        $editContent .= "                                <?php endforeach; ?>\n";
        $editContent .= "                            <?php endforeach; ?>\n";
        $editContent .= "                        </ul>\n";
        $editContent .= "                    </div>\n";
        $editContent .= "                <?php endif; ?>\n\n";
        $editContent .= "                <form action=\"<?= getenv('APP_URL') ?>/$table/update/<?= \$item['$primaryKey'] ?>\" method=\"post\">\n";

        // ສ້າງຟິວຟອມແກ້ໄຂສຳລັບແຕ່ລະຄອລັມ
        foreach ($fields as $field) {
            // ຂ້າມບາງຄອລັມທີ່ບໍ່ຕ້ອງການສະແດງ
            if (in_array($field['name'], $this->skipFields)) {
                continue;
            }

            // ກວດສອບວ່າແມ່ນ primary key ຫຼືບໍ່
            if ($field['name'] == $primaryKey) {
                // ຖ້າເປັນ primary key ໃຫ້ສະແດງແຕ່ບໍ່ໃຫ້ແກ້ໄຂໄດ້
                $editContent .= "                    <div class=\"mb-3\">\n";
                $editContent .= "                        <label for=\"{$field['name']}\" class=\"form-label\">" . ucfirst($field['name']) . "</label>\n";
                $editContent .= "                        <input type=\"text\" class=\"form-control\" id=\"{$field['name']}\" value=\"<?= \$item['{$field['name']}'] ?>\" readonly disabled>\n";
                $editContent .= "                    </div>\n\n";
                continue;
            }

            // ສະແດງສະເພາະຄອລັມທີ່ເລືອກລວມ
            if (isset($config['include'][$field['name']]) && $config['include'][$field['name']] == 1) {
                // ກຳນົດປະເພດຂອງ input
                $inputType = 'text';
                if (strpos($field['type'], 'int') !== false) {
                    $inputType = 'number';
                } else if (strpos($field['name'], 'email') !== false) {
                    $inputType = 'email';
                } else if (strpos($field['name'], 'password') !== false) {
                    $inputType = 'password';
                } else if (strpos($field['type'], 'text') !== false) {
                    $inputType = 'textarea';
                } else if (strpos($field['type'], 'date') !== false) {
                    $inputType = 'date';
                } else if ($field['name'] === 'status') {
                    $inputType = 'select-status';
                } else if ($field['name'] === 'role') {
                    $inputType = 'select-role';
                }

                $editContent .= "                    <div class=\"mb-3\">\n";
                $editContent .= "                        <label for=\"{$field['name']}\" class=\"form-label\">" . ucfirst($field['name']) . " ";

                // ເພີ່ມເຄື່ອງໝາຍບັງຄັບຖ້າຈຳເປັນ
                if ($field['null'] === 'NO') {
                    $editContent .= "<span class=\"text-danger\">*</span>";
                }

                $editContent .= "</label>\n";

                // ສ້າງ input ຕາມປະເພດ
                if ($inputType === 'textarea') {
                    $editContent .= "                        <textarea class=\"form-control\" id=\"{$field['name']}\" name=\"{$field['name']}\" ";
                    if ($field['null'] === 'NO') {
                        $editContent .= "required";
                    }
                    $editContent .= "><?= \$item['{$field['name']}'] ?></textarea>\n";
                } else if ($inputType === 'select-status') {
                    $editContent .= "                        <select class=\"form-select\" id=\"{$field['name']}\" name=\"{$field['name']}\">\n";
                    $editContent .= "                            <option value=\"active\" <?= \$item['{$field['name']}'] === 'active' ? 'selected' : '' ?>>ໃຊ້ງານ</option>\n";
                    $editContent .= "                            <option value=\"inactive\" <?= \$item['{$field['name']}'] === 'inactive' ? 'selected' : '' ?>>ປິດໃຊ້ງານ</option>\n";
                    $editContent .= "                        </select>\n";
                } else if ($inputType === 'select-role') {
                    $editContent .= "                        <select class=\"form-select\" id=\"{$field['name']}\" name=\"{$field['name']}\">\n";
                    $editContent .= "                            <option value=\"user\" <?= \$item['{$field['name']}'] === 'user' ? 'selected' : '' ?>>ຜູ້ໃຊ້ທົ່ວໄປ</option>\n";
                    $editContent .= "                            <option value=\"admin\" <?= \$item['{$field['name']}'] === 'admin' ? 'selected' : '' ?>>ຜູ້ດູແລລະບົບ</option>\n";
                    $editContent .= "                        </select>\n";
                } else if ($inputType === 'password') {
                    $editContent .= "                        <input type=\"$inputType\" class=\"form-control\" id=\"{$field['name']}\" name=\"{$field['name']}\">\n";
                    $editContent .= "                        <small class=\"text-muted\">ປະວ່າງໄວ້ຖ້າບໍ່ຕ້ອງການປ່ຽນ</small>\n";
                } else {
                    $editContent .= "                        <input type=\"$inputType\" class=\"form-control\" id=\"{$field['name']}\" name=\"{$field['name']}\" ";
                    if ($field['null'] === 'NO') {
                        $editContent .= "required ";
                    }
                    $editContent .= "value=\"<?= \$item['{$field['name']}'] ?>\">\n";
                }

                $editContent .= "                    </div>\n\n";
            }
        }

        $editContent .= "                    <div class=\"mt-4\">\n";
        $editContent .= "                        <button type=\"submit\" class=\"btn btn-primary\">ບັນທຶກການແກ້ໄຂ</button>\n";
        $editContent .= "                        <a href=\"<?= getenv('APP_URL') ?>/$table/show/<?= \$item['$primaryKey'] ?>\" class=\"btn btn-secondary\">ຍົກເລີກ</a>\n";
        $editContent .= "                    </div>\n";
        $editContent .= "                </form>\n";
        $editContent .= "            </div>\n";
        $editContent .= "        </div>\n";
        $editContent .= "    </div>\n\n";

        // ເພີ່ມຄຳອະທິບາຍຊ່ວຍເຫຼືອໃນການປ້ອນຂໍ້ມູນ
        $editContent .= "    <div class=\"col-md-4\">\n";
        $editContent .= "        <div class=\"card\">\n";
        $editContent .= "            <div class=\"card-header bg-info text-white\">\n";
        $editContent .= "                <h5 class=\"card-title mb-0\">ຄຳແນະນຳ</h5>\n";
        $editContent .= "            </div>\n";
        $editContent .= "            <div class=\"card-body\">\n";
        $editContent .= "                <p>ຂໍ້ມູນທີ່ສາມາດແກ້ໄຂໄດ້:</p>\n";
        $editContent .= "                <ul>\n";

        foreach ($fields as $field) {
            // ຂ້າມບາງຄອລັມທີ່ບໍ່ຕ້ອງການສະແດງ
            if (in_array($field['name'], $this->skipFields) || $field['name'] == $primaryKey) {
                continue;
            }

            // ສະແດງສະເພາະຄອລັມທີ່ເລືອກລວມ
            if (isset($config['include'][$field['name']]) && $config['include'][$field['name']] == 1) {
                $validation = isset($config['validation'][$field['name']]) ? $config['validation'][$field['name']] : '';
                $hint = '';

                if (strpos($validation, 'required') !== false) {
                    $hint .= 'ຈຳເປັນຕ້ອງປ້ອນ';
                }

                if (strpos($validation, 'min:') !== false) {
                    preg_match('/min:(\d+)/', $validation, $matches);
                    if (isset($matches[1])) {
                        $hint .= ($hint ? ', ' : '') . 'ຢ່າງໜ້ອຍ ' . $matches[1] . ' ຕົວອັກສອນ';
                    }
                }

                if (strpos($validation, 'max:') !== false) {
                    preg_match('/max:(\d+)/', $validation, $matches);
                    if (isset($matches[1])) {
                        $hint .= ($hint ? ', ' : '') . 'ບໍ່ເກີນ ' . $matches[1] . ' ຕົວອັກສອນ';
                    }
                }

                if (strpos($validation, 'email') !== false) {
                    $hint .= ($hint ? ', ' : '') . 'ຕ້ອງເປັນຮູບແບບອີເມລທີ່ຖືກຕ້ອງ';
                }

                if (strpos($field['name'], 'password') !== false) {
                    $hint .= ($hint ? ', ' : '') . 'ປະວ່າງໄວ້ຖ້າບໍ່ຕ້ອງການປ່ຽນ';
                }

                $editContent .= "                    <li><strong>" . ucfirst($field['name']) . ":</strong> " . ($hint ? $hint : 'ຂໍ້ມູນທົ່ວໄປ') . "</li>\n";
            }
        }

        $editContent .= "                </ul>\n";
        $editContent .= "            </div>\n";
        $editContent .= "        </div>\n";
        $editContent .= "    </div>\n";
        $editContent .= "</div>";

        // ບັນທຶກໄຟລ໌ edit
        $editPath = "$viewPath/edit.php";
        if (file_put_contents($editPath, $editContent) === false) {
            throw new Exception("ບໍ່ສາມາດບັນທຶກໄຟລ໌: $editPath");
        }
        $viewFiles[] = $editPath;

        return $viewFiles;
    }

    /**
     * ສ້າງ detail view (show)
     * 
     * @param string $table ຊື່ຕາຕະລາງ
     * @param string $viewPath ເສັ້ນທາງໂຟລເດີສຳລັບ views
     * @param array $fields ຂໍ້ມູນ fields ຂອງຕາຕະລາງ
     * @param array $config ຂໍ້ມູນການຕັ້ງຄ່າ
     * @param string $primaryKey ຊື່ຄອລັມ primary key
     * @return string ເສັ້ນທາງໄຟລ໌ view ທີ່ສ້າງແລ້ວ
     */
    private function generateShowView($table, $viewPath, $fields, $config, $primaryKey)
    {
        $singular = $this->getSingularName($table);
        $content = "<div class=\"row mb-4\">\n";
        $content .= "    <div class=\"col-md-12\">\n";
        $content .= "        <h1><?= \$pageTitle ?></h1>\n";
        $content .= "        <nav aria-label=\"breadcrumb\">\n";
        $content .= "            <ol class=\"breadcrumb\">\n";
        $content .= "                <li class=\"breadcrumb-item\"><a href=\"<?= getenv('APP_URL') ?>\">ໜ້າຫຼັກ</a></li>\n";
        $content .= "                <li class=\"breadcrumb-item\"><a href=\"<?= getenv('APP_URL') ?>/$table\">$table</a></li>\n";
        $content .= "                <li class=\"breadcrumb-item active\">ລາຍລະອຽດ</li>\n";
        $content .= "            </ol>\n";
        $content .= "        </nav>\n";
        $content .= "    </div>\n";
        $content .= "</div>\n\n";

        $content .= "<div class=\"row\">\n";
        $content .= "    <div class=\"col-md-8\">\n";
        $content .= "        <div class=\"card\">\n";
        $content .= "            <div class=\"card-header bg-primary text-white\">\n";
        $content .= "                <h5 class=\"card-title mb-0\">ຂໍ້ມູນ " . ucfirst($singular) . "</h5>\n";
        $content .= "            </div>\n";
        $content .= "            <div class=\"card-body\">\n";
        $content .= "                <table class=\"table table-bordered\">\n";

        // ສ້າງລາຍການຂໍ້ມູນລາຍລະອຽດ
        foreach ($fields as $field) {
            // ຂ້າມບາງຄອລັມທີ່ບໍ່ຕ້ອງການສະແດງ
            if (in_array($field['name'], $this->skipFields)) {
                continue;
            }

            // ສະແດງຂໍ້ມູນ
            $content .= "                    <tr>\n";
            $content .= "                        <th style=\"width: 200px;\">" . ucfirst($field['name']) . "</th>\n";

            // ຮູບແບບພິເສດສຳລັບບາງຄອລັມ
            if ($field['name'] === 'status') {
                $content .= "                        <td>\n";
                $content .= "                            <span class=\"badge bg-<?= \$item['{$field['name']}'] === 'active' ? 'success' : 'secondary' ?>\">\n";
                $content .= "                                <?= \$item['{$field['name']}'] === 'active' ? 'ໃຊ້ງານ' : 'ປິດໃຊ້ງານ' ?>\n";
                $content .= "                            </span>\n";
                $content .= "                        </td>\n";
            } else if ($field['name'] === 'role') {
                $content .= "                        <td>\n";
                $content .= "                            <span class=\"badge bg-<?= \$item['{$field['name']}'] === 'admin' ? 'danger' : 'info' ?>\">\n";
                $content .= "                                <?= \$item['{$field['name']}'] === 'admin' ? 'ຜູ້ດູແລລະບົບ' : 'ຜູ້ໃຊ້ທົ່ວໄປ' ?>\n";
                $content .= "                            </span>\n";
                $content .= "                        </td>\n";
            } else if (strpos($field['name'], 'password') !== false) {
                $content .= "                        <td>********</td>\n";
            } else if (strpos($field['type'], 'datetime') !== false || $field['name'] === 'created_at' || $field['name'] === 'updated_at') {
                $content .= "                        <td><?= isset(\$item['{$field['name']}']) ? date('d/m/Y H:i', strtotime(\$item['{$field['name']}'])) : 'N/A' ?></td>\n";
            } else if (strpos($field['type'], 'date') !== false) {
                $content .= "                        <td><?= isset(\$item['{$field['name']}']) ? date('d/m/Y', strtotime(\$item['{$field['name']}'])) : 'N/A' ?></td>\n";
            } else {
                $content .= "                        <td><?= \$item['{$field['name']}'] ?></td>\n";
            }

            $content .= "                    </tr>\n";
        }

        $content .= "                </table>\n";
        $content .= "            </div>\n";
        $content .= "            <div class=\"card-footer\">\n";
        $content .= "                <div class=\"btn-group\">\n";
        $content .= "                    <a href=\"<?= getenv('APP_URL') ?>/$table/edit/<?= \$item['$primaryKey'] ?>\" class=\"btn btn-warning\">\n";
        $content .= "                        <i class=\"bi bi-pencil\"></i> ແກ້ໄຂ\n";
        $content .= "                    </a>\n";
        $content .= "                    <a href=\"<?= getenv('APP_URL') ?>/$table/delete/<?= \$item['$primaryKey'] ?>\"\n";
        $content .= "                        class=\"btn btn-danger\"\n";
        $content .= "                        onclick=\"return confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການລຶບລາຍການນີ້?')\">\n";
        $content .= "                        <i class=\"bi bi-trash\"></i> ລຶບ\n";
        $content .= "                    </a>\n";
        $content .= "                    <a href=\"<?= getenv('APP_URL') ?>/$table\" class=\"btn btn-secondary\">\n";
        $content .= "                        <i class=\"bi bi-arrow-left\"></i> ກັບຄືນ\n";
        $content .= "                    </a>\n";
        $content .= "                </div>\n";
        $content .= "            </div>\n";
        $content .= "        </div>\n";
        $content .= "    </div>\n";
        $content .= "</div>";

        // ບັນທຶກໄຟລ໌
        $showPath = "$viewPath/show.php";
        if (file_put_contents($showPath, $content) === false) {
            throw new Exception("ບໍ່ສາມາດບັນທຶກໄຟລ໌: $showPath");
        }

        return $showPath;
    }

    private function generateRoute($table)
    {
        $controllerName = $this->getControllerName($table);

        // ສ້າງຂໍ້ຄວາມແນະນຳສຳລັບການເພີ່ມເສັ້ນທາງ
        $routeInfo = "/**\n";
        $routeInfo .= " * ເສັ້ນທາງສຳລັບ $table\n";
        $routeInfo .= " * ຄັດລອກໂຄດລຸ່ມນີ້ໄປໃສ່ໃນໄຟລ໌ routes/web.php\n";
        $routeInfo .= " */\n\n";
        $routeInfo .= "// ເສັ້ນທາງສຳລັບ $table\n";
        $routeInfo .= "\$router->get('/$table', [$controllerName::class, 'index']);\n";
        $routeInfo .= "\$router->get('/$table/create', [$controllerName::class, 'create']);\n";
        $routeInfo .= "\$router->post('/$table/store', [$controllerName::class, 'store']);\n";
        $routeInfo .= "\$router->get('/$table/show/{id}', [$controllerName::class, 'show']);\n";
        $routeInfo .= "\$router->get('/$table/edit/{id}', [$controllerName::class, 'edit']);\n";
        $routeInfo .= "\$router->post('/$table/update/{id}', [$controllerName::class, 'update']);\n";
        $routeInfo .= "\$router->get('/$table/delete/{id}', [$controllerName::class, 'delete']);\n\n";
        $routeInfo .= "// ຫຼື ໃຊ້ resource ເພື່ອສ້າງທຸກເສັ້ນທາງພ້ອມກັນ\n";
        $routeInfo .= "// \$router->resource('$table', [$controllerName::class]);\n";

        // ບັນທຶກໄຟລ໌ແນະນຳເສັ້ນທາງ
        $routePath = VIEWS_PATH . '/generator/routes_info.txt';
        file_put_contents($routePath, $routeInfo);

        return $routeInfo;
    }

    /**
     * ຮັບຊື່ Model ຈາກຊື່ຕາຕະລາງ
     * 
     * @param string $table ຊື່ຕາຕະລາງ
     * @return string ຊື່ Model
     */
    private function getModelName($table)
    {
        // ປ່ຽນຊື່ຕາຕະລາງເປັນຊື່ Model
        // ຕົວຢ່າງ: users -> UserModel, blog_posts -> BlogPostModel
        $singular = $this->getSingularName($table);
        $modelName = str_replace(' ', '', ucwords(str_replace('_', ' ', $singular))) . 'Model';

        return $modelName;
    }

    /**
     * ຮັບຊື່ Controller ຈາກຊື່ຕາຕະລາງ
     * 
     * @param string $table ຊື່ຕາຕະລາງ
     * @return string ຊື່ Controller
     */
    private function getControllerName($table)
    {
        // ປ່ຽນຊື່ຕາຕະລາງເປັນຊື່ Controller
        // ຕົວຢ່າງ: users -> UserController, blog_posts -> BlogPostController
        $singular = $this->getSingularName($table);
        $controllerName = str_replace(' ', '', ucwords(str_replace('_', ' ', $singular))) . 'Controller';

        return $controllerName;
    }

    /**
     * ປ່ຽນຊື່ຕາຕະລາງເປັນຮູບແບບເອກະພົດ (singular)
     * 
     * @param string $table ຊື່ຕາຕະລາງ
     * @return string ຊື່ເອກະພົດ
     */
    private function getSingularName($table)
    {
        // ກົດການງ່າຍໆສຳລັບປ່ຽນຊື່ຕາຕະລາງເປັນຮູບແບບເອກະພົດ
        if (substr($table, -1) === 's') {
            return substr($table, 0, -1);
        }

        // ກົດການສຳລັບບາງຮູບແບບພິເສດ
        $irregular = [
            'children' => 'child',
            'people' => 'person',
            'men' => 'man',
            'women' => 'woman',
            'mice' => 'mouse',
            'geese' => 'goose',
            'categories' => 'category',
            'queries' => 'query',
            'abilities' => 'ability',
            'agencies' => 'agency',
            'activities' => 'activity'
        ];

        if (isset($irregular[$table])) {
            return $irregular[$table];
        }

        return $table;
    }

    /**
     * ສ້າງໂຄດຕົວຢ່າງສຳລັບການນຳໃຊ້ API
     * 
     * @param string $table ຊື່ຕາຕະລາງ
     * @param array $fields ຂໍ້ມູນ fields ຂອງຕາຕະລາງ
     * @param string $primaryKey ຊື່ຄອລັມ primary key
     * @return string ໂຄດຕົວຢ່າງ API
     */
    private function generateApiExample($table, $fields, $primaryKey)
    {
        $modelName = $this->getModelName($table);
        $singular = $this->getSingularName($table);

        $code = "<?php\n\n";
        $code .= "/**\n";
        $code .= " * ຕົວຢ່າງການໃຊ້ງານ API ສຳລັບ $table\n";
        $code .= " * ນີ້ເປັນພຽງຕົວຢ່າງຂອງຝັ່ງຜູ້ໃຊ້ງານ API\n";
        $code .= " */\n\n";

        // ຕົວຢ່າງການຮຽກຂໍ້ມູນທັງໝົດ
        $code .= "// ຮຽກຂໍ້ມູນ $table ທັງໝົດ\n";
        $code .= "\$response = file_get_contents('http://localhost/api/$table');\n";
        $code .= "\$data = json_decode(\$response, true);\n\n";

        // ຕົວຢ່າງການຮຽກຂໍ້ມູນຕາມ ID
        $code .= "// ຮຽກຂໍ້ມູນຕາມ ID\n";
        $code .= "\$id = 1;\n";
        $code .= "\$response = file_get_contents('http://localhost/api/$table/' . \$id);\n";
        $code .= "\$data = json_decode(\$response, true);\n\n";

        // ຕົວຢ່າງການເພີ່ມຂໍ້ມູນໃໝ່
        $code .= "// ເພີ່ມຂໍ້ມູນໃໝ່\n";
        $code .= "\$data = [\n";

        foreach ($fields as $field) {
            // ຂ້າມບາງຄອລັມທີ່ບໍ່ຕ້ອງການສະແດງ
            if (in_array($field['name'], $this->skipFields) || $field['name'] == $primaryKey) {
                continue;
            }

            // ສ້າງຕົວຢ່າງຂໍ້ມູນ
            if (strpos($field['type'], 'int') !== false) {
                $code .= "    '{$field['name']}' => 1,\n";
            } else if (strpos($field['type'], 'double') !== false || strpos($field['type'], 'float') !== false || strpos($field['type'], 'decimal') !== false) {
                $code .= "    '{$field['name']}' => 1.5,\n";
            } else if (strpos($field['name'], 'email') !== false) {
                $code .= "    '{$field['name']}' => 'example@email.com',\n";
            } else if (strpos($field['name'], 'password') !== false) {
                $code .= "    '{$field['name']}' => 'securepassword',\n";
            } else if (strpos($field['type'], 'date') !== false) {
                $code .= "    '{$field['name']}' => date('Y-m-d'),\n";
            } else if (strpos($field['type'], 'time') !== false) {
                $code .= "    '{$field['name']}' => date('H:i:s'),\n";
            } else if ($field['name'] === 'status') {
                $code .= "    '{$field['name']}' => 'active',\n";
            } else if ($field['name'] === 'role') {
                $code .= "    '{$field['name']}' => 'user',\n";
            } else {
                $code .= "    '{$field['name']}' => '$singular value',\n";
            }
        }

        $code .= "];\n\n";
        $code .= "\$options = [\n";
        $code .= "    'http' => [\n";
        $code .= "        'method' => 'POST',\n";
        $code .= "        'header' => 'Content-Type: application/json',\n";
        $code .= "        'content' => json_encode(\$data)\n";
        $code .= "    ]\n";
        $code .= "];\n\n";
        $code .= "\$context = stream_context_create(\$options);\n";
        $code .= "\$response = file_get_contents('http://localhost/api/$table', false, \$context);\n";
        $code .= "\$result = json_decode(\$response, true);\n\n";

        // ຕົວຢ່າງການອັບເດດຂໍ້ມູນ
        $code .= "// ອັບເດດຂໍ້ມູນ\n";
        $code .= "\$id = 1;\n";
        $code .= "\$data = [\n";
        $code .= "    // ຂໍ້ມູນທີ່ຕ້ອງການອັບເດດ\n";
        $code .= "];\n\n";
        $code .= "\$options = [\n";
        $code .= "    'http' => [\n";
        $code .= "        'method' => 'PUT',\n";
        $code .= "        'header' => 'Content-Type: application/json',\n";
        $code .= "        'content' => json_encode(\$data)\n";
        $code .= "    ]\n";
        $code .= "];\n\n";
        $code .= "\$context = stream_context_create(\$options);\n";
        $code .= "\$response = file_get_contents('http://localhost/api/$table/' . \$id, false, \$context);\n";
        $code .= "\$result = json_decode(\$response, true);\n\n";

        // ຕົວຢ່າງການລຶບຂໍ້ມູນ
        $code .= "// ລຶບຂໍ້ມູນ\n";
        $code .= "\$id = 1;\n";
        $code .= "\$options = [\n";
        $code .= "    'http' => [\n";
        $code .= "        'method' => 'DELETE'\n";
        $code .= "    ]\n";
        $code .= "];\n\n";
        $code .= "\$context = stream_context_create(\$options);\n";
        $code .= "\$response = file_get_contents('http://localhost/api/$table/' . \$id, false, \$context);\n";
        $code .= "\$result = json_decode(\$response, true);\n";

        return $code;
    }

    /**
     * ສະແດງໜ້າສະຫຼຸບຜົນການສ້າງ MVC
     * 
     * @param string $table ຊື່ຕາຕະລາງ
     * @param array $files ລາຍຊື່ໄຟລ໌ທີ່ຖືກສ້າງແລ້ວ
     */
    public function result()
    {
        $table = $this->request->get('table');
        $files = [
            'model' => $this->request->get('model'),
            'controller' => $this->request->get('controller'),
            'views' => $this->request->get('views'),
            'route' => $this->request->get('route')
        ];

        $this->view('generator/result', [
            'table' => $table,
            'files' => $files,
            'pageTitle' => 'ສ້າງ MVC ສຳເລັດ'
        ]);
    }
}
