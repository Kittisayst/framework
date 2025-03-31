<?php

/**
 * User Model
 * ໂມເດລສຳລັບຈັດການຂໍ້ມູນຜູ້ໃຊ້
 */
class UserModel extends Model
{
    // ຊື່ຕາຕະລາງໃນຖານຂໍ້ມູນ
    protected $table = 'users';

    // ກຳນົດຄອລັມທີ່ສາມາດເພີ່ມຫຼືອັບເດດໄດ້
    protected $fillable = ['name', 'email', 'password', 'role', 'status'];

    // ຄອລັມທີ່ບໍ່ສະແດງໃນຜົນລັບ
    protected $hidden = ['password'];

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * ຄົ້ນຫາຜູ້ໃຊ້ຕາມຊື່ຫຼືອີເມລ
     */
    public function search($keyword)
    {
        $sql = "SELECT * FROM {$this->table} WHERE name LIKE :keyword OR email LIKE :keyword";
        return $this->db->query($sql, [':keyword' => "%$keyword%"]);
    }

    /**
     * ຊອກຫາຜູ້ໃຊ້ຕາມອີເມລ
     */
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $result = $this->db->query($sql, [':email' => $email]);

        return !empty($result) ? $result[0] : null;
    }

    /**
     * ກວດສອບການເຂົ້າສູ່ລະບົບ
     */
    public function login($email, $password)
    {
        // ຊອກຫາຜູ້ໃຊ້ຕາມອີເມລ
        $user = $this->findByEmail($email);

        // ຖ້າບໍ່ພົບຜູ້ໃຊ້ ຫຼື ລະຫັດຜ່ານບໍ່ຖືກຕ້ອງ
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        return $user;
    }

    /**
     * ກວດສອບວ່າອີເມລມີຢູ່ແລ້ວຫຼືບໍ່
     */
    public function emailExists($email, $excludeId = null)
    {
        if ($excludeId) {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = :email AND id != :id";
            $result = $this->db->query($sql, [':email' => $email, ':id' => $excludeId]);
        } else {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = :email";
            $result = $this->db->query($sql, [':email' => $email]);
        }

        return ($result[0]['count'] > 0);
    }

    /**
     * ຮັບລາຍຊື່ຜູ້ໃຊ້ຕາມບົດບາດ
     */
    public function findByRole($role)
    {
        return $this->where('role', $role);
    }

    /**
     * ຮັບຈຳນວນຜູ້ໃຊ້ທັງໝົດ
     */
    public function getTotal()
    {
        return $this->count();
    }

    /**
     * ປ່ຽນສະຖານະຜູ້ໃຊ້
     */
    public function changeStatus($id, $status)
    {
        return $this->update($id, ['status' => $status]);
    }

    /**
     * ປ່ຽນລະຫັດຜ່ານ
     */
    public function changePassword($id, $newPassword)
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($id, ['password' => $hashedPassword]);
    }
}
