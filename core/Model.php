<?php

/**
 * Base Model Class
 * ຄລາສພື້ນຖານສຳລັບໂມເດລທຸກຕົວ
 */
class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * ຕັ້ງຄ່າຊື່ຕາຕະລາງ
     */
    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * ຕັ້ງຄ່າຊື່ຄອລັມທີ່ເປັນກະແຈຫຼັກ
     */
    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;
        return $this;
    }

    /**
     * ຮັບຂໍ້ມູນທັງໝົດຈາກຕາຕະລາງ
     */
    public function all()
    {
        $sql = "SELECT * FROM {$this->table}";
        return $this->db->query($sql);
    }

    /**
     * ຮັບຂໍ້ມູນແຖວດຽວໂດຍໃຊ້ ID
     */
    public function find($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        $result = $this->db->query($sql, [':id' => $id]);

        return !empty($result) ? $result[0] : null;
    }

    /**
     * ຮັບຂໍ້ມູນທັງໝົດທີ່ກົງກັບເງື່ອນໄຂ
     */
    public function where($column, $operator, $value = null)
    {
        // ຖ້າມີພຽງແຕ່ 2 ພາລາມິເຕີ, ໃຫ້ຖືວ່າ $operator ແມ່ນຄ່າ ແລະໃຊ້ = ເປັນຜູ້ປະຕິບັດການ
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $sql = "SELECT * FROM {$this->table} WHERE {$column} {$operator} :value";
        return $this->db->query($sql, [':value' => $value]);
    }

    /**
     * ບັນທຶກຂໍ້ມູນໃໝ່
     */
    public function create($data)
    {
        // ກັ່ນຕອງຂໍ້ມູນຕາມ fillable
        $filteredData = $this->filterData($data);

        if (empty($filteredData)) {
            return false;
        }

        // ສ້າງຄຳສັ່ງ SQL
        $columns = implode(', ', array_keys($filteredData));
        $placeholders = implode(', ', array_map(function ($key) {
            return ':' . $key;
        }, array_keys($filteredData)));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";

        // ກະກຽມພາລາມິເຕີ
        $params = [];
        foreach ($filteredData as $key => $value) {
            $params[':' . $key] = $value;
        }

        // ປະຕິບັດຄຳສັ່ງ SQL
        $this->db->prepare($sql);
        $result = $this->db->execute($params);

        if ($result) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * ອັບເດດຂໍ້ມູນທີ່ມີຢູ່ແລ້ວ
     */
    public function update($id, $data)
    {
        // ກັ່ນຕອງຂໍ້ມູນຕາມ fillable
        $filteredData = $this->filterData($data);

        if (empty($filteredData)) {
            return false;
        }

        // ສ້າງຊຸດສຳລັບ SET
        $setClause = [];
        foreach (array_keys($filteredData) as $key) {
            $setClause[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $setClause);

        // ສ້າງຄຳສັ່ງ SQL
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :id";

        // ກະກຽມພາລາມິເຕີ
        $params = [':id' => $id];
        foreach ($filteredData as $key => $value) {
            $params[':' . $key] = $value;
        }

        // ປະຕິບັດຄຳສັ່ງ SQL
        $this->db->prepare($sql);
        $result = $this->db->execute($params);

        return ($result && $this->db->rowCount() > 0);
    }

    /**
     * ລຶບຂໍ້ມູນ
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $this->db->prepare($sql);
        $result = $this->db->execute([':id' => $id]);

        return ($result && $this->db->rowCount() > 0);
    }

    /**
     * ກັ່ນຕອງຂໍ້ມູນໂດຍໃຊ້ fillable
     */
    protected function filterData($data)
    {
        if (empty($this->fillable)) {
            return $data;
        }

        $filteredData = [];
        foreach ($this->fillable as $field) {
            if (isset($data[$field])) {
                $filteredData[$field] = $data[$field];
            }
        }

        return $filteredData;
    }

    /**
     * ເຊື່ອງຂໍ້ມູນທີ່ບໍ່ຕ້ອງການສະແດງ
     */
    protected function hideFields($data)
    {
        if (empty($this->hidden) || empty($data)) {
            return $data;
        }

        // ຖ້າຂໍ້ມູນເປັນຊຸດແຖວ
        if (isset($data[0]) && is_array($data[0])) {
            foreach ($data as &$row) {
                foreach ($this->hidden as $field) {
                    if (isset($row[$field])) {
                        unset($row[$field]);
                    }
                }
            }
        }
        // ຖ້າຂໍ້ມູນເປັນແຖວດຽວ
        else {
            foreach ($this->hidden as $field) {
                if (isset($data[$field])) {
                    unset($data[$field]);
                }
            }
        }

        return $data;
    }

    /**
     * ປະຕິບັດຄຳສັ່ງ SQL ທີ່ກຳນົດເອງ
     */
    public function query($sql, $params = [])
    {
        return $this->db->query($sql, $params);
    }

    /**
     * ນັບຈຳນວນແຖວທັງໝົດ
     */
    public function count()
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $result = $this->db->query($sql);

        return isset($result[0]['count']) ? (int) $result[0]['count'] : 0;
    }

    /**
     * ນັບຈຳນວນແຖວຕາມເງື່ອນໄຂ
     */
    public function countWhere($column, $operator, $value = null)
    {
        // ຖ້າມີພຽງແຕ່ 2 ພາລາມິເຕີ, ໃຫ້ຖືວ່າ $operator ແມ່ນຄ່າ ແລະໃຊ້ = ເປັນຜູ້ປະຕິບັດການ
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$column} {$operator} :value";
        $result = $this->db->query($sql, [':value' => $value]);

        return isset($result[0]['count']) ? (int) $result[0]['count'] : 0;
    }
}
