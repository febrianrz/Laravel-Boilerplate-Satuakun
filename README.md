
<p  align="center"><a  href="https://alterindonesia.com"  target="_blank"><img  src="https://alterindonesia.com/front/images/logo.png"  width="150"></a></p>

  

<p  align="center">

<a  href="https://packagist.org/packages/laravel/framework"><img  src="https://img.shields.io/packagist/l/laravel/framework"  alt="License"></a>

</p>

  

## Laravel Integrate Satuakun.id

  Boilerplate Laravel 8 dan integrasi authentication dengan Satuakun.id.
  
## Learning
Menggunakan native Laravel dengan customisasi authentication yang terintegrasi dengan website satuakun.id dan penambahan generator REST API.
## Authentication
Dengan menggunakan boilerplate ini, tidak perlu melakukan authentikasi user secara manual, cukup ganti middleware API dengan ***alt_sso*** middleware. Contoh penggunaan pada file ***routes/api.php*** :
```php
<?php
use Illuminate\Support\Facades\Route;

Route::middleware(['alt_sso'])->group(function(){
	Route::namespace('Api')->group(function(){

		/** Your code here **/
	});
});
```
Middleware alt_sso akan melakukan authentikasi user via API seperti Laravel Passport API. Konfigurasi pada middleware ini berada pada folder ***config/micro.php*** dan berisi:
```php
<?php
return [
	'key' => [
		'service_key' 		=> 'xxx',
		'service_secret' 	=> 'xxx'
	],
	'url' => [
		'auth' => 'https://satuakun.id',
	],
];
```
***service_key*** dan ***service_secret*** didapatkan dari development mode pada website Satuakun.id.

Setiap melakukan request, membutuhkan attribute tambahan yang terletak pada Header HTTP Request sebagai berikut:
|Field| Value|Required |  Keterangan|
|--|--|--|--|
| Accept |*application/json*  |Ya|Permintaan berupa JSON type|
|Authorization|Bearer **{{access_token}}**|Ya| Access token yang didapat dari login.|
|S-Frontend|(integer)|No|Kode frontend aplikasi|
|S-Company|(uuid)|No|Kode Company yang dipilih user|

Setelah berhasil terauthentikasi, Anda dapat melakukan pemanggilan:

 - Informasi User Authenticated
 - Informasi User PI (ex:Pupuk Indonesia)
 - Informasi Service saat ini
 - Informasi Daftar Company User
 - Informasi Company yang dipilih user (jika ada)
 - Informasi Frontend Request
 - Informasi Role Pengguna

Seluruh function authentikasi, diembed kedalam ***$request->_auth*** .

### Informasi User Terauthentikasi
Menampilkan informasi user yang login saat ini.

    $user = $request->_auth->user;
Akan mengembalikan object json yang berisi
```json
{
    "id": 1,
    "name": "Febrian Reza Update",
    "email": "Febrianrz@gmail.com",
    "unique_key": "ALT-03 update",
    "position": "Developer",
    "position_2": "Posisi 2",
    "photo_url": "https://dev-apis.alterindonesia.com/api/files/public/storage/files/2020-10-08/Depj3F3OAtwEDUvnYwJR1QwN8obNAqV0yCArfeV2.png",
    "bio": "Programmer Alter Indonesia",
    "address": "Tangerang",
    "phone": null,
    "last_login_at": "2019-01-01 00:00:00"
}
```
### Informasi User PI
Menampilkan informasi user / pegawai PI.

    $pi = $request->_auth->pi;
Akan mengembalikan object json yang berisi
```json
not implemented
```
### Informasi Service
Menampilkan informasi Service saat ini.

    $service = $request->_auth->service;
Akan mengembalikan object json yang berisi
```json
{
    "id": "7a010280-0f46-11eb-abb5-991e14fe1e7a",
    "name": "Tes Boilerplate",
    "description": null,
    "owner": {
        "id": 1,
        "email": "Febrianrz@gmail.com",
        "phone": null,
        "name": null,
        "photo_url": "https://dev-apis.alterindonesia.com/api/files/public/storage/files/2020-10-08/Depj3F3OAtwEDUvnYwJR1QwN8obNAqV0yCArfeV2.png"
    }
}
```

