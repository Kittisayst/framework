<?php

class GeneratorController extends Controller {
    protected $db;

    public function __construct() {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    /**
     * ສະແດງໜ້າຫຼັກຂອງ Generator
     */
    public function index() {
        $tables = $this->getDatabaseTables();
        return $this->view('generator/index', ['tables' => $tables]);
    }

    /**
     * ສະແດງໜ້າຕັ້ງຄ່າສຳລັບຕາຕະລາງທີ່ເລືອກ
     */
    public function configure($table) {
        $fields = $this->getTableFields($table);
        $relations = $this->getPossibleRelations($table);
        return $this->view('generator/configure', [
            'table' => $table,
            'fields' => $fields,
            'relations' => $relations
        ]);
    }

    /**
     * ສ້າງໄຟລ໌ MVC ຕາມການຕັ້ງຄ່າ
     */
    public function generate() {
        if ($this->request->isPost()) {
            $config = $this->request->post();
            $table = $config['table'];
            
            // ສ້າງ Model
            $this->generateModel($table, $config);
            
            // ສ້າງ Controller
            $this->generateController($table, $config);
            
            // ສ້າງ Views
            $this->generateViews($table, $config);
            
            return $this->redirect('/generator')->with('success', 'Generated successfully');
        }
        
        return $this->redirect('/generator')->with('error', 'Invalid request');
    }

    /**
     * ດຶງຂໍ້ມູນຕາຕະລາງທັງໝົດຈາກຖານຂໍ້ມູນ
     */
    private function getDatabaseTables() {
        $sql = "SHOW TABLES";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $tables = [];
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            if (isset($row[0])) {
                $tables[] = $row[0];
            }
        }
        return $tables;
    }

