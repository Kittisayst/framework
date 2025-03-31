<?php

/**
 * Database Class
 * ຄລາສສຳລັບຈັດການການເຊື່ອມຕໍ່ກັບຖານຂໍ້ມູນ
 */
class Database
{
    protected $connection;
    protected $statement;
    protected static $instance = null;
    protected $config = [];

    /**
     * Constructor
     */
    public function __construct($config = [])
    {
        // ໃຊ້ຄ່າຈາກ .env ເປັນຄ່າເລີ່ມຕົ້ນ ຖ້າບໍ່ມີຄ່າ config ທີ່ຮັບເຂົ້າມາ
        if (empty($config)) {
            $this->config = [
                'host' => getenv('DB_HOST') ?: 'localhost',
                'port' => getenv('DB_PORT') ?: '3306',
                'database' => getenv('DB_DATABASE') ?: 'my_database',
                'username' => getenv('DB_USERNAME') ?: 'root',
                'password' => getenv('DB_PASSWORD') ?: '',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ];
        } else {
            $this->config = $config;
        }

        $this->connect();
    }

    /**
     * ເຊື່ອມຕໍ່ກັບຖານຂໍ້ມູນ
     */
    public function connect()
    {
        try {
            $dsn = "mysql:host={$this->config['host']};port={$this->config['port']};dbname={$this->config['database']};charset={$this->config['charset']}";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $options
            );

            return $this;
        } catch (PDOException $e) {
            die("ເຊື່ອມຕໍ່ກັບຖານຂໍ້ມູນບໍ່ສຳເລັດ: " . $e->getMessage());
        }
    }

    /**
     * ຮັບອິນສະຕັນຂອງ Database (Singleton)
     */
    public static function getInstance($config = [])
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    /**
     * ກະກຽມຄຳສັ່ງ SQL
     */
    public function prepare($sql)
    {
        $this->statement = $this->connection->prepare($sql);
        return $this;
    }

    /**
     * ກຳນົດຄ່າພາລາມິເຕີໃຫ້ກັບຄຳສັ່ງ SQL
     */
    public function bind($param, $value, $type = null)
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }

        $this->statement->bindValue($param, $value, $type);
        return $this;
    }

    /**
     * ກຳນົດຄ່າຫຼາຍພາລາມິເຕີພ້ອມກັນ
     */
    public function bindParams($params = [])
    {
        foreach ($params as $param => $value) {
            $this->bind($param, $value);
        }

        return $this;
    }

    /**
     * ປະຕິບັດຄຳສັ່ງ SQL
     */
    public function execute($params = [])
    {
        if (!empty($params)) {
            return $this->statement->execute($params);
        }

        return $this->statement->execute();
    }

    /**
     * ປະຕິບັດຄຳສັ່ງ SQL ແລະ ຮັບຜົນລັບທັງໝົດ
     */
    public function query($sql, $params = [])
    {
        $this->prepare($sql);
        $this->execute($params);
        return $this->fetchAll();
    }

    /**
     * ຮັບຜົນລັບແຖວດຽວ
     */
    public function fetch()
    {
        return $this->statement->fetch();
    }

    /**
     * ຮັບຜົນລັບທັງໝົດ
     */
    public function fetchAll()
    {
        return $this->statement->fetchAll();
    }

    /**
     * ຮັບຈຳນວນແຖວທີ່ໄດ້ຮັບຜົນກະທົບ
     */
    public function rowCount()
    {
        return $this->statement->rowCount();
    }

    /**
     * ຮັບຄ່າ ID ຫຼ້າສຸດທີ່ຖືກເພີ່ມ
     */
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    /**
     * ປະຕິບັດຄຳສັ່ງ SQL ໂດຍກົງ
     */
    public function exec($sql)
    {
        return $this->connection->exec($sql);
    }

    /**
     * ເລີ່ມຕົ້ນການເຮັດທຸລະກຳ
     */
    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }

    /**
     * ຢືນຢັນການເຮັດທຸລະກຳ
     */
    public function commit()
    {
        return $this->connection->commit();
    }

    /**
     * ຍົກເລີກການເຮັດທຸລະກຳ
     */
    public function rollBack()
    {
        return $this->connection->rollBack();
    }

    /**
     * ຮັບຂໍ້ຜິດພາດ
     */
    public function getError()
    {
        return $this->statement->errorInfo();
    }

    /**
     * ປິດການເຊື່ອມຕໍ່
     */
    public function close()
    {
        $this->connection = null;
    }
}