### Informasi Daftar Company User
Menampilkan informasi Company yang dimiliki user.

    $companies = $request->_auth->companies;
Akan mengembalikan object json yang berisi
```json
[
    {
        "id": "6b401750-0f47-11eb-ab60-8b868a457bb7",
        "code": "ALT",
        "initial": "ALT",
        "name": "CV. Alter Indonesia"
    },
    {
        "id": "77d377c0-0f47-11eb-a7cb-fb6df3c0ac23",
        "code": "ALT2",
        "initial": "ALT2",
        "name": "PT. Alterindonesia"
    }
]
```

### Informasi Company User
Karena setiap user dapat memiliki lebih dari 1 company, maka user dapat memilih company yang digunakannya yang dilemparkan pada header ***S-Company***

    $company = $request->_auth->company;
Akan mengembalikan object json yang berisi
```json
{
    "id": "77d377c0-0f47-11eb-a7cb-fb6df3c0ac23",
    "code": "ALT2",
    "initial": "ALT2",
    "name": "PT. Alterindonesia"
}
```
### Informasi Frontend
Untuk mendapatkan role pengguna, maka Frontend harus melemparkan parameter ***S-Frontend*** pada header, berikut ini menampilkan detail frontend yang request.

    $fe = $request->_auth->frontend;
Berikut adalah hasilnya
```json
{
    "id": 12,
    "name": "Dev Server Alter",
    "icon_url": "https://satuakun.id/images/app-icon.png"
}
```
### Informasi Role Pengguna
Setelah menerapkan header ***S-Frontend***, maka service akan mendapatkan informasi terkait Role Pengguna aplikasi:

    $role = $request->_auth->role;

Berikut adalah hasilnya
```json
{
    "id": 14,
    "name": "Superadmin",
    "menu_json": null,
    "company": null
}
```
### Informasi Scope
Scope merupakan metode Permission pada microservice ini, untuk menampilkan seluruh scope, maka perintahnya adalah:

    $role = $request->_auth->scope;

Berikut adalah hasilnya
```json
[
    "list-user",
    "edit-user",
    "config-user"
]
```
### Menampilkan seluruh response
Untuk menampilkan seluruh response dari SSO, maka perintah yang digunakan adalah:
```json
{
    "user": {
        "id": 1,
        "name": "Febrian Reza Update",
        "email": "Febrianrz@gmail.com",
        "unique_key": "ALT-03 update",
        "position": "Developer",
        "position_2": "Posisi 2",
        "photo_url": "https://dev-apis.alterindonesia.com/api/files/public/storage/files/2020-10-08/Depj3F3OAtwEDUvnYwJR1QwN8obNAqV0yCArfeV2.png",
        "bio": "Programmer Alter Indonesia",
        "address": "Tangerang",
        "phone": null,
        "last_login_at": "2019-01-01 00:00:00"
    },
    "pi": null,
    "service": {
        "id": "7a010280-0f46-11eb-abb5-991e14fe1e7a",
        "name": "Tes Boilerplate",
        "description": null,
        "owner": {
            "id": 1,
            "email": "Febrianrz@gmail.com",
            "phone": null,
            "name": null,
            "photo_url": "https://dev-apis.alterindonesia.com/api/files/public/storage/files/2020-10-08/Depj3F3OAtwEDUvnYwJR1QwN8obNAqV0yCArfeV2.png"
        }
    },
    "role": {
        "id": 14,
        "name": "Superadmin",
        "menu_json": null,
        "company": null
    },
    "frontend": {
        "id": 12,
        "name": "Admintel Dev Server Alter",
        "icon_url": "https://satuakun.id/images/app-icon.png"
    },
    "scopes": [],
    "companies": [
        {
            "id": "6b401750-0f47-11eb-ab60-8b868a457bb7",
            "code": "ALT",
            "initial": "ALT",
            "name": "CV. Alter Indonesia"
        },
        {
            "id": "77d377c0-0f47-11eb-a7cb-fb6df3c0ac23",
            "code": "ALT2",
            "initial": "ALT2",
            "name": "PT. Alterindonesia"
        }
    ],
    "company": {
        "id": "77d377c0-0f47-11eb-a7cb-fb6df3c0ac23",
        "code": "ALT2",
        "initial": "ALT2",
        "name": "PT. Alterindonesia"
    },
    "timestamp": 1602809619,
    "sign": "2ad60bb3a900b1b3ed22274b54046ed9ec8374fd4d147f99ed4ef7dbfb879ac3"
}
```