    /**
     * ດຶງຂໍ້ມູນ fields ຂອງຕາຕະລາງ
     */
    private function getTableFields($table) {
        $sql = "DESCRIBE $table";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $fields = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $fields[] = [
                'name' => $row['Field'],
                'type' => $row['Type'],
                'null' => $row['Null'],
                'key' => $row['Key'],
                'default' => $row['Default'],
                'extra' => $row['Extra']
            ];
        }
        return $fields;
    }

    /**
     * ຊອກຫາຄວາມສຳພັນທີ່ເປັນໄປໄດ້ຂອງຕາຕະລາງ
     */
    private function getPossibleRelations($table) {
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
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':table', $table);
        $stmt->execute();
        
        $relations = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $relations[] = [
                'table' => $row['TABLE_NAME'],
                'column' => $row['COLUMN_NAME'],
                'referenced_table' => $row['REFERENCED_TABLE_NAME'],
                'referenced_column' => $row['REFERENCED_COLUMN_NAME']
            ];
        }
        return $relations;
    }

    /**
     * ສ້າງ Model
     */
    private function generateModel($table, $config) {
        $modelName = $this->getModelName($table);
        $fields = $this->getTableFields($table);
        $relations = $this->getPossibleRelations($table);
        
        $modelContent = "<?php\n\n";
        $modelContent .= "class $modelName extends Model {\n";
        $modelContent .= "    protected \$table = '$table';\n\n";
        
        // ເພີ່ມ validation rules
        $modelContent .= "    protected \$rules = [\n";
        foreach ($fields as $field) {
            if (isset($config['validation'][$field['name']])) {
                $modelContent .= "        '{$field['name']}' => '{$config['validation'][$field['name']]}',\n";
            }
        }
        $modelContent .= "    ];\n\n";
        
        // ເພີ່ມ relationships
        foreach ($relations as $relation) {
            $relatedModel = $this->getModelName($relation['referenced_table']);
            $modelContent .= "    public function {$relation['referenced_table']}() {\n";
            $modelContent .= "        return \$this->belongsTo('$relatedModel', '{$relation['column']}');\n";
            $modelContent .= "    }\n\n";
        }
        
        $modelContent .= "}\n";
        
        // ບັນທຶກໄຟລ໌
        $modelPath = MODELS_PATH . "/$modelName.php";
        file_put_contents($modelPath, $modelContent);
    }

    /**
     * ສ້າງ Controller
     */
    private function generateController($table, $config) {
        $controllerName = $this->getControllerName($table);
        $modelName = $this->getModelName($table);
        
        $controllerContent = "<?php\n\n";
        $controllerContent .= "class $controllerName extends Controller {\n";
        $controllerContent .= "    private \$$modelName;\n\n";
        
        $controllerContent .= "    public function __construct() {\n";
        $controllerContent .= "        parent::__construct();\n";
        $controllerContent .= "        \$this->$modelName = new $modelName();\n";
        $controllerContent .= "    }\n\n";
        
        // ເພີ່ມ CRUD methods
        $controllerContent .= $this->generateCrudMethods($table, $modelName);
        
        $controllerContent .= "}\n";
        
        // ບັນທຶກໄຟລ໌
        $controllerPath = CONTROLLERS_PATH . "/$controllerName.php";
        file_put_contents($controllerPath, $controllerContent);
    }

    /**
     * ສ້າງ Views
     */
    private function generateViews($table, $config) {
        $viewPath = VIEWS_PATH . "/" . strtolower($table);
        if (!is_dir($viewPath)) {
            mkdir($viewPath, 0755, true);
        }
        
        // ສ້າງ list view
        $this->generateListView($table, $viewPath, $config);
        
        // ສ້າງ form view
        $this->generateFormView($table, $viewPath, $config);
        
        // ສ້າງ show view
        $this->generateShowView($table, $viewPath, $config);
    }

    /**
     * ສ້າງ CRUD methods ສຳລັບ Controller
     */
    private function generateCrudMethods($table, $modelName) {
        $methods = "";
        
        // index method
        $methods .= "    public function index() {\n";
        $methods .= "        \$items = \$this->$modelName->all();\n";
        $methods .= "        return \$this->view('$table/index', ['items' => \$items]);\n";
        $methods .= "    }\n\n";
        
        // create method
        $methods .= "    public function create() {\n";
        $methods .= "        return \$this->view('$table/form');\n";
        $methods .= "    }\n\n";
        
        // store method
        $methods .= "    public function store() {\n";
        $methods .= "        if (\$this->request->isPost()) {\n";
        $methods .= "            \$data = \$this->request->post();\n";
        $methods .= "            if (\$this->$modelName->create(\$data)) {\n";
        $methods .= "                return \$this->redirect('/$table')->with('success', 'Created successfully');\n";
        $methods .= "            }\n";
        $methods .= "            return \$this->redirect('/$table/create')->with('error', 'Failed to create');\n";
        $methods .= "        }\n";
        $methods .= "        return \$this->redirect('/$table');\n";
        $methods .= "    }\n\n";
        
        // edit method
        $methods .= "    public function edit(\$id) {\n";
        $methods .= "        \$item = \$this->$modelName->find(\$id);\n";
        $methods .= "        if (!\$item) {\n";
        $methods .= "            return \$this->redirect('/$table')->with('error', 'Item not found');\n";
        $methods .= "        }\n";
        $methods .= "        return \$this->view('$table/form', ['item' => \$item]);\n";
        $methods .= "    }\n\n";
        
        // update method
        $methods .= "    public function update(\$id) {\n";
        $methods .= "        if (\$this->request->isPost()) {\n";
        $methods .= "            \$data = \$this->request->post();\n";
        $methods .= "            if (\$this->$modelName->update(\$id, \$data)) {\n";
        $methods .= "                return \$this->redirect('/$table')->with('success', 'Updated successfully');\n";
        $methods .= "            }\n";
        $methods .= "            return \$this->redirect('/$table/edit/' . \$id)->with('error', 'Failed to update');\n";
        $methods .= "        }\n";
        $methods .= "        return \$this->redirect('/$table');\n";
        $methods .= "    }\n\n";
        
        // delete method
        $methods .= "    public function delete(\$id) {\n";
        $methods .= "        if (\$this->$modelName->delete(\$id)) {\n";
        $methods .= "            return \$this->redirect('/$table')->with('success', 'Deleted successfully');\n";
        $methods .= "        }\n";
        $methods .= "        return \$this->redirect('/$table')->with('error', 'Failed to delete');\n";
        $methods .= "    }\n\n";
        
        return $methods;
    }

    /**
     * ສ້າງ list view
     */
    private function generateListView($table, $viewPath, $config) {
        $fields = $this->getTableFields($table);
        
        $content = "<div class='container mt-4'>\n";
        $content .= "    <h1>" . ucfirst($table) . " List</h1>\n";
        $content .= "    <a href='/<?= \$table ?>/create' class='btn btn-primary mb-3'>Create New</a>\n";
        $content .= "    <table class='table table-striped'>\n";
        $content .= "        <thead>\n";
        $content .= "            <tr>\n";
        
        foreach ($fields as $field) {
            $content .= "                <th>" . ucfirst($field['name']) . "</th>\n";
        }
        
        $content .= "                <th>Actions</th>\n";
        $content .= "            </tr>\n";
        $content .= "        </thead>\n";
        $content .= "        <tbody>\n";
        $content .= "            <?php foreach (\$items as \$item): ?>\n";
        $content .= "            <tr>\n";
        
        foreach ($fields as $field) {
            $content .= "                <td><?= \$item['{$field['name']}'] ?></td>\n";
        }
        
        $content .= "                <td>\n";
        $content .= "                    <a href='/<?= \$table ?>/edit/<?= \$item['id'] ?>' class='btn btn-sm btn-warning'>Edit</a>\n";
        $content .= "                    <a href='/<?= \$table ?>/delete/<?= \$item['id'] ?>' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure?\")'>Delete</a>\n";
        $content .= "                </td>\n";
        $content .= "            </tr>\n";
        $content .= "            <?php endforeach; ?>\n";
        $content .= "        </tbody>\n";
        $content .= "    </table>\n";
        $content .= "</div>\n";
        
        file_put_contents("$viewPath/index.php", $content);
    }

    /**
     * ສ້າງ form view
     */
    private function generateFormView($table, $viewPath, $config) {
        $fields = $this->getTableFields($table);
        
        $content = "<div class='container mt-4'>\n";
        $content .= "    <h1><?= isset(\$item) ? 'Edit' : 'Create' ?> " . ucfirst($table) . "</h1>\n";
        $content .= "    <form method='POST' action='/<?= \$table ?>/<?= isset(\$item) ? 'update/' . \$item['id'] : 'store' ?>'>\n";
        
        foreach ($fields as $field) {
            if ($field['name'] == 'id') continue;
            
            $content .= "        <div class='mb-3'>\n";
            $content .= "            <label for='{$field['name']}' class='form-label'>" . ucfirst($field['name']) . "</label>\n";
            
            if (strpos($field['type'], 'text') !== false) {
                $content .= "            <textarea class='form-control' id='{$field['name']}' name='{$field['name']}'><?= isset(\$item) ? \$item['{$field['name']}'] : '' ?></textarea>\n";
            } else {
                $content .= "            <input type='text' class='form-control' id='{$field['name']}' name='{$field['name']}' value='<?= isset(\$item) ? \$item['{$field['name']}'] : '' ?>'>\n";
            }
            
            $content .= "        </div>\n";
        }
        
        $content .= "        <button type='submit' class='btn btn-primary'>Save</button>\n";
        $content .= "        <a href='/<?= \$table ?>' class='btn btn-secondary'>Cancel</a>\n";
        $content .= "    </form>\n";
        $content .= "</div>\n";
        
        file_put_contents("$viewPath/form.php", $content);
    }

    /**
     * ສ້າງ show view
     */
    private function generateShowView($table, $viewPath, $config) {
        $fields = $this->getTableFields($table);
        
        $content = "<div class='container mt-4'>\n";
        $content .= "    <h1>" . ucfirst($table) . " Details</h1>\n";
        $content .= "    <div class='card'>\n";
        $content .= "        <div class='card-body'>\n";
        
        foreach ($fields as $field) {
            $content .= "            <h5 class='card-title'>" . ucfirst($field['name']) . "</h5>\n";
            $content .= "            <p class='card-text'><?= \$item['{$field['name']}'] ?></p>\n";
        }
        
        $content .= "            <a href='/<?= \$table ?>/edit/<?= \$item['id'] ?>' class='btn btn-warning'>Edit</a>\n";
        $content .= "            <a href='/<?= \$table ?>' class='btn btn-secondary'>Back</a>\n";
        $content .= "        </div>\n";
        $content .= "    </div>\n";
        $content .= "</div>\n";
        
        file_put_contents("$viewPath/show.php", $content);
    }

    /**
     * ສ້າງຊື່ Model ຈາກຊື່ຕາຕະລາງ
     */
    private function getModelName($table) {
        return ucfirst(strtolower($table)) . 'Model';
    }

    /**
     * ສ້າງຊື່ Controller ຈາກຊື່ຕາຕະລາງ
     */
    private function getControllerName($table) {
        return ucfirst(strtolower($table)) . 'Controller';
    }
} 