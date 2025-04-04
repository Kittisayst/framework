# ຊື່ເຟຣມເວີກຂອງເຈົ້າ

ຄຳອະທິບາຍສັ້ນໆກ່ຽວກັບເຟຣມເວີກຂອງເຈົ້າ ແລະ ມັນຖືກສ້າງຂຶ້ນມາເພື່ອຫຍັງ.

## ຕາຕະລາງເນື້ອໃນ

1.  [ຄຸນສົມບັດຫຼັກ](#ຄຸນສົມບັດຫຼັກ)
2.  [ຄວາມຕ້ອງການ](#ຄວາມຕ້ອງການ)
3.  [ການຕິດຕັ້ງ](#ການຕິດຕັ້ງ)
4.  [ໂຄງສ້າງໂຄງການ](#ໂຄງສ້າງໂຄງການ)
5.  [ການສ້າງໜ້າເວັບ](#ການສ້າງໜ້າເວັບ)
6.  [ຕົວຢ່າງໂຄດ](#ຕົວຢ່າງໂຄດ)
7.  [ຄຳແນະນຳເພີ່ມເຕີມ](#ຄຳແນະນຳເພີ່ມເຕີມ)
8.  [ການປະກອບສ່ວນ](#ການປະກອບສ່ວນ)
9.  [License](#license)

## ຄຸນສົມບັດຫຼັກ <a name="ຄຸນສົມບັດຫຼັກ"></a>

* ຄຸນສົມບັດ 1
* ຄຸນສົມບັດ 2
* ຄຸນສົມບັດ 3

## ຄວາມຕ້ອງການ <a name="ຄວາມຕ້ອງການ"></a>

* PHP 7.4 ຫຼືສູງກວ່າ
* MySQL 5.7 ຫຼືສູງກວ່າ
* Composer

## ການຕິດຕັ້ງ <a name="ການຕິດຕັ້ງ"></a>

1.  Clone repository: `git clone <repository_url>`
2.  ຕິດຕັ້ງ dependencies: `composer install`
3.  ຕັ້ງຄ່າຖານຂໍ້ມູນ: ແກ້ໄຂໄຟລ໌ `.env`
4.  Run the application: `php -S localhost:8000 -t public`

## ໂຄງສ້າງໂຄງການ <a name="ໂຄງສ້າງໂຄງການ"></a>
app/
├── core/         # ໄຟລ໌ຫຼັກຂອງເຟຣມເວີກ
│   ├── App.php   # ໄຟລ໌ຫຼັກຂອງແອັບພລິເຄຊັນ
│   └── ...
├── controllers/  # ຄອນໂທລເລີ
│   ├── HomeController.php
│   └── ...
├── models/       # ໂມເດລ
│   └── UserModel.php
├── views/        # ວິວ
│   ├── home/
│   │   └── index.php
│   └── ...
routes/         # ເສັ້ນທາງ
└── ...
* `app/core/`: ໄຟລ໌ຫຼັກຂອງເຟຣມເວີກ.
* `app/controllers/`: ຄອນໂທລເລີທີ່ຈັດການກັບການຮ້ອງຂໍຂອງຜູ້ໃຊ້.
* `app/models/`: ໂມເດລທີ່ຈັດການກັບການດຶງ ແລະ ບັນທຶກຂໍ້ມູນຈາກຖານຂໍ້ມູນ.
* `app/views/`: ວິວທີ່ສະແດງຂໍ້ມູນໃຫ້ຜູ້ໃຊ້.
* `routes/`: ໄຟລ໌ກຳນົດເສັ້ນທາງຂອງແອັບພລິເຄຊັນ.

## ການສ້າງໜ້າເວັບ <a name="ການສ້າງໜ້າເວັບ"></a>

1.  ສ້າງ route ໃໝ່ໃນ `routes/web.php`:

    ```php
    <?php

    use Core\Router;

    Router::get('/users', 'UserController@index');
    ?>
    ```

2.  ສ້າງ controller ໃໝ່ໃນ `app/controllers/UserController.php`:

    ```php
    <?php

    namespace App\Controllers;

    use Core\Controller;
    use App\Models\UserModel;

    class UserController extends Controller
    {
        public function index()
        {
            $users = (new UserModel)->all();
            return $this->view('user/index', ['users' => $users]);
        }
    }
    ?>
    ```

3.  ສ້າງ view ໃໝ່ໃນ `app/views/user/index.php`:

    ```php
    <h1>ລາຍຊື່ຜູ້ໃຊ້</h1>
    <ul>
        <?php foreach ($users as $user): ?>
            <li><?php echo $user['name']; ?></li>
        <?php endforeach; ?>
    </ul>
    ```

## ຕົວຢ່າງໂຄດ <a name="ຕົວຢ່າງໂຄດ"></a>

```php
// ຕົວຢ່າງການ query ຂໍ້ມູນຈາກຖານຂໍ້ມູນ
$users = (new UserModel)->all();

// ຕົວຢ່າງການສະແດງ view
return $this->view('home/index', ['message' => 'ສະບາຍດີ']);
ຄຳແນະນຳເພີ່ມເຕີມ <a name="ຄຳແນະນຳເພີ່ມເຕີມ"></a>
ລິ້ງໄປຫາເອກະສານອ້າງອີງ
ລິ້ງໄປຫາບົດຮຽນວິດີໂອ
ການດີບັກ: Log ໄຟລ໌ສາມາດພົບໄດ້ໃນ logs/.
ການປະກອບສ່ວນ <a name="ການປະກອບສ່ວນ"></a>
ຖ້າເຈົ້າຕ້ອງການປະກອບສ່ວນໃນການພັດທະນາເຟຣມເວີກນີ້, ກະລຸນາອ່ານ CONTRIBUTING.md.

License <a name="license"></a>
This framework is open-sourced under the MIT license.