## Permission
Microservice ini memungkinkan pengaturan permission. Pendekatan Permission yang digunakan adalah ***Role-Scope***.

 - Role Merupakan informasi Jabatan Pengguna yang diterima dari SSO denga  perintah ***$request->_auth->role->name***.
 - Scope adalah daftar array yang terletak pada ***App\Altcore\scopes\namafile.php***. Pembuatan nama file Scope sama dengan nama role dengan skema Snake String, contoh: Role Name: **Staff Perusahaan**, maka nama file Scopenya adalah: **staff_perusahaan.php** dan berisi .
 ```php
return [
	'User-index',
	'User-store',
	'User-update',
	'User-destroy'
];
 ```
 Disarankan untuk prefixnya menggunakan nama Model dan subfixinya menggunakan namafunction contoh: **User-store**.
 
 Untuk melakukan pengecekkan hak aksesnya valid atau tidak, dapat menggunakan perintah:
 

    /** Mengembalikan True False **/
    $request->_check->can('User-index');
    /** atau **/
    request()->_check->can('User-index');
    
Untuk dapat langsung mengembalikan response 401 Permission Denied, maka gunakan perintah:

    /** mengembalikan response 401 Permission Denied kepada user. **/
    $request->_check->authorize('User-index');
    /** atau **/
    request()->_check->authorize('User-index');

Untuk mengecek apakah user ini adalah superadmin, dapat menggunakan perintah:

    /** return true false **/
    request()->_check->isSuperadmin();


## Make API
Adalah API Rest Generator yang dikembangkan untuk mempercepat pembuatan REST API. Make API yang ada disini, merupakan pengembangan dari https://github.com/febrianrz/makeapi versi sebelumnya. Dengan melakukan perintah:

    $ php artisan make:api CompanyUser
maka akan membuat beberapa file, yaitu:

 - Controller: **App\Http\Controllers\Api\CompanyUserController.php**
 - Model: **App\CompanyUser.php**
 - Policy: **App\Policies\CompanyUserPolicy.php**
 - Request: **App\Http\Request\CompanyUserRequest.php**
 - Resource: **App\Http\Resource\CompanyUserResource.php**
 - Migration: **database/migrations/create_company_users_table.php**
 - Seeder: **database/seeders/CompanyUserSeeder.php**
 - Factory:**database/factories/CompanyUserFactory.php**
 - Unit Testing: **tests/Feature/Api/CompanyUserTest.php**

dan otomatis membuatkan Routing pada ***routes/api.php*** pada bagian ***/ * make:api New Route * /***

Untuk dokumentasi lengkapnya, silahkan lihat di https://github.com/febrianrz/makeapi


### Premium Partners

 

-  **[Alter Indonesia](https://alterindonesia.com/)**

-  **[Satuakun.id](https://satuakun.id)**

## Contributing

 Untuk mencoba microservice Laravel ini dan ikut berkontribusi dalam pengembangannya, silahkan email ke febrianrz@alterindonesia.com.

## Packagist
Mohon maaf, saat ini belum tersedia packagistnya, karena belum sempat deploy kesana, terima kasih.

 
## License

  

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

